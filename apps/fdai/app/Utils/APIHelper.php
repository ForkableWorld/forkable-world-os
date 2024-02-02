<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Computers\ThisComputer;
use App\Exceptions\RateLimitConnectorException;
use App\Logging\QMLog;
use App\Slim\Model\QMUnit;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Types\ObjectHelper;
use App\Variables\QMVariableCategory;
use Illuminate\Database\Query\Builder;
use LogicException;
use stdClass;
/** Class APIHelper
 * @package App\Slim\Model
 */
class APIHelper {
	public const HEADER_HTTP_USER_AGENT = 'HTTP_USER_AGENT';
	public const HEADER_X_Newrelic_Synthetics = 'X-Newrelic-Synthetics';
	/**
	 * Set Pagination Headers
	 * @param Builder $qb
	 * @param string $countColumn
	 * @return void
	 * @internal param Builder $qb
	 * @internal param string $countColumn
	 */
	public static function setPaginationHeaders(Builder $qb, string $countColumn = 'id'){
		$app = QMSlim::getInstance();
		if($app && (get_class($app) === QMSlim::class)){
			$previousColumns = $qb->columns;
			$qb->columns = [$countColumn];
			$totalNumberOfRecords = $qb->count();
			$qb->columns = $previousColumns;
			$params = $app->params();
			$routePath = $app->request()->getResourceUri();
			$headers = self::preparePaginationHeaders($totalNumberOfRecords, $params, $routePath);
			foreach($headers as $singleHeader => $value){
				$app->response()->header($singleHeader, $value);
			}
		}
	}
	/**
	 * Prepares Pagination headers array based on parameters
	 * @param int $totalNumberOfRecords
	 * @param array $params
	 * @param string $routePath
	 * @return array
	 * @internal param int $totalCount
	 */
	public static function preparePaginationHeaders(int $totalNumberOfRecords, array $params, string $routePath): array{
		$headers = [];
		$headers['Total-Count'] = $totalNumberOfRecords;
		if(isset($params['offset'])){
			$offset = $params['offset'];
			unset($params['offset']);
		} else{
			$offset = 0;
		}
		$limit = $params['limit'] ?? 100;
		if($offset > 0){
			$headers['Link-First'] = UrlHelper::buildApiUrl($routePath, $params);
		}
		if($totalNumberOfRecords > ($offset + $limit)){
			$params['offset'] = $totalNumberOfRecords - $limit;
			$headers['Link-Last'] = UrlHelper::buildApiUrl($routePath, $params);
		}
		if($offset > 0){
			$prevOffset = $offset - $limit;
			if($prevOffset > 0){
				$params['offset'] = $prevOffset;
			} else{
				unset($params['offset']);
			}
			$headers['Link-Prev'] = UrlHelper::buildApiUrl($routePath, $params);
		}
		if(($limit + $offset) < $totalNumberOfRecords){
			$nextOffset = $offset + $limit;
			$params['offset'] = $nextOffset;
			$headers['Link-Next'] = UrlHelper::buildApiUrl($routePath, $params);
		}
		return $headers;
	}
	// Method: POST, PUT, GET etc
	// Data: array("param" => "value") ==> index.php?param=value
	/**
	 * @param string $method
	 * @param string $url
	 * @param array|string $data
	 * @param string|null $bearerAccessToken
	 * @param string|null $userAgent
	 * @return mixed
	 * @throws RateLimitConnectorException
	 */
	public static function callAPI(string $method, string $url, $data = null, string $bearerAccessToken = null,
		string $userAgent = null){
		$url = str_replace('https://localhost', 'http://localhost', $url);
		$curl = curl_init();
		if($data && is_string($data)){
			$data = json_decode($data);
		}
		switch($method) {
			case "POST":
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				if($data){
					$jsonEncoded = json_encode($data);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonEncoded);
				}
				break;
			case "PUT":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
				if($data){
					curl_setopt($curl, CURLOPT_HTTPHEADER, [
						"Content-type: application/json;charset=UTF-8",
						'Content-Length: ' . strlen(json_encode($data)),
					]);
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			case "DELETE":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				if($data){
					curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
				}
				break;
			default:
				if(isset($data['referrer'])){
					curl_setopt($curl, CURLOPT_REFERER, $data['referrer']);
				}
				if($data){
					$url = sprintf("%s?%s", $url, http_build_query($data));
				}
		}
		if($userAgent){
			curl_setopt($curl, CURLOPT_HTTPHEADER, ["User-Agent: mikepsinn"]);
		}
		if($bearerAccessToken){
			curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer $bearerAccessToken"]);
		}
		// Optional Authentication:
		//curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		//curl_setopt($curl, CURLOPT_USERPWD, "username:password");
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		QMLog::info("Making $method request to $url...");
		$result = curl_exec($curl);
		//$info = curl_getinfo($curl);
		QMLog::info($url . ' request result: ' . substr($result, 0, 20) . '...', [
			'data' => $data,
			'result' => $result,
		]);
		if($result === ""){
			$message = "Empty string response from $method $url";
			QMLog::errorOrInfoIfTesting($message);
			$result = new stdClass();
			$result->error = $message;
			curl_close($curl);
			return $result;
		}
		if(!json_decode($result)){ //API response is not json
			$curlError = curl_error($curl);
			if(!empty($curlError)){
				QMLog::error($curlError, [
					'url' => $url,
					'data' => $data,
					'curl error' => curl_error($curl),
				], $url);
			}
			curl_close($curl);
			return $result;
		}
		curl_close($curl);
		$response = json_decode($result);
		if(!empty($response->error)){
			le("Could not $method $url because: ", $response);
		}
		if(stripos(\App\Logging\QMLog::print_r($response, true), "limit exceeded") !== false){
			throw new RateLimitConnectorException($url, $response);
		}
		return $response;
	}
	/**
	 * @param string $url
	 * @return mixed
	 */
	public static function getModifiedTimeOfRemoteFile(string $url): bool{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_NOBODY, true); // Only header
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Do not print anything to output
		curl_setopt($curl, CURLOPT_FILETIME, true);  // get last modified date
		$result = curl_exec($curl);
		$info = curl_getinfo($curl);
		$time = $info['filetime'];
		if($time == -1){
			return false;
		}
		return $time;
	}
	/**
	 * @param string $url
	 * @param array $data
	 * @param string|null $bearerAccessToken
	 * @return array|stdClass
	 */
	public static function makePostRequest(string $url, array|string $data, string $bearerAccessToken = null){
		try {
			return self::callAPI('POST', $url, $data, $bearerAccessToken, null);
		} catch (RateLimitConnectorException $e) {
			/** @var LogicException $e */
			throw $e;
		}
	}
	/**
	 * @param $url
	 * @param bool $data
	 * @param null $bearerAccessToken
	 * @return array|stdClass
	 * @throws RateLimitConnectorException
	 */
	public static function makePutRequest($url, bool $data, $bearerAccessToken = null){
		return self::callAPI('PUT', $url, $data, $bearerAccessToken);
	}
	/**
	 * @param $url
	 * @param array|null $opts
	 * @param null $bearerAccessToken
	 * @return mixed
	 * @throws RateLimitConnectorException
	 */
	public static function getRequest($url, array $opts = null, $bearerAccessToken = null){
		return self::callAPI('GET', $url, $opts, $bearerAccessToken);
	}
	/**
	 * @param string $url
	 * @param null $bearerAccessToken
	 * @return mixed
	 * @throws RateLimitConnectorException
	 */
	public static function makeDeleteRequest(string $url, $bearerAccessToken = null){
		return self::callAPI('DELETE', $url, null, $bearerAccessToken);
	}
	/**
	 * @return array
	 */
	public static function getGlobalLegacyRequestParams(): array{
		// legacy => current
		return [
			'cause' => 'causeVariableName',
			'effect' => 'effectVariableName',
		];
	}
	/**
	 * @return mixed
	 */
	public static function getRequestMethod(): ?string{
		return $_SERVER['REQUEST_METHOD'] ?? null;
	}
	/**
	 * @param [] $array
	 * @param $legacyKeyMap
	 * @return array
	 */
	public static function updateSortFieldName($array, $legacyKeyMap): array{
		if(!isset($array['sort'])){
			return $array;
		}
		if(!$legacyKeyMap){
			$legacyKeyMap = ObjectHelper::getGlobalLegacyProperties();
		}
		foreach($legacyKeyMap as $legacyKey => $currentKey){
			if($array['sort'] === $legacyKey){
				$array['sort'] = $currentKey;
			}
			if($array['sort'] === '-' . $legacyKey){
				$array['sort'] = '-' . $currentKey;
			}
		}
		return $array;
	}
	/**
	 * @return float
	 */
	public static function getRequestDurationInSeconds(): float{
		if(!Memory::get(Memory::REQUEST_START_MICRO_TIME, Memory::MISCELLANEOUS)){
			return 0;
		}
		$duration = microtime(true) - Memory::get(Memory::REQUEST_START_MICRO_TIME, Memory::MISCELLANEOUS);
		if($duration < 0){
			QMLog::error('Request duration cannot be less than 0!', ['duration' => $duration]);
		}
		return $duration;
	}
	public static function getRequestTime(): int{
		return self::getRequestDurationInSeconds();
	}
	/**
	 * @param int $bufferInSeconds
	 * @return bool
	 */
	public static function timeLimitExceeded(int $bufferInSeconds = 3): bool{
		if(!AppMode::isApiRequest()){
			return false;
		}
		$timeLimit = QMRequest::getApiRequestTimeLimit();
		$elapsed = self::getRequestDurationInSeconds();
		$timeLimitMinusBuffer = $timeLimit - $bufferInSeconds;
		$timeLimitExceeded = $timeLimitMinusBuffer < $elapsed;
		if($timeLimitExceeded){
			QMLog::error("Request time ($elapsed seconds) exceeded time limit minus buffer ($timeLimitMinusBuffer seconds)");
			return true;
		}
		return false;
	}
	public static function setRequestStartMicroTime(){
		Memory::set(Memory::REQUEST_START_MICRO_TIME, microtime(true));
	}
	/**
	 * @return int
	 */
	public static function getApiVersion(): int{
		if(isset($_SERVER['REQUEST_URI'])){
			$path = $_SERVER['REQUEST_URI'];
		} else{
			return 4;
		}
		if(strpos($path, '/api/v4/') === 0){
			return 4;
		}
		if(strpos($path, '/api/v3/') === 0){
			return 3;
		}
		if(strpos($path, '/api/v2/') === 0){
			return 2;
		}
		if(strpos($path, '/api/v1/') === 0){
			return 1;
		}
		return 0;
	}
	/**
	 * @param int $versionNumber
	 * @return bool
	 */
	public static function isApiVersion(int $versionNumber): bool{
		return self::getApiVersion() === $versionNumber;
	}
	/**
	 * @param $versionNumber
	 * @return bool
	 */
	public static function apiVersionIsBelow($versionNumber): bool{
		if(!AppMode::isApiRequest()){
			return false;
		}
		return self::getApiVersion() < $versionNumber;
	}
	/**
	 * @param $versionNumber
	 * @return bool
	 */
	public static function apiVersionIsAbove($versionNumber): bool{
		if(!AppMode::isApiRequest()){
			return true;
		}
		return self::getApiVersion() > $versionNumber;
	}
	/**
	 * @param array|object $array
	 * @return array
	 */
	public static function replaceNamesWithIdsInArray($array): array{
		$array = (array)$array;
		$array = QMUnit::replaceUnitNamesWithUnitIds($array);
		return QMVariableCategory::replaceVariableCategoryNameWithIdInArray($array);
	}
	//public static function getAuthCookieName(){ return 'wordpress_sec_' . self::getCookieHash(); }
	// Function to get the client IP address
	/**
	 * @return array
	 */
	public static function getLegacyParametersForPath(): array{
		$currentPath = QMRequest::requestUri();
		if(!$currentPath){
			return [];
		}
		switch($currentPath) {
			case "/api/v3/pairs":
				return [
					'timestamp' => 'eventAtUnixTime',
					'startTimeString' => 'eventAt',
					'causeMeasurement' => 'causeMeasurementValue',
					'effectMeasurement' => 'effectMeasurementValue',
				];
			default:
				return [];
		}
	}
	/**
	 * @param string $url
	 * @param $data
	 * @param string $accessToken
	 * Sometimes this works when PHPCurl and Guzzle return 415 errors
	 * @return string
	 */
	public static function postWithCliCurl(string $url, $data, string $accessToken): string{
		$json = json_encode($data);
		$json = addcslashes($json, '"');
		// Not sure why PHPCurl and Guzzle return 415 errors
		$res = ThisComputer::exec('curl -X POST --header "Authorization: " --header "Authorization: Bearer ' .
			$accessToken . '" --header "Content-Type: application/json" -d "' . $json . '" "' . $url . '"');
		if(stripos($res, "You don't have permission to access this resource.") !== false){
			le($res);
		}
		return $res;
	}
	public static function post_without_wait(string $url, array $params){
		foreach($params as $key => &$val){
			if(is_array($val)) $val = implode(',', $val);
			$post_params[] = $key . '=' . urlencode($val);
		}
		$post_string = implode('&', $post_params);
		$parts = parse_url($url);
		$fp = fsockopen($parts['host'], $parts['port'] ?? 80, $errno, $errstr, 30);
		$out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
		$out .= "Host: " . $parts['host'] . "\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-Length: " . strlen($post_string) . "\r\n";
		$out .= "Connection: Close\r\n\r\n";
		if(isset($post_string)) $out .= $post_string;
		fwrite($fp, $out);
		fclose($fp);
	}
}
