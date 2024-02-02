<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidUrlException;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\UrlHelper;
use Guzzle\Http\Client;
use Guzzle\Http\Message\RequestInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use Tests\QMAssert;
use const CURLOPT_SSL_VERIFYPEER;
trait HasTestUrl {
	abstract public function getUrl(array $params = []);
	/**
	 * @throws BadResponseException
	 */
	public function testUrl(): bool{
		$this->logInfo("Testing $this");
		if($this->requiresAdmin()){
			$this->getTestResponse(401);
            $u = \App\Models\User::getAdminUser();
            $t = $u->getOrCreateAccessTokenString(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
			$contents =
				$this->getTestResponse(200, [BaseAccessTokenProperty::NAME => $t]);
		} elseif($this->requiresAuthentication()){
			$contents = $this->getTestResponse(200);
			$this->getTestResponse(200, [BaseAccessTokenProperty::NAME => BaseAccessTokenProperty::DEMO_TOKEN]);
		} else{
			$contents = $this->getTestResponse(200);
		}
		if(!$this->expectsHtml()){
			$this->validateJsonResponse($contents);
		} else{
			$this->validateTestUrlHtmlResponse($contents);
		}
		return true;
	}
	/**
	 * @param string $message
	 */
	private function throwBadResponseException(string $message): void{
		throw new BadResponseException($message, new Request("GET", $this->getUrl()));
	}
	public function getRequiredTestUrlResponseStrings(): array{
		return [
			"<html",
		];
	}
	public function getBlackListedTestUrlResponseStrings(): array{
		return [];
	}
	/**
	 * @param string $html
	 */
	private function validateTestUrlHtmlResponse(string $html): void{
		try {
			HtmlHelper::assertHtmlContains($this->getRequiredTestUrlResponseStrings(), $html, $this->getUrl());
		} catch (InvalidStringException $e) {
			$this->throwBadResponseException($e->getMessage());
		}
		try {
			HtmlHelper::assertHtmlDoesNotContain($this->getBlackListedTestUrlResponseStrings(), $html, $this->getUrl());
		} catch (InvalidStringException $e) {
			$this->throwBadResponseException($e->getMessage());
		}
	}
	protected function expectsHtml(): bool{
		return true;
	}
	/**
	 * @param string $json
	 */
	private function validateJsonResponse(string $json): void{
		$contents = json_decode($json);
		QMAssert::validJson($contents);
		try {
			QMStr::assertContains($json, $this->getRequiredTestUrlResponseStrings(), $this->getUrl());
		} catch (InvalidStringException $e) {
			$this->throwBadResponseException(__METHOD__.": ".$e->getMessage());
		}
		try {
			QMStr::assertDoesNotContain($json, $this->getBlackListedTestUrlResponseStrings(), $this->getUrl());
		} catch (InvalidStringException $e) {
			$this->throwBadResponseException(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param int $expectedCode
	 * @param array $params
	 * @return string
	 * @throws BadResponseException
	 */
	private function getTestResponse(int $expectedCode, array $params = []): string{
		$url = $this->getUrl($params);
		try {
			UrlHelper::validateUrl($url, static::class);
		} catch (InvalidUrlException $e) {
			le($e);
		}
		$this->logInfo("Making GET request to $url...");
		$request = $this->guzzle($url);
		try {
			$response = $request->send();
		} catch (\Throwable $e) {
			throw new BadResponseException("Could not get $url because:\n\t" . $e->getMessage(),
				new Request("GET", $this->getUrl()));
		}
		$code = $response->getStatusCode();
		$body = (string)$response->getBody();
		$loginPage = strpos($body, "Sign In") !== false || strpos($body, ' type="password" ') !== false;
		if($expectedCode === 401 && $loginPage){
			return $body;
		}
		if(200 !== $expectedCode && $loginPage){
			$this->throwBadResponseException("Expected $expectedCode from\n\t$url\n\tbut we got sent to login!\n");
		}
		if($code !== $expectedCode){
			$message = $response->getReasonPhrase();
			$this->throwBadResponseException("Expected $expectedCode from\n\t$url\n\tbut got $code!\n" .
				$message);
		}
		return $body;
	}
	public function requiresAuthentication(): bool{
		if($this->requiresAdmin()){
			return true;
		}
		return false;
	}
	public function requiresAdmin(): bool{
		return false;
	}
	/**
	 * @param string $url
	 * @return RequestInterface
	 */
	private function guzzle(string $url): RequestInterface{
		$client = new Client($url);
		$request = $client->get();
		if($this->requiresAuthentication()){
			if(stripos($url, \App\Utils\Env::getAppUrl()) !== false){
				$u = QMAuth::getUser();
				if($u){
					$request->addHeader('Authorization', "Bearer ".$u->getOrCreateAccessTokenString
						(BaseClientIdProperty::CLIENT_ID_QUANTIMODO));
				}
			}
		}
		$request->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
		$request->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
		return $request;
	}
}
