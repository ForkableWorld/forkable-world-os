<?php /** @noinspection PhpClassConstantAccessedViaChildClassInspection */
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\SlimTests;
use App\AppSettings\AdditionalSettings\BuildSettings;
use App\AppSettings\AppSettings;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\Cards\TrackingReminderNotificationCard;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\Computers\ThisComputer;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\InputFields\InputField;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserClient;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\Application\ApplicationUserIdProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseForwardPearsonCorrelationCoefficientProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Controller\Feed\UserFeedResponse;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementV1;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\Reminders\TrackingRemindersResponse;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Studies\StudyHtml;
use App\Studies\StudyText;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\Units\DollarsUnit;
use App\Units\MilligramsUnit;
use App\Units\OneToFiveRatingUnit;
use App\Units\PercentUnit;
use App\Units\YesNoUnit;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\CommonVariables\SymptomsCommonVariables\BackPainCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMCommonTag;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserTag;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use LogicException;
use PDO;
use Slim\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use stdClass;
use Tests\ApiTestTrait;
use Tests\DBUnitTestCase;
use Tests\SlimTests\Analytics\StudyTest;
use Tests\QMAssert;
use Tests\QMBaseTestCase;
use Tests\UnitTestCase;
use Throwable;
abstract class SlimTestCase extends UnitTestCase {
    use ApiTestTrait;
	/**
	 * @param TestResponse $response
	 * @return mixed
	 */
	public function getBodyFromSlimOrLaravelResponse(TestResponse|\Slim\Http\Response $response): mixed{
		if(method_exists($response, 'getBody')){
			$decodedResponse = json_decode($response->getBody(), false);
		} else{
			$decodedResponse = $response->json();
			$decodedResponse = json_decode(json_encode($decodedResponse));
		}
		return $decodedResponse;
	}
	public static function deleteGlobalVariableRelationships(): void{
		Variable::query()->update([Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID => null]);
		UserVariableRelationship::query()->update([UserVariableRelationship::FIELD_AGGREGATE_CORRELATION_ID => null]);
		GlobalVariableRelationship::deleteAll();
		$numberOfGlobalVariableRelationships = GlobalVariableRelationship::count();
		self::assertEquals(0, $numberOfGlobalVariableRelationships);
	}
	/**
	 * Make a GET request to the given URL with the given parameters
	 * @param string $path
	 * @param array $params
	 * @param int $code
	 * @param bool $checkJson
	 * @param string|null $expectedString
	 * @return Response
	 */
	public function slimGet(string $path, array $params = [], int $code = 200, bool $checkJson = true, string $expectedString = null){
		$path = str_replace(' ', '%20', $path); // Url validator rejects it below even though it works in browser
		$expectedException = QMBaseTestCase::getExpectedException();
		if($code > 399 && !$expectedException){$this->expectQMException();}
		Memory::resetClearOrDeleteAll();
		if(is_object($params)){$params = json_decode(json_encode($params), true);}
		if(!isset($params['clientId']) && !isset($params['client_id'])){$params['clientId'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;}
		if(!str_contains($path, 'api/')){$path = '/api/'.$path;}
		$path = '/'.$path;
		$path = str_replace('//', '/', $path);
		$params = self::getQueryParamsFromUrl($path, $params);
		$path = QMStr::before('?', $path, $path);
		$r = self::slimCall([
			                            'REQUEST_METHOD' => QMRequest::METHOD_GET,
			                            'PATH_INFO'      => $path,
			                            'QUERY_STRING'   => http_build_query($params),
			                            'HTTP_COOKIE'    => $this->getCookies(),
		                            ], $params);
		$err = self::getErrorMessageFromResponse($r);
		$this->assertNotNull($r, $err ?? "");
		if($code !== $r->getStatus()){
			le("Got ".$r->getStatus() . " from $path!
            Error Message: $err
            Response: ", $r);
		}
		$this->assertEquals($code, $r->getStatus(), $err);
		if($expectedString){self::assertResponseBodyContains($expectedString, $r);}
		$decodedBody = self::decodeBodyAndLogErrors($path, $r);
		if($code === 200 && $this->apiVersionIsGreaterThanThree($path)){
			$this->assertTrue($decodedBody->success);
		}  // Old versions of API just return arrays with data without success field
		//$this->expectException(null);
		return $r;
	}
	/**
	 * @param string $apiUrl
	 * @param array $params
	 * @return Response
	 */
	public function getWithoutResponseValidation(string $apiUrl, array $params){
		if(!str_contains($apiUrl, 'api/')){$apiUrl = '/api/'.$apiUrl;}
		$apiUrl = '/'.$apiUrl;
		$apiUrl = str_replace('//', '/', $apiUrl);
		$params = $this->getQueryParamsFromUrl($apiUrl, $params);
		$apiUrl = QMStr::before('?', $apiUrl, $apiUrl);
		$response = static::slimCall([
			                                     'REQUEST_METHOD' => QMRequest::METHOD_GET,
			                                     'PATH_INFO'      => $apiUrl,
			                                     'QUERY_STRING'   => http_build_query($params),
			                                     'HTTP_COOKIE'    => $this->getCookies(),
		                                     ], $params);
		self::resetHttpGlobals();
		return $response;
	}
	/**
	 * @param $apiUrl
	 * @param array $params
	 * @return array
	 */
	public static function getQueryParamsFromUrl($apiUrl, array $params): array{
		if(stripos($apiUrl, '?') !== false){
			$stringParams = UrlHelper::getParams($apiUrl);
			$params = array_merge($params, $stringParams);
		}
		return $params;
	}
	/**
	 * Make a DELETE request to the given URL with the given body.
	 * @param string $apiUrl
	 * @param string|array $postData
	 * @param null $expectedString
	 * @return Response
	 */
	public function slimDelete(string $apiUrl, $postData, $expectedString = null){
		if(!is_string($postData)){
			$postData = json_encode($postData);
		}
		$response = self::slimCall([
			                                   'REQUEST_METHOD' => Request::METHOD_DELETE,
			                                   'PATH_INFO'      => $apiUrl,
			                                   'slim.input'     => $postData,
			                                   'HTTP_COOKIE'    => $this->getCookies(),
		                                   ], []);
		$this->assertEquals(204, $response->getStatus());
		$decodedBody = self::decodeBodyAndLogErrors($apiUrl, $response);
		$this->assertTrue($decodedBody->success);
		if($expectedString){
			$this->assertResponseBodyContains($expectedString, $response);
		}
		return $response;
	}
	/**
	 * @param array $env
	 * @param array|null $GET
	 * @return Response
	 */
	public static function slimCall(array $env, array $GET = []): Response{
		ThisComputer::setAPIMemoryLimit();
		if(!isset($env['REMOTE_ADDR'])){$env['REMOTE_ADDR'] = '24.216.168.142';}
		if(!$GET){$GET = $env["slim.request.query_hash"] ?? [];}
		self::mockSlimApiRequest($env, $GET);
		$slim = new QMSlim(false);
		self::logTestUrl($env, $GET);
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
		$slim->call();
		ThisComputer::setWorkerMemoryLimit();
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
		self::resetHttpGlobals();
		// Why? QMAuth::logout(__METHOD__);
		$response = $slim->response();
		if(!ExceptionHandler::getExpectedRequestException()){
			self::globalResponseChecks($env, json_decode($response->getBody(), false));
		}
		return $response;
	}
    /**
     * @param string $path
     * @param $body
     * @param int $expectedStatus
     * @param bool $returnObject
     * @return Response
     */
    protected function postApiV3(string $path, $body, int $expectedStatus = 201, bool $returnObject = false){
        if(is_string($body)){
            $body = json_decode($body, true);
        } else {
            $body = json_decode(json_encode($body), true);
        }
        $r = $this->slimPost('api/v3/'.$path, $body, false, $expectedStatus);
        $this->assertEquals($expectedStatus, $r->getStatus());
        return $r;
    }
    /**
     * Make a POST request to the given URL with the given body.
     * @param string $apiUrl
     * @param array|string $postData
     * @param bool $doNotAddClientId
     * @param int $expectedCode
     * @param array $urlParams
     * @param string|null $expectedString
     * @return Response
     */
	public function slimPost(string $apiUrl, $postData, bool $doNotAddClientId = false, int $expectedCode = 201,
                             array $urlParams = [], string $expectedString = null){
		if(!is_array($urlParams)){$urlParams = json_decode(json_encode($urlParams), true);}
		if(!str_contains($apiUrl, 'api')){$apiUrl = '/api'.$apiUrl;}
		if(!str_starts_with($apiUrl, '/')){$apiUrl = '/'.$apiUrl;}
		/** @noinspection SpellCheckingInspection */
		$apiUrl = str_replace('/apiv', '/api/v', $apiUrl);
		if(is_string($postData) && !$doNotAddClientId){
			$body = json_decode($postData);
		}else{
			$body = $postData;
		}
		if(!$doNotAddClientId){
			if(!$body){
				$body['clientId'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
			}else if(is_array($body)){
				if(!isset($body['clientId']) && !isset($body['client_id'])){
					$body['clientId'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
				}
			}else{
				if(!isset($body->clientId) && !isset($body->client_id)){
					$body->clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
				}
			}
		}
		$this->seeIfUserIsLoggedIn();
		$response = $this->callPostMethod($apiUrl, $doNotAddClientId, $urlParams, $body);
		$decodedBody = self::decodeBodyAndLogErrors($apiUrl, $response);
		$err = self::getErrorMessageFromResponse($response);
		$this->assertNotNull($response, $err);
		$this->assertEquals($expectedCode, $response->getStatus(), $err);
		if($expectedCode === 201 && $this->apiVersionIsGreaterThanThree($apiUrl)){
			$this->assertTrue($decodedBody->success, $err);
		}
		if($expectedString){$this->assertResponseBodyContains($expectedString, $response);}
		QMBaseTestCase::setExpectedRequestException(null);
		return $response;
	}
	/**
	 * @param string $apiUrl
	 * @param bool $doNotAddClientId
	 * @param $urlQueryParameters
	 * @param $decodedPostData
	 * @return Response The API response.
	 */
	private function callPostMethod(string $apiUrl, bool $doNotAddClientId, $urlQueryParameters, $decodedPostData){
		$encodedPostData = $decodedPostData;
		if(!is_string($encodedPostData)){
			$encodedPostData = json_encode($encodedPostData);
		}
		$urlQueryParameters = $this->getQueryParamsFromUrl($apiUrl, $urlQueryParameters);
        if(!$doNotAddClientId){
            $urlQueryParameters['client_id'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
		$apiUrl = QMStr::before('?', $apiUrl, $apiUrl);
		$response = self::slimCall([
			                                   'REQUEST_METHOD' => QMRequest::METHOD_POST,
			                                   'PATH_INFO'      => $apiUrl,
			                                   'slim.input'     => $encodedPostData,
			                                   'HTTP_COOKIE'    => $this->getCookies(),
			                                   'QUERY_STRING'   => http_build_query($urlQueryParameters),
		                                   ], $urlQueryParameters);
		return $response;
	}
	/**
	 * @param array $slimEnvironmentSettings
	 * @param string $testName
	 * @param string $expectedResponseString
	 * @return QMResponseBody
	 */
	public function callAndCheckResponse(array $slimEnvironmentSettings, string $testName, string $expectedResponseString = null){
		$expectedStatusCode = ($slimEnvironmentSettings['REQUEST_METHOD'] === QMRequest::METHOD_GET) ? 200 : 201;
		$response = static::slimCall($slimEnvironmentSettings);
		$apiUrl = $slimEnvironmentSettings['PATH_INFO'];
		$decodedBody = static::decodeBodyAndLogErrors($apiUrl, $response);
		$this->assertNotNull($response, self::getErrorMessageFromResponse($response));
		$this->assertEquals($expectedStatusCode, $response->getStatus(), self::getErrorMessageFromResponse($response));
		if($expectedStatusCode === 201 && $this->apiVersionIsGreaterThanThree($apiUrl)){
			$this->assertTrue($decodedBody->success);
		}
		if($expectedResponseString){
			self::assertResponseBodyContains($expectedResponseString, $response);
		}
		return $decodedBody;
	}
	/**
	 * @param string $string
	 * @param Response $response
	 */
	public static function assertResponseBodyContains(string $string, $response){
		self::assertNotFalse(strpos($response->getBody(), $string), "Body does not contain: " . $string);
	}
	/**
	 * Check that the given object has all required variable properties with correct type
	 * @param QMCommonVariable|stdClass $variable
	 * @param int $userId
	 * @param string $searchTerm
	 */
	public function checkSharedQmVariableObjectStructureV3($variable, $searchTerm = null, $userId = 1){
		$this->assertInstanceOf('stdClass', $variable);
		if($userId && $searchTerm){
			$ownVariable = false;
			$cleanedSearchTerm = str_replace(array(
				                                 '*',
				                                 '%'
			                                 ), '', $searchTerm);
			$exactMatch = $variable->name === $cleanedSearchTerm;
			if(isset($variable->userId)){
				$ownVariable = $userId === $variable->userId;
			}
			$this->assertTrue($ownVariable || $variable->isPublic || $exactMatch,
			                  "We should not have gotten this variable!");
		}
        $this->assertIsArray($variable->synonyms, 'Synonyms should be an array');
		$floatAttributes = [
			'fillingValue',
			'kurtosis',
			'maximumAllowedValue',
			'mean',
			'median',
			'minimumAllowedValue',
			'mostCommonValue',
			'secondMostCommonValue',
			'skewness',
			'standardDeviation',
			'thirdMostCommonValue',
			'variance'
		];
		$this->checkFloatAttributes($floatAttributes, $variable);
		$intAttributes = [
			'durationOfAction',
			'id',
			'numberOfGlobalVariableRelationshipsAsCause',
			'numberOfGlobalVariableRelationshipsAsEffect',
			'numberOfRawMeasurements',
			'numberOfMeasurements',
			'numberOfUniqueValues',
			'numberOfUserVariables',
			'onsetDelay',
			'unitId',
			'variableCategoryId'
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $variable);
		$booleanAttributes = [
			'causeOnly',
			QMCommonVariable::FIELD_OUTCOME,
            QMCommonVariable::FIELD_IS_PUBLIC,
			'manualTracking'
		];
		$this->checkBooleanAttributes($booleanAttributes, $variable);
		$stringAttributes = [
			'combinationOperation',
			'createdAt',
			'ionIcon',
			'name',
			'pngUrl',
			'svgUrl',
			'unitAbbreviatedName',
			'unitName',
			'updatedAt',
			'variableCategoryName',
			//'commonUnitAbbreviatedName',
			//'commonUnitName',
		];
		$this->checkStringAttributes($stringAttributes, $variable);
		$notNullAttributes = [
			//'causeOnly',
			'combinationOperation',
			'createdAt',
			'id',
			'ionIcon',
			'manualTracking',
			'name',
			'onsetDelay',
			'pngUrl',
			//'isPublic',
			'svgUrl',
			//'unit',
			'unitAbbreviatedName',
			'unitName',
			'updatedAt',
			'variableCategoryId',
			'variableCategoryName',
			QMCommonVariable::FIELD_OUTCOME,
		];
		$this->checkNotNullAttributes($notNullAttributes, $variable);
	}
	/**
	 * Check that the given object has all required variable properties with correct type
	 * @param QMCommonVariable|QMVariable $v
	 * @param string|null $searchTerm
	 * @param int $userId
	 */
	public function checkSharedQmVariableObjectStructureV4($v, string $searchTerm = null, int $userId = 1){
		$this->assertInstanceOf('stdClass', $v);
		if($userId && $searchTerm){
			$ownVariable = false;
			$cleanedSearchTerm = str_replace(array(
				                                 '*',
				                                 '%'
			                                 ), '', $searchTerm);
			$exactMatch = $v->name === $cleanedSearchTerm;
			if(isset($v->userId)){
				$ownVariable = $userId === $v->userId;
			}
			$this->assertTrue($ownVariable || $v->getIsPublic() || $exactMatch,
			                  "We should not have gotten this variable!");
		}
		$floatAttributes = [
			'fillingValue',
			'kurtosis',
			'maximumAllowedValue',
			'mean',
			'median',
			'minimumAllowedValue',
			'mostCommonValue',
			'secondMostCommonValue',
			'skewness',
			'standardDeviation',
			'thirdMostCommonValue',
			'variance'
		];
		$this->checkFloatAttributes($floatAttributes, $v);
		$intAttributes = [
			'durationOfAction',
			'id',
			'numberOfRawMeasurements',
			'numberOfMeasurements',
			'numberOfUniqueValues',
			'numberOfUserVariables',
			'onsetDelay',
			'unitId',
			'variableCategoryId'
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $v);
		$booleanAttributes = QMVariable::getBooleanAttributes();
		$this->checkBooleanAttributes($booleanAttributes, $v);
		$doesNotHave = [];
		$this->checkDoesNotHaveAttributes($doesNotHave, $v);
		$doesNotHave = [
			'commonUnitAbbreviatedName',
			'commonUnitName',
			//'numberOfUserVariableRelationshipsAsCause',  // Why shouldn't we have this?
			//'numberOfUserVariableRelationshipsAsEffect',
			//            'numberOfAggregatedCorrelationsAsCause',
			//            'numberOfAggregatedCorrelationsAsEffect',
			//            'numberOfGlobalVariableRelationshipsAsCause',
			//            'numberOfGlobalVariableRelationshipsAsEffect',
		];
		$this->checkDoesNotHaveAttributes($doesNotHave, $v);
		$stringAttributes = [
			'combinationOperation',
			'createdAt',
			'ionIcon',
			'name',
			'pngUrl',
			'svgUrl',
			'unitAbbreviatedName',
			'unitName',
			'updatedAt',
			'variableCategoryName',
		];
		$this->checkStringAttributes($stringAttributes, $v);
		$notNullAttributes = [
			'causeOnly',
			'combinationOperation',
			'createdAt',
			'id',
			'ionIcon',
			'manualTracking',
			'name',
			'onsetDelay',
			'pngUrl',
			//'isPublic',
			'svgUrl',
			//'unit',
			'unitAbbreviatedName',
			'unitName',
			'updatedAt',
			'variableCategoryId',
			'variableCategoryName',
			QMCommonVariable::FIELD_OUTCOME,
		];
		$this->checkNotNullAttributes($notNullAttributes, $v);
	}
	/**
	 * Check that the given object has all required variable properties with correct type
	 * @param QMCommonVariable|stdClass $variable
	 * @param string|null $searchTerm
	 * @param int|null $userId
	 * @internal param bool $requirePublic
	 */
	public function checkCommonVariable($variable, string $searchTerm = null, int $userId = null){
		$this->checkSharedQmVariableObjectStructureV3($variable, $searchTerm, $userId);
		$this->assertNotTrue(isset($variable->userId), "user id should not be on a common variable!");
		$stringAttributes = [];
		$this->checkStringAttributes($stringAttributes, $variable);
		$intAttributes = [
			'unitId',
			'numberOfCorrelations',
			'numberOfGlobalVariableRelationshipsAsCause',
			'numberOfGlobalVariableRelationshipsAsEffect',
			'numberOfCorrelations',
			'numberOfUserVariables',
			'numberOfUserVariableRelationshipsAsCause',
			'numberOfUserVariableRelationshipsAsEffect'
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $variable);
		$floatAttributes = [
			'mostCommonValue',
			'secondMostCommonValue',
			'thirdMostCommonValue',
		];
		$this->checkFloatAttributes($floatAttributes, $variable);
		$notNullAttributes = [//'unit',
		];
		$this->checkNotNullAttributes($notNullAttributes, $variable);
		if($searchTerm && $searchTerm !== $variable->name){
			$this->assertEquals(true, $variable->isPublic, "Variable should be public ");
		}
	}
	/**
	 * @param int $expected
	 */
	protected function makeSureVariablesNeedCorrelation(int $expected): void{
		/** @var UserVariable[] $variables */
		$variables = UserVariable::all();
		$this->assertCount($expected, $variables);
		foreach($variables as $uv){
			$this->assertGreaterThan(0, $uv->number_of_unique_daily_values);
			$this->assertGreaterThan(0, $uv->number_of_changes);
			// Kind of slow $this->assertGreaterThan(0, $uv->getSpread());
			$this->assertTrue($uv->needToCorrelate());
			$this->assertEquals(0, $uv->number_of_measurements_with_tags_at_last_correlation);
			$correlations = $uv->getUserVariableIdsToCorrelateWith();
			if(!$correlations){
				$dbm = $uv->getQMUserVariable();
				$dbm->debugCorrelationsQB();
			}
			$this->assertCount(1, $correlations);
		}
	}
	/**
	 * @return int
	 */
	public static function setMeasurementSourceNamesToTestClientId(){
		return QMMeasurement::writable()->whereNull(QMMeasurement::FIELD_SOURCE_NAME)->update([QMMeasurement::FIELD_SOURCE_NAME => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
	}
	/**
	 * @param int $userId
	 * @param MeasurementSet $submittedMeasurementSets
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
	public function saveMeasurementSets($userId, $submittedMeasurementSets){
		foreach($submittedMeasurementSets as $set){$set->clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;}
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		MeasurementSet::saveMeasurementSets($userId, $submittedMeasurementSets);
	}
	public static function deleteUserData(){
		TestDB::deleteUserData();
	}
	/**
	 * @return QMUser
	 */
	protected function getUser(){
		return QMUser::findInMemoryOrDB(1);
	}
	protected function truncateTrackingReminders(){
		QMTrackingReminder::writable()->hardDelete("testing");
	}
	/**
	 * @param string $tableName
	 */
	protected function truncateTable(string $tableName){
		try {
			Writable::getBuilderByTable($tableName)->truncate();
		} catch (Throwable $e){
			QMLog::info(__METHOD__.": ".$e->getMessage());
			Writable::getBuilderByTable($tableName)->delete();
		}
	}
	/**
	 */
	protected function truncateMeasurementsRemindersCorrelationsTables(){
		Memory::resetClearOrDeleteAll();
		$this->truncateTrackingReminders();
		$this->truncateTable(QMMeasurement::TABLE);
		$this->truncateTable(QMUserVariableRelationship::TABLE);
		$this->truncateTable(QMTrackingReminderNotification::TABLE);
		$this->truncateTable(QMUserVariable::TABLE);
		$this->truncateTable(QMCommonTag::TABLE);
		$this->truncateTable(QMUserTag::TABLE);
		$this->truncateTable(QMGlobalVariableRelationship::TABLE);
		//$this->truncateTable(CommonVariable::TABLE); // Can't truncate with foreign keys
		//CommonVariable::writable()->hardDelete(__METHOD__, true);
	}
	/**
	 * @param int $expected
	 */
	protected function assertNumberOfTrackingRemindersEquals(int $expected){
		$reminders = $this->getUser()->getTrackingReminders();
		$numberOfTrackingReminders = count($reminders);
		$this->assertEquals($expected, $numberOfTrackingReminders, \App\Logging\QMLog::print_r($reminders, true));
	}
	/**
	 * @param string|null $variableName
	 * @param string $clientId
	 */
	protected function assertHasTrackingReminderFor(string $variableName, string $clientId){
		$reminders = $this->getUser()->getTrackingReminders();
		$this->assertTrue(count($reminders) > 0);
		$lemonsReminders = $this->getUser()->getTrackingRemindersByVariableName($variableName);
		$this->assertTrue(count($lemonsReminders) > 0, "No reminders for $variableName");
		$this->assertEquals($clientId, $lemonsReminders[0]->getClientId());
	}
	/**
	 * @param string|null $variableName
	 */
	protected function assertDoesNotHaveTrackingReminderFor(string $variableName){
		$reminders = $this->getUser()->getTrackingRemindersByVariableName($variableName);
		$this->assertCountAndPrintIfFalse(0, $reminders);
	}
	/**
	 * @param int $expected
	 * @param array $array
	 */
	protected function assertCountAndPrintIfFalse(int $expected, array $array){
		$count = count($array);
		if($count !== $expected){
			$this->obfuscateAndPrintR($array);
		}
		$this->assertCount($expected, $array);
	}
	protected function makeSureAllUserVariableUnitIdsAreNull(){
		$rows = QMUserVariable::readonly()->getArray();
		foreach($rows as $row){
			$this->assertNull($row->default_unit_id);
		}
	}
	/**
	 * @param $notNullAttributes
	 * @param $object
	 */
	public function checkNotNullAttributes($notNullAttributes, $object){
		foreach($notNullAttributes as $attribute){
			$this->assertNotNull($object->$attribute, "$attribute should not be null");
		}
	}
	/**
	 * @param $intAttributes
	 * @param $object
	 */
	public static function checkIntAttributes(array $intAttributes, $object){
		foreach($intAttributes as $attribute){
			if(!is_object($object)){
				throw new LogicException("Not an object: ".\App\Logging\QMLog::print_r($object, true));
			}
			if(!property_exists($object, $attribute)){
				throw new LogicException("$attribute property does not exist");
			}
			if($object->$attribute !== null && !is_int($object->$attribute)){
				\App\Logging\QMLog::print_r($object->$attribute);
			}
			if(is_string($object->$attribute)){
				throw new LogicException("$attribute should be an integer but ".$object->$attribute . ' is a string');
			}
			if($object->$attribute !== null && !is_int($object->$attribute)){
				throw new LogicException("$attribute should be an integer but " . $object->$attribute . ' is ' . gettype($object->$attribute));
			}
		}
	}
	/**
	 * @param $variableId
	 */
	protected function addDummyMeasurementRowForVariable(int $variableId){
		$uv = UserVariable::whereUserId(1)->where(UserVariable::FIELD_VARIABLE_ID, $variableId)->first();
		//$uv->setAttribute(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, null);
		//$uv->setLatestTaggedMeasurementStartAtAttribute(null);
		//$uv->save();
		if(!$uv){
			return;
		}
		$time = time() - random_int(0, 86400 * 30);
		try {
			Measurement::insert([
				                    Measurement::FIELD_USER_VARIABLE_ID     => $uv->id,
				                    Measurement::FIELD_VARIABLE_ID          => $variableId,
				                    Measurement::FIELD_VALUE                => 1,
				                    Measurement::FIELD_UNIT_ID              => $uv->getCommonUnit()->id,
				                    Measurement::FIELD_ORIGINAL_VALUE       => 1,
				                    Measurement::FIELD_ORIGINAL_UNIT_ID     => $uv->getCommonUnit()->id,
				                    Measurement::FIELD_START_TIME           => $time,
				                    Measurement::FIELD_SOURCE_NAME          => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
				                    Measurement::FIELD_CLIENT_ID            => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
				                    Measurement::FIELD_VARIABLE_CATEGORY_ID => $uv->getVariableCategoryId(),
				                    Measurement::FIELD_USER_ID              => 1,
				                    Measurement::FIELD_CREATED_AT           => date('Y-m-d H:i:s'),
				                    Measurement::FIELD_UPDATED_AT           => date('Y-m-d H:i:s'),
			                    ]);
		} catch (NoChangesException $e){
			QMLog::error(__METHOD__.": ".$e->getMessage());
		}
	}
	protected function addDummyMeasurementsForUserVariableRelationships(){
		$userVariableRelationships = QMUserVariableRelationship::readonly()->getArray();
		foreach($userVariableRelationships as $userVariableRelationship){
			$this->addDummyMeasurementRowForVariable($userVariableRelationship->cause_variable_id);
			try {
				$this->addDummyMeasurementRowForVariable($userVariableRelationship->effect_variable_id);
			} catch (\Throwable $e){
				$this->addDummyMeasurementRowForVariable($userVariableRelationship->effect_variable_id);
			}
		}
	}
	protected function addDummyMeasurementsAndUpdateAggregatedCorrelations(){
		$this->addDummyMeasurementsForUserVariableRelationships();
		QMUserVariableRelationship::writable()->whereNull(QMUserVariableRelationship::FIELD_DATA_SOURCE_NAME)->update([
			                                                                                            QMUserVariableRelationship::FIELD_DATA_SOURCE_NAME => GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER
		                                                                                            ]);
		self::deleteAndRecreateAllAggregatedCorrelations();
		QMDB::flushQueryLogs(__METHOD__);
	}
	/**
	 * @param int $userId
	 * @return QMMeasurement[]
	 * @throws InvalidVariableValueException
	 */
	protected function createMoodMeasurements(int $userId = 1): array {
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$mood = $this->getMoodQMUserVariable($userId);
		$uv = $mood->l();
		$uncombined = $this->generateHighLowMeasurementsForLast120Days();
		$effectMeasurementItems = [];
		foreach($uncombined as $m){
			$effectMeasurementItems[] = $uv->newMeasurementData([
				                                                    Measurement::FIELD_START_TIME     => $m->startTime,
				                                                    Measurement::FIELD_VALUE          => $m->originalValue,
				                                                    Measurement::FIELD_ORIGINAL_VALUE => $m->originalValue,
				                                                    Measurement::FIELD_CLIENT_ID      => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
				                                                    Measurement::FIELD_SOURCE_NAME    => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
			                                                    ]);
		}
		$measurements = $uv->bulkMeasurementInsert($effectMeasurementItems);
		$str = $uv->getDBModel()->getCorrelationDataRequirementAndCurrentDataQuantityString();
		$this->assertNotNull($str);
		return $measurements;
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable
	 */
	protected function getMoodQMUserVariable(int $userId = 1): QMUserVariable {
		$mood = QMUserVariable::findOrCreateByNameOrId($userId, "Overall Mood", [], [
			'variableCategoryName' => EmotionsVariableCategory::NAME,
			'unitName'             => OneToFiveRatingUnit::NAME
		]);
		$cv = $mood->getCommonVariable();
		$cv->l()->assertHasStatusAttribute();
		return $mood;
	}
	/**
	 * @param $days
	 * @return float|int
	 */
	protected function getTimeMinusXDays(int $days){
		$baseTime = strtotime("2020-01-01");
		return $baseTime - $days * 86400;
	}
	/**
	 * @param array $environmentSettings
	 */
	public static function populateServerVariables(array $environmentSettings): void{
		unset($_SERVER['HTTP_AUTHORIZATION']);
		foreach($environmentSettings as $key => $value){
			$_SERVER[$key] = $value;
		}
		$_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
		$_SERVER['REQUEST_URI'] = UrlHelper::addParams($environmentSettings["PATH_INFO"],
		                                                                           $environmentSettings['slim.request.query_hash'] ?? []);
	}
	public static function globalResponseChecks(array $environmentSettings, $body){
		StudyTest::globalStudyChecks($body, $environmentSettings);
	}
	/**
	 * @param string $apiUrl
	 * @param array $params
	 * @param int $expectedStatusCode
	 * @param bool $checkJson
	 * @return mixed
	 */
	public function getAndDecodeBody(string $apiUrl, array $params = [], int $expectedStatusCode = 200,
                                     bool $checkJson = true){
		$response = $this->slimGet($apiUrl, $params, $expectedStatusCode, $checkJson);
		return json_decode($response->getBody(), false);
	}
	/**
	 * @param string $query
	 * @param array $params
	 * @param int|null $userId
	 * @return QMVariable[]|object[]
	 */
	protected function searchVariables(string $query, array $params, int $userId = 1): array {
		if($userId){$this->setAuthenticatedUser($userId);}
        $response = $this->slimGet('api/v1/variables/search/' . $query, $params, 200, false);
        return (array)json_decode($response->getBody());
	}
	/**
	 * @param Response $response
	 * @param null $object
	 * @return string
	 */
	public static function getErrorMessageFromResponse($response, $object = null): string {
		$body = $response->getBody();
		$message = "";
		if($body){$decodedBody = json_decode($body, false);}
		if(isset($decodedBody) && isset($decodedBody->errorMessage)){$message = "Error: ".$decodedBody->errorMessage.' --- '.$message;}
		if(isset($object->updateError)){$message = "Update Error: ".$object->updateError.' --- '.$message;}
		if(isset($object->connectError)){$message = "Connect Error: ".$object->connectError.' --- '.$message;}
		if(isset($object->error)){$message = "Error: ".$object->error;}
		return $message;
	}
	/**
	 * @param $apiUrl
	 * @param $params
	 * @param int $expectedStatusCode
	 * @return Response
	 */
	public function getWithBugsnagDisabled($apiUrl, $params, $expectedStatusCode = 200){
		$this->expectQMException();
		$response = $this->slimGet($apiUrl, $params, $expectedStatusCode);
		return $response;
	}
	/**
	 * @param string $apiUrl
	 * @param array|string $postData
	 * @param null $expectedString
	 * @return object
	 */
	public function deleteViaPostMethod($apiUrl, $postData, $expectedString = null){
		$response = $this->slimPost($apiUrl, $postData, $doNotAddClientId = false, $expectedStatusCode = 204, [], $expectedString);
		return json_decode($response->getBody(), false);
	}
	/**
	 * @param string $apiUrl
	 * @param $postData
	 * @param bool $doNotAddClientId
	 * @param int $expectedStatusCode
	 * @param array $urlQueryParameters
	 * @param string|null $expectedResponseString
	 * @return array|mixed
	 */
	public function postAndGetDecodedBody(string $apiUrl, $postData, bool $doNotAddClientId = false, int $expectedStatusCode = 201, $urlQueryParameters = [],
	                                      string $expectedResponseString = null){
		$response = $this->slimPost($apiUrl, $postData, $doNotAddClientId, $expectedStatusCode, $urlQueryParameters,
		                            $expectedResponseString);
		return json_decode($response->getBody(), false);
	}
	/**
	 * @param string $path
	 * @return bool
	 */
	public function apiVersionIsGreaterThanThree(string $path){
		if(strpos($path, '/v4/') !== false){
			return true;
		}
		if(strpos($path, '/v5/') !== false){
			return true;
		}
		if(strpos($path, '/v6/') !== false){
			return true;
		}
		if(strpos($path, '/v7/') !== false){
			return true;
		}
		return false;
	}
	/**
	 * @param string $variableName
	 * @return QMMeasurement[]
	 */
	public function getMeasurementsForVariable($variableName){
		$this->setAuthenticatedUser(1);
		$body = $this->getAndDecodeBody("/api/v1/measurements", ['variableName' => $variableName]);
		return $body;
	}
	/**
	 * @param $response
	 */
	public function checkDeletionResponse($response){
		$this->assertResponseBodyContains('204', $response);
		$this->assertResponseBodyContains('"success":true', $response);
	}
	/**
	 * @param string $apiUrl
	 * @param Response $response
	 * @return mixed|QMResponseBody
	 */
	public static function decodeBodyAndLogErrors(string $apiUrl, $response){
		$decodedBody = json_decode($response->getBody(), false);
		if($decodedBody && !is_array($decodedBody) && isset($decodedBody->errors)){
			foreach($decodedBody->errors as $qmError){
				if(is_object($qmError) || is_array($qmError)){
					QMLog::error("$apiUrl error: ".\App\Logging\QMLog::print_r($qmError, true));
				} else {
					QMLog::error("$apiUrl error: ".$qmError);
				}
			}
			if(isset($decodedBody->message) && !empty($decodedBody->message)){
				ConsoleLog::info("\n\n$apiUrl response message:\n".$decodedBody->message, [],  false);
			}
		}
		return $decodedBody;
	}
	/**
	 * @return QMUser[]
	 */
	protected function getUsersRequest(){
		$body = $this->getAndDecodeBody('v1/users', []);
		return $body->users;
	}
	/**
	 * @param int $userId
	 * @param array $params
	 * @return QMUser
	 */
	protected function getUserRequest(int $userId = 1, array $params = []){
		$this->setAuthenticatedUser($userId);
		$user = $this->getAndDecodeBody('v1/user', $params);
		return $user;
	}
	/**
	 * @return QMUser
	 */
	protected function getUserRequestWithClientParams(){
		$user = $this->getUserRequest(1, [
			'limit'      => 200,
			'appName'    => 'MoodiModo',
			'appVersion' => '2.1.1.0',
			'client_id'  => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
		]);
		return $user;
	}
	/**
	 * @param array $params
	 * @param int $expectedCount
	 * @param string $message
	 * @return QMMeasurement[]
	 */
	protected function getMeasurements(array $params, int $expectedCount = null, string $message = ''): array {
		if(!QMAuth::getQMUserIfSet()){
            $this->setAuthenticatedUser(1);
        }
        $measurements = $this->getAndDecodeBody('/api/v1/measurements', $params);
		if ($expectedCount !== null) {
			$this->assertCount($expectedCount, $measurements, $message);
		}
		foreach ($measurements as $m) {
			$this->checkMeasurementPropertyTypes($m);
		}
		if (isset($params['limit'])) {
			$this->assertLessThanOrEqual($params['limit'], count($measurements), $message);
		}
		return $measurements;
	}
	protected function deleteMeasurementsAndReminders(){
		Measurement::deleteAll();
		TrackingReminder::deleteAll();
		TrackingReminderNotification::deleteAll();
		UserVariableClient::deleteAll();
		UserClient::deleteAll();
	}
	/**
	 * @param int $defaultValue
	 * @param int $frequency
	 * @return QMTrackingReminderNotification[]
	 * @throws InvalidTimestampException
	 */
	public function generateOverallMoodReminderNotifications($defaultValue = 2, $frequency = 86400){
		$this->deleteMeasurementsAndReminders();
		$trackingReminder = [
			'variableId'        => 1398,
			'reminderFrequency' => $frequency,
			'defaultValue'      => $defaultValue
		];
		$response = $this->postAndCheckTrackingRemindersResponse($trackingReminder);
		// What was this for?
		//TrackingReminderNotification::whereId(1)->update(['tracking_reminder_id' => 2]);
		//TrackingReminder::whereId(1)->update(['id' => 2]);
		/** @var array $trackingReminderNotifications */
		$fromDatabase = QMTrackingReminderNotification::readonly()->getArray();
		$this->assertCount(1, $fromDatabase);
		foreach($fromDatabase as $trn){
			$this->assertNotNull($trn->notify_at);
		}
		$notDeleted =
			QMTrackingReminderNotification::readonly()->whereNull(QMTrackingReminderNotification::FIELD_DELETED_AT)->getArray();
		$this->assertCount(1, $notDeleted);
		$notifications = QMTrackingReminderNotification::getTrackingReminderNotifications(1, []);
		$this->assertCount(1, $notifications);
		$body = $this->getAndDecodeBody('/api/v1/trackingReminderNotifications', []);
		$fromApi = $body->data;
		$this->assertCount(1, $fromApi, "No notifications from v1/trackingReminderNotifications even though we have " .
		                                count($fromDatabase) . " in the database!");
		/** @var QMTrackingReminderNotification[] $fromApi */
		foreach($fromApi as $trn){
			$this->assertEquals(1398, $trn->variableId);
			$this->assertEquals($defaultValue, $trn->defaultValue);
			$this->assertEquals(86400, $trn->reminderFrequency);
			$this->assertEquals('/5', $trn->unitAbbreviatedName);
			$this->assertEquals('Overall Mood', $trn->variableName);
			$this->assertEquals('Emotions', $trn->variableCategoryName);
			$this->assertEquals('MEAN', $trn->combinationOperation);
			$this->assertNotNull($trn->trackingReminderNotificationTimeLocal);
		}
		$this->checkTrackingReminderNotificationProperties($fromApi, null);
		return $fromApi;
	}
	/**
	 * @param bool $correlateOverTime
	 * @return QMUserVariableRelationship
	 */
	public function calculateCorrelation(bool $correlateOverTime = true){
		$userId = 1;
		$this->setAuthenticatedUser($userId);
		$cause = $this->getCauseUserVariable();
		$this->assertEquals(1800, $cause->getOnsetDelay());
		$cause->analyzeFullyIfNecessary(__FUNCTION__);
		$causeMeasurements = $cause->getDailyMeasurementsWithTagsAndFilling();
		$this->assertGreaterThan(50, $causeMeasurements);
		$effect = $this->getEffectUserVariable();
		lei($effect->onsetDelay === null);
		$effect->analyzeFullyIfNecessary(__FUNCTION__);
		$allCorrelationObjects = $effect->calculateCorrelationsIfNecessary();
		$correlations = QMUserVariableRelationship::getUserVariableRelationships(['userId' => $userId]);
		$correlations[0]->addStudyHtmlChartsImages();
		$this->assertCount(1, $correlations,
		                   'We should only have 1 correlation for CauseVariableName and EffectVariableName');
		$this->checkCalculatedCorrelationObject($correlations[0], $correlateOverTime);
		/** @var UserVariableRelationship $l */
		$l = UserVariableRelationship::find($correlations[0]->id);
		$this->assertIsString($l->reason_for_analysis);
		$this->assertIsString($l->analysis_ended_at->toString());
		return $correlations[0];
	}
	/**
	 * @param $idOrName
	 * @param array $newVariableParams
	 * @return QMUserVariable
	 */
	public function getUserVariable($idOrName, array $newVariableParams = []): QMUserVariable{
		$uv = QMUserVariable::findOrCreateByNameOrId($this->getUser()->getId(), $idOrName, [] , $newVariableParams);
		lei(!$uv->variableName);
		$this->assertEquals($uv->displayName, $uv->getTitleAttribute());
		lei($uv->onsetDelay === null);
		return $uv;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getCauseUserVariable(): QMUserVariable{
		$v = $this->getUserVariable('CauseVariableName', [
			QMCommonVariable::FIELD_VARIABLE_CATEGORY_ID  => TreatmentsVariableCategory::ID,
			QMCommonVariable::FIELD_DEFAULT_UNIT_ID       => MilligramsUnit::ID,
			QMCommonVariable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_SUM,
		]);
		$this->assertTrue($v->isPredictor());
		$this->assertFalse($v->isOutcome());
		$this->assertZeroFillingValue($v);
		$this->assertEquals(1, $v->getInterestingFactor());
		return $v;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getCausePurchaseUserVariable(): QMUserVariable{
		$v = $this->getUserVariable(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX.' CauseVariableName', [
			QMCommonVariable::FIELD_VARIABLE_CATEGORY_ID  => TreatmentsVariableCategory::ID,
			QMCommonVariable::FIELD_DEFAULT_UNIT_ID       => DollarsUnit::ID,
			QMCommonVariable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_SUM,
		]);
		$this->assertEquals(QMCommonVariable::PURCHASE_DURATION_OF_ACTION, $v->durationOfAction);
		return $v;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getEffectUserVariable(): QMUserVariable{
		$v = $this->getUserVariable('EffectVariableName', [
			QMCommonVariable::FIELD_VARIABLE_CATEGORY_ID  => SymptomsVariableCategory::ID,
			QMCommonVariable::FIELD_DEFAULT_UNIT_ID       => PercentUnit::ID,
			QMCommonVariable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_MEAN,
		]);
		lei($v->onsetDelay === null);
		$this->assertTrue($v->isPredictor());
		$this->assertTrue($v->isOutcome());
		$this->assertNoFillingValue($v);
		$this->assertEquals(1, $v->getInterestingFactor());
		return $v;
	}
	/**
	 * @param string|null $type
	 * @param int $expectedCode
	 * @param int|null $userId
	 * @return QMCohortStudy|QMPopulationStudy|QMUserStudy
	 */
	public function createStudyTypeAndCheckResponse(string $type = null, int $expectedCode = 201, int $userId = null){
		if($userId){
			$this->setAuthenticatedUser($userId);
		}
		if(!$userId){$expectedCode = 401;}
		$requestData = [
			'causeVariableName'  => BupropionSrCommonVariable::NAME,
			'effectVariableName' => BackPainCommonVariable::NAME,
			'studyName'          => "Super study",
			'type'               => $type
		];
		if($userId){
			$this->assertUserIsLoggedIn($userId);
		}
		$body = $this->postAndGetDecodedBody('/api/v1/study/create', $requestData, false, $expectedCode);
		if(!$userId){return $body;}
		$study = $body->study;
		$this->getStudyById($study->id, $userId);
		return $study;
	}
	/**
	 * @param string $studyId
	 * @param int|null $userId
	 * @return void
     */
	private function getStudyById(string $studyId, int $userId = null): void
    {
		if($userId){$this->setAuthenticatedUser($userId);}
		/** @var QMStudy $study */
		$study = $this->getAndDecodeBody('v3/study', ['studyId' => $studyId]);
		$this->assertEquals($studyId, $study->id);
		//$row = Study::readonly()->where(Study::FIELD_ID, $studyId)->first();
		//$this->assertTrue(!empty($row->statistics));
	    if($userId){$this->setAuthenticatedUser($userId);}
		$body = $this->getAndDecodeBody('v4/studies', ['studyId' => $studyId]);
		$studies = $body->studies;
		$this->assertEquals($studyId, $studies[0]->id);
    }

	/**
	 * @param int $expectedCount
	 * @return QMStudy[]
	 */
	public function checkStudiesCreatedCount(int $expectedCount){
		$studies = $this->getStudiesCreated();
		$this->assertCount($expectedCount, $studies);
		return $studies;
	}
	/**
	 * @return QMStudy[]
	 */
	public function getStudiesCreated(){
		$this->setAuthenticatedUser(1);
		return $this->getAndDecodeBody('v1/studies/created')->studies;
	}
	/**
	 * Check that the given object has all required variable properties with correct type
	 * @param QMUserVariable|QMCommonVariable|stdClass $uv
	 */
	public function checkUserVariableObjectStructureV3($uv){
		$this->checkSharedQmVariableObjectStructureV3($uv);
		if(isset($uv->userUnitAbbreviatedName)){
			$this->checkStringAttributes(['userUnitAbbreviatedName'], $uv);
		}
		$intAttributes = [
			'id',
			'unitId',
			'numberOfCorrelations',
			//'numberOfProcessedDailyMeasurements',
			'numberOfRawMeasurements',
			'numberOfMeasurements',
			'numberOfUniqueDailyValues',
			'numberOfUserVariableRelationshipsAsCause',
			'numberOfUserVariableRelationshipsAsEffect',
			'numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis',
			'earliestMeasurementTime',
			'latestMeasurementTime',
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $uv);
		$floatAttributes = [
			'mean',
			'median',
			'mostCommonValue',
			'skewness',
			'standardDeviation',
			'variance',
			'lastValue',
		];
		$this->checkFloatAttributes($floatAttributes, $uv);
		$notNullAttributes = [
			//'unit',
			//'userUnitAbbreviatedName'
		];
		$this->checkNotNullAttributes($notNullAttributes, $uv);
		if($uv->latestTaggedMeasurementTime){
			self::assertDateLessThanOrEqual($uv->latestFillingTime, $uv->latestTaggedMeasurementTime,
			                                'latestFillingTime', 'latestTaggedMeasurementTime');
		}
		// Why can't latestFillingTime be less than latestSourceTime?
		//        $this->assertFalse($userVariable->latestFillingTime < $userVariable->latestSourceTime,
		//            "latestFillingTime $userVariable->latestFillingTime should not be less than latestSourceTime $userVariable->latestSourceTime");
		if($uv->earliestTaggedMeasurementTime){
			static::assertDateGreaterThanOrEqual($uv->earliestFillingTime, $uv->earliestTaggedMeasurementTime,
			                                     'earliestFillingTime', 'earliestTaggedMeasurementTime',
			                                     "Earliest filling time $uv->earliestFillingTime should not be greater than earliest measurement time $uv->earliestTaggedMeasurementTime");
		}
		// I think we're not going by source times anymore
		//$this->assertFalse($userVariable->earliestFillingTime > $userVariable->earliestSourceTime,
		//    "Earliest filling time $userVariable->earliestFillingTime should not be greater than earliest source time $userVariable->earliestSourceTime");
	}
	/**
	 * @param QMUserVariable|QMVariable $uv
	 */
	public function checkUserVariableObjectStructureV4($uv){
		$this->checkSharedQmVariableObjectStructureV4($uv);
		$doesNotHave = [
			//'numberOfUserVariableRelationshipsAsCause', // Why not?
			//'numberOfUserVariableRelationshipsAsEffect',
		];
		$this->checkDoesNotHaveAttributes($doesNotHave, $uv);
		$stringAttributes = [
			'unitAbbreviatedName'
		];
		$this->checkStringAttributes($stringAttributes, $uv);
		$intAttributes = [
			'id',
			'unitId',
			'numberOfCorrelations',
			'numberOfProcessedDailyMeasurements',
			'numberOfRawMeasurements',
			'numberOfMeasurements',
			'numberOfUniqueDailyValues',
			'numberOfCorrelationsAsCause',
			'numberOfCorrelationsAsEffect',
			'numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis',
			'earliestTaggedMeasurementTime',
			'latestTaggedMeasurementTime',
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $uv);
		$floatAttributes = [
			'mean',
			'median',
			'mostCommonValue',
			'skewness',
			'standardDeviation',
			'variance',
			'lastValue',
		];
		$this->checkFloatAttributes($floatAttributes, $uv);
		$notNullAttributes = [
			//'unit',
			'unitAbbreviatedName',
			'availableUnitNames'
		];
		$this->checkNotNullAttributes($notNullAttributes, $uv);
		if($uv->latestTaggedMeasurementTime){
			self::assertDateGreaterThanOrEqual($uv->latestFillingTime, $uv->latestTaggedMeasurementTime, 'latestFillingTime', 'latestTaggedMeasurementTime');
		}
		// Why not $userVariable->latestFillingTime < $userVariable->latestSourceTime?
		//$this->assertFalse($userVariable->latestFillingTime < $userVariable->latestSourceTime);
		if($uv->earliestTaggedMeasurementTime){
			$this->assertDateLessThanOrEqual($uv->earliestFillingTime, $uv->earliestTaggedMeasurementTime);
		}
		if($uv->earliestSourceTime){
			$this->assertDateLessThanOrEqual($uv->earliestFillingTime, $uv->earliestSourceTime);
		}
	}
	/**
	 * @param QMGlobalVariableRelationship|QMVariableRelationship $ac
	 * @param int $minimumUsers
	 */
	public function checkAggregatedCorrelationProperties($ac, $minimumUsers = 0){
		/** @var QMVariableRelationship $ac */
		$ac = (object)$ac;
		$this->checkSharedCorrelationProperties($ac);
		$this->assertNotTrue(isset($ac->userId), "Should not have userId!");
		$this->assertGreaterThan('0000-00-00 00:00:00', $ac->createdAt);
		$this->assertGreaterThan('0000-00-00 00:00:00', $ac->updatedAt);
		$this->assertGreaterThan(0, $ac->aggregateQMScore);
		$this->checkIntStringAndFloatsOnGlobalVariableRelationship($ac, $minimumUsers);
		$notNullAttributes = [
			'numberOfCorrelations',
			'numberOfUsers',
			'gaugeImage',
			'studyObjective',
			'studyAbstract',
		];
		$this->checkNotNullAttributes($notNullAttributes, $ac);
	}
	/**
	 * @param QMGlobalVariableRelationship $ac
	 * @param int $minimumUsers
	 * @param bool $studyCorrelation
	 */
	public function checkAggregatedCorrelationV4Properties($ac, int $minimumUsers = 0, bool $studyCorrelation = false){
		if(is_array($ac)){$ac = new QMGlobalVariableRelationship($ac);}
		if(!is_object($ac)){
			throw new LogicException("Provided aggregatedCorrelation is not an object and is: ".\App\Logging\QMLog::print_r($ac));
		}
		$this->checkSharedCorrelationV4Properties($ac, $studyCorrelation);
		$this->assertGreaterThan('0000-00-00 00:00:00', $ac->createdAt);
		$this->assertGreaterThan('0000-00-00 00:00:00', $ac->updatedAt);
		$this->assertGreaterThan(0, $ac->aggregateQMScore, "aggregateQMScore should be greater than 0!");
		$this->checkIntStringAndFloatsOnGlobalVariableRelationship($ac, $minimumUsers);
		$notNullAttributes = [
			'numberOfCorrelations',
			'numberOfUsers'
		];
		$this->checkNotNullAttributes($notNullAttributes, $ac);
	}
	/**
	 * @param QMUserVariableRelationship $correlation
	 */
	public function checkUserVariableRelationshipObject($correlation){
		if(is_array($correlation)){
			$correlation = (object)$correlation;
		}
		$this->checkSharedCorrelationProperties($correlation);
		$intAttributes = [
			'userId',
			'userVote'
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $correlation);
		$stringAttributes = [
			'studyLinkDynamic',
		];
		$this->checkStringAttributes($stringAttributes, $correlation);
		$floatAttributes = [
			'predictsLowEffectChange',
			'predictsHighEffectChange'
		];
		$this->checkFloatAttributes($floatAttributes, $correlation);
		$notNullAttributes = [
			'userId',
			'studyLinkDynamic',
			'gaugeImage',
		];
		$this->checkNotNullAttributes($notNullAttributes, $correlation);
	}
	/**
	 * @param QMVariableRelationship $correlation
	 * @param bool $correlateOverTime
	 */
	public function checkCalculatedCorrelationObject(QMVariableRelationship $correlation, bool $correlateOverTime = true){
		$this->checkSharedCorrelationV4Properties($correlation);
		$intAttributes = [];
		if($correlateOverTime){
			$intAttributes[] = 'onsetDelayWithStrongestPearsonCorrelation';
		}
		DBUnitTestCase::checkIntAttributes($intAttributes, $correlation);
		$this->checkNotNullAttributes($intAttributes, $correlation);
		$stringAttributes = [];
		$this->checkStringAttributes($stringAttributes, $correlation);
		$this->checkNotNullAttributes($stringAttributes, $correlation);
		$floatAttributes = [
			'confidenceInterval',
			'correlationCoefficient',
			'optimalPearsonProduct',
			'predictsHighEffectChange',
			'predictsLowEffectChange',
			//'reversePearsonCorrelationCoefficient',
			'statisticalSignificance',
			//'forwardSpearmanCorrelationCoefficient',
		];
		if($correlateOverTime){
			$floatAttributes[] = 'strongestPearsonCorrelationCoefficient';
			$floatAttributes[] = 'pearsonCorrelationWithNoOnsetDelay';
			$floatAttributes[] = 'averageForwardPearsonCorrelationOverOnsetDelays';
			$floatAttributes[] = 'averageReversePearsonCorrelationOverOnsetDelays';
		}
		$this->checkFloatAttributes($floatAttributes, $correlation);
		$this->checkNotNullAttributes($floatAttributes, $correlation);
		$this->checkNotNullAttributes($this->getNotNullAttributes(), $correlation);
		Memory::flush();
	}
	/**
	 * @return array
	 */
	private function getNotNullAttributes(){
		return [
			'avgDailyValuePredictingHighOutcome',
			'avgDailyValuePredictingLowOutcome',
		];
	}
	/**
	 * @param $correlation
	 */
	public function checkCorrelationObjectWithoutStudy($correlation){
		$correlation = (object)$correlation;
		$intAttributes = [
			'causeVariableCommonUnitId',
			'causeVariableCategoryId',
			'durationOfAction',
			'effectVariableCommonUnitId',
			'effectVariableCategoryId',
			'numberOfPairs',
			'onsetDelay',
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $correlation);
		$this->checkNotNullAttributes($intAttributes, $correlation);
		$stringAttributes = [
			//'cause',
			'causeVariableCategoryName',
			'causeVariableName',
			'confidenceLevel',
			'direction',
			//'effect',
			'effectVariableCategoryName',
			'effectSize',
			'effectVariableName',
			'causeVariableName',
			'effectVariableName',
			'analysisEndedAt',
			'reasonForAnalysis'
		];
		$this->checkStringAttributes($stringAttributes, $correlation);
		$this->checkNotNullAttributes($stringAttributes, $correlation);
		$floatAttributes = [
			'correlationCoefficient',
			'optimalPearsonProduct',
			'statisticalSignificance',
			'durationOfActionInHours',
			'onsetDelayInHours'
		];
		$this->checkFloatAttributes($floatAttributes, $correlation);
		$this->checkNotNullAttributes($floatAttributes, $correlation);
		$this->checkNotNullAttributes($this->getNotNullAttributes(), $correlation);
	}
	/**
	 * @param QMVariableRelationship $c
	 */
	public function checkSharedCorrelationProperties($c){
		if(is_array($c)){$c = (object)$c;}
		//$this->checkCorrelationCharts($correlation);
		$intAttributes = [
			'causeVariableCommonUnitId',
			'causeVariableCategoryId',
			'durationOfAction',
			'effectVariableCommonUnitId',
			'effectVariableCategoryId',
			'numberOfPairs',
			'onsetDelay',
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $c);
		$this->checkNotNullAttributes($intAttributes, $c);
		$stringAttributes = [
			//'cause',
			'causeVariableCategoryName',
			'causeVariableName',
			'confidenceLevel',
			//'dataAnalysis',
			'direction',
			//'effect',
			'effectVariableCategoryName',
			'effectSize',
			'effectVariableName',
			'causeVariableName',
			'effectVariableName',
			'predictorExplanation',
			//'significanceExplanation',
			'strengthLevel',
			'studyAbstract',
			//'studyBackground',
			'studyDesign',
			//'studyLimitations', // Too slow because it makes Vote queries
			'studyObjective',
			//'studyResults',
			'studyTitle',
			//'studyHtml'
		];
		$this->checkStringAttributes($stringAttributes, $c);
		$this->checkNotNullAttributes($stringAttributes, $c);
		$floatAttributes = [
			'confidenceInterval',
			'correlationCoefficient',
			'optimalPearsonProduct',
			'predictsHighEffectChange',
			'predictsLowEffectChange',
			//'reversePearsonCorrelationCoefficient',
			'statisticalSignificance',
			'durationOfActionInHours',
			'onsetDelayInHours'
			//'forwardSpearmanCorrelationCoefficient',
		];
		$this->checkFloatAttributes($floatAttributes, $c);
		$this->checkNotNullAttributes($floatAttributes, $c);
		$this->checkNotNullAttributes($this->getNotNullAttributes(), $c);
	}
	/**
	 * @param QMUserVariableRelationship|QMGlobalVariableRelationship|object $correlation
	 * @param bool $studyCorrelation
	 */
	public function checkSharedCorrelationV4Properties($correlation, $studyCorrelation = false){
		if(is_array($correlation)){
			$correlation = (object)$correlation;
		}
		if(!is_object($correlation)){
			throw new LogicException("Provided correlation is not an object and is: ".\App\Logging\QMLog::print_r($correlation));
		}
		//$this->checkCorrelationCharts($correlation);
		$intAttributes = [
			//'causeVariableCommonUnitId',
			//'causeVariableCategoryId',
			'durationOfAction',
//			'effectVariableCommonUnitId',
//			'effectVariableCategoryId',
			'numberOfPairs',
			'onsetDelay',
		];
		DBUnitTestCase::checkIntAttributes($intAttributes, $correlation);
		$this->checkNotNullAttributes($intAttributes, $correlation);
		$stringAttributes = [
			//'cause',
			'causeVariableCategoryName',
			'causeVariableName',
			'confidenceLevel',
			'direction',
			//'effect',
			'effectVariableCategoryName',
			'effectSize',
			'effectVariableName',
			'causeVariableName',
			'effectVariableName',
			'strengthLevel',
		];
		$this->checkStringAttributes($stringAttributes, $correlation);
		$this->checkNotNullAttributes($stringAttributes, $correlation);
		$floatAttributes = [
			'confidenceInterval',
			'correlationCoefficient',
			'optimalPearsonProduct',
			'predictsHighEffectChange',
			'predictsLowEffectChange',
			//'reversePearsonCorrelationCoefficient',
			'statisticalSignificance',
			'durationOfActionInHours',
			'onsetDelayInHours'
			//'forwardSpearmanCorrelationCoefficient',
		];
		$this->checkFloatAttributes($floatAttributes, $correlation);
		$this->checkNotNullAttributes($floatAttributes, $correlation);
		$notNullAttributes = [
			'studyText',
			'studyImages',
			'studyLinks'
		];
		if(!$studyCorrelation){
			$this->checkNotNullAttributes($notNullAttributes, $correlation);
		}
		$this->checkNotNullAttributes($this->getNotNullAttributes(), $correlation);
	}
	/**
	 * @param $stringAttributes
	 * @param $object
	 */
	public function checkStringAttributes($stringAttributes, $object){
		foreach($stringAttributes as $attribute){
			if(!is_string($object->$attribute)){
				QMLog::info("$attribute should be a string but is ".gettype($object->$attribute));
			}
			$this->assertIsString($object->$attribute,
			                          "$attribute should be a string but " . $object->$attribute . ' is ' . gettype($object->$attribute));
		}
	}
	/**
	 * @param $attributes
	 * @param $object
	 */
	public function checkDoesNotHaveAttributes($attributes, $object){
		foreach($attributes as $attribute){
			$this->assertNotTrue(isset($object->$attribute), "Should not have $attribute attribute");
		}
	}
	public function savePositivelyCorrelatedCauseAndEffectMeasurements(){
		$causeMeasurementItems = [];
		$effectMeasurementItems = [];
		for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
			$measurementValue = random_int(0, 100);
			$causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
			$effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
		}
		$this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
	}
	/**
	 * @return QMUserVariableRelationship
	 * @throws BadRequestException
	 */
	public function seedWithPositiveRandomizedPredictiveCorrelationOverTime(){
		QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
		$causeMeasurementItems = [];
		$effectMeasurementItems = [];
		$min = SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
		for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
			$measurementValue = random_int(0, 100);
			$causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
			$effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * ($i + 1) + $min, $measurementValue);
		}
		$this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
		$correlation = $this->calculateCorrelation();
		// For some reason, getProcessedDailyMeasurementsWithTagsJoinsChildrenInCommonUnitWithinTimeRange brings strongestPearsonCorrelationCoefficient below 1
		//$this->assertEquals(1, $correlation->strongestPearsonCorrelationCoefficient);
		$this->assertGreaterThan(0.85, $correlation->strongestPearsonCorrelationCoefficient);
		$this->assertEquals(86400, $correlation->onsetDelayWithStrongestPearsonCorrelation);
		$study = $this->getUserStudyV4();
		$this->assertContains('higher', strtolower($study->studyText->studyTitle));
		return $correlation;
	}

    /**
     * @param array|null $params
     * @param bool $expectPopulationStudy
     * @return QMUserStudy|QMPopulationStudy
     */
	public function getUserStudyV4(array $params = null, bool $expectPopulationStudy = false){
		if(!$params){
			$params = [
				'causeVariableName'  => 'CauseVariableName',
				'effectVariableName' => 'EffectVariableName'
			];
		}
		$response = $this->slimGet('/api/v4/study', $params);
		if($expectPopulationStudy){
			/** @noinspection PhpParamsInspection */
			return $this->checkPopulationStudyV4($response);
		}
		return DBUnitTestCase::checkUserStudyWithData($response);
	}
	/**
	 * @param Response|QMStudy $response
	 * @return mixed
	 */
	public static function checkUserStudyWithData($response){
		$s = self::checkUserStudyWithOrWithoutData($response);
		/** @var StudyText $studyText */
		$studyText = $s->studyText;
		QMStr::assertStringDoesNotContain($studyText->studyAbstract, "couldn't determine",
            "Study contains text: couldn't determine");
		self::assertNotSame($s->statistics->avgDailyValuePredictingHighOutcome, $s->statistics->avgDailyValuePredictingLowOutcome);
		return $s;
	}
	/**
	 * @param StudyHtml $sh
	 * @param bool $public
	 */
	public static function checkStudyChartHtml($sh, bool $public = false){
		$requireLinkedImagesOnPublic = false; // Too slow to constantly check S3 for images
        //self::assertNotNull($studyHtml->chartHtmlWithEmbeddedImages, "Missing chartHtmlWithEmbeddedImages!");
		//HtmlHelper::checkForMissingHtmlClosingTags($studyHtml->chartHtmlWithEmbeddedImages, 'chartHtmlWithEmbeddedImages');
		if(QMStudy::USE_STATIC_CHART_IMAGES){
			QMStr::assertStringContains($sh->fullStudyHtml, [
                'class="chart-img"',
                ImageHelper::CHART_IMAGE_STYLES,
                //'src="data:image/png;base64,'
            ], AppMode::getCurrentTestName() . "-" . __FUNCTION__);
		} else {
			QMStr::assertStringContains($sh->fullStudyHtml, [
                'new Highcharts.Chart',
            ], AppMode::getCurrentTestName() . "-" . __FUNCTION__);
		}
	}
	/**
	 * @return QMUserVariableRelationship
	 * @throws BadRequestException
	 */
	public function seedWithUncorrelatedCorrelationOverTime(){
		QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
		$causeMeasurementItems = [];
		$effectMeasurementItems = [];
		for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
			$causeMeasurementValue = random_int(0, 100);
			$causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $causeMeasurementValue);
			$effectMeasurementValue = random_int(0, 100);
			$effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $effectMeasurementValue);
		}
		$this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
		$uncorrelated = $this->calculateCorrelation();
		$this->assertEquals(1, $uncorrelated->getInterestingFactor());
		$this->assertBetween(0.3, 0.35, $uncorrelated->statisticalSignificance);
		$this->assertBetween(-0.5, 0.5, $uncorrelated->correlationCoefficient,
		                     "These are random so highly unlikely to be greater than 0.5 correlation");
		$this->assertLessThan(0.5, $uncorrelated->qmScore,
		                      "These are random so highly unlikely to be greater than 0.5 correlation");
		return $uncorrelated;
	}
	public function assertBetween(float $min, float  $max, float $actual, string $message = ''){
		$this->assertLessThan($max, $actual,$message);
		$this->assertGreaterThan($min, $actual, $message);
	}
	/**
	 * @return QMUserVariableRelationship
	 * @throws BadRequestException
	 */
	public function seedWithNegativeLinearPredictiveCorrelation(){
		$causeMeasurementItems = [];
		$effectMeasurementItems = [];
		for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
			$measurementValue = $i;
			$causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
			$effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * ($i + 1), -$measurementValue);
		}
		Measurement::deleteAll();
		$this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
		$correlation = $this->calculateCorrelation();
		return $this->checkNegativeCorrelation($correlation);
	}
	/**
	 * @return QMUserVariableRelationship
	 * @throws BadRequestException
	 */
	public function seedWithPositiveLinearPredictiveCorrelation(){
		QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
		$this->seedWithPositiveLinearCauseEffectMeasurements();
		$user = $this->getCauseUserVariable()->getQMUser();
		$user->analyzeFully(__FUNCTION__);
		$this->assertEquals(1, UserVariableRelationship::count(), "We should have 1 correlation!");
		$correlations = QMUserVariableRelationship::getUserVariableRelationships([]);
		$this->checkOptimalValueMessageAndStudyCardsForUserVariables();
		$this->assertEquals(1, $correlations[0]->strongestPearsonCorrelationCoefficient);
		return $this->checkCorrelationPropertiesAndStudyText($correlations[0]);
	}
	/**
	 * @return QMUserVariableRelationship
	 * @throws BadRequestException
	 */
	public function seedWithPositivePurchaseCorrelation(): QMUserVariableRelationship {
		QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
		$causeMeasurements = [];
		$effectMeasurements = [];
		$latestEffectMeasurementAt = null;
		$baseTime = Stats::roundToNearestMultipleOf(self::BASE_TIME, SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS);
		$earliestSourceTime = $earliestEffectMeasurementTime = $baseTime + self::DAY;
		for($i = 0; $i < 100; $i++){
			$measurementValue = $i;
			$time = $baseTime + self::DAY * ($i + 1);
			$effectMeasurements[] = new QMMeasurement($baseTime + self::DAY * ($i + 1), $measurementValue);
			$latestEffectMeasurementAt = db_date($time);
		}
		$earliestCauseMeasurementTime = $baseTime + self::DAY * 50;
		$latestCauseMeasurementTime = $baseTime + self::DAY * 75;
		$causeMeasurements[] = new QMMeasurement($earliestCauseMeasurementTime, 1);
		$causeMeasurements[] = new QMMeasurement($latestCauseMeasurementTime, 1);
		$cause = $this->getCausePurchaseUserVariable();
		BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
		$cause->saveMultipleMeasurements($causeMeasurements);
		$this->assertNotNull(UserVariableClient::whereUserVariableId($cause->id)->first());
		$effect = $this->getEffectUserVariable();
		$effect->saveMultipleMeasurements($effectMeasurements);
		$this->assertNotNull(UserVariableClient::whereUserVariableId($effect->id)->first());
		$this->assertEquals(2, UserVariableClient::count());
		$cause->analyzeFully(__FUNCTION__);
		$effect->analyzeFully(__FUNCTION__);
		$this->assertDateEquals($effect->l()->latest_source_measurement_start_at, UserVariable::find($effect->id)->latest_source_measurement_start_at,
		                        '$effect->l()->latest_source_measurement_start_at', 'latest_source_measurement_start_at from DB');
		$user = $cause->getQMUser();
		$sources = $user->getUserDataSources();
		$dataSources = $cause->getDataSourceNames();
		$this->assertGreaterThan(0, count($dataSources), "There should be data sources for cause!");
		$earliestCauseSourceTime = $cause->l()->earliest_source_measurement_start_at;
		$this->assertDateEquals($earliestEffectMeasurementTime, $earliestCauseSourceTime);
		$latestSourceAt = $cause->l()->latest_source_measurement_start_at;
		$this->assertDateEquals($latestEffectMeasurementAt, $latestSourceAt, 'latestEffectMeasurementTime', 'latestSourceTime');
		$dailyCause = $cause->getDailyMeasurementsWithTagsAndFilling();
		foreach($dailyCause as $measurement){if($measurement->getValue() > 1){le("Cause value should not exceed 1!");}}
		$this->assertDateEquals($cause->l()->latest_source_measurement_start_at, $effect->l()->latest_source_measurement_start_at,
		                        'cause->getLatestSourceAt', 'effect->getLatestSourceAt', "They both have same source");
		ThisComputer::setMaximumPhpExecutionTimeLimit(60);
		$c = $this->calculateCorrelation(false);
		if($c->getOrSetCauseQMVariable()->getUserOrCommonUnit()->name === DollarsUnit::NAME){
			$this->assertEquals(QMCommonVariable::PURCHASE_DURATION_OF_ACTION, $c->durationOfAction);
		}
		$cause = $c->getOrSetCauseQMVariable();
		$effect = $c->getOrSetEffectQMVariable();
		$cAt = $cause->getEarliestFillingAt();
		$earliestCauseFillingTime = strtotime($cAt);
		self::assertDateEquals($earliestSourceTime, $cause->l()->earliest_source_measurement_start_at,
		                       '$earliestSourceTime', 'earliest_source_measurement_start_at');
		self::assertDateEquals($earliestCauseMeasurementTime - 30 * self::DAY, $earliestCauseFillingTime, 'earliestCauseMeasurementTime - 30 * self::DAY',
		                       '$earliestCauseFillingTime',
		                       "Earliest cause filling should be one month before earliest measurement time because that's still after earliest source time");
		$this->assertDateEquals($cause->l()->latest_source_measurement_start_at, $effect->l()->latest_source_measurement_start_at,
		                        'cause->getLatestSourceAt', 'effect->getLatestSourceAt', "They both have same source");
		$causeLatestFillingAt = $cause->getLatestFillingAt();
		$this->assertDateEquals($latestSourceAt, $causeLatestFillingAt, 'latestSourceTime', 'causeLatestFillingTime');
		$eEarliestFilling = $effect->getEarliestFillingAt();
		$minSecs = $effect->getMinimumAllowedSecondsBetweenMeasurements();
		$expected = Stats::roundToNearestMultipleOf(1348159040, $minSecs);
		$this->assertDateEquals($expected, $eEarliestFilling, 'Stats::roundToNearestMultipleOf(1348159040, $minSecs)', 'effectEarliestFilling');
		$effectLatestFillingAt = $effect->getLatestFillingAt();
		$expected = Stats::roundToNearestMultipleOf(1356712640, $minSecs);
		//$this->assertEquals($expected, $effectLatestFillingTime);
		$pairs = $c->getPairs();
		$this->assertCount(80, $pairs, "Should have 80 pairs");
		$this->assertEquals(QMVariableRelationship::DIRECTION_HIGHER, $c->direction);
		$this->assertEquals(BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_strongly_positive, $c->effectSize);
		$this->assertEquals(1, $c->getGroupedValueOverDurationOfActionClosestToValuePredictingHighOutcome());
		$this->assertEquals(0, $c->getGroupedCauseValueClosestToValuePredictingLowOutcome());
		$tagLine = $c->getStudyText()->getTagLine();
		$OptimalValueSentenceWithPercentChange = $c->getOptimalValueSentenceWithPercentChange();
		$OptimalValueSentence = $c->getStudyText()->getOptimalValueSentence();
		$PredictorExplanation = $c->getStudyText()->getStudyTitle();
		$this->assertGreaterThan(0.02, (float)$c->avgDailyValuePredictingHighOutcome);
		$this->assertLessThan(0.01, (float)$c->avgDailyValuePredictingLowOutcome);
		$this->assertGreaterThan(0.03, (float)$c->averageDailyHighCause);
		$this->assertLessThan(0.02, (float)$c->averageDailyLowCause);
		$study = $this->getUserStudyV4([
			                               'causeVariableName'  => $cause->name,
			                               'effectVariableName' => $effect->name
		                               ]);
		$this->assertContains('higher', strtolower($study->studyText->studyTitle));
		return $c;
	}
	/**
	 * @param $postData
	 * @param string $unitAbbreviatedName
	 * @param int $expectedCode
	 * @return Response
	 */
	public function postAndCheckMeasurementsResponse($postData, $unitAbbreviatedName = null, int $expectedCode = 201){
		$start = microtime(true);
		$this->setAuthenticatedUser(1);
		$response = $this->slimPost('/api/measurements/v2', $postData, false, $expectedCode);
		$firstRequestDuration = microtime(true) - $start;
		$this->assertLessThan(30, $firstRequestDuration);
		if($expectedCode < 300){
			return $this->checkPostMeasurementsResponse($response, $unitAbbreviatedName);
		}
		return null;
	}
	/**
	 * @param Response $response
	 * @param string $unitAbbreviatedName
	 * @return mixed
	 */
	public function checkPostMeasurementsResponse($response, $unitAbbreviatedName = null){
		$this->assertResponseBodyContains('userVariables', $response);
		$decodedResponse = json_decode($response->getBody(), false);
		$userVariables = $decodedResponse->data->userVariables;
		/** @var QMUserVariable $uv */
		$uv = $userVariables[0];
		$this->assertNotNull($uv, "No user variables returned");
		$byVariableName = $decodedResponse->data->measurements;
		foreach($userVariables as $uv){
			$name = $uv->name;
			$byDate = $byVariableName->$name;
			$measurements = QMArr::toArray($byDate);
			$this->assertGreaterThan(0, count($measurements));
			foreach($byDate as $date => $m){
				lei(!$m->id, "measurement in response does not have id", $m);
				$this->assertNotNull($m->id, "measurement in response does not have id");
			}
		}
		if($unitAbbreviatedName){
			$this->assertEquals($unitAbbreviatedName, $uv->unitAbbreviatedName);
		}
		return $decodedResponse->data;
	}

    /**
     * @param array|string $postData
     * @param string|null $expectedUnitAbbreviatedName
     * @param bool $requireNotifications
     * @return TrackingRemindersResponse
     */
	public function postAndCheckTrackingRemindersResponse($postData, string $expectedUnitAbbreviatedName = null, bool $requireNotifications = true){
		if(is_string($postData)){$postData  = json_decode($postData, true);}
		QMBaseTestCase::setAuthenticatedUser(1);
		$response = $this->slimPost('api/v1/trackingReminders', $postData);
		$this->assertEquals(201, $response->status(), DBUnitTestCase::getErrorMessageFromResponse($response));
		$decodedResponse = $this->getBodyFromSlimOrLaravelResponse($response);
		$responseData = $decodedResponse->data;
		/** @var QMTrackingReminderNotification[] $reminders */
		$notifications = $responseData->trackingReminderNotifications;
		/** @var QMTrackingReminder[] $reminders */
		$reminders = $responseData->trackingReminders;
		$uvID = $reminders[0]->userVariableId;
		$uv = QMUserVariable::find($uvID);
		if(isset($postData['variableCategoryName'])){
			$lUV = UserVariable::find($uvID);
			$this->assertEquals($postData['variableCategoryName'], $uv->getVariableCategoryName(), $uv->getUrl());
		}
		if($unitName = $postData["unitAbbreviatedName"] ?? null){
			$uv = QMUserVariable::find($reminders[0]->userVariableId);
			$this->assertEquals($unitName, $uv->getUnitAbbreviatedName(), $uv->getUrl());
		}
		if($expectedUnitAbbreviatedName){
			$this->assertEquals($expectedUnitAbbreviatedName, $reminders[0]->unitAbbreviatedName, $uv->getUrl());
		}
		if($requireNotifications){
			$this->assertGreaterThan(0, count($notifications),
			                         "No trackingReminderNotifications returned from reminder post request!");
			$this->checkTrackingReminderNotificationProperties($notifications, count($reminders));
		}
		if(isset($postData["reminderStartTime"])){
			$utcHis = $postData["reminderStartTime"];
			$u = $this->getUser();
			$localHis = $u->utcToLocalHis($utcHis);
			$allTimes = $reminders[0]->localDailyReminderNotificationTimesForAllReminders;
		}
		$this->assertGreaterThan(0, count($reminders));
		return $decodedResponse;
	}
	/**
	 * @param array $variableSettings
	 */
	public function postVariableSettings(array $variableSettings): void {
		$this->setAuthenticatedUser(1);
		$body = $this->postAndGetDecodedBody('/api/v1/userVariables', $variableSettings);
		$this->assertNotNull($body->userVariable);
		$this->assertNotNull($body->userVariables);
		$this->assertEquals($body->userVariable->variableId, $body->userVariable->id,
		                    "Need to return variable id instead of user variable id for backward compatibility");
	}
	/**
	 * @param QMPopulationStudy $s
	 * @return QMPopulationStudy
	 */
	public function checkPopulationStudyV4($s){
		$this->assertEquals(StudyTypeProperty::TYPE_POPULATION, $s->type);
		$this->checkAggregatedCorrelationV4Properties($s->statistics, 0, true);
		$this->assertNotNull($s->studyText);
		$this->assertNotNull($s->causeVariable);
		$this->assertNotNull($s->effectVariable);
		$this->assertNotNull($s->studyCharts);
		$this->assertNotNull($s->studyHtml);
		DBUnitTestCase::checkStudyChartHtml($s->studyHtml, true);
		$this->assertNotNull($s->studyHtml->fullStudyHtml);
		HtmlHelper::checkForMissingHtmlClosingTags($s->studyHtml->fullStudyHtml, 'fullStudyHtml');
		$this->assertNotNull($s->studyImages);
		return $s;
	}
	/**
	 * @param array $params
	 * @param string $clientId
	 * @param $clientSecret
	 * @return AppSettings
	 */
	public function getAppSettings(array $params = [], string $clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
	                               string $clientSecret = null){
		$params['clientId'] = $clientId;
		if($clientSecret){
			$params['clientSecret'] = $clientSecret;
		}
		$getResponse = $this->getAndDecodeBody('/api/v1/appSettings', $params);
		$appSettings = $getResponse->appSettings;
		$this->checkAppSettingsPropertiesExist($appSettings);
		return $appSettings;
	}
	/**
	 * @param AppSettings $appSettings
	 */
	public function checkAppSettingsPropertiesExist($appSettings){
		$appSettingsProperties = ObjectHelper::getAllPropertiesOfClassAsKeyArray(new AppSettings());
		foreach($appSettingsProperties as $propertyName){
			$this->assertObjectHasAttribute($propertyName, $appSettings);
		}
		$buildSettingsProperties = BuildSettings::getAllProperties();
		foreach($buildSettingsProperties as $propertyName){
			$this->assertObjectHasAttribute($propertyName, $appSettings->additionalSettings->buildSettings);
		}
		$this->checkBooleanAttributes(BuildSettings::getBooleanPropertyNames(), $appSettings->additionalSettings->buildSettings);
	}
	/**
	 * @return mixed
	 */
	protected function getAppSettingsWithClientSecret(){
		$client = OAClient::whereClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT)->first();
		$this->assertEquals(QMClient::TEST_CLIENT_SECRET, $client->client_secret);
		$this->assertEquals(ApplicationUserIdProperty::DEFAULT, $client->user_id);
		$body = $this->getAndDecodeBody('/api/v1/appSettings', [
			                                                     'clientId'     => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
			                                                     'clientSecret' => QMClient::TEST_CLIENT_SECRET
		                                                     ]);
		return $body;
	}
	/**
	 * @param QMTrackingReminderNotification $n
	 * @param $numberOfReminders
	 * @throws InvalidTimestampException
	 */
	private function checkTrackingReminderNotification($n, int $numberOfReminders = null){
		$frequency = $n->reminderFrequency;
		$longQuestion = $n->longQuestion;
		$question = $n->question;
		$this->assertGreaterThan(0, $n->userVariableId);
		$this->assertNotNull($n->userVariableId);
		$this->assertNotEmpty($n->title);
		$time = $n->trackingReminderNotificationTimeEpoch;
		$user = $this->getUser();
		$hoursSinceMidnight = $user->getHourDifferenceFromLastMidnight($time);
		$message = "HourDifferenceFromLastMidnight is $hoursSinceMidnight and long question is ".$longQuestion;
		if($numberOfReminders < 2 && $frequency === 86400){
			if($hoursSinceMidnight < 0 && $hoursSinceMidnight > -24){
				$containsYesterday = stripos($longQuestion, 'yesterday') !== false;
				if(!$containsYesterday && $numberOfReminders === null){
					$numberOfReminders = $this->getNumberOfRemindersForVariable($n);
					if($numberOfReminders > 1){
						return;
					}
				}
				$this->assertTrue($containsYesterday, $message);
			}
		}
		$buttons = $n->actionArray;
		$titles = QMArr::pluckColumn($buttons, 'longTitle');
		$this->assertUnique($titles);
	}
	public function assertUnique($arr){
		$this->assertArrayEquals(array_unique($arr), $arr);
	}
	/**
	 * @param $trackingReminderNotification
	 * @return int
	 */
	private function getNumberOfRemindersForVariable($trackingReminderNotification){
		$numberOfReminders = QMTrackingReminder::readonly()->where(QMTrackingReminder::FIELD_USER_ID, $trackingReminderNotification->userId)
		                                                   ->where(QMTrackingReminder::FIELD_VARIABLE_ID, $trackingReminderNotification->variableId)
		                                                   ->count();
		return $numberOfReminders;
	}
	/**
	 * @param QMTrackingReminderNotification[] $trackingReminderNotifications
	 * @param null $numberOfReminders
	 * @throws InvalidTimestampException
	 */
	public function checkTrackingReminderNotificationProperties(array $trackingReminderNotifications, $numberOfReminders){
		foreach($trackingReminderNotifications as $n){
			$this->checkTrackingReminderNotification($n, $numberOfReminders);
		}
		$this->assertIsArray( $trackingReminderNotifications);
		$stringAttributes = [
			'pngUrl',
			'svgUrl',
			'ionIcon',
			'variableCategoryName',
		];
		foreach($trackingReminderNotifications as $n){
			$this->assertNotNull($n->inputType, "inputType should not be null!");
			$this->checkStringAttributes($stringAttributes, $n);
			$booleanAttributes = QMVariable::getBooleanAttributes();
			$this->checkBooleanAttributes($booleanAttributes, $n);
		}
		$notNullAttributes = [
			'trackingReminderNotificationTimeEpoch',
			'inputType'
		];
		foreach($trackingReminderNotifications as $n){
			$this->checkNotNullAttributes($notNullAttributes, $n);
		}
		$floatAttributes = [
			'maximumAllowedValue',
			'minimumAllowedValue',
		];
		foreach($trackingReminderNotifications as $n){
			$v = QMUserVariable::findByNameOrId($n->userId, $n->variableId);
			$this->assertEquals($n->fillingValue, $v->fillingValue, $v->name);
			$this->checkFloatAttributes($floatAttributes, $n);
			if($n->inputType === 'slider'){
				$this->assertGreaterThan($n->minimumAllowedValue, $n->maximumAllowedValue);
			}
			if($n->inputType === 'oneToTen'){
				$this->assertEquals(1, $n->minimumAllowedValue);
				$this->assertEquals(10, $n->maximumAllowedValueInUserUnit);
			}
			/** @var TrackingReminderNotificationCard $card */
			$card = $n->card;
			$cat = QMVariableCategory::find($n->variableCategoryName);
			$variable = QMCommonVariable::find($n->variableId);
			$this->assertEquals($variable->imageUrl, $card->avatar);
			$this->assertNull($card->image);
			/** @var InputField[] $fields */
			$fields = $card->inputFields;
			$this->assertCount(1, $fields);
			if($n->unitAbbreviatedName !== YesNoUnit::ABBREVIATED_NAME && count($card->buttons) < 3){
				/** @var QMButton $button */
				$button = $fields[0]->submitButton;
				$this->assertEquals("Record", $button->text);
				$this->assertTrue($fields[0]->show);
			}
			if($n->unitAbbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME){
				$this->assertFalse($fields[0]->show);
			}
			$this->assertEquals(ucfirst($n->trackingReminderNotificationTimeLocalHumanString), $card->subHeader);
			$this->assertEquals($n->shortQuestion, $card->headerTitle);
            $this->assertEquals($n->trackingReminderNotificationTimeEpoch, $card->parameters->trackingReminderNotificationTimeEpoch);
		}
	}
	/**
	 * @param array $params
	 * @param string $apiUrl
	 * @return QMTrackingReminderNotification[]
	 * @throws InvalidTimestampException
	 */
	public function getAndCheckNotificationsAndFeed($params = [], $apiUrl = '/api/v1/trackingReminderNotifications'){
		$this->setAuthenticatedUser(1);
		$response = $this->getAndDecodeBody($apiUrl, $params);
		/** @var QMTrackingReminderNotification[] $notifications */
		$notifications = $response->data;
		$this->checkTrackingReminderNotificationProperties($notifications, null);
		$past = [];
		$currentTime = time();
		foreach($notifications as $notification){
			$nTime = $notification->trackingReminderNotificationTimeEpoch;
			lei(!$nTime);
			if($nTime < $currentTime){
				$past[] = $notification;
			}
		}
		$cards = $this->getAndCheckFeedCards();
		$notificationCards = [];
		foreach($cards as $card){
			if($card->type !== QMCard::TYPE_tracking_reminder_notification){
				continue;
			}
			$notificationCards[] = $card;
		}
		$numberOfNotificationTimesInThePast = count($past);
		$numberOfTrackingReminderNotificationCards = count($notificationCards);
		$this->assertTrue($numberOfTrackingReminderNotificationCards > $numberOfNotificationTimesInThePast - 1,
			"There are $numberOfTrackingReminderNotificationCards total notification cards and $numberOfNotificationTimesInThePast notification times in the past");
		return $notifications;
	}
	/**
	 * @return QMCard[]
	 */
	public function getAndCheckFeedCards(){
		$this->setAuthenticatedUser(1);
		/** @var UserFeedResponse $feedResponse */
		$feedResponse = $this->getAndDecodeBody('v1/feed');
		$this->checkFeedCards($feedResponse->cards);
		return $feedResponse->cards;
	}
	/**
	 * @param QMCard $card
	 */
	private function checkTrackingReminderNotificationCard($card){
        $this->assertEquals($card->id, $card->parameters->trackingReminderNotificationId);
		/** @var InputField $inputField */
		$inputField = $card->inputFields[0];
		if(!isset($inputField->unitAbbreviatedName)){
			\App\Logging\QMLog::print_r($inputField);
		}
		$submitButton = $inputField->submitButton;
		$cardParameters = $card->parameters;
		$submitButtonParameters = $submitButton->parameters;
		if($submitButtonParameters->unitAbbreviatedName === YesNoUnit::ABBREVIATED_NAME){
			$str = "You can say yes or no or snooze or I don't remember.";
			if($inputField->hint !== $str){
				$user = $this->getUser();
				$cards = $user->getTrackingRemindersNotificationCards(false);
			}
			$this->assertEquals($str, $inputField->hint);
		}
		if($submitButtonParameters->unitAbbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME){
			$this->assertEquals("You can say a number from 1 to 5 or I don't remember.", $inputField->hint);
		}
		if($submitButtonParameters->unitAbbreviatedName !== YesNoUnit::ABBREVIATED_NAME){
			$this->assertNotNull($this->getActionSheetButtonWithText($card, $inputField->unitAbbreviatedName));
		}
		$this->assertNotNull($this->getActionSheetButtonWithText($card, "edit reminder"));
		$this->assertNotNull($this->getSecondaryButtonWithText($card, "don't remember"));
		$this->assertNotNull($this->getSecondaryButtonWithText($card, "note"));
	}
	/**
	 * @param QMCard[] $cards
	 */
	private function checkFeedCards($cards){
		foreach($cards as $card){
			if($card->type !== QMCard::TYPE_intro && $card->type !== QMCard::TYPE_onboarding){
				$this->assertNotNull($card->actionSheetButtons);
			}
			if($card->type === QMCard::TYPE_tracking_reminder_notification){
				$this->checkTrackingReminderNotificationCard($card);
			}
		}
	}
	/**
	 * @param QMCard $card
	 * @param string $text
	 * @return bool|QMButton
	 */
	protected function getActionSheetButtonWithText($card, string $text){
		return $this->getButtonWithText($card, $text, 'actionSheetButtons');
	}
	/**
	 * @param QMCard $card
	 * @param string $text
	 * @return bool|QMButton
	 */
	protected function getSecondaryButtonWithText($card, string $text){
		return $this->getButtonWithText($card, $text, 'buttonsSecondary');
	}
	/**
	 * @param QMCard $card
	 * @param string $text
	 * @param string $buttonType
	 * @return bool|QMButton
	 */
	protected function getButtonWithText($card, string $text, string $buttonType = 'buttons'){
		/** @var QMButton $button */
		foreach($card->$buttonType as $button){
			if(stripos($button->text, $text) !== false){
				return $button;
			}
		}
		return null;
	}
	/**
	 * @param array $params
	 * @return void
     */
	private function getAndCheckUserVariablesV3(array $params = []): array
    {
		$variables = $this->getUserVariablesV3($params);
		$this->assertIsArray( $variables);
		$this->assertGreaterThan(0, count($variables));
		foreach($variables as $variable){
			$this->checkUserVariableObjectStructureV3($variable);
			$this->checkUserVariableHighchartsConfigs($params, $variable);
		}
        return $variables;
    }
	/**
	 * @param array $params
	 * @return QMUserVariable[]
	 */
	private function getAndCheckUserVariablesV4($params = []){
		/** @var QMUserVariable[] $variables */
		$body = $this->getAndDecodeBody('/api/v4/variables', $params);
		$variables = $body->variables;
		$this->assertGreaterThan(0, count($variables));
		foreach($variables as $key => $variable){
			if(property_exists($variable, 'childUserTagVariables')){
				$this->assertNotNull($variable->childUserTagVariables);
			}
			$this->checkUserVariableObjectStructureV4($variable);
			$this->checkUserVariableHighchartsConfigs($params, $variable);
		}
		return $variables;
	}
	/**
	 * @param array $params
	 * @return QMUserVariable[]
	 */
	protected function getAndCheckUserVariables(array $params = []){
		//$variables = $this->getAndCheckUserVariablesV4($params);
        $variables = $this->getAndCheckUserVariablesV3($params);
		return $variables;
	}
	/**
	 * Test /api/v1/measurements method for adding measurements
	 * @group api
	 * @param string $variableName
	 * @return array
	 */
	public function createTestSymptomRatingMeasurement(string $variableName = "Back Pain"){
		return $this->createTestMeasurement($variableName, '/5');
	}
	/**
	 * Test /api/v1/measurements method for adding measurements
	 * @group api
	 * @param string $variableName
	 * @param string $unitAbbreviatedName
	 * @param string $variableCategoryName
	 * @return array
	 */
	public function createTestMeasurement($variableName = "Back Pain", $unitAbbreviatedName = '/5',
	                                      $variableCategoryName = 'Symptoms'){
		$db = Writable::db();
		$db->table('measurements')->delete();
		$this->setAuthenticatedUser(1);
		$postData = '[{"measurements":[{"startTime":1406519965,"value":"3"}],
            "name":"' . $variableName . '","source":"test source name","category":"' . $variableCategoryName .
		            '","combinationOperation":"MEAN","unit":"' . $unitAbbreviatedName . '",
            "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
		$this->postAndGetDecodedBody('api/v3/measurements', $postData);
		$dbh = Writable::pdo();
		$sqlCheckVariables = "SELECT * FROM variables WHERE name = '".$variableName."'";
		$variables = $dbh->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
		return $variables[0];
	}
	/**
	 * @param QMStudy $study
	 * @param $needles
	 */
	public function assertParticipantInstructionsContain($study, $needles){
		if(!is_array($needles)){
			$needles = [$needles];
		}
		foreach($needles as $needle){
			/** @var StudyHtml $studyHtml */
			$studyHtml = $study->studyHtml;
			$this->assertContains($needle, $studyHtml->participantInstructionsHtml);
			/** @var StudyText $studyText */
			$studyText = $study->studyText;
			$this->assertContains($needle, $studyText->participantInstructions);
		}
	}
	protected function checkOptimalValueMessageForCommonVariables(){
		$cause = QMCommonVariable::findByNameOrId("CauseVariableName");
		$cause->analyzeFully(__FUNCTION__);
		$this->assertStringStartsWith("Higher CauseVariableName Intake predicts significantly higher EffectVariableName. EffectVariableName was 25% higher following above average CauseVariableName over the previous 24 hours. ",
		                              $cause->commonOptimalValueMessage);
		$this->assertNotFalse(stripos($cause->commonOptimalValueMessage, "CauseVariableName"));
		$primaryOutcome = QMCommonVariable::findByNameOrId("EffectVariableName");
		$primaryOutcome->analyzeFully(__FUNCTION__);
		$this->assertStringStartsWith("Higher CauseVariableName Intake predicts significantly higher EffectVariableName. EffectVariableName was 25% higher following above average CauseVariableName over the previous 24 hours. ",
		                              $primaryOutcome->commonOptimalValueMessage);
		$this->assertNotFalse(stripos($primaryOutcome->commonOptimalValueMessage, "CauseVariableName"));
		$this->assertNotFalse(stripos($cause->bestPopulationStudyLink, "effectVariableId=$primaryOutcome->variableId"),
		                      "cause->bestPopulationStudyLink is $cause->bestPopulationStudyLink");
		$this->assertNotFalse(stripos($primaryOutcome->bestPopulationStudyLink, "causeVariableId=$cause->variableId"),
		                      "primaryOutcome->bestPopulationStudyLink is $primaryOutcome->bestPopulationStudyLink");
		$this->assertNotFalse(stripos($cause->bestStudyLink, "effectVariableId=$primaryOutcome->variableId"));
		$this->assertNotFalse(stripos($primaryOutcome->bestStudyLink, "causeVariableId=$cause->variableId"));
		$this->assertEquals($cause->getCommonBestEffectVariableId(), $primaryOutcome->variableId);
		/** @var Variable $row */
		$row = QMCommonVariable::readonly()->where(QMCommonVariable::FIELD_ID, $cause->variableId)->first();
		$this->assertNotNull($row->best_global_variable_relationship_id);
		$this->assertNotNull($row->optimal_value_message);
		$row = QMCommonVariable::readonly()->where(QMCommonVariable::FIELD_ID, $primaryOutcome->variableId)->first();
		$this->assertNotNull($row->best_global_variable_relationship_id);
		$this->assertNotNull($row->optimal_value_message);
		$this->assertEquals($cause->variableId, $primaryOutcome->getCommonBestCauseVariableId());
		$notifications = $this->getAndCheckNotificationsAndFeed();
		$cards = $this->getAndCheckFeedCards();
		$this->assertTrue(count($cards) > count($notifications));
	}
	/**
	 * @throws InvalidTimestampException
	 */
	protected function checkOptimalValueMessageAndStudyCardsForUserVariables(){
		$cause = QMUserVariable::findByNameOrId(1, "CauseVariableName");
		//$cause->update(true);
		$this->assertEquals("Higher CauseVariableName Intake predicts significantly higher EffectVariableName.  EffectVariableName was 25% higher following above average CauseVariableName over the previous 24 hours. ",
			//"Your EffectVariableName was lowest after a daily total of ",
			                $cause->userOptimalValueMessage);
		$this->assertNotFalse(stripos($cause->userOptimalValueMessage, "CauseVariableName"));
		$primaryOutcome = QMUserVariable::findByNameOrId(1, "EffectVariableName");

		//$primaryOutcome->update(true);
		$this->assertStringStartsWith("Higher CauseVariableName Intake predicts significantly higher EffectVariableName.  EffectVariableName was 25% higher following above average CauseVariableName over the previous 24 hours. ",
		                              $primaryOutcome->userOptimalValueMessage);
		$this->assertNotFalse(stripos($primaryOutcome->userOptimalValueMessage, "CauseVariableName"));
		$this->assertNotFalse(stripos($cause->bestUserStudyLink, "effectVariableId=$primaryOutcome->variableId"),
		                      "cause bestUserStudyLink is $cause->bestUserStudyLink");
		$this->assertNotFalse(stripos($primaryOutcome->bestUserStudyLink, "causeVariableId=$cause->variableId"),
		                      "primaryOutcome bestUserStudyLink is $primaryOutcome->bestUserStudyLink");
		$this->assertNotFalse(stripos($cause->bestStudyLink, "effectVariableId=$primaryOutcome->variableId"));
		$this->assertNotFalse(stripos($primaryOutcome->bestStudyLink, "causeVariableId=$cause->variableId"));
		$this->assertEquals($cause->getUserBestEffectVariableId(), $primaryOutcome->variableId);
		$this->assertEquals($cause->variableId, $primaryOutcome->getUserBestCauseVariableId());
		/** @var UserVariable $row */
		$row = UserVariable::query()
			->where(UserVariable::FIELD_VARIABLE_ID, $cause->variableId)
			->where(UserVariable::FIELD_USER_ID, $cause->userId)
			->first();
		$this->assertNotNull($row->best_user_variable_relationship_id);
		$this->assertNotNull($row->optimal_value_message);
		$row = UserVariable::query()
			->where(UserVariable::FIELD_VARIABLE_ID, $primaryOutcome->variableId)
			->where(UserVariable::FIELD_USER_ID, $primaryOutcome->userId)
			->first();
		$this->assertNotNull($row->best_user_variable_relationship_id);
		$this->assertNotNull($row->optimal_value_message);
		$primaryOutcome->getOrCreateTrackingReminder();
		$notifications = $this->getAndCheckNotificationsAndFeed();
		$message = "We should have gotten a notification card for effect because a reminder should have been " .
		           "created when updating primary outcome";
		$this->assertCount(1, $notifications, $message);

		$cards = $this->getAndCheckFeedCards();
		$notificationCards = collect($cards)->where('type', QMCard::TYPE_tracking_reminder_notification)->all();
		$studyCards = collect($cards)->where('type', QMCard::TYPE_study)->all();
		$this->assertCount(1, $studyCards, "No study cards!");
		$this->assertNotEmpty($notificationCards, $message);
		$this->checkUserStudyCardFromAPI(collect($studyCards)->first());
	}
	/**
	 * @param $params
	 * @param $variable
	 */
	private function checkUserVariableHighchartsConfigs(array $params, $variable){
		if(isset($params[QMRequest::PARAM_INCLUDE_CHARTS])){
			/** @var UserVariableChartGroup $charts */
			$charts = $variable->charts;
			/** @var QMChart $chart */
			foreach($charts as $key => $chart){
				if(!is_object($chart)){continue;}
				$this->assertNotNull($chart->highchartConfig, "No highchartConfig on $key");
				/** @var HighchartConfig $config */
				$config = $chart->highchartConfig;
				lei(isset($config->chart->margin), "chart->margin Cuts off labels", $config);
			}
		}
	}
	/**
	 * @return QMTrackingReminderNotification[]
	 * @throws InvalidTimestampException
	 */
	protected function createYesNoReminderAndNotifications(){
		$this->setAuthenticatedUser(1);
		$this->deleteMeasurementsAndReminders();
		$this->postAndCheckTrackingRemindersResponse([
			                                             'variableName'         => 'Hot Shower',
			                                             'variableCategoryName' => 'Treatments',
			                                             'timeZoneOffset'       => 300,
			                                             'reminderFrequency'    => 86400,
			                                             'unitAbbreviatedName'  => 'yes/no',
		                                             ]);
		ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
		$notifications = $this->getAndCheckNotificationsAndFeed();
		$hasZeroOption = $hasOneOption = false;
		foreach($notifications[0]->actionArray as $actionArray){
			/** @noinspection TypeUnsafeComparisonInspection */
			if($actionArray->modifiedValue == 0){
				$hasZeroOption = true;
			}
			/** @noinspection TypeUnsafeComparisonInspection */
			if($actionArray->modifiedValue == 1){
				$hasOneOption = true;
			}
		}
		if(!$hasZeroOption){
			throw new LogicException("YesNo notification should have zero option");
		}
		if(!$hasOneOption){
			throw new LogicException("YesNo notification should have one option");
		}
		return $notifications;
	}
	/**
	 * @param QMCard $card
	 * @param string $string
	 */
	private function cardButtonsDoNotContain($card, string $string){
		foreach($card->buttons as $button){
			$this->assertFalse(stripos(json_encode($button), $string));
		}
	}
	/**
	 * @param StudyCard $studyCard
	 */
	private function checkUserStudyCardFromAPI($studyCard){
		$html = $studyCard->htmlContent;
		$this->assertNotEmpty($html);
		$this->assertStringNotContainsString('for most', strtolower($html));
		//$this->assertContains("higher following above average", $html);
		//$this->assertContains("Your ", $html);
		$this->assertContains("Predicts", $html);
		$this->cardButtonsDoNotContain($studyCard, "Join Study");
        $this->assertEquals($studyCard->id, $studyCard->parameters->studyId);
	}
	/**
	 * @param int $earliest
	 * @return Response
	 */
	protected function post4SymptomMeasurements($earliest = 1407019860): Response{
		$this->setAuthenticatedUser(1);
		$second = $earliest + SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS + 1;
		$third = $second + SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS + 1;
		$fourth = $third + SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS + 1;
		$postData = '[
            {
            "measurements":
                [
                    {"timestamp":' . $earliest . ',"value":"1"},
                    {"timestamp":' . $second . ',"value":"2"},
                    {"timestamp":' . $third . ',"value":"3"},
                    {"timestamp":' . $fourth . ',"value":"4"}
                ],
            "name":"' . $this->getUpperCaseName() . '",
            "source":"' . $this->getUpperCaseName() . '",
            "category":"Symptoms",
            "combinationOperation":"MEAN",
            "unit":"/5"}]';
		$res = $this->slimPost('/api/measurements/v2', $postData);
		$v = QMUserVariable::findByName(UserIdProperty::USER_ID_DEMO, $this->getUpperCaseName());
		$this->assertEquals(4, $v->numberOfMeasurements);
		$this->assertEquals(4, $v->numberOfRawMeasurementsWithTagsJoinsChildren);
		return $res;
	}
	/**
	 * @param $aggregatedCorrelation
	 * @param $minimumUsers
	 */
	private function checkIntStringAndFloatsOnGlobalVariableRelationship($aggregatedCorrelation, $minimumUsers): void{
		$this->assertGreaterThan(0, $aggregatedCorrelation->qmScore);
		$this->assertGreaterThan(1, $aggregatedCorrelation->numberOfPairs);
		$this->assertGreaterThan($minimumUsers, $aggregatedCorrelation->numberOfUsers);
		$this->assertGreaterThan(0, $aggregatedCorrelation->numberOfCorrelations);
		$intAttributes = QMGlobalVariableRelationship::getIntAttributes();
		DBUnitTestCase::checkIntAttributes($intAttributes, $aggregatedCorrelation);
		$stringAttributes = [];
		$this->checkStringAttributes($stringAttributes, $aggregatedCorrelation);
		$floatAttributes = QMGlobalVariableRelationship::getFloatAttributes();
		$this->checkFloatAttributes($floatAttributes, $aggregatedCorrelation);
	}
	/**
	 * @param QMUserVariableRelationship $correlation
	 * @return QMUserVariableRelationship
	 */
	private function checkCorrelationPropertiesAndStudyText(QMUserVariableRelationship $correlation): QMUserVariableRelationship{
		$this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$correlation->avgDailyValuePredictingHighOutcome);
		$this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$correlation->avgDailyValuePredictingLowOutcome);
		$this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$correlation->averageDailyHighCause);
		$this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$correlation->averageDailyLowCause);
		$this->setAuthenticatedUser(1);
		$study = $this->getUserStudyV4();
		$this->assertContains('higher', strtolower($study->studyText->studyTitle));
		return $correlation;
	}
	/**
	 * @param QMMeasurement|stdClass $m
	 */
	protected function checkMeasurementPropertyTypes($m): void{
		$this->assertNotNull($m->value);
		$this->assertNotNull($m->startTime);
		$this->assertObjectHasAttribute('sourceName', $m);
		$this->assertNotNull($m->value);
		$this->assertIsInt($m->unitId);
		$this->assertIsInt($m->variableId);
		$this->assertIsInt($m->startTimeEpoch);
		$this->assertIsString($m->variableName);
		$this->assertIsString($m->unitAbbreviatedName);
		$this->assertIsString($m->variableName);
		$this->assertIsString($m->variableCategoryName);
		$this->assertIsInt($m->variableCategoryId);
		if($m->unitAbbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME){
			$this->assertContains("rating", $m->pngPath);
		}
	}
	/**
	 * @param QMUserVariableRelationship $nc
	 * @return QMUserVariableRelationship
	 */
	protected function checkNegativeCorrelation($nc): QMUserVariableRelationship{
		$this->assertNotNull($nc);
		$this->assertEquals(-1, $nc->correlationCoefficient);
		$this->assertEquals(-1, $nc->strongestPearsonCorrelationCoefficient);
		$this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->avgDailyValuePredictingLowOutcome);
		$this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->avgDailyValuePredictingHighOutcome);
		$this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->averageDailyHighCause);
		$this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->averageDailyLowCause);
		$study = $this->getUserStudyV4();
		/** @var QMVariableRelationship $statistics */
		$statistics = $study->statistics;
		$this->assertContains('lower', strtolower($study->studyText->studyTitle),
		                      "correlation: " . $statistics->correlationCoefficient . " value predicting high: " .
		                      $statistics->avgDailyValuePredictingHighOutcome . " low: " . $statistics->avgDailyValuePredictingLowOutcome);
		return $nc;
	}
	private static function fixCombinationOperationAndDataSourcesCountInTestDB(): void{
		QMCommonVariable::writable()->update([
			                                     QMCommonVariable::FIELD_COMBINATION_OPERATION => null,
			                                     QMVariable::FIELD_DATA_SOURCES_COUNT          => null
		                                     ]);
		QMUserVariable::writable()->update([QMVariable::FIELD_DATA_SOURCES_COUNT => null]);
		QMQB::setAllowFullTableQueries(false);
		QMDB::flushQueryLogs(__METHOD__);
	}
	private static function validateDBUrl(): void{
		$dbUrl = \App\Utils\Env::get('CLEARDB_DATABASE_URL');
		if(stripos($dbUrl, 'test') === false){
			le("DB $dbUrl should contain test");
		}
		if(stripos($dbUrl, 'production') !== false){
			le("DB $dbUrl should contain test");
		}
	}
	public static function resetHttpGlobals(): void{
		$_GET = []; // Need to reset anything left over from previous tests
		foreach($_SERVER as $key => $value){
			if(str_starts_with($key, "HTTP_")){unset($_SERVER[$key]);}
		}
		AppMode::setIsApiRequest(false);
	}

	public static function assertNotContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true,
	                                         bool $checkForNonObjectIdentity = false): void{
		QMBaseTestCase::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity,
		                              $checkForNonObjectIdentity);
		self::assertTrue(true);
	}
	protected function seedWithPositiveLinearCauseEffectMeasurements(): void{
		$cause = $this->getCauseUserVariable()->l();
		$this->assertEquals(1800, $cause->onset_delay);
		$this->assertZeroFillingValue($cause);
		$effect = $this->getEffectUserVariable();
		$this->assertNoFillingValue($effect);
		$effect = $effect->l();
		$causeMeasurementItems = [];
		$effectMeasurementItems = [];
		for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
			$measurementValue = $i;
			$causeMeasurementItems[] = $cause->newMeasurementData([
				                                                      Measurement::FIELD_START_TIME     => self::BASE_TIME + self::DAY * $i,
				                                                      Measurement::FIELD_VALUE          => $measurementValue,
				                                                      Measurement::FIELD_ORIGINAL_VALUE => $measurementValue,
				                                                      Measurement::FIELD_CLIENT_ID      => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
			                                                      ]);
			$effectMeasurementItems[] = $effect->newMeasurementData([
				                                                        Measurement::FIELD_START_TIME     => self::BASE_TIME + self::DAY * ($i + 1),
				                                                        Measurement::FIELD_VALUE          => $measurementValue,
				                                                        Measurement::FIELD_ORIGINAL_VALUE => $measurementValue,
				                                                        Measurement::FIELD_CLIENT_ID      => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
			                                                        ]);
		}
		$cause->bulkMeasurementInsert($causeMeasurementItems);
		$effect->bulkMeasurementInsert($effectMeasurementItems);
	}
	protected function resetUserTables(){
		TestDB::resetUserTables();
	}
	/**
	 * @param string $variableName
	 * @param QMUserVariable[]|QMCommonVariable[] $userVariables
	 */
	public static function assertVariablesContain(string $variableName, array $userVariables){
		$found = false;
		$names = [];
		foreach($userVariables as $userVariable){
			$userVariableId = $userVariable->userVariableId;
			$id = $userVariable->id;
			$variableId = $userVariable->variableId;
			lei($userVariableId === $variableId);
			$userVariable = new QMUserVariable($userVariable, $userVariable->userId, $userVariable->variableId);
			QMBaseTestCase::assertPropertyExistsAndOutputIfFalse($userVariable, 'name');
			$names[] = $userVariable->name;
			if($userVariable->name === $variableName || $userVariable->inSynonyms($variableName)){
				$found = true;
			}
		}
		lei(!$names, "no variables returned");
		if(!$found){
			throw new LogicException("Variables do not contain $variableName. Names: ".implode(", ", $names));
		}
	}
	public static function setUserVariablesWithZeroStatusToWaiting(){
		$numberOfUserVariablesWithWrongStatus = QMUserVariable::readonly()->where('status', '0')->count();
		if($numberOfUserVariablesWithWrongStatus){
			GoogleAnalyticsEvent::logEventToGoogleAnalytics(QMUserVariable::TABLE,
			                                                'setUserVariablesWithZeroStatusToWaiting', 1, null, null);
			QMLog::error($numberOfUserVariablesWithWrongStatus.' have status set to 0.  Setting status to WAITING...');
			QMUserVariable::writable()->where('status', '0')->update(['status' => UserVariableStatusProperty::STATUS_WAITING]);
		}
	}
	/**
	 * Retrieve average results from user_variable_relationships table
	 * @return array
	 */
	public static function deleteAndRecreateAllAggregatedCorrelations(): array{
		self::deleteGlobalVariableRelationships();
		$userVariableRelationships = UserVariableRelationship::all();
		$agg = [];
		foreach($userVariableRelationships as $uc){
			$agg[] = $uc->getOrCreateGlobalVariableRelationship();
		}
		return $agg;
	}
	/**
	 * @param $response
	 * @return QMUserStudy
	 */
	public static function checkUserStudyWithOrWithoutData($response){
		if($response instanceof Response){
			$s = json_decode($response->getBody(), false);
		}else{
			$s = $response;
		}
		/** @var QMUserStudy $s */
		self::assertEquals(StudyTypeProperty::TYPE_INDIVIDUAL, $s->type, "Type is ".$s->type);
		self::assertNotNull($s->studyText);
		self::assertNotNull($s->causeVariable);
		self::assertNotNull($s->effectVariable);
		self::assertNotNull($s->userId);
		$userTagsCount = QMUserTag::readonly()->count();
		if($userTagsCount){
			self::assertNotNull($s->effectVariable->childUserTagVariables,
			                    "No effectVariable->childUserTagVariables!  I guess the user study failed and it got population study instead");
		}
		self::assertNotNull($s->studyCharts);
		self::assertNotNull($s->statistics);
		self::assertNotNull($s->studyHtml);
		/** @var StudyHtml $studyHtml */
		$studyHtml = $s->studyHtml;
		self::assertNotNull($studyHtml->fullStudyHtml);
		/** @var StudyHtml $studyHtml */
		$studyHtml = $s->studyHtml;
		DBUnitTestCase::checkStudyChartHtml($studyHtml, $s->studySharing->shareUserMeasurements);
		self::assertNotNull($s->studyImages);
		self::assertNotNull($s->userId);
		return $s;
	}
	/**
	 * @param array $environmentSettings
	 * @param array $getParams
	 */
	protected static function mockSlimApiRequest(array $environmentSettings, array $getParams): void{
		self::resetHttpGlobals();
		if(!$getParams && !empty($environmentSettings["slim.request.query_hash"])){
			$getParams = $environmentSettings["slim.request.query_hash"];
		}
		$_GET = $getParams;
		Memory::flush();
		Environment::mock($environmentSettings);
		AppMode::setIsApiRequest(true);
		self::populateServerVariables($environmentSettings);
	}
	public static function updateOrCreateAllUserVariablesAndSetMeasurementSourceNamesToTestClientId(){
		QMQB::setAllowFullTableQueries(true);
		if(AppMode::isTestingOrStaging()){
			BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
			self::setMeasurementSourceNamesToTestClientId();
		}
		$users = QMUser::getAll();
		foreach($users as $user){
			if(!$user->id){
				throw new LogicException("No id!");
			}
			$userVariables = QMUserVariable::getUserVariables($user->id);
			/** @var QMUserVariable $userVariable */
			foreach($userVariables as $userVariable){
				try {
					$userVariable->analyzeFully(__FUNCTION__);
				} catch (AlreadyAnalyzingException $e) {
					continue;
				}
			}
			$newVariableData['clientId'] = 'createNewUserVariablesFromMeasurements';
			$variables = QMMeasurement::readonly()->select('variable_id')->where('user_id', $user->id)->where('deleted_at', null)
			                                      ->groupBy('variable_id')->getArray();
			foreach($variables as $variable){
				/** @noinspection MissingIssetImplementationInspection */
				if(!empty($variable->variable_id)){
					QMUserVariable::createOrUnDeleteById($user->id, $variable->variable_id);
				}
				/** @noinspection MissingIssetImplementationInspection */
				if(!empty($variable->variable_id)){
					QMUserVariable::getOrCreateAndAnalyze($user->id, $variable->variable_id);
				}
			}
		}
		Memory::resetClearOrDeleteAll();
		QMQB::setAllowFullTableQueries(false);
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable
	 */
	protected function getTreatmentUserVariable(int $userId = 1): QMUserVariable{
		return BupropionSrCommonVariable::getUserVariableByUserId($userId);
	}
	/**
	 * @return QMUser[]
	 */
	protected function getUsers(){
		$body = $this->getAndDecodeBody('v1/users', []);
		return $body->users;
	}
	/**
	 * @param QMMeasurement[] $measurements
	 */
	protected function checkNewMeasurements(array $measurements){
		foreach($measurements as $m){
			$this->checkMeasurement($m);
		}
	}
	/**
	 * @param QMMeasurement $measurement
	 */
	protected function checkMeasurement(QMMeasurement $measurement): void{
		$row = $measurement->getDbRow();
		$this->assertPropertyGreaterThanAnHourAgo($row, "created_at");
	}
	/**
	 * @param object $obj
	 * @param string $propertyName
	 */
	protected function assertPropertyGreaterThanAnHourAgo($obj, string $propertyName): void{
		$hourAgoAt = Writable::getDate(time() - 60);
		$at = $obj->$propertyName;
		if(!$at){
			throw new LogicException("$propertyName not found on ".\App\Logging\QMLog::print_r($obj, true));
		}
		if($at && $hourAgoAt > $at){
			$this->assertGreaterThan($hourAgoAt, $at, "Created at is $at on " . \App\Logging\QMLog::print_r($obj, true));
		}
	}
	/**
	 * @return QMConnector[]
	 */
	protected function getConnectorsRequest(){
		$response = $this->getAndDecodeBody('/api/v3/connectors/list');
		/** @var QMConnector[] $connectors */
		$connectors = $response->connectors;
		foreach($connectors as $c){
			if(!$c->enabled){
				throw new LogicException("Connectors from API should be enabled but got: ".\App\Logging\QMLog::print_r($c, true));
			}
		}
		return $connectors;
	}
	protected function deleteWpData(){
		TestDB::deleteWpData();
	}
	/**
	 * @return QMMeasurement[]
	 */
	protected function generateHighLowMeasurementsForLast120Days(): array{
		$measurements = [];
		for($i = -120; $i < -90; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 1);
		}
		for($i = -90; $i < -60; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 5);
		}
		for($i = -60; $i < -30; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 1);
		}
		for($i = -30; $i < 0; $i++){
			$measurements[] = new QMMeasurement($this->getTimeMinusXDays(-1 * $i), 5);
		}
		return $measurements;
	}
	/**
	 * @param QMMeasurement[] $uncombined
	 * @param QMMeasurement[] $combined
	 */
	protected function checkEarliestTimesOnNewVariable(array $uncombined, array $combined): void{
		$uncombined = collect($uncombined);
		$combined = collect($combined);
		/** @var QMMeasurement $firstMeasurement */
		$firstMeasurement = $uncombined->first();
		$uv = UserVariable::find($firstMeasurement->userVariableId);
		$fromDb = Measurement::whereVariableId($firstMeasurement->variableId)->orderBy(Measurement::FIELD_START_TIME, 'asc')
		                                                                     ->first();
		$maxUpdatedAt = Measurement::whereUserVariableId($firstMeasurement->userVariableId)->max(Measurement::UPDATED_AT);
		$this->assertDateWithinXSecondsOf(2, $maxUpdatedAt, $uv->newest_data_at, "maxUpdatedAt", "v->newest_data_at");
		//$this->assertEquals($fromDb->start_at->toDateTimeString(), $v->earliest_non_tagged_measurement_start_at->toDateTimeString());
		//$this->assertEquals($fromDb->start_at->toDateTimeString(), $v->earliest_tagged_measurement_start_at->toDateTimeString());
		$this->assertDateEquals($fromDb->start_at->timestamp, $uv->earliest_filling_time,
		                        "Earliest measurement start_at", "$uv earliest_filling_time");
		$this->assertDateEquals($fromDb->start_at->timestamp, $uv->earliest_non_tagged_measurement_start_at,
		                        "Earliest measurement start_at", "$uv earliest_non_tagged_measurement_start_at");
		$this->assertDateEquals($fromDb->start_at->timestamp, $uv->earliest_source_measurement_start_at,
		                        "Earliest measurement start_at", "$uv earliest_source_measurement_start_at");
		$this->assertDateEquals($fromDb->start_at->timestamp, $uv->earliest_tagged_measurement_start_at,
		                        "Earliest measurement start_at", "$uv earliest_tagged_measurement_start_at");
	}
	/**
	 * @param Measurement[] $measurements
	 */
	protected function checkLatestTimesOnNewVariable(array $measurements): void{
		$uncombined = collect($measurements);
		/** @var QMMeasurement $lastMeasurement */
		$lastMeasurement = $uncombined->last();
		$v = UserVariable::find($lastMeasurement->getUserVariableId());
		$fromDb = Measurement::whereVariableId($lastMeasurement->getVariableId())->orderBy
        (Measurement::FIELD_START_TIME,
'desc')
		                                                                         ->first();
		$this->assertDateWithinXSecondsOf(2, $fromDb->max(Measurement::UPDATED_AT), $v->newest_data_at->toDateTimeString(), 'UPDATED_AT', 'newest_data_at');
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_AT), $v->latest_non_tagged_measurement_start_at->toDateTimeString());
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_AT), $v->latest_tagged_measurement_start_at->toDateTimeString());
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_non_tagged_measurement_start_at);
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_tagged_measurement_start_at);
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_filling_time);
		$this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_source_measurement_start_at);
	}
	/**
	 * @param string $html
	 * @param QMUserVariable $v
	 */
	protected function assertContainsVariableChartImagesWithInlineStyles(string $html, QMUserVariable $v): void{
		$slug = QMStr::slugify($v->name);
		$this->assertContains("<img id=\"average-$slug-by-day-of-week", $html);
		$this->assertContains("<img id=\"average-$slug-by-month", $html);
		$this->assertContains("Daily $v->name Distribution", $html);
		$this->assertContains(ImageHelper::CHART_IMAGE_STYLES, $html, "We need these styles inline to avoid blurriness");
	}
	/**
	 * Asserts the number of elements of an array, Countable or Traversable.
	 * @param int $expectedCount
	 * @param iterable|Collection|array $haystack
	 * @param string $message
	 */
	public static function assertCountGreaterThan($expectedCount, $haystack, $message = ''): void{
		QMAssert::assertCountGreaterThan($expectedCount, $haystack, $message);
		self::assertTrue(true);
	}
	protected function getOrCreateStudy(int $userId = 1){
		$c = UserVariableRelationship::whereUserId($userId)->first();
		/** @var UserVariableRelationship $c */
		if($c){return $c->getStudy();}
		$c = $this->seedWithPositiveLinearCorrelation();
		return $c->getStudy()->getOrSetQMStudy();
	}
	protected function getTrackingReminderNotifications(array $params){
		QMBaseTestCase::setAuthenticatedUser(1);
		return $this->getAndDecodeBody('/api/v1/trackingReminderNotifications', $params);
	}
	protected function getDailyMeasurements(array $params){
		$this->actingAsUserId(1);
		return $this->getAndDecodeBody('/api/v1/measurements/daily', $params);
	}
	/**
	 * @param array $params
	 * @return QMTrackingReminder[]
	 */
	protected function getTrackingReminders(array $params = []): array {
		QMBaseTestCase::setAuthenticatedUser(1);
		$r = $this->getAndDecodeBody('/api/v1/trackingReminders', $params);
		return $r->data;
	}

    /**
     * @param QMUserVariable $variable
     * @param int $day1
     * @param int $day2
     * @param int $day3
     * @param int $day4
     * @param int $day5
     * @return array
     */
	public function getOneToFiveMgMeasurements(QMUserVariable $variable, int $day1, int $day2, int $day3, int $day4, int $day5): array{
		$rawCauseMeasurements = [];
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day1, 1, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day2, 2, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day3, 3, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day4, 4, 'mg');
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day5, 5, 'mg');
		return $rawCauseMeasurements;
	}
	/**
	 * @param QMUserVariable $variable
	 * @param int $day1
	 * @param $day2
	 * @param $day3
	 * @param $day4
	 * @param $day5
	 * @return array
	 */
	public function getOneToFiveRatingMeasurements(QMUserVariable $variable, int $day1, int $day2, int $day3, int $day4, int $day5): array{
		$rawCauseMeasurements = [];
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day1, 1, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day2, 2, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day3, 3, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day4, 4, OneToFiveRatingUnit::ABBREVIATED_NAME);
		$rawCauseMeasurements[] = new QMMeasurementV1(null, $variable, $day5, 5, OneToFiveRatingUnit::ABBREVIATED_NAME);
		return $rawCauseMeasurements;
	}
    protected function getApiV3(string $path, $params = [], bool $returnObject = false){
        if(!isset($params['client_id'])){
            $params['client_id'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
        $path = UrlHelper::addParams(str_replace("//", "/", 'api/v3/'.$path), $params);
        $r = $this->getAndDecodeBody($path, []);
        //$r->assertStatus(200);
        //$body = json_decode($r->getContent(), true);
        //$this->assertNotNull($body);
        //$data = $body['data'] ?? $body;
//        if($returnObject){
//            return json_decode(json_encode($data));
//        }
	    //$r = json_decode(json_encode($r));
        return $r;
    }
	protected function getVariablesV3(array $params = []): array {
		$data = $this->getAndDecodeBody('api/v3/variables', $params);
        if(is_object($data)){
            $data = (array)$data;
        }
		return $data;
	}
	protected function getUserVariablesV3(array $params = []): array {
		$data = $this->getAndDecodeBody('api/v3/userVariables', $params);
		if(is_object($data)){
			$data = (array)$data;
		}
		return $data;
	}
	/**
	 * @param array $array
	 * @return QMUserVariable[]
	 */
	protected function getUserVariablesRequest(array $params): array {
		return $this->getUserVariablesV3($params);
	}
	protected function slimGetUser(int $expectedUserId = null){
		$response = $this->slimGet('api/v1/user');
		$this->assertEquals(200, $response->status());
		$body = $response->getBody();
		$user = json_decode($body);
		if($expectedUserId){
			$this->assertEquals($expectedUserId, $user->id);
		}
		return $user;
	}
}
