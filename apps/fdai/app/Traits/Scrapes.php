<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\DataSources\LusitanianGuzzleClient;
use App\DataSources\QMConnector;
use App\DataSources\QMConnectorResponse;
use App\DevOps\XDebug;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\RateLimitConnectorException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Repos\ResponsesRepo;
use App\Slim\Controller\Connector\ConnectorException;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Exception;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Http\Uri\UriInterface;
use Psr\Http\Message\ResponseInterface;
trait Scrapes {
	protected $cookieJar;
	protected $guzzleRequests = [];
	protected $httpClient;
	protected $requestContainer = [];
	protected $currentUrl;
	protected $paths;
	protected $currentPath;
	private $lastResponse;
	/**
	 * @param string $path
	 * @param array $query
	 * @param array $pathParams
	 * @return string
	 */
	public function getUrlForPath(string $path, array $query = [], array $pathParams = []): string{
		$url = $path;
		if(stripos($path, "http") === false){
			if(!empty($path) && strpos($path, '/') !== 0){
				$path = "/$path";
			}
			$baseUrl = $this->getBaseApiUrl();
			$baseUrl = QMStr::removeIfLastCharacter("/", $baseUrl);
			$url = $baseUrl . $path;
		}
		$url = UrlHelper::addParams($url, $query);
		$url = UrlHelper::addPathParams($url, $pathParams);
		$this->setCurrentUrl($url);
		$this->logUrl();
		return $url;
	}
	/**
	 * @param string $url
	 * @return QMConnectorResponse
	 */
	protected function getLocalDataIfPossible(string $url): ?QMConnectorResponse{
		if(!$this->useFileResponsesInTesting){
			return null;
		}
		if(!$this->userId){
			return null;
		}
		if(!ResponsesRepo::shouldRequest($url, $this->getResponseType(), $this->getUserId())){
			return null;
		}
		if(AppMode::isUnitOrStagingUnitTest()){
			$data = ResponsesRepo::getResponse($url, $this->getResponseType(), $this->getUserId());
			if(!$data){
				return null;
			}
			if($data === "{}"){
				return null;
			}
			return new QMConnectorResponse($data, 200, [], $this);
		}
		return null;
	}
	/**
	 * @param string $url
	 * @return string
	 * @throws InvalidFilePathException
	 */
	public function getResponseFilePath(string $url): string{
		return ResponsesRepo::urlToPath($url, $this->getResponseType(), $this->getUserId());
	}
	/**
	 * @param string $html
	 * @param array $headers
	 * @param array $options
	 * @return string
	 */
	private function relativizeAndDownloadResources(string $html, array $headers, array $options): string{
		$html = str_replace('src="https://', 'src="https://', $html);
		$cssPaths = HtmlHelper::extractLocalCssTags($html);
		foreach($cssPaths as $rel){
			$html = $this->relativizeAndDownload($rel, $html, $headers, $options);
		}
		$jsPaths = HtmlHelper::extractLocalJsTags($html);
		foreach($jsPaths as $rel){
			$html = $this->relativizeAndDownload($rel, $html, $headers, $options);
		}
		$imagePaths = HtmlHelper::extractLocalImageTags($html);
		foreach($imagePaths as $rel){
			$html = $this->relativizeAndDownload($rel, $html, $headers, $options);
		}
		return $html;
	}
	/**
	 * @param string $relative
	 * @param array $headers
	 * @param array $options
	 * @param string $filePath
	 */
	private function downloadFile(string $relative, array $headers, array $options, string $filePath): void{
		$uri = $this->getOrigin() . $relative;
		$contents = $this->getRequest($uri, $options, $headers);
		ResponsesRepo::writeToFile($filePath, $contents);
	}
	/**
	 * @param string $htmlPath
	 * @param string $html
	 * @param string $relativeFilePath
	 * @return array|string|string[]
	 */
	private function relativizeJsOrCssPath(string $htmlPath, string $html, string $relativeFilePath): string{
		$host = $this->getHostName();
		$relativeFilePathWithoutHost = str_replace($host . "/", "", $relativeFilePath);
		$url = $this->getPathWithoutQuery();
		$depth = count(explode('/', $url)) - 2;
		$prefix = str_repeat("../", $depth);
		$html = str_replace($htmlPath, $prefix . $relativeFilePathWithoutHost, $html);
		return $html;
	}
	public function getOrigin(): string{
		return UrlHelper::origin($this->getCurrentUrl());
	}
	public function getHostName(): string{
		return UrlHelper::getHostName($this->getCurrentUrl());
	}
	public function getCurrentPath(): string{
		$url = $this->getCurrentUrl();
		$origin = $this->getOrigin();
		$path = str_replace($origin, '', $url);
		return $path;
	}
	public function getPathWithoutQuery(): string{
		$path = $this->getCurrentPath();
		if(!$path){
			le("No path for " . __METHOD__);
		}
		return QMStr::before("?", $path, $path);
	}
	/**
	 * @param string $relative
	 * @param string $html
	 * @param array $headers
	 * @param array $options
	 * @return array|string|string[]
	 */
	private function relativizeAndDownload(string $relative, string $html, array $headers, array $options){
		try {
			$filePath = $this->getHostName() . FileHelper::sanitizeFilePath($relative);
		} catch (InvalidFilePathException $e) {
			le($e);
		}
		$html = $this->relativizeJsOrCssPath($relative, $html, $filePath);
		if(!ResponsesRepo::fileExists($filePath)){
			try {
				$this->downloadFile($relative, $headers, $options, $filePath);
			} catch (ClientErrorResponseException $e) {
				QMLog::error("Could not download $relative because: " . $e->getMessage());
			}
		}
		return $html;
	}
	/**
	 * @return CookieJar
	 */
	protected function getCookieJar(): CookieJar{
		if($this->cookieJar){
			return $this->cookieJar;
		}
		$this->cookieJar = new CookieJar;  // This'll hold the cookies for this session
		return $this->cookieJar;
	}
	/**
	 * @return SetCookie[]
	 */
	protected function getCookies(): array{
		/** @var SetCookie[] $cookies */
		try {
			$cookies = $this->getCookieJar()->getIterator();
		} catch (Exception $e) {
			le($e);
		}
		return $cookies;
	}
	/**
	 * @param array $config
	 * @return LusitanianGuzzleClient
	 * https://docs.guzzlephp.org/en/stable/request-options.html#headers
	 */
	protected function getHttpClient(array $config = []): LusitanianGuzzleClient{
		if($this->httpClient){
			return $this->httpClient;
		}
		return $this->httpClient = $this->newGuzzle6Client($config);
	}
	/**
	 * @param array $config
	 * @return LusitanianGuzzleClient http://docs.guzzlephp.org/en/stable/index.html
	 * http://docs.guzzlephp.org/en/stable/index.html
	 */
	protected function newGuzzle6Client(array $config = []): LusitanianGuzzleClient{
		$config['base_uri'] = $this->getBaseApiUrl(); // See base_uri at http://docs.guzzlephp.org/en/stable/quickstart
		//.html#creating-a-client
		$config['cookies'] = $this->getCookieJar();
		$c = new LusitanianGuzzleClient($config, $this);
		return $c;
	}
	/**
	 * @return ResponseInterface
	 */
	protected function getLastResponse(): ResponseInterface{
		return $this->getHttpClient()->getLastResponse();
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @param array $extraHeaders
	 * @return array|object
	 * @throws ConnectorException
	 */
	public function getRequest(string $path, array $params = [], array $extraHeaders = []){
		$url = $this->getUrlForPath($path, $params);
		try {
			$r = $this->request($url, "GET", [], $extraHeaders);
		} catch (ClientErrorResponseException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			$r = $this->retryWithCURL($e, $url, $extraHeaders, []);
		}
		if(is_string($r)){
			$content = $r;
		} else {
			$content = $r->getContent();
		}
		if($this instanceof QMConnector){
			$this->saveConnectorRequestResponse($url, $content, $r->headers->all(), 'GET', $r->getStatusCode());
		}
		return $r;
	}
	/**
	 * Sends an authenticated API request to the path provided.
	 * If the path provided is not an absolute URI, the base API Uri (must be passed into constructor) will be used.
	 * @param string|UriInterface $path
	 * @param string $method HTTP method
	 * @param null $body Request body if applicable.
	 * @param array $extraHeaders Extra headers if applicable. These will override service-specific
	 *                                          any defaults.
	 * @return string
	 * @throws ConnectorException
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function request($path, $method = 'GET', $body = null, array $extraHeaders = array()){
		$uri = $this->determineRequestUriFromPath($path, new Uri($this->getBaseApiUrl()));
		$content = $this->getHttpClient()->retrieveResponse($uri, $body, $extraHeaders, $method);
		return $content;
	}
	/**
	 * @param UriInterface|string $path
	 * @param UriInterface|null $baseApiUri
	 * @return UriInterface
	 */
	protected function determineRequestUriFromPath($path, UriInterface $baseApiUri = null): UriInterface {
		if($path instanceof UriInterface){
			$uri = $path;
		} elseif(str_starts_with($path, 'http')){
			$uri = new Uri($path);
		} else{
			if(null === $baseApiUri){
				le('An absolute URI must be passed to ServiceInterface::request as no baseApiUri is set.');
			}
			$uri = clone $baseApiUri;
			if(str_contains($path, '?')){
				$parts = explode('?', $path, 2);
				$path = $parts[0];
				$query = $parts[1];
				$uri->setQuery($query);
			}
			if(is_array($path) && $path[0] === '/'){
				$path = substr($path, 1);
			}
            if(str_starts_with($path, '/')){
                $path = substr($path, 1);
            }
            $path = $uri->getPath() . $path;
			$uri->setPath($path);
		}
		$this->setCurrentUrl($uri->getAbsoluteUri());
		return $uri;
	}
	/**
	 * @param string $url
	 * @param $data
	 * @param array $options
	 * @return QMConnectorResponse
	 */
	protected function post(string $url, $data, array $options = []): QMConnectorResponse{
		$client = $this->getHttpClient($options);
		$response = $client->post($url, ['form_params' => $data]);
		$response = new QMConnectorResponse($response->getBody()->getContents(), $response->getStatusCode(),
			$response->getHeaders(), $this);
		return $response;
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @param array $headers
	 * @param array $options
	 * @return array
	 * @throws ConnectorException
	 */
	protected function fetchArray(string $path, array $params = [], array $headers = [], array $options = []): array{
		$r = $this->getRequest($path, $params, $headers, $options);
		return QMArr::toArray($r);
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @param array $headers
	 * @param array $options
	 * @return string
	 * @throws ConnectorException
	 */
	protected function fetchHtml(string $path, array $params = [], array $headers = [], array $options = []): string{
		$html = $this->getRequest($path, $params, $headers, $options);
		return $this->replaceHtmlRelativePathsWithAbsolute($html, $this->getCurrentUrl());
	}
	protected function sleepIfNotApi(): void{
		if(!AppMode::isApiRequest() && !XDebug::active() && !AppMode::isPHPStorm()){
			$this->sleep();
		}
	}
	public function sleep(): void{
		sleep(1);
	}
	/**
	 * @param string $url
	 * @param $body
	 * @param array $headers
	 * @param string $method
	 * @param int $code
	 */
	abstract public function saveConnectorRequestResponse(string $url, $body, array $headers = [],
		string $method = 'GET', int $code = 200): void;
	/**
	 * @param string $currentUrl
	 * @param array $params
	 * @return string
	 */
	public function setCurrentUrl(string $currentUrl, array $params = []): string{
		if($params){
			$currentUrl = UrlHelper::addParams($currentUrl, $params);
		}
		if($currentUrl === $this->currentUrl){
			return $currentUrl;
		}
//		if($this->currentUrl){
//			QMLog::logEndOfProcess(str_replace($this->getBaseApiUrl(), "", $this->currentUrl));
//		}
//		QMLog::logStartOfProcess(str_replace($this->getBaseApiUrl(), "", $currentUrl));
		return $this->currentUrl = $currentUrl;
	}
	/**
	 * @return string
	 */
	public function getCurrentUrl(): string{
		if(!$this->currentUrl){
			le('!$this->currentUrl');
		}
		return $this->currentUrl;
	}
	/**
	 * @return int
	 */
	public function getLastStatusCode(): int{
		return $this->getLastResponse()->getStatusCode();
	}
	/**
	 * @return string
	 */
	abstract public function getResponseType(): string;
	public function logUrl(): void{
		$this->logInfo(__FUNCTION__ . ":\n\t" . UrlHelper::stripAPIKeysFromURL($this->getCurrentUrl()));
	}
	/**
	 * @param QMConnectorResponse $response
	 * @param string $url
	 */
	protected function setLastResponse(QMConnectorResponse $response, string $url): void{
		$this->lastResponse = $this->guzzleRequests[$url] = $response;
	}
	/**
	 * @param \Throwable $e
	 * @param string $url
	 * @param array $headers
	 * @param array $options
	 * @return null|QMConnectorResponse
	 * @throws \App\Exceptions\RateLimitConnectorException
	 */
	private function retryWithCURL(\Throwable $e, string $url, array $headers, array $options): ?QMConnectorResponse{
		$this->logInfo("Retrying $url with APIHelper because " . $e->getMessage());
		$data = $this->getLocalDataIfPossible($url);
		if(!$data){
			$this->sleepIfNotApi();
			try {
				$content = APIHelper::getRequest($url, $headers);
				$response = new QMConnectorResponse($content, 200, $headers, $this);
				if(!AppMode::isApiRequest()){
					$this->saveConnectorRequestResponse($url, $response, $headers, "GET", 200);
				}
			} catch (RateLimitConnectorException $e) {
				throw new RateLimitConnectorException($url, null, $this);
			}
		}
		return $data;
	}
	/**
	 * @param $data
	 * @param string $url
	 * @return mixed|string|string[]
	 */
	private function replaceHtmlRelativePathsWithAbsolute($data, string $url){
		if(!AppMode::isApiRequest()){
			/** @noinspection HtmlRequiredLangAttribute */
			if(is_string($data) && stripos($data, '<html>') !== false){
				$data = HtmlHelper::replaceRelativePathsWithAbsoluteUrls($url, $data);
			}
		}
		return $data;
	}

}
