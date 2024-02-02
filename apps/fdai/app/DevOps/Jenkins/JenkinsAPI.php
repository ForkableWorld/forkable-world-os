<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpUnused */
namespace App\DevOps\Jenkins;
use App\CodeGenerators\CodeGenerator;
use App\Exceptions\NotFoundException;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use RuntimeException;
use stdClass;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
class JenkinsAPI {
	// Available ENVs at http://quantimodo2.asuscomm.com:8082/env-vars.html/
	public const BRANCH_NAME = 'BRANCH_NAME';
	public const BUILD_ID = 'BUILD_ID';
	public const BUILD_URL = 'BUILD_URL';
	public const CHANGE_TITLE = 'CHANGE_TITLE';
	public const CHANGE_URL = 'CHANGE_URL';
	public const GIT_BRANCH = 'GIT_BRANCH';
	public const GIT_COMMIT_SHA_HASH = 'GIT_COMMIT';
	public const GIT_URL = 'GIT_URL';
	public const JENKINS_HOME = 'JENKINS_HOME';
	public const NODE_NAME = 'NODE_NAME';
	public const WORKSPACE = 'WORKSPACE';
	/** @var JenkinsAPI */
	public static $instance;
	/**
	 * @var string
	 */
	public static $baseUrl = Jenkins::API_BASE_URL;
	public static $jobs = [];
	public static $buildsByJob;
	/**
	 * @var stdClass
	 */
	protected static $generalResponse;
	/**
	 * Whether or not to retrieve and send anti-CSRF crumb tokens
	 * with each request
	 * Defaults to false for backwards compatibility
	 * @var boolean
	 */
	protected static $crumbsEnabled = false;
	/**
	 * The anti-CSRF crumb to use for each request
	 * Set when crumbs are enabled, by requesting a new crumb from Jenkins
	 * @var string
	 */
	protected static $crumb;
	/**
	 * The header to use for sending anti-CSRF crumbs
	 * Set when crumbs are enabled, by requesting a new crumb from Jenkins
	 * @var string
	 */
	protected static $crumbRequestField;
	/**
	 * @param string $baseUrl
	 */
	public function __construct(string $baseUrl = Jenkins::API_BASE_URL){
		self::$baseUrl = $baseUrl;
	}
	/**
	 * Enable the use of anti-CSRF crumbs on requests
	 * @return void
	 */
	public static function enableCrumbs(){
		self::$crumbsEnabled = true;
		$crumbResult = JenkinsAPI::requestCrumb();
		if(!$crumbResult){
			self::$crumbsEnabled = false;
			return;
		}
		self::$crumb = $crumbResult->crumb;
		self::$crumbRequestField = $crumbResult->crumbRequestField;
	}
	/**
	 * @return object
	 */
	public static function requestCrumb(): object{
		$url = sprintf('%s/crumbIssuer/api/json', self::$baseUrl);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl, 'Error getting csrf crumb', $url);
		$crumbResult = json_decode($ret);
		if(!$crumbResult instanceof stdClass){
			throw new RuntimeException('Error during json_decode of csrf crumb');
		}
		return $crumbResult;
	}
	/**
	 * Validate curl_error() and http_code in a cURL request
	 * @param $curl
	 * @param string $context
	 * @param string $url
	 * @return mixed
	 */
	public static function validateCurl($curl, string $context, string $url){
		if($errorNumber = curl_errno($curl)){
			$errorMessage = curl_error($curl);
			throw new RuntimeException("
                $context
                Curl error number: $errorNumber
                Curl error message: $errorMessage
                URL: $url
           ");
		}
		$info = curl_getinfo($curl);
		if($info['http_code'] === 404){
			throw new NotFoundException("Not Found!  $context");
		}
		if($info['http_code'] > 399){
			throw new RuntimeException(\App\Logging\QMLog::print_r($info, true));
		}
		return $info;
	}
	/**
	 * Disable the use of anti-CSRF crumbs on requests
	 * @return void
	 */
	public static function disableCrumbs(){
		self::$crumbsEnabled = false;
	}
	/**
	 * @return boolean
	 */
	public static function isAvailable(): bool{
		$curl = curl_init(self::$baseUrl . '/api/json');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_exec($curl);
		if(curl_errno($curl)){
			return false;
		} else{
			try {
				JenkinsQueue::getQueue();
			} catch (RuntimeException $e) {
				return false;
			}
		}
		return true;
	}
	/**
	 * @return stdClass
	 * @throws RuntimeException
	 */
	public static function getGeneralData(): stdClass{
		if(self::$generalResponse){
			return self::$generalResponse;
		}
		$url = self::$baseUrl . '/api/json';
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl, sprintf('Error during getting list of jobs on %s', self::$baseUrl), $url);
		self::$generalResponse = json_decode($ret);
		self::generateStaticModelFromResponse("JenkinsResponse", self::$generalResponse);
		if(!self::$generalResponse instanceof stdClass){
			throw new RuntimeException('Error during json_decode');
		}
		return self::$generalResponse;
	}
	/**
	 * @param string $className
	 * @param $infos
	 */
	public static function generateStaticModelFromResponse(string $className, $infos): void{
		if(!\App\Utils\Env::get('GENERATE_MODELS')){
			return;
		}
		CodeGenerator::jsonToBaseModel("App\\DevOps\\Jenkins\\Generated\\" . $className, $infos);
	}
	/**
	 * @param $parameters
	 * @param string $url
	 * @return object|int
	 */
	public static function post($parameters, string $url){
		if(stripos($url, self::$baseUrl) === false){
			$url = self::$baseUrl . '/' . $url;
		}
		QMLog::info("POST $url " . \App\Logging\QMLog::print_r($parameters, true));
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
		//curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // DOESN'T WORK FOR SOME REASON. JUST MAKE GET REQUESTS!
		$headers = [];
		if(JenkinsAPI::areCrumbsEnabled()){
			$headers[] = JenkinsAPI::getCrumbHeader();
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Do not print anything to output
		$apiResponse = curl_exec($curl);
		JenkinsAPI::validateCurl($curl, "Error from $url", $url);
		curl_close($curl);
		$data = json_decode($apiResponse);
		if(stripos($data, '<a name="skip2content"></a>') !== false){
			$data = QMStr::between($data, '<a name="skip2content"></a>', '<div>');
		}
		QMLog::info("Got $data from $url");
		return $data;
	}
	/**
	 * Get the status of anti-CSRF crumbs
	 * @return boolean Whether or not crumbs have been enabled
	 */
	public static function areCrumbsEnabled(): bool{
		return self::$crumbsEnabled;
	}
	/**
	 * @return string
	 */
	public static function getCrumbHeader(): string{
		return self::$crumbRequestField . ": " . self::$crumb;
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function getUrlByPath(string $path): string{
		if(stripos($path, '/') !== 0){
			$path = '/' . $path;
		}
		return self::$baseUrl . $path;
	}
	/**
	 * Returns the content of a page according to the jenkins base url.
	 * Useful if you use jenkins plugins that provides specific APIs.
	 * (e.g. "/cloud/ec2-us-east-1/provision")
	 * @param string $uri
	 * @param array $curlOptions
	 * @return string
	 */
	public static function curl(string $uri, array $curlOptions = [CURLOPT_RETURNTRANSFER => 1]): string{
		$url = self::$baseUrl . ((strpos($uri, "/") === 0) ? $uri : '/' . $uri);
		$curl = curl_init($url);
		curl_setopt_array($curl, $curlOptions);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl, sprintf('Error calling "%s"', $url), $url);
		return $ret;
	}
	/**
	 * @param $xmlConfiguration
	 * @param string $url
	 * @return false|resource
	 */
	public static function postXmlConfig($xmlConfiguration, string $url): bool{
		return self::postXML($url, $xmlConfiguration);
	}
	/**
	 * @param string $path
	 * @param array $xmlBody
	 * @return false|resource
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function postXML(string $path, array $xmlBody = []){
		if(stripos($path, self::$baseUrl) === false){
			$path = self::$baseUrl . "/" . $path;
		}
		$curl = curl_init($path);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlBody);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$headers = ['Content-Type: text/xml'];
		if(JenkinsAPI::areCrumbsEnabled()){
			$headers[] = JenkinsAPI::getCrumbHeader();
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		return $curl;
	}
	/**
	 * @return JenkinsAPI
	 */
	public static function getInstance(): self{
		if(self::$instance){
			return self::$instance;
		}
		return self::$instance = new static();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return UrlHelper::addParams(self::$baseUrl, $params);
	}
}
