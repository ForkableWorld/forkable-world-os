<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests;
use App\Buttons\Auth\LoginButton;
use App\Buttons\Auth\RegistrationButton;
use App\Computers\ThisComputer;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\User;
use App\Override\GeneratedTestRequest;
use App\Override\QMServerBag;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Storage\DB\StagingDB;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Traits\DataTableTestTrait;
/**
 * @backupGlobals disabled
 */
abstract class LaravelStagingTestCase extends QMBaseTestCase {
	use DataTableTestTrait, ApiTestTrait;
	public const TEST_USER_ACCESS_TOKEN = BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535;
	public $expectedResponseSizes;
	public $expectedCode;  // Never use fixture files in production tests!
	public $maximumResponseArrayLength;
	public $minimumResponseArrayLength;
	protected $REQUEST_URI;
	public $responseBody;
	public $slimEnvironmentSettings;
	protected $defaultFixtureFiles = [];
	protected $fixtureFiles = [];
	protected $keepAuthCookies = false;
	protected $accessToken;
	protected GeneratedTestRequest $serializedRequest; // Set true in test if it uses too much memory
	protected function setUp(): void{
		parent::setUp();
		$_SERVER['REQUEST_URI'] = $this->slimEnvironmentSettings ? $this->getRequestUri() : false;
	}
	protected function getAllowedDBNames(): array{return [StagingDB::DB_NAME];}
	/**
	 * @param int|null $expectedCode
	 * @param string|null $expectedString
	 * @return object|string
	 */
	public function callAndCheckResponse(int $expectedCode = null, string $expectedString = null): TestResponse{
		if(!ExceptionHandler::getExpectedRequestException()){
			if($expectedCode === 401){QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);}
			if($expectedCode === 403){QMBaseTestCase::setExpectedRequestException(HttpException::class);}
		}
		$req = $this->getRequest();
		$this->populateServer($req);
		AppMode::setIsApiRequest(true);
		ThisComputer::setAPIMemoryLimit();
		$parameters = array_merge($req->query(), $req->request->all());
		$response = $r = $this->testResponse = $this->call($req->getMethod(), $req->getUri(),
			$parameters, $req->cookie(), $req->file(), $req->server(), $req->getContent());
		DBUnitTestCase::resetHttpGlobals();
		// TODO: Why is logout necessary? QMAuth::logout(__METHOD__);
		ThisComputer::setWorkerMemoryLimit();
		$this->assertStatusCode($response, $req, $expectedCode);
		if($expectedString){$this->checkExpectedString($expectedString, $response);}
		return $response;
	}
	/**
	 * @return Request
	 */
	protected function getRequest(): Request{
		$req = $this->serializedRequest;
		if(is_string($req)){
			$req = unserialize($this->serializedRequest);
			if(!$req){
				throw new InvalidArgumentException("Could not parse serializedRequest");
			}
		}
		if(!$this->keepAuthCookies){
			foreach($req->cookies->all() as $key => $value){
				if(QMStr::contains($key, 'session') ||
					QMStr::contains($key, 'login') ||
					QMStr::contains($key, 'logged_in') ||
					QMStr::contains($key, 'final_callback_url') ||
					QMStr::contains($key, 'remember')){
					$req->cookies->remove($key);
				}
			}
		}
		/** @var GeneratedTestRequest $req */
		$req->headers->set("host", UrlHelper::STAGING_QM_HOST);
		$req->server->set("HTTP_HOST", UrlHelper::STAGING_QM_HOST); // Need for consistent fixture files
		if($this->REQUEST_URI){
			$req->server->set("REQUEST_URI", $this->REQUEST_URI);
		}
		return $req;
	}
	/**
	 * @return array
	 */
	private function getPostBody(): array{
		return $this->getRequest()->post();
	}
	private function assertStatusCode(TestResponse $response, Request $req, int $expectedCode = null){
		if(!$expectedCode){
			if($req->getMethod() === "GET"){
				$expectedCode = 200;
			} elseif($req->getMethod() === "DELETE"){
				$expectedCode = 204;
			} else{
				$expectedCode = 201;
			}
		}
		$this->assertStatusCodeEquals($expectedCode, $response, $req->getUri(), $req->getMethod());
	}
	/**
	 * @param mixed $body
	 */
	protected function checkResponseSize($body){
		if(!$this->expectedResponseSizes){
			return;
		}
		$responseBodyArray = json_decode(json_encode($body), true);
		ObjectHelper::logPropertySizes("Actual Response Sizes: ", $responseBodyArray, false);
		foreach($this->expectedResponseSizes as $property => $expectedResponseSize){
			if(!isset($responseBodyArray[$property])){
				$this->assertNotNull($responseBodyArray[$property], "Response property $property should not be null!");
			}
			$actualSize = ObjectHelper::getSizeInKiloBytes($responseBodyArray[$property]);
			$buffer = 0.5;
			$tooSmall = $actualSize < (1 - $buffer) * $expectedResponseSize;
			$tooBig = $actualSize > (1 + $buffer) * $expectedResponseSize;
			if($tooBig || $tooSmall){
				ObjectHelper::logPropertySizes($property, $responseBodyArray[$property]);
			}
			$this->assertFalse($tooSmall,
			                   "Key $property size $actualSize kb is less than expected size $expectedResponseSize kb");
			$this->assertFalse($tooBig,
			                   "Key $property size $actualSize kb is greater than expected size $expectedResponseSize kb");
		}
	}
	/**
	 * @param int $expected
	 * @param object $response
	 */
	public function assertResourceCount(int $expected, object $response): void{
		/** @var Resource[] $resources */
		$resources = $this->lastResponseData('resources');
		$this->assertCount($expected, $resources);
	}
	/**
	 * @param int $expected
	 * @param object $response
	 * @param string|null $message
	 */
	public function assertFieldCount(int $expected, object $response, string $message = null): void{
		$resources = $this->lastResponseData('resources');
		foreach($resources as $resource){
			$this->assertCount($expected, $resource->fields, $message);
		}
	}
	/**
	 * @param TestResponse $response
	 * @param string|null $message
	 */
	public function assertFieldNames(TestResponse $response, string $message = null): void{
		$response = json_decode($response->getContent());
		if($fields = $response->fields ?? $response->resource->fields){
			self::compareObjectFixture(__FUNCTION__, BaseNameProperty::pluckArray($fields), $message);
			return;
		}
		$resources = $this->lastResponseData('resources');
		foreach($resources as $resource){
			$fields = $resource->fields;
			self::compareObjectFixture(__FUNCTION__, BaseNameProperty::pluckArray($fields), $message);
		}
	}
	/**
	 * @param object $response
	 * @param string|null $message
	 */
	public function assertFields(object $response, string $message = null): void{
		if($fields = $response->fields ?? $response->resource->fields){
			//$this->assertNames($expected, $fields, $message);
			self::compareObjectFixture(__FUNCTION__, $fields, $message);
			return;
		}
		$data = $this->lastResponseData();
		if(isset($data->resource)){
			$resources = [$data->resource];
		} else{
			$resources = $data->resources;
		}
		foreach($resources as $resource){
			$fields = $resource->fields;
			//$this->assertNames($expected, $fields, $message);
			self::compareObjectFixture(__FUNCTION__, $fields, $message);
		}
	}
	protected function assertNumberOfPatients(int $expected): void{
		$u = \Auth::user();
		/** @noinspection PhpUnusedLocalVariableInspection */
		if($updateNumberOfPatients = false){
			$u->logAdminerUrl();
			$calculated = UserNumberOfPatientsProperty::calculate($u);
			$u->save();
			$this->assertEquals($expected, $calculated);
		}
		$num = $u->number_of_patients;
		$this->assertNotNull($num);
		$this->assertEquals($expected, $num);
	}
	/**
	 * @return TestResponse
	 */
	protected function assertCanAccessDatalab(): TestResponse{
		$headers = [];
		if($t = $this->accessToken){
			$headers = ['authorization' => "Bearer $t"];
		}
		$r = $this->get('/datalab', $headers);
		$r->assertSee("Measurements");
		$cookies = $r->headers->getCookies();
		$this->assertCount(1, $cookies);
		$this->assertEquals($cookies[0]->getName(), 'laravel_session');
		return $r;
	}
	protected function assertCannotAccessDatalab(): TestResponse{
		self::setExpectedRequestException(AuthenticationException::class);
		$r = $this->get('/datalab');
		$r->isRedirect(LoginButton::PATH);
		$cookies = $r->headers->getCookies();
		$this->assertCount(1, $cookies);
		$this->assertEquals($cookies[0]->getName(), 'laravel_session');
		return $r;
	}
	protected function actAsAdmin(){
		$user = User::find(230);
		$this->actingAs($user);
		$this->assertAuthenticatedAs($user);
	}
	protected function actAsTestUser(){
		$user = User::find(UserIdProperty::USER_ID_TEST_USER);
		$this->actingAs($user);
		$this->assertAuthenticatedAs($user);
	}
	protected function unauthorized(int $code = 401): TestResponse{
		QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);
		$this->stagingRequest($code, "Unauthorized");
		$response = $this->getTestResponse();
		$response->assertLocation(RegistrationButton::PATH);
		$response->assertDontSee("Measurements");
		$this->checkTestDuration(5);
		$this->checkQueryCount(2);
		return $response;
	}
	protected function unauthorizedAccess(int $code = 403): TestResponse{
		QMBaseTestCase::setExpectedRequestException(\Illuminate\Auth\Access\AuthorizationException::class);
		$this->stagingRequest($code);
		$response = $this->getTestResponse();
		//$response->assertLocation(RegistrationButton::PATH);
		$response->assertDontSee("Measurements");
		$this->checkTestDuration(5);
		return $response;
	}
	protected function unauthenticated(int $code = 302): TestResponse{
		QMBaseTestCase::setExpectedRequestException(AuthenticationException::class);
		$this->stagingRequest($code, "Redirecting");
		$response = $this->getTestResponse();
		$redirect = $response->headers->get('location');
		$this->assertStringStartsWith(RegistrationButton::url(), $redirect);
		$response->assertDontSee("Measurements");
		$this->checkTestDuration(5);
		$this->checkQueryCount(4);
		return $response;
	}
	protected function assertGuestRedirectToLogin(): TestResponse{
		$this->assertGuest();
		return $this->unauthenticated();
	}
	/**
	 * @return mixed
	 */
	protected function assertUnauthenticatedResponse(): TestResponse{
		$this->assertGuest();
		$this->expectException(AuthenticationException::class);
		return $this->stagingRequest(401, "Unauthenticated");
	}
	/**
	 * @param int $expectedCode
	 * @param string|null $expectedString
	 * @return mixed
	 */
	abstract protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse;
	/**
	 * @param $content
	 * @param string|null $expectedString
	 */
	private function checkHtml($content, ?string $expectedString): void{
		if($html = $content){
			$html = QMStr::after("'pageview');</script>", $html, $html);
			$html = QMStr::before("<!-- global js -->", $html, $html);
		}
		if(!empty($expectedString)){
			$this->assertContains($expectedString, $html);
		}
	}
	/**
	 * @param $decodedJson
	 * @param string|null $expectedString
	 * @param $content
	 * @param TestResponse $response
	 * @return TestResponse
	 */
	private function checkJson(string $expectedString, TestResponse $response): TestResponse{
		$this->checkResponseSize(json_decode($response->getContent()));
		if(!empty($expectedString)){
			$response->assertSee($expectedString);
		}
		return $response;
	}
	/**
	 * @param \Illuminate\Http\Request $req
	 */
	private function populateServer(Request $req): void{
		$headers = $req->header();
		$server = $this->transformHeadersToServerVars($headers);
		QMServerBag::populate($server);
		if($this->accessToken){
			$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->accessToken;
		}
		$_GET = $this->getRequest()->query();
		$_POST = $this->getPostBody();
		$_SERVER['REQUEST_METHOD'] = $req->method();
		$path = $req->path();
		$path = QMStr::addPrefixIfNecessary('/', $path);
		$_SERVER['REQUEST_URI'] = $path;
		$_SERVER['SERVER_NAME'] = $req->getHost();
		$_SERVER['SERVER_PORT'] = $req->getPort();
		$_SERVER['REMOTE_ADDR'] = $req->ip();
		$_SERVER['REQUEST_TIME'] = time();
		$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
		//$_SERVER['SCRIPT_NAME'] = $req->getScriptName();
	}
	/**
	 * @param string $expectedString
	 * @param TestResponse $response
	 */
	private function checkExpectedString(string $expectedString, TestResponse $response): void{
		$content = $response->getContent();
		if(json_decode($content)){
			$this->checkJson($expectedString, $response);
		} else{
			$this->checkHtml($response->getContent(), $expectedString);
		}
	}
}
