<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use Illuminate\Cache\RateLimiter;
trait HasRateLimit {
	protected $decayMinutes; // Make sure to set this in your class
	protected $maxAttempts = 1; // Make sure to set this in your class
	protected $rateLimitKeySuffix = 'send_analytics'; // Make sure to set this in your class
	public function exceptionIfTooSoon($uniqueKey){
		$key = $uniqueKey . ':' . $this->rateLimitKeySuffix;
		if($this->tooManyAttempts($key)){
			$availableAt = now()->addSeconds($this->availableIn($key))->ago();
			throw new \RuntimeException('Try again ' . $availableAt);
		}
		$this->hit($key);
	}
	/**
	 * Increment the counter for a given key for a given decay time.
	 * @param string $key
	 * @return int
	 */
	private function hit($key){
		$limiter = $this->getRateLimiter();
		return $limiter->hit($key, $this->decayMinutes * 60);
	}
	/**
	 * @return RateLimiter
	 */
	private function getRateLimiter(): RateLimiter{
		/** @var RateLimiter $limiter */
		$limiter = app(RateLimiter::class);
		return $limiter;
	}
	/**
	 * Get the number of attempts for the given key.
	 * @param string $key
	 * @return mixed
	 */
	public function attempts($key){
		$limiter = $this->getRateLimiter();
		return $limiter->attempts($key);
	}
	/**
	 * Get the number of seconds until the "key" is accessible again.
	 * @param string $key
	 * @return int
	 */
	public function availableIn($key){
		$limiter = $this->getRateLimiter();
		return $limiter->availableIn($key);
	}
	/**
	 * Determine if the given key has been "accessed" too many times.
	 * @param string $key
	 * @param int $maxAttempts
	 * @return bool
	 */
	public function tooManyAttempts($key){
		$limiter = $this->getRateLimiter();
		return $limiter->tooManyAttempts($key, $this->maxAttempts);
	}
}
