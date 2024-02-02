<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\RequestRateExceededException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Storage\MemoryOrRedisCache;
class RequestRateLimiter {
	private $prefix;
	/**
	 * RequestRateLimiter constructor.
	 * @param $ip
	 * @param string $prefix
	 */
	public function __construct($ip, string $prefix = "rate"){
		$this->prefix = $prefix . $ip;
	}
	/**
	 * @param $allowedRequests
	 * @param $periodInMinutes
	 * @return void
	 * @throws RequestRateExceededException
	 */
	public function limitRequestsInMinutes($allowedRequests, $periodInMinutes): void{
		$requestsOverPeriod = 1;
		$requestCounts = [];
		$currentMinute = date("i", time());
		$currentKey = $this->prefix . '_' . $currentMinute;
		$keys = $this->getKeys($periodInMinutes);
		foreach($keys as $key){
			$requestCounts[$key] = (int)MemoryOrRedisCache::get($key);
		}
		foreach($requestCounts as $requestCount){
			$requestsOverPeriod += $requestCount;
		}
		$incrementBy = 1;
		$storedValue = MemoryOrRedisCache::increment($currentKey, $incrementBy, $periodInMinutes * 60);
		//QMLog::debug('Request counts', ['Current Minute' => $storedValue, 'requestsOverPeriod' => $requestsOverPeriod, 'requestCounts' => $requestCounts]);
		if($requestsOverPeriod > $allowedRequests){
			QMLog::error('Rate limit exceeded ' . $allowedRequests . ' requests in last ' . $periodInMinutes .
				' minutes', [
				'requests in current minute' => $storedValue,
				'requestsOverPeriod' => $requestsOverPeriod,
				'requestCounts' => $requestCounts,
			]);
			throw new RequestRateExceededException;
		}
	}
	/**
	 * @param $minutes
	 * @return array
	 */
	private function getKeys($minutes): array{
		$keys = [];
		$currentMinute = date("i", time());
		for($i = 0; $i < $minutes; $i++){
			$keys[] = $this->prefix . '_' . $currentMinute;
			$currentMinute--;
			if($currentMinute < 0){
				$currentMinute += 60;
			}
		}
		return $keys;
	}
	/**
	 * @param int $allowedRequests
	 * @param int $periodInMinutes
	 * @throws RequestRateExceededException
	 */
	public static function rateLimit(int $allowedRequests = 500, int $periodInMinutes = 5){
		if(!Env::get('RATE_LIMIT_ENABLED')){
			return; // Let's handle rate limiting with cloudflare or something else
		}
		$uri = strtok($_SERVER["REQUEST_URI"], '?');
		$ip = IPHelper::getClientIp();
		if(!$ip){
			ConsoleLog::error("No IP address!");
			return;
		}
		$limiter = new RequestRateLimiter($ip . '_' . $uri);
		$limiter->limitRequestsInMinutes($allowedRequests, $periodInMinutes);
	}
}
