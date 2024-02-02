<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMultipleClassDeclarationsInspection */
namespace App\DevOps;
use App\Exceptions\InvalidResponseDataException;
use App\Exceptions\InvalidUrlException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Utils\IPHelper;
use App\Utils\UrlHelper;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\QMBaseTestCase;
class TestPath {
	public string $path;
	public string $expectedString;
	public string $host;
	/**
	 * TestPath constructor.
	 * @param string $path
	 * @param string $expectedString
	 */
	public function __construct(string $path, string $expectedString){
		$this->path = $path;
		$this->expectedString = $expectedString;
	}
	/**
	 * @return string
	 */
	public function getHost(): string{
		return $this->host;
	}
	/**
	 * @return string
	 */
	public function getPath(): string{
		return $this->path;
	}
	/**
	 * @param string $path
	 * @return TestPath
	 */
	public function setPath(string $path): TestPath{
		$this->path = $path;
		return $this;
	}
	/**
	 * @param string $host
	 * @return TestPath
	 */
	public function setHost(string $host): TestPath{
		$this->host = $host;
		return $this;
	}
	/**
	 * @param string $host
	 * @throws InvalidResponseDataException
	 */
	public function validate(string $host): void{
		$this->setHost($host);
		try {
			$res = $this->get();
			$res->assertOk();
			$res->assertSee($this->expectedString);
		} catch (ExpectationFailedException $e) {
			$this->throwInvalidResponseException(__METHOD__.": ".$e->getMessage());
		}
		$url = $this->getUrl();
		ConsoleLog::info("Success! $url contains " . $this->expectedString);
	}
	/**
	 * @return \Illuminate\Testing\TestResponse
	 * @throws InvalidResponseDataException
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	private function get(): \Illuminate\Testing\TestResponse{
		$url = $this->getUrl();
		try {
			return QMBaseTestCase::getTestResponseFrommExternalUrl($url, [
				'verify' => false,
				'timeout' => 15,
			]);
		} catch (GuzzleException $e) {
			$this->throwInvalidResponseException(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @return string
	 */
	private function getUrl(): string{
		if(IPHelper::isIp($this->getHost())){
			$url = "http://" . $this->getHost() . $this->getPath();
		} else{
			$url = "https://" . $this->getHost() . $this->getPath();
		}
		try {
			UrlHelper::assertIsUrl($url, __METHOD__);
		} catch (InvalidUrlException $e) {
			le($e);
		}
		return $url;
	}
	/**
	 * @param $res
	 * @throws InvalidResponseDataException
	 */
	private function throwInvalidResponseException($res): void{
		$url = $this->getUrl();
		throw new InvalidResponseDataException("$url returned " . \App\Logging\QMLog::print_r($res, true), $res);
	}
	/**
	 * @param string $res
	 */
	private function logResponse(string $res): void{
		$url = $this->getUrl();
		QMLog::info("$url returned: $res ");
	}
}
