<?php /** @noinspection PhpDocMissingThrowsInspection */
namespace Tests;
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
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NoChangesException;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UnexpectedStatusCodeException;
use App\Http\Parameters\SortParam;
use App\Http\Resources\BaseJsonResource;
use App\InputFields\InputField;
use App\InputFields\NumberInputField;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Models\UserClient;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseForwardPearsonCorrelationCoefficientProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Controller\Feed\UserFeedResponse;
use App\Slim\Controller\Share\ShareResponse;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\MeasurementSet;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\Reminders\TrackingRemindersResponse;
use App\Slim\Model\User\AuthorizedClients;
use App\Slim\Model\User\QMUser;
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
use App\Utils\QMRoute;
use App\Utils\Stats;
use App\Utils\UrlHelper;
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
use GuzzleHttp\Psr7\Query;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Testing\TestResponse;
use LogicException;
use PDO;
use Slim\Http\Request;
use Slim\Http\Response;
use stdClass;
use Tests\SlimTests\Analytics\StudyTest;
use Throwable;
/**
 * Trait ApiTestTrait
 * @package Tests
 * @mixin QMBaseTestCase
 */
trait ApiTestTrait
{
    protected TestResponse $testResponse;
    private array $fakeData = [];

    /**
     * @param array $submittedData
     */
    public function assertApiResponse(Array $submittedData){
        $this->assertApiSuccess();
        $data = $this->getJsonResponseData();
		if(isset($data[0])){$data = $data[0];}
        $data = BaseJsonResource::removeDateAttributes($data);
        $submittedData = BaseJsonResource::removeDateAttributes($submittedData);
        $this->assertContains($submittedData, $data);
    }
    public function assertApiSuccess(){
        $statusCode = $this->testResponse->getStatusCode();
        $this->assertTrue(in_array($statusCode, [200, 201, 204]), "Response status code $statusCode is not a success code");
        $data = json_decode($this->testResponse->getContent(), true);
        //$this->testResponse->assertJson(['success' => true]);
    }

    /**
     * @param $responseData
     * @param array $submittedData
     */
    public function assertModelData($responseData, array $submittedData){
        foreach ($submittedData as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            $this->assertEquals($value, $responseData->$key, "Failed asserting that $key is equal");
        }
    }
    /**
     * @param QMRoute $route
     * @return BaseModel
     */
    protected function getOrCreateModelForRoute(QMRoute $route): BaseModel{
        try {
            return QMBaseTestCase::firstOrFakeNew($route->getFullClassName());
        } catch (\Throwable $e){
            ExceptionHandler::dumpOrNotify($e);
            return QMBaseTestCase::firstOrFakeNew($route->getFullClassName());
        }
    }
	/**
	 * @param $requiredStrings
	 * @param string $haystack
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $message
	 */
	public static function assertHtmlContains($requiredStrings, string $haystack, string $type, $ignoreCase = false,
                                       string $message = null){
        /** @noinspection PhpUnhandledExceptionInspection */
        static::assertStringContains($haystack, $requiredStrings, $type, $ignoreCase, $message);
    }
	/**
	 * @param $blackList
	 * @param string $haystack
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $message
	 * @throws \App\Exceptions\InvalidStringException
	 */
	public static function assertHtmlDoesNotContain($blackList, string $haystack, string $type, $ignoreCase = false,
                                       string $message = null){
        /** @noinspection PhpUnhandledExceptionInspection */
        QMStr::assertStringDoesNotContain($haystack, $blackList, $type, $ignoreCase, $message);
    }
	/**
	 * @param $expected
	 * @param TestResponse $response
	 * @param string $path
	 * @param string $method
	 * @param string|null $message
	 * @throws UnexpectedStatusCodeException
	 */
    public function assertStatusCodeEquals($expected, TestResponse $response, string $path, string $method, string $message =
    null){
		$actual = $response->getStatusCode();
        if($expected !== $response->getStatusCode()){
			if($actual == 302 || $actual === 301){
				$location = $response->baseResponse->headers->get("Location");
				le("Expect for $expected from $path\n but got $actual to\n".$location."\n");
			}
			//$message = "HERE'S THE TRUNCATED RESPONSE:\n".QMStr::truncate($message, 120);
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new UnexpectedStatusCodeException($expected, $actual,
                $path, $method, $message);
        }
    }
    /**
     * @return string|BaseModel
     */
    public function getClassBeingTested(): string {
        $testClass = static::class;
        $short = QMStr::toShortClassName($testClass);
        $short = str_replace('ApiTest', '', $short);
        $full = QMStr::toFullClassName($short);
        return $full;
    }
    /**
     * @return BaseModel
     */
    public function getModel(): BaseModel {
        $testClass = $this->getClassBeingTested();
        $id = $this->getIdFromTestResponse();
        if($id){
            return $testClass::findInMemoryOrDB($id);
        }
        return new $testClass();
    }
    protected function getUnits(){
        $data = $this->getApiV6('units');
        return $data;
    }
    protected function postMeasurements($body){
        $data = $this->postApiV6('measurements', $body);
        return $data;
    }
    protected function getVariables(string $q){
        $data = $this->getAPIV6('variables', ['q' => $q]);
        return $data;
    }
    protected function createUserStudy(string $cause, string $effect, array $data = []): array
    {

        $data = $this->postApiV6('user_studies', array_merge($data, ['cause' => $cause, 'effect' => $effect]));
        return $data;
    }
    protected function getApiV6(string $path, $params = [], bool $returnObject = false){
        if(!isset($params['client_id'])){
            $params['client_id'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
        $path = UrlHelper::addParams(str_replace("//", "/", 'api/v6/'.$path), $params);
        $r = $this->getJson($path, []);
        $r->assertStatus(200);
        $body = json_decode($r->getContent(), true);
        $this->assertNotNull($body);
        $data = $body['data'] ?? $body;
        if($returnObject){
            return json_decode(json_encode($data));
        }
        return $data;
    }
    /**
     * @param string $path
     * @param $body
     * @param int $expectedStatus
     * @param bool $returnObject
     * @return array|object
     */
    protected function postApiV6(string $path, $body, int $expectedStatus = 201, bool $returnObject = false){
        if(is_string($body)){
            $body = json_decode($body, true);
        } else {
            $body = json_decode(json_encode($body), true);
        }
        if(!isset($body['client_id'])){
            $body['client_id'] = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
	    if(str_starts_with($path, '/')){$path = substr($path, 1);}
	    if(str_starts_with($path, 'api/')){
		    le("You should not include api in the path when passing to ". __METHOD__);
	    }
	    $path = 'api/v6/' . $path;
	    $r = $this->postJson($path, $body);
        $r->assertStatus($expectedStatus);
        $body = json_decode($r->getContent(), true);
        $this->assertNotNull($body);
        $data = $body['data'] ?? (object)$body;
        if($returnObject){
            return json_decode(json_encode($data), false);
        }
        return $data;
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
            if($userVariableId === $variableId){
                le('$userVariableId === $variableId');
            }
            $userVariable = new QMUserVariable($userVariable, $userVariable->userId, $userVariable->variableId);
            SlimStagingTestCase::assertPropertyExistsAndOutputIfFalse($userVariable, 'name');
            $names[] = $userVariable->name;
            if($userVariable->name === $variableName || $userVariable->inSynonyms($variableName)){
                $found = true;
            }
        }
        if(!$names){
            le("no variables returned");
        }
        if(!$found){
            throw new LogicException("Variables do not contain $variableName. Names: " . implode(", ", $names));
        }
    }
    public static function setUserVariablesWithZeroStatusToWaiting(){
        $numberOfUserVariablesWithWrongStatus = QMUserVariable::readonly()->where('status', '0')->count();
        if($numberOfUserVariablesWithWrongStatus){
            GoogleAnalyticsEvent::logEventToGoogleAnalytics(UserVariable::TABLE,
                'setUserVariablesWithZeroStatusToWaiting', 1, null, null);
            QMLog::error($numberOfUserVariablesWithWrongStatus .
                ' have status set to 0.  Setting status to WAITING...');
            QMUserVariable::writable()->where('status', '0')
                ->update(['status' => UserVariableStatusProperty::STATUS_WAITING]);
        }
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
            $variables =
                QMMeasurement::readonly()
                    ->select('variable_id')
                    ->where('user_id', $user->id)
                    ->where('deleted_at', null)
                    ->groupBy(['variable_id'])
                    ->getArray();
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
     * @return int
     */
    public static function setMeasurementSourceNamesToTestClientId(){
        return QMMeasurement::writable()->whereNull(Measurement::FIELD_SOURCE_NAME)
            ->update([Measurement::FIELD_SOURCE_NAME => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
    }
    private static function fixCombinationOperationAndDataSourcesCountInTestDB(): void{
        QMCommonVariable::writable()->update([
            Variable::FIELD_COMBINATION_OPERATION => null,
            Variable::FIELD_DATA_SOURCES_COUNT => null,
        ]);
        QMUserVariable::writable()->update([Variable::FIELD_DATA_SOURCES_COUNT => null]);
        QMQB::setAllowFullTableQueries(false);
        QMDB::flushQueryLogs(__METHOD__);
    }
    /**
     * @param QMStudy|object $study
     * @param $needles
     */
    public function assertParticipantInstructionsContain(object $study, $needles){
        if(!is_array($needles)){
            $needles = [$needles];
        }
        foreach($needles as $needle){
            $studyHtml = $study->studyHtml;
            $this->assertContains($needle, $studyHtml->participantInstructionsHtml);
            /** @var StudyText $studyText */
            $studyText = $study->studyText;
            $this->assertContains($needle, $studyText->participantInstructions);
        }
    }
    /**
     * Assert that the given Response object represents an API error.
     * @param int $expectedCode The expected status code.
     * @param string $expectedMessage The expected error message.
     * @param Response $response The API response.
     */
    public function assertResponseIsError(int $expectedCode, string $expectedMessage, Response $response){
        $this->assertNotNull($response);
        $this->assertEquals($expectedCode, $response->getStatus(), self::getErrorMessageFromResponse($response));
        $responseArray = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('error', $responseArray, "Got " . \App\Logging\QMLog::print_r($responseArray, true));
        $errorArray = $responseArray['error'];
        $this->assertArrayHasKey('message', $errorArray);
        if($expectedMessage){
            $this->assertStringStartsWith($expectedMessage, $errorArray['message']);
        }
    }
    /**
     * @param TestResponse|Response $response
     * @param null $object
     * @return string
     */
    public static function getErrorMessageFromResponse($response, $object = null): string
    {
		if($response instanceof TestResponse){
			$body = json_decode($response->content());
		} else if($response instanceof Response){
			$body = json_decode($response->getBody());
		} else {
			$body = $response;
		}
        $message = "";
        if($body && is_string($body)){
            $decodedBody = json_decode($body, false);
        }
        if(isset($decodedBody) && isset($decodedBody->errorMessage)){
            $message = "Error: " . $decodedBody->errorMessage . ' --- ' . $message;
        }
        if(isset($object->updateError)){
            $message = "Update Error: " . $object->updateError . ' --- ' . $message;
        }
        if(isset($object->connectError)){
            $message = "Connect Error: " . $object->connectError . ' --- ' . $message;
        }
        if(isset($object->error)){
            $message = "Error: " . $object->error;
        }
        return $message;
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
     * @param QMGlobalVariableRelationship|QMVariableRelationship $ac
     * @param int $minimumUsers
     */
    public function checkAggregatedCorrelationProperties($ac, int $minimumUsers = 0){
        /** @var QMGlobalVariableRelationship $ac */
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
     * @param QMVariableRelationship|object $c
     */
    public function checkSharedCorrelationProperties($c){
        if(is_array($c)){
            $c = (object)$c;
        }
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
     * @return array
     */
    private function getNotNullAttributes(){
        return [
            'avgDailyValuePredictingHighOutcome',
            'avgDailyValuePredictingLowOutcome',
        ];
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
            'numberOfUserVariableRelationshipsAsEffect',
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
     * Check that the given object has all required variable properties with correct type
     * @param QMCommonVariable|stdClass $variable
     * @param string|null $searchTerm
     * @param int|null $userId
     */
    public function checkSharedQmVariableObjectStructureV3($variable, string $searchTerm = null, ?int $userId = 1){
        if(is_array($variable)){
            $variable = json_decode(json_encode($variable));
        }
        $this->assertInstanceOf('stdClass', $variable);
        if($userId && $searchTerm){
            $ownVariable = false;
            $cleanedSearchTerm = str_replace([
                '*',
                '%',
            ], '', $searchTerm);
            $exactMatch = $variable->name === $cleanedSearchTerm;
            if(isset($variable->userId)){
                $ownVariable = $userId === $variable->userId;
            }
            $this->assertTrue($ownVariable || $variable->isPublic || $exactMatch,
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
            'variance',
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
            'variableCategoryId',
        ];
        DBUnitTestCase::checkIntAttributes($intAttributes, $variable);
        $booleanAttributes = [
            'causeOnly',
            Variable::FIELD_OUTCOME,
            Variable::FIELD_IS_PUBLIC,
            'manualTracking',
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
            Variable::FIELD_OUTCOME,
        ];
        $this->checkNotNullAttributes($notNullAttributes, $variable);
    }
    /**
     * @param array $intAttributes
     * @param $object
     */
    public static function checkIntAttributes(array $intAttributes, $object){
        foreach($intAttributes as $attribute){
            if(!is_object($object)){
                throw new LogicException("Not an object: " . QMLog::print_r($object, true));
            }
            if(!property_exists($object, $attribute)){
	            static::fail("$attribute property does not exist on ".QMLog::print_r($object, true));
            }
            if($object->$attribute !== null && !is_int($object->$attribute)){
                QMLog::print_r($object->$attribute);
            }
            if(is_string($object->$attribute)){
	            static::fail("$attribute should be an integer but " . $object->$attribute . ' is a string');
            }
            if($object->$attribute !== null && !is_int($object->$attribute)){
	            static::fail("$attribute should be an integer but " . $object->$attribute . ' is ' .
                    gettype($object->$attribute));
            }
        }
    }
    /**
     * @param $stringAttributes
     * @param $object
     */
    public function checkStringAttributes($stringAttributes, $object){
        foreach($stringAttributes as $attribute){
            if(!is_string($object->$attribute)){
                QMLog::info("$attribute should be a string but is " . gettype($object->$attribute));
            }
            $this->assertIsString($object->$attribute,
                "$attribute should be a string but " . $object->$attribute . ' is ' . gettype($object->$attribute));
        }
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
            'reasonForAnalysis',
        ];
        $this->checkStringAttributes($stringAttributes, $correlation);
        $this->checkNotNullAttributes($stringAttributes, $correlation);
        $floatAttributes = [
            'correlationCoefficient',
            'optimalPearsonProduct',
            'statisticalSignificance',
            'durationOfActionInHours',
            'onsetDelayInHours',
        ];
        $this->checkFloatAttributes($floatAttributes, $correlation);
        $this->checkNotNullAttributes($floatAttributes, $correlation);
        $this->checkNotNullAttributes($this->getNotNullAttributes(), $correlation);
    }
    /**
     * @param $response
     */
    public function checkDeletionResponse($response){
        $this->assertResponseBodyContains('204', $response);
        $this->assertResponseBodyContains('"success":true', $response);
    }
    /**
     * @param object|QMPopulationStudy $s
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
        DBUnitTestCase::checkStudyChartHtml($s->studyHtml);
        $this->assertNotNull($s->studyHtml->fullStudyHtml);
        HtmlHelper::checkForMissingHtmlClosingTags($s->studyHtml->fullStudyHtml, 'fullStudyHtml');
        $this->assertNotNull($s->studyImages);
        return $s;
    }
    /**
     * @param QMGlobalVariableRelationship|array|object $ac
     * @param int $minimumUsers
     * @param bool $studyCorrelation
     */
    public function checkAggregatedCorrelationV4Properties($ac, int $minimumUsers = 0, bool $studyCorrelation = false){
        if(is_array($ac)){
            $ac = new QMGlobalVariableRelationship($ac);
        }
        if(!is_object($ac)){
            throw new LogicException("Provided aggregatedCorrelation is not an object and is: " . \App\Logging\QMLog::print_r($ac, true));
        }
        $this->checkSharedCorrelationV4Properties($ac, $studyCorrelation);
        $this->assertGreaterThan('0000-00-00 00:00:00', $ac->createdAt);
        $this->assertGreaterThan('0000-00-00 00:00:00', $ac->updatedAt);
        $this->assertGreaterThan(0, $ac->aggregateQMScore, "aggregateQMScore should be greater than 0!");
        $this->checkIntStringAndFloatsOnGlobalVariableRelationship($ac, $minimumUsers);
        $notNullAttributes = [
            'numberOfCorrelations',
            'numberOfUsers',
        ];
        $this->checkNotNullAttributes($notNullAttributes, $ac);
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
        return $this->getAndDecodeBody('v1/studies/created')->studies;
    }
    /**
     * @param QMUserVariableRelationship|object|array $correlation
     */
    public function checkUserVariableRelationshipObject($correlation){
        if(is_array($correlation)){
            $correlation = (object)$correlation;
        }
        $this->checkSharedCorrelationProperties($correlation);
        $intAttributes = [
            'userId',
            'userVote',
        ];
        DBUnitTestCase::checkIntAttributes($intAttributes, $correlation);
        $stringAttributes = [
            'studyLinkDynamic',
        ];
        $this->checkStringAttributes($stringAttributes, $correlation);
        $floatAttributes = [
            'predictsLowEffectChange',
            'predictsHighEffectChange',
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
     * @param string|null $type
     * @param int $expectedCode
     * @param int|null $userId
     * @return QMCohortStudy|QMPopulationStudy|QMUserStudy
     */
    public function createStudyTypeAndCheckResponse(string $type = null, int $expectedCode = 201,
                                                    int $userId = null){
        if($userId){
            $this->setAuthenticatedUser($userId);
        }
        if(!$userId){
            $expectedCode = 401;
        }
        $requestData = [
            'causeVariableName' => BupropionSrCommonVariable::NAME,
            'effectVariableName' => BackPainCommonVariable::NAME,
            'studyName' => "Super study",
            'type' => $type,
        ];
        $body = $this->postAndGetDecodedBody('/api/v1/study/create', $requestData, false, $expectedCode);
        if(!$userId){
            return $body;
        }
        $study = $body->study;
        $this->getStudyById($study->id, $userId);
        return $study;
    }
    /**
     * @param string $studyId
     * @param int|null $userId
     * @return QMStudy
     */
    private function getStudyById(string $studyId, int $userId = null){
        if($userId){
            $this->setAuthenticatedUser($userId);
        }
        /** @var QMStudy $study */
        $study = $this->getAndDecodeBody('v3/study', ['studyId' => $studyId]);
        $this->assertEquals($studyId, $study->id);
        //$row = Study::readonly()->where(Study::FIELD_ID, $studyId)->first();
        //$this->assertTrue(!empty($row->statistics));
        $body = $this->getAndDecodeBody('v4/studies', ['studyId' => $studyId]);
        $studies = $body->studies;
        $this->assertEquals($studyId, $studies[0]->id);
        return $study;
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
    public function createTestMeasurement(string $variableName = "Back Pain", string $unitAbbreviatedName = '/5',
                                          string $variableCategoryName = 'Symptoms'){
        $db = Writable::db();
        $db->table('measurements')->delete();
        $this->setAuthenticatedUser(1);
        $postData = '[{"measurements":[{"startTime":1406519965,"value":"3"}],
            "name":"' . $variableName . '","source":"test source name","category":"' . $variableCategoryName .
            '","combinationOperation":"MEAN","unit":"' . $unitAbbreviatedName . '",
            "latitude":26.56,"longitude":56.53,"location":"Test Location"}]';
        $this->postApiV3('measurements', $postData);
        $dbh = Writable::pdo();
        $sqlCheckVariables = "SELECT * FROM variables WHERE name = '" . $variableName . "'";
        $variables = $dbh->query($sqlCheckVariables)->fetchAll(PDO::FETCH_ASSOC);
        return $variables[0];
    }
    /**
     * @param string $clientId
     * @return ShareResponse
     */
    public function deleteShare(string $clientId, int $userId){
		$this->setAuthenticatedUser($userId);
        /** @var ShareResponse $response */
        $response = $this->deleteViaPostMethod('v1/shares/delete', ['clientIdToRevoke' => $clientId]);
        /** @var AuthorizedClients $authorizedClients */
        $authorizedClients = $response->authorizedClients;
        $this->assertIsArray($authorizedClients->studies);
        $this->assertIsArray($authorizedClients->individuals);
        if($authorizedClients->apps === null){
            le("authorizedClients->apps should not be null.  authorizedClients is " .
                var_export($authorizedClients, true));
        }
        $this->assertIsArray($authorizedClients->apps);
        return $response;
    }
    /**
     * @param string $apiUrl
     * @param array|string $postData
     * @param null $expectedString
     * @return object
     */
    public function deleteViaPostMethod(string $apiUrl, $postData, $expectedString = null){
        $response = $this->postApiV6($apiUrl, $postData, 204);
        return json_decode($response->getBody(), false);
    }
    /**
     * @param array $params
     * @return QMTrackingReminder[]
     */
    public function getAndCheckTrackingReminders(array $params = []): array
    {
        $trackingReminders = $this->getAndDecodeBody('api/v1/trackingReminders', $params);
        $trackingReminders = $trackingReminders->data;
        /** @var QMTrackingReminder[] $trackingReminders */
        $this->assertIsArray($trackingReminders);
        foreach($trackingReminders as $trackingReminder){
            $stringAttributes = [
                'pngUrl',
                'svgUrl',
                'ionIcon',
                'variableCategoryName',
                'unitAbbreviatedName',
                'variableName',
            ];
            $this->checkStringAttributes($stringAttributes, $trackingReminder);
            $intAttributes = [
                'unitId',
                'id',
                'reminderFrequency',
                'variableId',
            ];
            DBUnitTestCase::checkIntAttributes($intAttributes, $trackingReminder);
        }
        return $trackingReminders;
    }
    /**
     * @param array $params
     * @param string $clientId
     * @param string|null $clientSecret
     * @return AppSettings
     */
    public function getAppSettings(array $params = [],
                                   string $clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, string $clientSecret = null){
        $params['clientId'] = $clientId;
        if($clientSecret){
            $params['clientSecret'] = $clientSecret;
        }
        $getResponse = $this->getAndDecodeBody('api/v1/appSettings', $params);
        $appSettings = $getResponse->appSettings;
        $this->checkAppSettingsPropertiesExist($appSettings);
        return $appSettings;
    }
    /**
     * @param AppSettings|object $appSettings
     */
    public function checkAppSettingsPropertiesExist($appSettings){
        $appSettingsProperties = ObjectHelper::getAllPropertiesOfClassAsKeyArray(new AppSettings());
        foreach($appSettingsProperties as $propertyName){
            $this->assertObjectHasAttribute($propertyName, $appSettings);
        }
//		$buildSettingsProperties = BuildSettings::getAllProperties();
//		foreach($buildSettingsProperties as $propertyName){
//			$this->assertObjectHasAttribute($propertyName, $appSettings->additionalSettings->buildSettings);
//		}
        $this->checkBooleanAttributes(BuildSettings::getBooleanPropertyNames(),
            $appSettings->additionalSettings->buildSettings);
    }
    /**
     * @param string $variableName
     * @return QMMeasurement[]
     */
    public function getMeasurementsForVariable(string $variableName){
        $this->setAuthenticatedUser(1);
        $body = $this->getAndDecodeBody("/api/v1/measurements", ['variableName' => $variableName]);
        return $body;
    }
    /**
     * @param string $apiUrl
     * @param array $params
     * @return Response
     */
    public function getWithoutResponseValidation(string $apiUrl, array $params){
        if(strpos($apiUrl, 'api/') === false){
            $apiUrl = '/api/' . $apiUrl;
        }
        $apiUrl = '/' . $apiUrl;
        $apiUrl = str_replace('//', '/', $apiUrl);
        $params = $this->getQueryParamsFromUrl($apiUrl, $params);
        $apiUrl = QMStr::before('?', $apiUrl, $apiUrl);
        $response = static::slimCall([
            'REQUEST_METHOD' => QMRequest::METHOD_GET,
            'PATH_INFO' => $apiUrl,
            'QUERY_STRING' => http_build_query($params),
            'HTTP_COOKIE' => '',
        ], $params);
        self::resetHttpGlobals();
        //QMAuth::logout(__METHOD__);
        return $response;
    }
    /**
     * @param $apiUrl
     * @param array $params
     * @return array
     */
    private static function getQueryParamsFromUrl($apiUrl, array $params): array{
        if(stripos($apiUrl, '?') !== false){
            $stringParams = QMRequest::getQueryParamsFromStringAndRequest($apiUrl);
            $params = array_merge($params, $stringParams);
        }
        return $params;
    }
    public static function resetHttpGlobals(): void{
        $_GET = []; // Need to reset anything left over from previous tests
        foreach($_SERVER as $key => $value){
            if(strpos($key, "HTTP_") === 0){
                unset($_SERVER[$key]);
            }
        }
        AppMode::setIsApiRequest(false);
    }
    /**
     * @param array $env
     * @param array $query
     * @return void
     */
    public static function logTestUrl(array $env, array $query): string{
        if(isset($env["HTTP_AUTHORIZATION"])){
            $query['access_token'] = $env["HTTP_AUTHORIZATION"];
        }
        $withQuery = $env["PATH_INFO"];
        if($query){
            $withQuery .= "?" . Query::build($query);
        }
        $url = UrlHelper::LOCAL_ORIGIN . $withQuery;
        QMLog::immediately($env["REQUEST_METHOD"] . " $url" . "...");
        return $env["REQUEST_METHOD"] . " $url";
    }
    /**
     * @param array $environmentSettings
     * @param $body
     */
    public static function globalResponseChecks(array $environmentSettings, $body){
        StudyTest::globalStudyChecks($body, $environmentSettings);
    }
    /**
     * @return string Cookies required for an ordinary request.
     */
    protected function getCookies(){
        $cookies = '';
        return $cookies;
    }
    /**
     * @param $postData
     * @param string|null $unitAbbreviatedName
     * @param int $expectedCode
     * @return Response
     */
    public function postAndCheckMeasurementsResponse($postData, string $unitAbbreviatedName = null,
                                                     int $expectedCode = 201){
        $start = microtime(true);
        $response = $this->postApiV6('v6/measurements', $postData, false, $expectedCode);
        $firstRequestDuration = microtime(true) - $start;
        $this->assertLessThan(30, $firstRequestDuration);
        if($expectedCode < 300){
            return $this->checkPostMeasurementsResponse($response, $unitAbbreviatedName);
        }
        return null;
    }
    /**
     * @param Response $response
     * @param string|null $unitAbbreviatedName
     * @return mixed
     */
    public function checkPostMeasurementsResponse(Response $response, string $unitAbbreviatedName = null){
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
                if(!$m->id){
                    le("measurement in response does not have id", $m);
                }
                $this->assertNotNull($m->id, "measurement in response does not have id");
            }
        }
        if($unitAbbreviatedName){
            $this->assertEquals($unitAbbreviatedName, $uv->unitAbbreviatedName);
        }
        return $decodedResponse->data;
    }
    /**
     * @param array $variableSettings
     */
    public function postVariableSettings(array $variableSettings): void{
        $this->setAuthenticatedUser(1);
        $body = $this->postAndGetDecodedBody('/api/v1/userVariables', $variableSettings);
        $this->assertNotNull($body->userVariable);
        $this->assertNotNull($body->userVariables);
        $this->assertEquals($body->userVariable->variableId, $body->userVariable->id,
            "Need to return variable id instead of user variable id for backward compatibility");
    }
    /**
     * @param int $userId
     * @param MeasurementSet[] $submittedMeasurementSets
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws NoChangesException
     */
    public function saveMeasurementSets(int $userId, array $submittedMeasurementSets){
        foreach($submittedMeasurementSets as $set){
            $set->clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
        BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        MeasurementSet::saveMeasurementSets($userId, $submittedMeasurementSets);
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
     * @param string $studyId
     * @return QMStudy
     */
    /**
     * @param array $causeMeasurementItems
     * @param array $effectMeasurementItems
     * @param string|null $causeUnit
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     * @throws NoChangesException
     */
    protected function saveCauseAndEffectMeasurementItems(array $causeMeasurementItems, array $effectMeasurementItems,
                                                          string $causeUnit = null){
        $cause = $this->getCauseUserVariable();
        $this->assertEquals(1800, $cause->onsetDelay);
        $this->assertZeroFillingValue($cause);
        $effect = $this->getEffectUserVariable();
        $this->assertNoFillingValue($effect);
        BaseClientIdProperty::setInMemory(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $userId = 1;
        $measurementSets = [];
        if(!$causeUnit){
            $causeUnit = "mg";
        }
        $measurementSets[] =
            new MeasurementSet($cause->name, $causeMeasurementItems, $causeUnit, $cause->getVariableCategoryName(),
                'test', $cause->getOrSetCombinationOperation());
        $measurementSets[] =
            new MeasurementSet($effect->name, $effectMeasurementItems, $effect->getUnitAbbreviatedName(),
                $effect->getVariableCategoryName(), 'test', $effect->getOrSetCombinationOperation());
        foreach($measurementSets as $s){
            $s->clientId = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
        }
        MeasurementSet::saveMeasurementSets($userId, $measurementSets);
        $numberOfSavedMeasurements = Measurement::count();
        $totalMeasurementsToSave = count($causeMeasurementItems) + count($effectMeasurementItems);
        if($totalMeasurementsToSave !== $numberOfSavedMeasurements){
            MeasurementSet::saveMeasurementSets($userId, $measurementSets);
        }
        $this->assertEquals($totalMeasurementsToSave, $numberOfSavedMeasurements);
        $this->assertEquals(count($causeMeasurementItems),
            Measurement::whereVariableId($cause->getVariableIdAttribute())->count());
        $this->assertEquals(count($effectMeasurementItems),
            Measurement::whereVariableId($effect->getVariableIdAttribute())->count());
    }
    /**
     * @return QMUserVariable
     */
    public function getCauseUserVariable(): QMUserVariable{
        $v = $this->getUserVariable('CauseVariableName', [
            Variable::FIELD_VARIABLE_CATEGORY_ID => TreatmentsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID => MilligramsUnit::ID,
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_SUM,
        ]);
        $this->assertTrue($v->isPredictor());
        $this->assertFalse($v->isOutcome());
        $this->assertZeroFillingValue($v);
        $this->assertEquals(1, $v->getInterestingFactor());
        return $v;
    }
    /**
     * @param $idOrName
     * @param array $newVariableParams
     * @return QMUserVariable
     */
    public function getUserVariable($idOrName, array $newVariableParams = []): QMUserVariable{
        $uv = QMUserVariable::findOrCreateByNameOrId($this->getOrSetAuthenticatedUser(1)->getId(), $idOrName, [],
            $newVariableParams);
        if(!$uv->variableName){
            le('!$uv->variableName');
        }
        $this->assertEquals($uv->displayName . " Overview", $uv->getReportTitleAttribute());
        if($uv->onsetDelay === null){
            le('$uv->onsetDelay === null');
        }
        return $uv;
    }
    /**
     * @return QMUserVariable
     */
    public function getEffectUserVariable(): QMUserVariable{
        $v = $this->getUserVariable('EffectVariableName', [
            Variable::FIELD_VARIABLE_CATEGORY_ID => SymptomsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID => PercentUnit::ID,
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_MEAN,
        ]);
        if($v->onsetDelay === null){
            le('$v->onsetDelay === null');
        }
        $this->assertTrue($v->isPredictor());
        $this->assertTrue($v->isOutcome());
        $this->assertNoFillingValue($v);
        $this->assertEquals(1, $v->getInterestingFactor());
        return $v;
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
     * @param QMUserVariableRelationship $nc
     * @return QMUserVariableRelationship
     */
    protected function checkNegativeCorrelation(QMUserVariableRelationship $nc): QMUserVariableRelationship{
        $this->assertNotNull($nc);
        $this->assertEquals(-1, $nc->correlationCoefficient);
        $this->assertEquals(-1, $nc->strongestPearsonCorrelationCoefficient);
        $this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS,
            (float)$nc->avgDailyValuePredictingLowOutcome);
        $this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS,
            (float)$nc->avgDailyValuePredictingHighOutcome);
        $this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->averageDailyHighCause);
        $this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$nc->averageDailyLowCause);
        $study = $this->getUserStudyV4();
        $statistics = $study->statistics;
        $this->assertContains('lower', strtolower($study->studyText->studyTitle),
            "correlation: " . $statistics->correlationCoefficient . " value predicting high: " .
            $statistics->avgDailyValuePredictingHighOutcome . " low: " .
            $statistics->avgDailyValuePredictingLowOutcome);
        return $nc;
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
                Measurement::FIELD_START_TIME => self::BASE_TIME + self::DAY * $i,
                Measurement::FIELD_VALUE => $measurementValue,
                Measurement::FIELD_ORIGINAL_VALUE => $measurementValue,
                Measurement::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            ]);
            $effectMeasurementItems[] = $effect->newMeasurementData([
                Measurement::FIELD_START_TIME => self::BASE_TIME + self::DAY * ($i + 1),
                Measurement::FIELD_VALUE => $measurementValue,
                Measurement::FIELD_ORIGINAL_VALUE => $measurementValue,
                Measurement::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            ]);
        }
        $cause->bulkMeasurementInsert($causeMeasurementItems);
        $effect->bulkMeasurementInsert($effectMeasurementItems);
    }
    /**
     * @throws InvalidTimestampException
     */
    protected function checkOptimalValueMessageAndStudyCardsForUserVariables(){
        $cause = QMUserVariable::getByNameOrId(1, "CauseVariableName");
        //$cause->update(true);
        $this->assertEquals("Higher CauseVariableName Intake predicts significantly higher EffectVariableName.  EffectVariableName was 25% higher following above average CauseVariableName over the previous 24 hours. ",
            //"Your EffectVariableName was lowest after a daily total of ",
            $cause->userOptimalValueMessage);
        $this->assertNotFalse(stripos($cause->userOptimalValueMessage, "CauseVariableName"));
        $primaryOutcome = QMUserVariable::getByNameOrId(1, "EffectVariableName");
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
        $row = UserVariable::whereVariableId($cause->variableId)->where(UserVariable::FIELD_USER_ID, $cause->userId)
            ->first();
        $this->assertNotNull($row->best_user_variable_relationship_id);
        $this->assertNotNull($row->optimal_value_message);
        $row = UserVariable::whereVariableId($primaryOutcome->variableId)
            ->where(UserVariable::FIELD_USER_ID, $primaryOutcome->userId)->first();
        $this->assertNotNull($row->best_user_variable_relationship_id);
        $this->assertNotNull($row->optimal_value_message);
        $notifications = $this->getAndCheckNotificationsAndFeed();
        $message = "We should have gotten a notification card for effect because a reminder should have been " .
            "created when updating primary outcome";
        $this->assertCount(1, $notifications, $message);
        $cards = $this->getAndCheckFeedCards();
        $notificationCards = collect($cards)->where('type', QMCard::TYPE_tracking_reminder_notification)->all();
        $studyCards = collect($cards)->where('type', QMCard::TYPE_study)->all();
        $this->assertCount(1, $studyCards, "No study cards!");
        $this->assertTrue(count($notificationCards) > 0, $message);
        $this->checkUserStudyCardFromAPI(collect($studyCards)->first());
    }
    /**
     * @param array $params
     * @param string $apiUrl
     * @return QMTrackingReminderNotification[]
     * @throws InvalidTimestampException
     */
    public function getAndCheckNotificationsAndFeed(array $params = [],
                                                    string $apiUrl = '/api/v1/trackingReminderNotifications'){
        $response = $this->getAndDecodeBody($apiUrl, $params);
        /** @var QMTrackingReminderNotification[] $notifications */
        $notifications = $response->data;
        $this->checkTrackingReminderNotificationProperties($notifications, null);
        $past = [];
        $currentTime = time();
        foreach($notifications as $notification){
            $nTime = $notification->trackingReminderNotificationTimeEpoch;
            if(!$nTime){
                le('!$nTime');
            }
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
        $this->assertTrue(count($notificationCards) > count($past) - 1);
        return $notifications;
    }
    /**
     * @param QMTrackingReminderNotification[] $trackingReminderNotifications
     * @param null $numberOfReminders
     * @throws InvalidTimestampException
     */
    public function checkTrackingReminderNotificationProperties(array $trackingReminderNotifications,
                                                                      $numberOfReminders){
        foreach($trackingReminderNotifications as $n){
            $this->checkTrackingReminderNotification($n, $numberOfReminders);
        }
        $this->assertIsArray($trackingReminderNotifications);
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
            'inputType',
        ];
        foreach($trackingReminderNotifications as $n){
            $this->checkNotNullAttributes($notNullAttributes, $n);
        }
        $floatAttributes = [
            'maximumAllowedValue',
            'minimumAllowedValue',
        ];
        foreach($trackingReminderNotifications as $n){
            $v = QMUserVariable::getByNameOrId($n->userId, $n->variableId);
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
            /** @noinspection PhpUndefinedFieldInspection */
            $this->assertEquals($n->trackingReminderNotificationTimeEpoch,
                $card->parameters->trackingReminderNotificationTimeEpoch);
        }
    }
    /**
     * @param QMTrackingReminderNotification|object $n
     * @param int|null $numberOfReminders
     */
    private function checkTrackingReminderNotification($n, int $numberOfReminders = null){
        $frequency = $n->reminderFrequency;
        $longQuestion = $n->longQuestion;
        $question = $n->question;
        $this->assertGreaterThan(0, $n->userVariableId);
        $this->assertNotNull($n->userVariableId);
        $this->assertNotEmpty($n->title);
        $time = $n->trackingReminderNotificationTimeEpoch;
        $user = $this->getOrSetAuthenticatedUser(1);
        $hoursSinceMidnight = $user->getHourDifferenceFromLastMidnight($time);
        $message = "HourDifferenceFromLastMidnight is $hoursSinceMidnight and long question is " . $longQuestion;
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
    /**
     * @param $trackingReminderNotification
     * @return int
     */
    private function getNumberOfRemindersForVariable($trackingReminderNotification){
        $numberOfReminders = TrackingReminder::query()
            ->where(TrackingReminder::FIELD_USER_ID, $trackingReminderNotification->userId)
            ->where(TrackingReminder::FIELD_VARIABLE_ID, $trackingReminderNotification->variableId)
            ->count();
        return $numberOfReminders;
    }
    /**
     * @param $arr
     */
    public function assertUnique($arr){
        $this->assertArrayEquals(array_unique($arr), $arr);
    }
    /**
     * @return QMCard[]
     */
    public function getAndCheckFeedCards(){
        /** @var UserFeedResponse $feedResponse */
        $feedResponse = $this->getAndDecodeBody('v1/feed');
        $this->checkFeedCards($feedResponse->cards);
        return $feedResponse->cards;
    }
    /**
     * @param QMCard[]|object[] $cards
     */
    private function checkFeedCards(array $cards){
        foreach($cards as $card){
            if($card->type !== QMCard::TYPE_intro && $card->type !== QMCard::TYPE_onboarding){
                if(!$card->actionSheetButtons){
                    $this->assertNotNull($card->actionSheetButtons,
                        "No action sheet buttons on this card: " . QMStr::print($card));
                }
            }
            if($card->type === QMCard::TYPE_tracking_reminder_notification){
                $this->checkTrackingReminderNotificationCard($card);
            }
        }
    }
    /**
     * @param QMCard|object $card
     */
    private function checkTrackingReminderNotificationCard($card){
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals($card->id, $card->parameters->trackingReminderNotificationId);
        /** @var NumberInputField $inputField */
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
                $user = $this->getOrSetAuthenticatedUser(1);
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
     * @param QMCard|object $card
     * @param string $text
     * @return null|QMButton
     */
    protected function getActionSheetButtonWithText($card, string $text){
        return $this->getButtonWithText($card, $text, 'actionSheetButtons');
    }
    /**
     * @param QMCard|object $card
     * @param string $text
     * @param string $buttonType
     * @return QMButton|null
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
     * @param QMCard|object $card
     * @param string $text
     * @return QMButton
     */
    protected function getSecondaryButtonWithText($card, string $text){
        return $this->getButtonWithText($card, $text, 'buttonsSecondary');
    }
    /**
     * @param StudyCard|object $studyCard
     */
    private function checkUserStudyCardFromAPI(object $studyCard){
        $html = $studyCard->htmlContent;
        $this->assertNotEmpty($html);
        $this->assertNotContains('for most', strtolower($html));
        //$this->assertContains("higher following above average", $html);
        //$this->assertContains("Your ", $html);
        $this->assertContains("Predicts", $html);
        $this->cardButtonsDoNotContain($studyCard, "Join Study");
        /** @noinspection PhpUndefinedFieldInspection */
        $this->assertEquals($studyCard->id, $studyCard->parameters->studyId);
    }
    /**
     * @param QMCard|object $card
     * @param string $string
     */
    private function cardButtonsDoNotContain($card, string $string){
        foreach($card->buttons as $button){
            $this->assertNotContains($string, json_encode($button));
        }
    }
    /**
     * @param QMUserVariableRelationship $correlation
     * @return QMUserVariableRelationship
     */
    private function checkCorrelationPropertiesAndStudyText(QMUserVariableRelationship $correlation): QMUserVariableRelationship{
        $this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS,
            (float)$correlation->avgDailyValuePredictingHighOutcome);
        $this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS,
            (float)$correlation->avgDailyValuePredictingLowOutcome);
        $this->assertGreaterThan(0.7 * self::NUMBER_OF_GENERATED_MEASUREMENTS,
            (float)$correlation->averageDailyHighCause);
        $this->assertLessThan(0.3 * self::NUMBER_OF_GENERATED_MEASUREMENTS, (float)$correlation->averageDailyLowCause);
        $study = $this->getUserStudyV4();
        $this->assertContains('higher', strtolower($study->studyText->studyTitle));
        return $correlation;
    }
    /**
     * @return QMUserVariableRelationship
     * @throws BadRequestException
     */
    public function seedWithPositivePurchaseCorrelation(): QMUserVariableRelationship{
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurements = [];
        $effectMeasurements = [];
        $latestEffectMeasurementAt = null;
        $baseTime = Stats::roundToNearestMultipleOf(self::BASE_TIME,
            SymptomsVariableCategory::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS);
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
        $effect = $this->getEffectUserVariable();
        $effect->saveMultipleMeasurements($effectMeasurements);
        $cause->analyzeFully(__FUNCTION__);
        $effect->analyzeFully(__FUNCTION__);
        $this->assertDateEquals($effect->l()->latest_source_measurement_start_at,
            UserVariable::find($effect->id)->latest_source_measurement_start_at,
            '$effect->l()->latest_source_measurement_start_at', 'latest_source_measurement_start_at from DB');
        $user = $cause->getQMUser();
        $sources = $user->getUserDataSources();
        $dataSources = $cause->getDataSourceNames();
        $this->assertGreaterThan(0, count($dataSources), "There should be data sources for cause!");
        $earliestCauseSourceTime = $cause->l()->earliest_source_measurement_start_at;
        $this->assertDateEquals($earliestEffectMeasurementTime, $earliestCauseSourceTime);
        $latestSourceAt = $cause->l()->latest_source_measurement_start_at;
        $this->assertDateEquals($latestEffectMeasurementAt, $latestSourceAt, 'latestEffectMeasurementTime',
            'latestSourceTime');
        $dailyCause = $cause->getValidDailyMeasurementsWithTagsAndFilling();
        foreach($dailyCause as $measurement){
            if($measurement->getValue() > 1){
                le("Cause value should not exceed 1!");
            }
        }
        $this->assertDateEquals($cause->l()->latest_source_measurement_start_at,
            $effect->l()->latest_source_measurement_start_at, 'cause->getLatestSourceAt', 'effect->getLatestSourceAt',
            "They both have same source");
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
        self::assertDateEquals($earliestCauseMeasurementTime - 30 * self::DAY, $earliestCauseFillingTime,
            'earliestCauseMeasurementTime - 30 * self::DAY', '$earliestCauseFillingTime',
            "Earliest cause filling should be one month before earliest measurement time because that's still after earliest source time");
        $this->assertDateEquals($cause->l()->latest_source_measurement_start_at,
            $effect->l()->latest_source_measurement_start_at, 'cause->getLatestSourceAt', 'effect->getLatestSourceAt',
            "They both have same source");
        $causeLatestFillingAt = $cause->getLatestFillingAt();
        $this->assertDateEquals($latestSourceAt, $causeLatestFillingAt, 'latestSourceTime', 'causeLatestFillingTime');
        $eEarliestFilling = $effect->getEarliestFillingAt();
        $minSecs = $effect->getMinimumAllowedSecondsBetweenMeasurements();
        $expected = Stats::roundToNearestMultipleOf(1348159040, $minSecs);
        $this->assertDateEquals($expected, $eEarliestFilling, 'Stats::roundToNearestMultipleOf(1348159040, $minSecs)',
            'effectEarliestFilling');
        $effectLatestFillingAt = $effect->getLatestFillingAt();
        $expected = Stats::roundToNearestMultipleOf(1356712640, $minSecs);
        //$this->assertEquals($expected, $effectLatestFillingTime);
        $pairs = $c->getPairs();
        $this->assertCount(80, $pairs, "Should have 80 pairs");
        $this->assertEquals(QMVariableRelationship::DIRECTION_HIGHER, $c->direction);
        $this->assertEquals(BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_strongly_positive,
            $c->effectSize);
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
            'causeVariableName' => $cause->name,
            'effectVariableName' => $effect->name,
        ]);
        $this->assertContains('higher', strtolower($study->studyText->studyTitle));
        return $c;
    }
    /**
     * @return QMUserVariable
     */
    public function getCausePurchaseUserVariable(): QMUserVariable{
        $v = $this->getUserVariable(VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX .
            ' CauseVariableName', [
            Variable::FIELD_VARIABLE_CATEGORY_ID => TreatmentsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID => DollarsUnit::ID,
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_SUM,
        ]);
        $this->assertEquals(QMCommonVariable::PURCHASE_DURATION_OF_ACTION, $v->durationOfAction);
        return $v;
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
            $effectMeasurementItems[] =
                new QMMeasurement(self::BASE_TIME + self::DAY * ($i + 1) + $min, $measurementValue);
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
     * @param bool $correlateOverTime
     * @return QMUserVariableRelationship
     */
    public function calculateCorrelation(bool $correlateOverTime = true){
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $cause = $this->getCauseUserVariable();
        $this->assertEquals(1800, $cause->getOnsetDelay());
        $cause->analyzeFullyIfNecessary(__FUNCTION__);
        $causeMeasurements = $cause->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertGreaterThan(50, $causeMeasurements);
        $effect = $this->getEffectUserVariable();
        if($effect->onsetDelay === null){
            le('$effect->onsetDelay === null');
        }
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
     * @param QMVariableRelationship $correlation
     * @param bool $correlateOverTime
     */
    public function checkCalculatedCorrelationObject(QMVariableRelationship $correlation, bool $correlateOverTime = true){
        if(is_array($correlation)){
            $correlation = (object)$correlation;
        }
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
     * @param QMUserVariableRelationship|QMGlobalVariableRelationship|object $correlation
     * @param bool $studyCorrelation
     */
    public function checkSharedCorrelationV4Properties($correlation, bool $studyCorrelation = false){
        if(is_array($correlation)){
            $correlation = (object)$correlation;
        }
        if(!is_object($correlation)){
            throw new LogicException("Provided correlation is not an object and is: " .
                \App\Logging\QMLog::print_r($correlation, true));
        }
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
            'studyLinks',
        ];
        if(!$studyCorrelation){
            $this->checkNotNullAttributes($notNullAttributes, $correlation);
        }
        $this->checkNotNullAttributes($this->getNotNullAttributes(), $correlation);
    }
    /**
     * @param array|null $params
     * @return QMUserStudy|object
     */
    public function getUserStudyV4(array $params = null){
        if(!$params){
            $params = [
                'causeVariableName' => 'CauseVariableName',
                'effectVariableName' => 'EffectVariableName',
            ];
        }
        $response = $this->slimGet('/api/v4/study', $params);
        return DBUnitTestCase::checkUserStudyWithData($response);
    }
    /**
     * @param Response|QMStudy $response
     * @return QMUserStudy
     */
    public static function checkUserStudyWithData($response){
        $s = self::checkUserStudyWithOrWithoutData($response);
        /** @var StudyText $studyText */
        $studyText = $s->studyText;
        QMStr::assertStringDoesNotContain($studyText->studyAbstract, "couldn't determine",
            "Study contains text: couldn't determine");
        self::assertNotSame($s->statistics->avgDailyValuePredictingHighOutcome,
            $s->statistics->avgDailyValuePredictingLowOutcome);
        return $s;
    }
    /**
     * @param $response
     * @return QMUserStudy
     */
    public static function checkUserStudyWithOrWithoutData($response){
        if($response instanceof Response){
            $s = json_decode($response->getBody(), false);
        } else{
            $s = $response;
        }
        /** @var QMUserStudy $s */
        self::assertEquals(StudyTypeProperty::TYPE_INDIVIDUAL, $s->type, "Type is " . $s->type);
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
        DBUnitTestCase::checkStudyChartHtml($studyHtml);
        self::assertNotNull($s->studyImages);
        self::assertNotNull($s->userId);
        return $s;
    }
    /**
     * @param StudyHtml|object $sh
     */
    public static function checkStudyChartHtml($sh){
        if(QMStudy::USE_STATIC_CHART_IMAGES){
            static::assertStringContains($sh->fullStudyHtml, [
                'class="chart-img"',
                ImageHelper::CHART_IMAGE_STYLES,
            ], AppMode::getCurrentTestName() . "-" . __FUNCTION__);
        } else{
            static::assertStringContains($sh->fullStudyHtml, [
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
    public function assertBetween(float $min, float $max, float $actual, string $message = ''){
        $this->assertLessThan($max, $actual, $message);
        $this->assertGreaterThan($min, $actual, $message);
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
            'PATH_INFO' => $apiUrl,
            'slim.input' => $postData,
            'HTTP_COOKIE' => $this->getCookies(),
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
     * @param string $apiUrl
     * @param Response $response
     * @return mixed|QMResponseBody
     */
    public static function decodeBodyAndLogErrors(string $apiUrl, Response $response){
        $decodedBody = json_decode($response->getBody(), false);
        if($decodedBody && !is_array($decodedBody) && isset($decodedBody->errors)){
            foreach($decodedBody->errors as $qmError){
                if(is_object($qmError) || is_array($qmError)){
                    QMLog::error("$apiUrl error: " . \App\Logging\QMLog::print_r($qmError, true));
                } else{
                    QMLog::error("$apiUrl error: " . $qmError);
                }
            }
            if(isset($decodedBody->message) && !empty($decodedBody->message)){
                ConsoleLog::info("\n\n$apiUrl response message:\n" . $decodedBody->message, []);
            }
        }
        return $decodedBody;
    }
    /**
     * @param string $expected
     * @param Response $response
     */
    public static function assertResponseBodyContains(string $expected, Response $response){
        $body = $response->getBody();
        self::assertStringContainsString($expected, $body,
            "Body does not contain: " . $expected.
            "\n\tActual Body: $body");
    }

    /**
     * @param string|null $variableName
     * @param string $clientId
     */
    protected function assertHasTrackingReminderFor(string $variableName, string $clientId){
        $reminders = $this->getOrSetAuthenticatedUser(1)->getTrackingReminders();
        $this->assertTrue(count($reminders) > 0);
        $lemonsReminders = $this->getOrSetAuthenticatedUser(1)->getTrackingRemindersByVariableName($variableName);
        $this->assertTrue(count($lemonsReminders) > 0, "No reminders for $variableName");
        $this->assertEquals($clientId, $lemonsReminders[0]->getClientId());
    }
    /**
     * @param int $expected
     */
    protected function assertNumberOfTrackingRemindersEquals(int $expected){
        $reminders = $this->getOrSetAuthenticatedUser(1)->getTrackingReminders();
        $numberOfTrackingReminders = count($reminders);
        $this->assertEquals($expected, $numberOfTrackingReminders, TrackingReminder::generateAstralIndexUrl());
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
        $row = Variable::find($cause->variableId);
        $this->assertNotNull($row->best_global_variable_relationship_id);
        $this->assertNotNull($row->optimal_value_message);
        $row = Variable::find($primaryOutcome->variableId);
        $this->assertNotNull($row->best_global_variable_relationship_id);
        $this->assertNotNull($row->optimal_value_message);
        $this->assertEquals($cause->variableId, $primaryOutcome->getCommonBestCauseVariableId());
        $notifications = $this->getAndCheckNotificationsAndFeed();
        $cards = $this->getAndCheckFeedCards();
        $this->assertTrue(count($cards) > count($notifications));
    }
    protected function createTreatmentOutcomeMeasurementsFor2Users(){
        if($dump = TestDB::shouldRegenerateFixtures()){
            $this->createTreatmentOutcomeMeasurements(1);
            $this->createTreatmentOutcomeMeasurements(2);
            TestDB::generateSeeds(__FUNCTION__);
        } else{
            TestDB::loadFixtures(__FUNCTION__);
            UserVariable::where(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION, ">", 1)
                ->update([UserVariable::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 0]);
        }
        $this->makeSureVariablesNeedCorrelation(4);
    }
    /**
     * @param int $userId
     * @return Measurement[]
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueException
     */
    protected function createTreatmentMeasurements(int $userId = 1): array
    {
        $baselineValue = 0;
        $treatmentValue = 150;
        $uncombined = $this->generateHighLowMeasurementsForLast120Days();
        $treatment = $this->getTreatmentUserVariable($userId);
        $uv = $treatment->getUserVariable();
        $this->assertZeroFillingValue($treatment);
        $effectMeasurementItems = [];
        foreach($uncombined as $m){
            $m->sourceName = BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT;
            if($m->originalValue > 3){
                $m->setOriginalValue($treatmentValue);
            } else{
                $m->setOriginalValue($baselineValue);
            }
            $m->setOriginalUnitByNameOrId(MilligramsUnit::ID);
            //$treatment->addToMeasurementQueue($m);
            $effectMeasurementItems[] = $uv->newMeasurementData([
                Measurement::FIELD_START_TIME => $m->startTime,
                Measurement::FIELD_VALUE => $m->originalValue,
                Measurement::FIELD_ORIGINAL_VALUE => $m->originalValue,
                Measurement::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
                Measurement::FIELD_SOURCE_NAME => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            ]);
        }
        $measurements = $uv->bulkMeasurementInsert($effectMeasurementItems);
        $str = $uv->getDBModel()->getCorrelationDataRequirementAndCurrentDataQuantityString();
        $this->assertNotNull($str);
        $this->checkLatestTimesOnNewVariable($measurements);
        $values = $treatment->getUniqueValuesWithTagsInReverseOrder();
        $this->assertCount(2, $values, UserVariable::generateDataLabUrl($treatment->id));
        return $measurements;
    }
    /**
     * @param int $userId
     * @return QMUserVariable
     */
    protected function getTreatmentUserVariable(int $userId = 1): QMUserVariable{
        return BupropionSrCommonVariable::getUserVariableByUserId($userId);
    }
    /**
     * @param Measurement[] $measurements
     */
    protected function checkLatestTimesOnNewVariable(array $measurements): void{
        $uncombined = collect($measurements);
        /** @var QMMeasurement $lastMeasurement */
        $lastMeasurement = $uncombined->last();
        $userVariableId = $lastMeasurement->getUserVariableId();
        $v = UserVariable::find($userVariableId);
        $fromDb = Measurement::whereVariableId($lastMeasurement->getVariableIdAttribute())
            ->orderBy(Measurement::FIELD_START_TIME, 'desc')->first();
        $newest_data_at = $v->newest_data_at;
        $this->assertDateWithinXSecondsOf(2, $fromDb->max(Measurement::UPDATED_AT),
            $newest_data_at->toDateTimeString(), 'UPDATED_AT', 'newest_data_at');
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_AT),
            $v->latest_non_tagged_measurement_start_at->toDateTimeString());
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_AT),
            $v->latest_tagged_measurement_start_at->toDateTimeString());
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME),
            $v->latest_non_tagged_measurement_start_at);
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_tagged_measurement_start_at);
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_filling_time);
        $this->assertDateEquals($fromDb->max(Measurement::FIELD_START_TIME), $v->latest_source_measurement_start_at);
    }
    /**
     * @param int $expected
     */
    protected function makeSureVariablesNeedCorrelation(int $expected): void{
        $collection = UserVariable::all();
        $variables = $collection->sortBy('user_id');
        $names = $variables->map(function(UserVariable $v){
            return ['variable' => $v->getVariableName(), 'user' => $v->getUser()->user_login];
        })->toArray();
        $this->assertArrayEquals(array (
            0 =>
                array (
                    'variable' => 'Overall Mood',
                    'user' => 'quantimodo',
                ),
            1 =>
                array (
                    'variable' => 'Bupropion Sr',
                    'user' => 'quantimodo',
                ),
            2 =>
                array (
                    'variable' => 'Overall Mood',
                    'user' => 'quint',
                ),
            3 =>
                array (
                    'variable' => 'Bupropion Sr',
                    'user' => 'quint',
                ),
        ), $names);
        $this->assertCount($expected, $variables, "whe should have $expected user variables");
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
     * @return QMTrackingReminderNotification[]
     * @throws InvalidTimestampException
     */
    protected function createYesNoReminderAndNotifications(){
        $this->setAuthenticatedUser(1);
        $this->deleteMeasurementsAndReminders();
        $this->postAndCheckTrackingRemindersResponse([
            'variableName' => 'Hot Shower',
            'variableCategoryName' => 'Treatments',
            'timeZoneOffset' => 300,
            'reminderFrequency' => 86400,
            'unitAbbreviatedName' => 'yes/no',
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
     */
    protected function deleteMeasurementsAndReminders(){
        Measurement::deleteAll();
        TrackingReminder::deleteAll();
        TrackingReminderNotification::deleteAll();
        UserVariableClient::deleteAll();
        UserClient::deleteAll();
    }
    /**
     * @param array|string $postData
     * @param string|null $expectedUnitAbbreviatedName
     * @param bool $requireNotifications
     * @return Response|TrackingRemindersResponse
     */
    public function postAndCheckTrackingRemindersResponse($postData, string $expectedUnitAbbreviatedName = null,
                                                          bool $requireNotifications = true){
        if(is_string($postData)){
            $postData = json_decode($postData, true);
        }
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('trackingReminders', $postData);
        $this->assertEquals(201, $response->getStatus(), DBUnitTestCase::getErrorMessageFromResponse($response));
        $decodedResponse = json_decode($response->getBody(), false);
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
            $u = $this->getOrSetAuthenticatedUser(1);
            $localHis = $u->utcToLocalHis($utcHis);
            $allTimes = $reminders[0]->localDailyReminderNotificationTimesForAllReminders;
        }
        $this->assertGreaterThan(0, count($reminders));
        return json_decode($response->getBody(), false);
    }
    protected function dumpTableForEachTestFixture(): void{
        Writable::statementStatic("
            update user_variable_relationships c
                set c.cause_variable_id = c.cause_variable_id,
                    c.effect_variable_id = c.effect_variable_id;
            update global_variable_relationships c
                set c.cause_variable_id = c.cause_variable_id,
                    c.effect_variable_id = c.effect_variable_id;
            update votes v
                set v.cause_variable_id = v.cause_variable_id,
                    v.effect_variable_id = v.effect_variable_id;
        ");
        $this->dumpSeeds();
    }
    /**
     * @param array $params
     * @return QMUserVariable[]
     */
    private function getAndCheckUserVariablesV4(array $params = []){
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
     * @param $attributes
     * @param $object
     */
    public function checkDoesNotHaveAttributes($attributes, $object){
        foreach($attributes as $attribute){
            $this->assertNotTrue(isset($object->$attribute), "Should not have $attribute attribute");
        }
    }
    /**
     * @param array $params
     * @param $variable
     */
    private function checkUserVariableHighchartsConfigs(array $params, $variable){
        if(isset($params[QMRequest::PARAM_INCLUDE_CHARTS])){
            /** @var UserVariableChartGroup $charts */
            $charts = $variable->charts;
            /** @var QMChart $chart */
            foreach($charts as $key => $chart){
                if(!is_object($chart)){
                    continue;
                }
                $this->assertNotNull($chart->highchartConfig, "No highchartConfig on $key");
                /** @var HighchartConfig $config */
                $config = $chart->highchartConfig;
                if(isset($config->chart->margin)){
                    le("chart->margin Cuts off labels", $config);
                }
            }
        }
    }

    /**
     * @param QMMeasurementExtended|stdClass $m
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
        $this->assertIsString('string', $m->variableCategoryName);
        $this->assertIsInt($m->variableCategoryId);
        if($m->unitAbbreviatedName === OneToFiveRatingUnit::ABBREVIATED_NAME){
            $this->assertContains("rating", $m->pngPath);
        }
    }
    /**
     * @param int $userId
     * @return QMPopulationStudy|QMStudy|QMUserStudy
     */
    protected function getOrCreateStudy(int $userId = 1){
        $c = UserVariableRelationship::whereUserId($userId)->first();
        /** @var UserVariableRelationship $c */
        if($c){
            return $c->findInMemoryOrNewQMStudy();
        }
        $c = $this->seedWithPositiveLinearCorrelation();
        return $c->findInMemoryOrNewQMStudy();
    }
    /**
     * @return QMUserVariableRelationship
     * @throws BadRequestException
     */
    public function seedWithPositiveLinearCorrelation(): QMUserVariableRelationship
    {
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurementItems = [];
        $effectMeasurementItems = [];
        for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
            $measurementValue = $i;
            $causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
            $effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
        }
        $this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
        $correlation = $this->calculateCorrelation();
        $this->checkOptimalValueMessageAndStudyCardsForUserVariables();
        $this->assertEquals(1, $correlation->strongestPearsonCorrelationCoefficient);
        return $this->checkCorrelationPropertiesAndStudyText($correlation);
    }
    protected function makeSureAllUserVariableUnitIdsAreNull(){
        $rows = QMUserVariable::readonly()->getArray();
        foreach($rows as $row){
            $this->assertNull($row->default_unit_id);
        }
    }
    /**
     * @return string
     */
    public function getUpperCaseName(): string
    {
        return ucfirst($this->getName());
    }
    /**
     * @param string $query
     * @param array $params
     * @param int|null $userId
     * @return QMVariable[]|object[]
     */
    protected function searchVariables(string $query, array $params, int $userId = 1): array{
        if($userId){
            $this->setAuthenticatedUser($userId);
        }
        return $this->getApiV6('variables/search/' . urlencode($query), $params);
    }
    protected function expectQMException(){
        QMBaseTestCase::setExpectedRequestException(QMException::class);
    }
    /**
     */
    protected function truncateMeasurementsRemindersCorrelationsTables(){
        Memory::resetClearOrDeleteAll();
        $this->truncateTrackingReminders();
        $this->truncateTable(Measurement::TABLE);
        $this->truncateTable(UserVariableRelationship::TABLE);
        $this->truncateTable(QMTrackingReminderNotification::TABLE);
        $this->truncateTable(UserVariable::TABLE);
        $this->truncateTable(QMCommonTag::TABLE);
        $this->truncateTable(QMUserTag::TABLE);
        $this->truncateTable(GlobalVariableRelationship::TABLE);
        //$this->truncateTable(CommonVariable::TABLE); // Can't truncate with foreign keys
        //CommonVariable::writable()->hardDelete(__METHOD__, true);
    }
    protected function truncateTrackingReminders(){
        TrackingReminder::deleteAll();
    }
    /**
     * @param string $tableName
     */
    protected function truncateTable(string $tableName){
        try {
            Writable::getBuilderByTable($tableName)->truncate();
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
            Writable::getBuilderByTable($tableName)->delete();
        }
    }
    /**
     * @return void
     */
    private function truncateEachFixtureDBTable(): void{
        foreach($this->fixtureFiles as $table => $fixtureFile){
            try {
                TestDB::getBuilderByTable($table)->truncate();
            } catch (Throwable $e) {
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
    }
    /**
     * @return mixed
     */
    protected function getAppSettingsWithClientSecret(){
        $client = OAClient::whereClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT)->first();
        $this->assertEquals(QMClient::TEST_CLIENT_SECRET, $client->client_secret);
        $this->assertEquals(1, $client->user_id);
        $body = $this->getAndDecodeBody('api/v1/appSettings', [
            'clientId'     => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'clientSecret' => QMClient::TEST_CLIENT_SECRET
        ]);
        return $body;
    }

    protected function getMeasurements(array $params, int $userId): array
    {
        $this->actingAs(User::findInMemoryOrDB($userId));
        $measurements = $this->getApiV6('measurements', $params);
        foreach ($measurements as $i => $measurement) {
            $measurements[$i] = (object)$measurement;
        }
        return $measurements;
    }
    /**
     * @param string $variableName
     * @return object
     */
    protected function getApiV6UserVariable(string $variableName): object
    {
        $uv = $this->getApiV6('user_variables/'.urlencode($variableName));
        //$uv = QMArr::first($uv);
        $uv = json_decode(json_encode($uv));
        return $uv;
    }
    /**
     * @return mixed
     */
    protected function getDecodedResponseContent()
    {
        return json_decode($this->testResponse->getContent());
    }
    protected function assertResponseValue(string $attribute, string $expectedValue, string $message = null){
        $actual = $this->getResponseValue($attribute);
        $message = $message ?? "Expected $attribute to be $expectedValue but was $actual";
        $this->assertEquals($expectedValue, $actual, $message);
    }
    protected function getResponseValue(string $attribute){
        $data = $this->getDecodedResponseContent();
        return $data->$attribute;
    }
    /**
     * @param string $newValue
     * @param string $attribute
     * @param BaseModel|string $class
     * @return void
     */
    protected function assertCanUpdateValueViaApiV6(string $newValue, string $attribute, string $class): void{
        $model = $class::firstOrFakeSave();
        $editedCorrelation = clone $model;
        $editedCorrelation->$attribute = $newValue;
        $r = $this->jsonAsUser18535(
            'PUT',
            $model->getApiV6IdPath(),
            $editedCorrelation->toArray()
        );
        $this->assertResponseValue($attribute, $newValue);
    }
    /**
     * @param array $voteData
     * @return BaseModel|object|array|BaseModel[]
     */
    protected function assertResponseData(array $voteData)
    {
        $this->assertApiSuccess();
        $responseData = $this->getDecodedResponseContent();
        $this->assertArrayContains($voteData, (array)$responseData->data);
        return $responseData->data;
    }

    /**
     * @param array|null $input
     * @return TestResponse
     */
    protected function createAndGetApiV6WithoutAuth(?array $input = null): TestResponse
    {
        return $this->createAndGetApiV6(null, $input);
    }

    /**
     * @param array|null $input
     * @return TestResponse
     */
    protected function createAndGetApiV6AsAdmin(?array $input = null): TestResponse
    {
        return $this->createAndGetApiV6(UserIdProperty::USER_ID_ADMIN, $input);
    }
    /**
     * @param array|null $input
     * @return TestResponse
     */
    protected function createAndGetApiV6AsTestUser(?array $input = null): TestResponse
    {
        return $this->createAndGetApiV6(UserIdProperty::USER_ID_TEST_USER, $input);
    }

    /**
     * @param int|null $userId
     * @param array|null $input
     * @return TestResponse
     */
    protected function createAndGetApiV6(?int $userId = UserIdProperty::USER_ID_TEST_USER, ?array $input = null):
    TestResponse
    {
        if(!$input){$input = $this->getFakeDataForClassBeingTested();}
        if(!$userId){
            $this->logout();
        } else {
            $this->actingAsUserId($userId);
        }
        $path = $this->getV6BasePathForClassTested();
        $r = $this->json(
            'POST',
            $path, $input
        );
        $r->assertStatus(201)
            ->assertJson(['data' => $input]);
        return $this->findApiV6($path, $input);
    }

    /**
     * @return BaseModel|string
     */
    protected function deleteAllRecordsForClassBeingTested()
    {
        $class = $this->getClassBeingTested();
        $class::deleteAll();
        $this->assertCount(0, $class::all());
        return $class;
    }
    /**
     * @return array
     */
    protected function getFakeDataForClassBeingTested(): array
    {
        $class = $this->getClassBeingTested();
        if(isset($this->fakeData[$class])){
            return $this->fakeData[$class];
        }
        $input = $class::factory()->makeOne()->toArray();
        return $this->fakeData[$class] = $input;
    }

    /**
     * @param array $newData
     * @param $id
     * @return TestResponse
     */
    protected function updateAttributeApiV6(array $newData, $id): TestResponse
    {
        $id = $id ?? $this->getIdFromTestResponse();
        $r = $this->json(
            'PUT',
            $this->getV6BasePathForClassTested().'/' . $id,
            $newData
        );
        $r->assertStatus(201);
        $data = $this->getJsonResponseData();
        $this->assertContains($newData, $data, "Response should contain submitted data");
        $this->assertResponseData($newData);
        return $r;
    }

    /**
     * @param int|string $id
     * @return \Illuminate\Testing\TestResponse
     */
    protected function deleteApiV6($id = null): \Illuminate\Testing\TestResponse{
        if(!$id) {$id = $this->getIdFromTestResponse();}
        $apiV6BasePath = $this->getV6BasePathForClassTested();
        $r = $this->json(
            'DELETE',
            $apiV6BasePath .'/'. $id
        );
        $this->expectModelNotFoundException();;
        $r->assertStatus(204);
        $r = $this->jsonAsUser18535(
            'GET',
            $apiV6BasePath .'/'. $id
        );
        $r->assertNotFound();
        $r->assertStatus(404);
        return $r;
    }
    /**
     * @return int|string
     */
    protected function getIdFromTestResponse(){
        $r = $this->getTestResponse();
        $id = $r->json('data.id');
	    if(!$id){
		    $id = $r->json('data.0.id');
	    }
        if(!$id){
            $this->fail("No id found in response");
        }
        return $id;
    }

    /**
     * @return string
     */
    protected function getV6BasePathForClassTested(): string
    {
        $classBeingTested = $this->getClassBeingTested();
        $apiV6BasePath = $classBeingTested::getApiV6BasePath();
        return $apiV6BasePath;
    }

    /**
     * @param array $expectedNames
     * @return void
     */
    protected function checkGetResponse(array $expectedNames): void{
        $this->checkExpectedNames($expectedNames);
        $this->checkRequiredAttributes();
    }

    /**
     * @return void
     */
    private function checkRequiredAttributes(): void
    {
        $data = $this->getJsonResponseData();
        $required = $this->getRequiredAttributes();
        foreach ($data as $datum) {
            $this->assertIsArray($datum);
            $str = \App\Logging\QMLog::print_r($datum, true);
            foreach ($required as $attribute) {
                $this->assertArrayHasKey($attribute, $datum, $str);
                $this->assertNotNull($datum[$attribute], $str);
            }
        }
    }

    /**
     * @param array $expectedNames
     * @return mixed
     */
    private function checkExpectedNames(array $expectedNames)
    {
        $data = $this->getJsonResponseData();
        $names = collect($data)->pluck('name')->toArray();
        $this->assertArrayEquals($expectedNames, $names);
        return $data;
    }

    /**
     * @return array|BaseModel|BaseModel[]
     */
    protected function getJsonResponseData(): array
    {
        $r = $this->getTestResponse();
	    $body = $r->json();
		if(isset($body['data'])){
			return $body['data'];
		}
        return $body;
    }

    protected function expectUnauthorizedException()
    {
        self::setExpectedRequestException(UnauthorizedException::class);
    }
	protected function expectAuthenticationException()
	{
		self::setExpectedRequestException(\Illuminate\Auth\AuthenticationException::class);
	}
    protected function updatePrimaryKeyAutoIncrementSequence(){
        $class = $this->getClassBeingTested();
        Writable::updatePrimaryKeySequence(new $class);
    }
    protected function expectRegularNotFoundException()
    {
        self::setExpectedRequestException(NotFoundException::class);
    }
    protected function expectModelNotFoundException()
    {
        self::setExpectedRequestException(ModelNotFoundException::class);
    }
    protected function expectBadRequestException()
    {
        self::setExpectedRequestException(BadRequestException::class);
    }

    /**
     * @param string $path
     * @param array|null $expectedDataSubset
     * @return TestResponse
     */
    protected function findApiV6(string $path, ?array $expectedDataSubset): TestResponse
    {
        $id = $this->getIdFromTestResponse();
        $r = $this->json(
            'GET',
            $path . "/" . $id
        );
        $r->assertStatus(200)
            ->assertJson(['data' => $expectedDataSubset]);
        $id = $this->getIdFromTestResponse();
        $r = $this->json(
            'GET',
            $path,
            [SortParam::NAME_SORT => '-'.BaseUpdatedAtProperty::NAME]);
        $data = $this->getJsonResponseData();
        $ids = collect($data)->pluck('id')->toArray();
        $this->assertContains($id, $ids);
        return $r;
    }
    protected function getRequiredAttributes(): array
    {
        $class = $this->getClassBeingTested();
        return (new $class)->getRequiredFields();
    }
    protected function assertHasRequiredAttributes(array $data): void
    {
        $required = $this->getRequiredAttributes();
        foreach ($required as $attribute) {
            $this->assertArrayHasKey($attribute, $data);
            $this->assertNotNull($data[$attribute]);
        }
    }
}
