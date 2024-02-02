<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection ForgottenDebugOutputInspection */
namespace Tests;
use App\DevOps\Jenkins\JenkinsQueue;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Logging\QMLog;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Slim\Model\QMResponseBody;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\StagingDB;
use App\Storage\DB\Writable;
use App\Studies\QMUserStudy;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use App\Variables\QMUserVariable;
use LogicException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Util;
use Tests\SlimTests\SlimTestCase;
/**
 * @backupGlobals disabled
 */
class SlimStagingTestCase extends QMBaseTestCase {
	protected const DISABLED_UNTIL = null;
	public const JOB_NAME = null;  // DO NOT DELETE: Helps to prevent use fixture files in production tests!
	public $expectedResponseSizes;         // DO NOT DELETE: Helps to prevent use fixture files in production tests!
	public $expectedCode;
	public $maximumResponseArrayLength;
	public $minimumResponseArrayLength;
	protected $REQUEST_URI;
	public $responseBody;
	public $slimEnvironmentSettings;
	/**
	 * @var \Slim\Http\Response
	 */
	private Response $slimResponse;
	private int $expectedStatusCode;
	protected function getAllowedDBNames(): array{return [StagingDB::DB_NAME];}
	public function setUp(): void{
		parent::setUp();
		if(Writable::getDbName() !== StagingDB::DB_NAME){
			$str = "Writable::getDbName() is: ".Writable::getDbName().", but it should be: ".StagingDB::DB_NAME;
			QMLog::error($str);
			die($str);
		}
	}
	/**
	 * @param int $expectedCode
	 */
	public function setExpectedStatusCode(int $expectedCode): void{
		$this->expectedStatusCode = $expectedCode;
	}
	/**
	 * @return void
	 */
	public function outputSlimEnvironmentSettings(){
		QMLog::infoWithoutContext("slimEnvironmentSettings: ".json_encode($this->slimEnvironmentSettings));
	}
	/**
	 * @return string
	 */
	public function getRequestUri(): string{
		return QMRequest::addProvidedAndRequestQueryParamsToUrl($this->getPathInfo(), $this->getQueryParamsArray());
	}
	/**
	 * @return string
	 */
	public function getPathInfo(): string{
		if(!is_string($this->slimEnvironmentSettings['PATH_INFO'])){
			QMLog::error("slimEnvironmentSettings['PATH_INFO'] is: ".$this->slimEnvironmentSettings['PATH_INFO']);
			QMLog::error("slimEnvironmentSettings: ".json_encode($this->slimEnvironmentSettings));
			$dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			$caller = $dbt[1]['function'] ?? null;
			QMLog::error("CALLER: $caller");
		}
		return $this->slimEnvironmentSettings['PATH_INFO'];
	}
	/**
	 * @return array
	 */
	public function getQueryParamsArray(): array{
		return $this->slimEnvironmentSettings['slim.request.query_hash'] ?? [];
	}
	/**
	 * @return QMResponseBody
	 */
	public function getResponseBody(): QMResponseBody{
		return $this->responseBody;
	}
	/**
	 * @return array
	 */
	protected function getCookies(): array{
		return Util::parseCookieHeader($this->slimEnvironmentSettings["HTTP_COOKIE"]);
	}
	/**
	 * @param string|null $expectedString
	 * @param bool $expectJson
	 * @param string|null $expectedRedirect
	 * @return QMResponseBody|Response
	 */
	public function callAndCheckResponse(string $expectedString = null, bool $expectJson = true,
	                                     string $expectedRedirect = null){
		$_POST = $this->getPostBody();
		$response =
		$this->slimResponse = SlimTestCase::slimCall($this->getSlimEnvironmentSettings(), $this->getQueryParamsArray());
		$decodedBody = $this->getDecodedBody();
		if($expectedRedirect){
			$this->assertEquals(302, $response->getStatus());
			$this->assertStringStartsWith($expectedRedirect, $response->header("Location"));
			return $response;
		}
		$this->checkStatusCode();
		if($expectJson && $this->getExpectedStatusCode() < 300){
			$b = $response->getBody();
			$this->checkSuccessField($decodedBody);
			$this->checkBodyForExpectedString($expectedString, $b);
			$this->checkResponseArrayLength($decodedBody);
			$this->checkResponseSize($decodedBody);
		}
		return $decodedBody;
		// TODO: Use laravel test Get
//		$_SERVER = $this->getSlimEnvironmentSettings();
//		\App\Computers\ThisComputer::user() = get_current_user();
//		$_GET = $this->getQueryParamsArray();
//		$_SERVER['SCRIPT_NAME'] = "/index.php";
//		$_SERVER['REQUEST_TIME'] = time();
//		$_COOKIE = $this->getCookies();
//		$_SERVER['QUERY_STRING'] = (new QueryString($this->getQueryParamsArray()))->__toString();
//		$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO']."?".$_SERVER['QUERY_STRING'];
//		$uri = UrlHelper::addParams($_SERVER["PATH_INFO"], $this->getQueryParamsArray());
//		$response = $this->response = $this->call($_SERVER["REQUEST_METHOD"], $uri,
//		                        $this->getPostBody(), $this->getCookies(), [], $_SERVER);
//		//$response =
//		//$this->response = DBUnitTestCase::slimCall($this->getSlimEnvironmentSettings(), $this->getQueryParamsArray());
//		//$decodedBody = $this->getDecodedBody();
//		if($expectedRedirect){
//			$this->assertEquals(302, $response->getStatusCode());
//			$response->assertHeader("Location", $expectedRedirect);
//			return $response->decodeResponseJson();
//		}
//		$this->checkStatusCode();
//		if($expectJson && $this->getExpectedStatusCode() < 300){
//			$this->checkSuccessField($response->decodeResponseJson());
//			$response->assertSee($expectedString);
//			$this->checkResponseArrayLength($response->decodeResponseJson());
//			$this->checkResponseSize($response->decodeResponseJson());
//		}
//		return $response->decodeResponseJson();
	}
	/**
	 * @return array
	 */
	private function getPostBody(): array{
		$body = [];
		if(isset($this->slimEnvironmentSettings["slim.input"])){
			$body = json_decode($this->slimEnvironmentSettings["slim.input"], true);
		}
		return $body ?? [];
	}
	/**
	 * @return array
	 */
	public function getSlimEnvironmentSettings(): array{
		$s = $this->slimEnvironmentSettings;
		$s['HTTP_HOST'] = UrlHelper::STAGING_QM_HOST; // Need for consistent fixture files
		return $s;
	}
	/**
	 * @return mixed|QMResponseBody
	 */
	private function getDecodedBody(){
		return $this->responseBody = DBUnitTestCase::decodeBodyAndLogErrors($this->getPathInfo(), $this->slimResponse);
	}
	private function checkStatusCode(){
		$expectedCode = $this->getExpectedStatusCode();
		$resp = $this->getSlimResponse();
		$statusCode = $resp->getStatus();
		if($statusCode !== $expectedCode){
			le("statusCode $statusCode !== expectedStatusCode $expectedCode! ".\App\Logging\QMLog::print_r($this->slimResponse, true));
		}
		$this->assertEquals($expectedCode, $statusCode, $this->getResponseErrorMessage());
	}
	/**
	 * @return int
	 */
	private function getExpectedStatusCode(): int{
		if(isset($this->expectedStatusCode)){
			return $this->expectedStatusCode;
		}
		if($expected = ExceptionHandler::getExpectedRequestException()){
			if(stripos($expected, 'NotFound') !== false){
				return BadRequestException::CODE_NOT_FOUND;
			}
			if($expected === AccessTokenExpiredException::class){
				return BadRequestException::CODE_UNAUTHORIZED;
			}
			return BadRequestException::CODE_BAD_REQUEST;
		}
		$slimEnvironmentSettings = $this->getSlimEnvironmentSettings();
		if(isset($slimEnvironmentSettings['responseStatusCode'])){
			$expectedCode = $slimEnvironmentSettings['responseStatusCode'];
		} elseif($slimEnvironmentSettings['REQUEST_METHOD'] === Request::METHOD_GET){
			$expectedCode = 200;
		} else{
			$expectedCode = 201;
		}
		return $expectedCode;
	}
	/**
	 * @return Response
	 */
	public function getSlimResponse(): Response{
		return $this->slimResponse;
	}
	/**
	 * @return string
	 */
	private function getResponseErrorMessage(): string{
		return DBUnitTestCase::getErrorMessageFromResponse($this->slimResponse);
	}
	/**
	 * @param $decodedBody
	 */
	private function checkSuccessField($decodedBody){
		if(stripos($this->getPathInfo(), '/v4/') !== false){
			$this->assertTrue($decodedBody->success);
		}
		$expectedSuccessValue = true;
		if($this->getExpectedStatusCode() && $this->getExpectedStatusCode() > 399){
			$expectedSuccessValue = false;
		}
		if(is_object($decodedBody) && property_exists($decodedBody, 'success')){
			$message = "Success field in response is: $decodedBody->success";
			if($expectedSuccessValue !== $decodedBody->success){
				$message .= ".  response: ".var_export($decodedBody, true);
				QMLog::error($message);
			}
			$this->assertEquals($expectedSuccessValue, $decodedBody->success, $message);
		}
	}
	/**
	 * @param string|null $expectedString
	 * @param $bodyString
	 */
	private function checkBodyForExpectedString(?string $expectedString, $bodyString): void{
		if($expectedString){
			if(stripos($bodyString, $expectedString) === false){
				$truncated = QMStr::truncate($bodyString, 1000, "[TRUNCATED]");
				throw new LogicException("The following was supposed to contain $expectedString but is:
                    $truncated");
			}
		}
	}
	/**
	 * @param $decodedBody
	 */
	private function checkResponseArrayLength($decodedBody): void{
		$array = $decodedBody;
		if(!is_array($array) && isset($array->data)){
			$array = $array->data;
		}
		if(!is_array($array) && isset($array->correlations)){
			$array = $array->correlations;
		}
		if(is_array($array)){
			if($this->maximumResponseArrayLength){
				$this->assertLessThanOrEqual($this->maximumResponseArrayLength, count($array));
			}
			if($this->minimumResponseArrayLength){
				$this->assertGreaterThanOrEqual($this->minimumResponseArrayLength, count($array));
			}
		}
	}
	/**
	 * @param mixed $body
	 */
	protected function checkResponseSize($body){
		$expectedResponseSizes = $this->expectedResponseSizes;
		if(!$expectedResponseSizes){
			return;
		}
		$responseBodyArray = json_decode(json_encode($body), true);
		ObjectHelper::logPropertySizes("Actual Response Sizes: ", $responseBodyArray, false);
		$exceptionMessage = null;
		foreach($expectedResponseSizes as $property => $expectedResponseSize){
			if(!isset($responseBodyArray[$property])){
				$message = "Response property $property should not be null!";
				QMLog::infoWithoutContext($message);
				$this->assertNotNull($responseBodyArray[$property], $message);
			}
			$valueArr = $responseBodyArray[$property];
			$actualSize = strlen(serialize($valueArr)) / 1000;
			$buffer = 0.5;
			$max = $expectedResponseSize['max'] ?? ((1 + $buffer) * $expectedResponseSize);
			$min = $expectedResponseSize['min'] ?? ((1 - $buffer) * $expectedResponseSize);
			$tooSmall = $actualSize < $min;
			$tooBig = $actualSize > $max;
			if($tooBig || $tooSmall){
				$sizes = ObjectHelper::logPropertySizes($property, $valueArr);
				if($tooBig){
					$exceptionMessage = "Key $property size $actualSize kb is greater than maximum $max kb";
				} else{
					$exceptionMessage = "Key $property size $actualSize kb is less than minimum $min kb ";
				}
				$exceptionMessage .= "\n=== $property Property Sizes in KB ===\n".QMLog::print_r($sizes, true);
				$this->compareObjectFixture($property, $valueArr);
			}
		}
		if($exceptionMessage){$this->fail($exceptionMessage);}
	}
	/**
	 * @param QMUserVariable $v
	 */
	protected function checkChildVariable(QMUserVariable $v){
		$this->assertCommonTagCount(1, $v);
		$tTagged = $v->getCommonTaggedVariables();
		$this->assertCount(0, $tTagged);
		$parents = $v->getParentCommonTagVariables();
		$this->assertCount(1, $parents);
	}
	/**
	 * @param int $expected
	 * @param QMUserVariable $uv
	 */
	protected function assertCommonTagCount(int $expected, QMUserVariable $uv): void{
		$cv = $uv->getCommonVariable();
		$number = $cv->getNumberOfCommonTags();
		$calculated = $cv->calculateNumberOfCommonTags();
		$this->assertEquals($calculated, $number, "NumberOfCommonTags is $number but should be $calculated");
		$this->assertEquals($expected, $number);
		$this->assertEquals($expected, $calculated);
		$tTags = $uv->getCommonTagVariables();
		$this->assertCount($expected, $tTags);
	}
	/**
	 * @param QMUserVariable $v
	 */
	protected function checkParentVariable(QMUserVariable $v){
		$this->assertCommonTaggedByGreaterThanZero($v);
		$tTags = $v->getCommonTagVariables();
		$this->assertCount(0, $tTags);
		$children = $v->getChildCommonTagVariables();
		$this->assertGreaterThan(0, count($children));
	}
	/**
	 * @param QMUserVariable $uv
	 */
	protected function assertCommonTaggedByGreaterThanZero(QMUserVariable $uv): void{
		$cv = $uv->getCommonVariable();
		$v = $cv->getVariable();
		$commonTaggedBy = $v->common_tags_where_tag_variable()->get();
		$this->assertGreaterThan(0, $commonTaggedBy->count());
		$number = $cv->getOrCalculateNumberCommonTaggedBy();
		if(!$number){
			$number = $cv->getOrCalculateNumberCommonTaggedBy();
		}
		$this->assertGreaterThan(0, $number);
		$calculated = $cv->calculateNumberCommonTaggedBy();
		$this->assertGreaterThan(0, $calculated);
		$tTagged = $uv->getCommonTaggedVariables();
		$this->assertGreaterThan(0, $tTagged);
	}
	/**
	 * @param QMUserVariable $a
	 * @param QMUserVariable $b
	 */
	protected function checkJoinedVariables(QMUserVariable $a, QMUserVariable $b){
		$this->assertEquals(UserVariableStatusProperty::STATUS_WAITING, $a->status);
		$this->assertContains($b->status, [
			UserVariableStatusProperty::STATUS_WAITING,
			UserVariableStatusProperty::STATUS_CORRELATE,
		]);
		$this->assertNotNull($a->getAnalysisSettingsModifiedAt());
		$this->assertNotNull($b->getAnalysisSettingsModifiedAt());
		$this->assertCommonTaggedByGreaterThanZero($a);
		$this->assertCommonTagsGreaterThanZero($a);
		$this->assertCommonTaggedByGreaterThanZero($b);
		$this->assertCommonTagsGreaterThanZero($b);
		$this->assertTrue($a->needToAnalyze(), "We should need to re-analyze after joining");
		$this->assertTrue($b->needToAnalyze(), "We should need to re-analyze after joining");
		$aWithTags = $a->getMeasurementsWithTags();
		$aRaw = $a->getQMMeasurements();
		$this->assertGreaterThan(count($aRaw), count($aWithTags));
		$bWithTags = $b->getMeasurementsWithTags();
		$bRaw = $b->getQMMeasurements();
		$this->assertGreaterThan(count($bRaw), count($bWithTags));
		return;
		/** @noinspection PhpUnreachableStatementInspection */
		$studyA = QMUserStudy::findOrCreateQMStudy($a->getId(), "Anxiety", $a->getUserId());
		$studyA->postToWordPress();
		$aStatistics = $studyA->getHasCorrelationCoefficient();
		$aMeasurements = $aStatistics->getCauseMeasurements();
		$studyB = QMUserStudy::findOrCreateQMStudy($b->getId(), "Anxiety", $b->getUserId());
		$bStatistics = $studyB->getHasCorrelationCoefficient();
		$bMeasurements = $bStatistics->getCauseMeasurements();
		$this->assertSameSize($aMeasurements, $bMeasurements);
		$this->assertEquals($aStatistics->correlationCoefficient, $bStatistics->correlationCoefficient);
		$this->assertEquals($studyA->getStudyText()->getTagLine(), $studyB->getStudyText()->getTagLine());
		// TODO: Make joined variables return measurements from secondary joins
		//$this->assertEquals(count($aMeasurements), count($bMeasurements));
		$this->assertFalse($a->needToAnalyze());
		$this->assertFalse($b->needToAnalyze());
	}
	/**
	 * @param QMUserVariable $uv
	 */
	protected function assertCommonTagsGreaterThanZero(QMUserVariable $uv): void{
		$cv = $uv->getCommonVariable();
		$number = $cv->getOrCalculateNumberCommonTaggedBy();
		$this->assertGreaterThan(0, $number);
		$calculated = $cv->calculateNumberCommonTaggedBy();
		$this->assertGreaterThan(0, $calculated);
		$tTagged = $uv->getCommonTaggedVariables();
		$this->assertGreaterThan(0, $tTagged);
	}
	/**
	 * @param QMUserVariable $child
	 * @param QMUserVariable $parent
	 */
	protected function checkChildParentMeasurements(QMUserVariable $child, QMUserVariable $parent): void{
		$childMeasurements = $child->getMeasurementsWithTags();
		$parentMeasurements = $parent->getMeasurementsWithTags();
		$this->assertGreaterThan(count($childMeasurements), count($parentMeasurements));
	}
	protected function skipIfQueued(string $jobName): bool{
		if(EnvOverride::isLocal()){
			return false;
		}
		try {
			$queued = JenkinsQueue::getQueuedItemsByJobName($jobName);
		} catch (\Throwable $e) {
			QMLog::info("Could not check jenkins queue because ".$e->getMessage());
			return false;
		}
		if($queued){
			$this->skipTest("Skipping because we have these items in the queue: ".\App\Logging\QMLog::print_r($queued, true));
			/** @noinspection PhpUnreachableStatementInspection */
			return true;
		}
		return false;
	}
	protected function setAccessTokenByUserId(int $userId){
		$u = User::findInMemoryOrDB($userId);
		if(!$u){
			le("Could not find user with id $userId. DB is ".Writable::db()->getDatabaseName().Env::printObfuscated());
		}
		$this->slimEnvironmentSettings['HTTP_AUTHORIZATION'] =
			"Bearer ".$u->getOrCreateAccessTokenString(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
	}
	/**
	 * @param string $key
	 * @param $value
	 */
	protected function setQueryParam(string $key, $value){
		$this->slimEnvironmentSettings['slim.request.query_hash'][$key] = $value;
	}
}
