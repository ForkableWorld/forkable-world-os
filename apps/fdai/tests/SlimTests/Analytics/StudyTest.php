<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedPrivateMethodInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnreachableStatementInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace Tests\SlimTests\Analytics;
use App\AppSettings\AppSettings;
use App\Buttons\QMButton;
use App\Cards\QMCard;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMClient;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Mail\QMSendgrid;
use App\Models\GlobalVariableRelationship;
use App\Models\Collaborator;
use App\Models\UserVariableRelationship;
use App\Models\DeviceToken;
use App\Models\OAAccessToken;
use App\Models\SentEmail;
use App\Models\Study;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\Vote;
use App\Models\WpPost;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Base\BaseForwardPearsonCorrelationCoefficientProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\UserVariable\UserVariableIsPublicProperty;
use App\Slim\Model\Auth\QMAccessToken;
use App\Slim\Model\Measurement\Pair;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Notifications\CorrelationPushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\User\AuthorizedClients;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Slim\Model\WordPress\WPPostApi;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\TestDB;
use App\Storage\MemoryOrRedisCache;
use App\Storage\QMFileCache;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Studies\StudyListResponseBody;
use App\Traits\HasCauseAndEffect;
use App\Types\QMArr;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\CaloriesCommonVariable;
use App\Variables\CommonVariables\SymptomsCommonVariables\BackPainCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMCommonVariable;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Database\Seeders\GlobalVariableRelationshipsTableSeeder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Support\Facades\Queue;
use LogicException;
use Tests\QMAssert;
use Tests\QMBaseTestCase;
use Tests\Traits\TestsCharts;
use Tests\Traits\TestsStudies;
class StudyTest extends \Tests\SlimTests\SlimTestCase {

	use InteractsWithDatabase, UsesClockwork;
	/**
	 * @return void
	 */
	public function assertStudiesCreated(int $expectedCount, int $userId = 1): void{
		$studies = User::findInMemoryOrDB($userId)->studies()->get();
		$this->assertCount($expectedCount, $studies);
	}
	use TestsCharts;
	use TestsStudies;
    protected function setUp(): void{

        parent::setUp();
	    Study::deleteAll();
	    UserVariableClient::deleteAll();
	    TrackingReminderNotification::deleteAll();
	    TrackingReminder::deleteAll();
		UserVariable::deleteAll();
		User::query()->update([User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => null]);
        //TestDB::resetUserTables();
    }
	/**
	 * @param $variable
	 */
	public static function checkTrackingInstructionButtons($variable): void{
		$card = $variable->trackingInstructionsCard;
		$buttons = $card->buttons;
		$titles = QMArr::pluckColumn($buttons, 'title');
		QMAssert::assertNotNull($card);
		if(count($card->buttons) === 1){
			static::assertButtonTitles(['Import Data'], $card->buttons);
			QMAssert::assertCount(1, $card->buttons);
		}else{
			if(in_array('Import Data', $titles)){
				static::assertButtonTitles(['Import Data', 'Get it here!'], $card->buttons);
			} elseif(in_array('Updating', $titles)){
				static::assertButtonTitles(['Disconnect', 'Updating',], $card->buttons);
			} else {
				static::assertButtonTitles(['Go to Inbox', 'Manage Reminders'], $card->buttons);
			}
		}
	}
    public function testPostToWordPressAPI(){
        $this->skipTest("Fuck the WP API");
        return;
        $slug = "test-study-slug-api-test-case";
        $content = "WP API Test Study Content";
        $title = "Test Study Title API Test";
        $postUrl = QMWordPressApi::getSiteUrl().'/'.$slug.'/';
        $result = WPPostApi::postByTitleContentSlug($title, $content, $slug);
        $this->assertNotNull($result);
        $this->assertEquals($postUrl, $result->link);
        $post = APIHelper::getRequest($postUrl);
        $this->assertNotFalse(stripos($post, $content));
        $this->assertNotFalse(stripos($post, $title));
        $result = QMWordPressApi::deletePostBySlug($slug);
        $deletedPost = APIHelper::getRequest($postUrl);
        if(!$deletedPost){
            throw new LogicException("Probably made a duplicate post that you need to delete manually at $postUrl");
        }
        $this->assertFalse(stripos($deletedPost, $content));
        $this->assertFalse(stripos($deletedPost, $title));
    }
	/**
	 * @covers \App\Models\Study::firstOrNewByData
	 */
	public function testStudyFirstOrNewByData(){
		$data = [
			'causeVariableName' => CaloriesCommonVariable::NAME,
			'effectVariableName' => OverallMoodCommonVariable::NAME,
			'type' => StudyTypeProperty::TYPE_INDIVIDUAL,
		];
		$this->setAuthenticatedUser($data['userId'] = 2);
		$study = Study::firstOrNewByData($data);
		$this->assertArrayEquals([
			'cause_variable_id' => 1499,
			'client_id' => 'system',
			'effect_variable_id' => 1398,
			'id' => 'cause-1499-effect-1398-user-2-user-study',
			'is_public' => false,
			'type' => 'individual',
			'user_id' => 2,],$study->attributesToArray());
	}
    public function assertNonLoggedInUsersCanSeeSharedStudies(array $requestData){
        $requestData['userId'] = 1;
        $this->setAuthenticatedUser(null);
        $this->getUserStudyV4($requestData);
    }
    public function testJoinStudy(){

        $this->setAuthenticatedUser($userId = 1);

        $this->deleteUserFromMailChimp();
        $responseBodyDecoded = $this->joinStudyAndGetDecodedBody([
            'causeVariableName' => BupropionSrCommonVariable::NAME,
            'effectVariableId'  => BackPainCommonVariable::ID,
            'joinStudy'         => true
        ]);
        $this->checkCreatedRemindersAndNotifications($responseBodyDecoded->study);
        $this->assertQueryCountLessThan(47);
    }
	/**
	 * @return QMCohortStudy|QMPopulationStudy|QMUserStudy
	 */
	public function testCreateGlobalStudyWithoutData(){
        $this->deleteStudiesAndTokens();
        $study = $this->createStudyTypeAndCheckResponse('global', 401, null);
        $study = $this->createStudyTypeAndCheckResponse('global', 201, 1);
        $this->assertContains('population', $study->id, "Study id is: ".$study->id);
		$this->setAuthenticatedUser(1);
        $this->checkStudiesCreatedCount(1);
        $this->compareStudyHtml($study->studyHtml->fullStudyHtml);
        return $study;
    }
    public function testCreateIndividualStudyWithoutData(){
        $this->deleteStudiesAndTokens();
		self::setExpectedRequestException(UnauthorizedException::class);
        $study = $this->createStudyTypeAndCheckResponse('individual', 401, null);
        $study = $this->createStudyTypeAndCheckResponse('individual', 201, 1);
        $this->assertContains('user', $study->id);
        $this->checkStudiesCreatedCount(1);
    }
    public function testCreateUnspecifiedTypeOfStudy(){
        $this->deleteStudiesAndTokens();
        $this->setAuthenticatedUser(null);
        $study = $this->createStudyTypeAndCheckResponse();
        //$this->assertTrue(stripos($study->id, 'population-study') !== false, $study->id);
        $study = $this->createStudyTypeAndCheckResponse(null, 201, 1);
        $this->assertContains('user-study', $study->id);
    }
    public function testCreateCohortStudy(){
		Study::truncate();
	    $studyId = 'cause-1276-effect-1919-user-1-cohort-study';
	    $causeId = BaseCauseVariableIdProperty::pluckNameOrId([
            'studyClientId' => $studyId,
            'clientId' => 'oauth_test_client',]);
	    $this->assertEquals(1276, $causeId);
		$cause = Variable::findInMemoryOrDB($causeId);
		$effectId = BaseEffectVariableIdProperty::fromStudyId($studyId);
		$effect = Variable::findInMemoryOrDB($effectId);
        $this->deleteStudiesAndTokens();
        $this->setAuthenticatedUser($userId = 1);
        TrackingReminder::deleteAll();
        $this->deleteUserFromMailChimp();
        $study = $this->createCohortStudyAndCheckResponse();
        $this->assertParticipantInstructionsContain($study, ["inbox",
            BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $this->assertContains(urlencode(BupropionSrCommonVariable::NAME), $study->scopes);
        $this->assertContains('cohort', $study->id);
        $client = $this->checkCohortStudyClient($study);
        $requestData = ['studyClientId' => $client->clientId];
        $this->checkAuthorizedClientsForUser($userId);
        $this->checkStudyCount(1, 'created', $userId);
        $this->checkStudyCount(1, 'joined', $userId);
        $this->setAuthenticatedUser($userId = 2);
		$this->slimGetUser($userId);
        $responseBodyDecoded = $this->joinStudyAndGetDecodedBody($requestData);
        $this->checkCreatedRemindersAndNotifications($responseBodyDecoded->study);
        /** @var QMStudy $study */
        $study = $responseBodyDecoded->study;
        /** @var QMCard $card */
        $card = $study->causeVariable->trackingInstructionsCard;
        $this->assertStringContains($card->htmlContent, 'reminder for Bupropion', "study-card");
        $this->checkAuthorizedClientsForUser($userId);
        $tokenRows = OAAccessToken::whereClientId($client->clientId)->get();
        $this->assertCount(2, $tokenRows, QMLog::print_r($tokenRows->toArray()));
        /** @var QMCohortStudy $study */
        $study = $this->getStudy($requestData);
        $this->assertEquals($study->client->clientId, $client->clientId);
		$studies = Study::all();
		$this->assertCount(1, $studies);
        $this->checkStudyCount(1, 'open', $userId);
        $this->checkStudyCount(1, 'joined', $userId);
		$this->assertStudiesCreated(0, $userId);
        $this->checkStudyCount(0, 'created', $userId);
        $this->checkSharesCount(1, 0, $userId);
        $this->deleteShare($client->clientId, $userId);
        $this->checkSharesCount(0, 0, $userId);
        $this->assertParticipantInstructionsContain($study, ["inbox",
            BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
        ]);
    }
    /**
     * @param int $studies
     * @param int $individuals
     */
    public function checkSharesCount(int $studies, int $individuals, int $userId){
		$this->setAuthenticatedUser($userId);
        $response = $this->getAndDecodeBody('v1/shares');
        $this->assertCount($studies, $response->authorizedClients->studies);
        $this->assertCount($individuals, $response->authorizedClients->individuals);
    }
    /**
     * @param int $expectedValue
     * @param string $type
     * @return void
     */
    public function checkStudyCount(int $expectedValue, string $type, int $userId): void {
		$this->setAuthenticatedUser($userId);
        $response = $this->getAndDecodeBody('v1/studies/' . $type);
        $studies = $response->studies;
        $this->assertCount($expectedValue, $studies,
	        "There should be $expectedValue $type studies");
	    $this->setAuthenticatedUser(1);
        $response = $this->getAndDecodeBody('v1/studies', [$type => true]);
        $this->assertCount($expectedValue, $studies);
    }
    /**
     * @return QMPopulationStudy|QMUserStudy|QMCohortStudy
     */
    public function checkAuthorizedClientsForUser(int $userId) {
        $client = QMClient::getMostRecentlyCreated();
        $token = QMAccessToken::getMostRecentlyCreated();
        //$this->assertEquals($expectedNewestTokenUserId, $token->getUserId());
        $this->assertEquals($client->getClientId(), $token->getClientId());
        $this->assertGreaterThan(time() + 86400, $token->getExpirationTime());
	    $expectedCount = 1;
		$ac = new AuthorizedClients($userId);
	    $this->assertCount($expectedCount, $ac->studies, "User should have 1 authorized study!\n");
		$this->setAuthenticatedUser($userId);
        /** @var QMUser $user */
        $user = $this->getAndDecodeBody('v1/user', ['includeAuthorizedClients' => true]);
	    $this->assertCount($expectedCount, $user->authorizedClients->studies, "User should have 1 authorized study!\n");
        return $user->authorizedClients->studies;
    }
    public function testQmScoreHigherForPredictiveCorrelationsOverTime(){
	    $this->checkInterestingFactors();
	    $positive = $this->seedWithPositiveRandomizedPredictiveCorrelationOverTime();
        $uncorrelated = $this->seedWithUncorrelatedCorrelationOverTime();
        $diff = $positive->statisticalSignificance - $uncorrelated->statisticalSignificance;
        if($diff > 0.3){
            $this->logError('Statistical significance should not be so different!');
        }
        //$tagLine = $positive->getTagLine();
        //$baseline = $positive->getOrCalculateChangeFromBaselineSentence();
        //$this->assertEquals($tagLine, $baseline);
        $this->assertLessThan(0.3, $positive->statisticalSignificance -
            $uncorrelated->statisticalSignificance);
        $this->assertGreaterThan(-0.3, $positive->statisticalSignificance -
            $uncorrelated->statisticalSignificance);
        $this->assertGreaterThan($uncorrelated->correlationCoefficient,
            $positive->correlationCoefficient, "Predictive positive correlation correlationCoefficient
            $positive->correlationCoefficient should be greater than
            uncorrelatedCorrelation correlationCoefficient $uncorrelated->correlationCoefficient.");
        $this->assertGreaterThan($uncorrelated->strongestPearsonCorrelationCoefficient,
            $positive->strongestPearsonCorrelationCoefficient,
            "positive correlation correlationCoefficient $positive->strongestPearsonCorrelationCoefficient should be
            greater than  uncorrelatedCorrelation correlationCoefficient  $uncorrelated->strongestPearsonCorrelationCoefficient.");
        $this->assertGreaterThan($uncorrelated->qmScore, $positive->qmScore,
            "positive correlation qmScore $positive->qmScore should be greater than
            uncorrelatedCorrelation qmScore $uncorrelated->qmScore.");

    }
    public function testNegativeRandomizedPredictiveCorrelation(){
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurements = [];
        $effectMeasurements = [];
        for($i = 0; $i < 30; $i++){
            $measurementValue = random_int(0, 100);
            $causeMeasurements[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i,
                $measurementValue);
            $effectMeasurements[] = new QMMeasurement(
                self::BASE_TIME + self::DAY * ($i + 1), -$measurementValue);
        }
        $this->saveCauseAndEffectMeasurementItems($causeMeasurements,
            $effectMeasurements);
        $correlation = $this->calculateCorrelation();
        $start = $correlation->getExperimentStartAt();
        $end = $correlation->getExperimentEndAt();
        $correlation->setMeasurements();
        $causeMeasurements = $correlation->getCauseMeasurements();
        $effectMeasurements = $correlation->getEffectMeasurements();
        $cause = $correlation->getCauseQMVariable();
        $this->assertEquals(1800, $cause->onsetDelay);
        $this->assertEquals(1800, $correlation->onsetDelay);
        $this->assertEquals(86400, $correlation->durationOfAction);
        //$this->assertEquals(86400, $correlation->onsetDelayWithStrongestPearsonCorrelation); // TODO: Figure out why this fails sometimes
        // For some reason, getProcessedDailyMeasurementsWithTagsJoinsChildrenInCommonUnitWithinTimeRange brings strongestPearsonCorrelationCoefficient below 1
        //$this->assertEquals(-1, $correlation->correlationCoefficient);
        $this->assertLessThan(-0.65, $correlation->correlationCoefficient, 'correlationCoefficient should be negative!');  // TODO: Figure out why this isn't -1
        //$this->assertEquals(-1, $correlation->strongestPearsonCorrelationCoefficient);
//        $this->assertLessThan(-0.65, $correlation->strongestPearsonCorrelationCoefficient,
//            'strongestPearsonCorrelationCoefficient should probably be negative?');
        $study = $this->getUserStudyV4();
        /** @var QMVariableRelationship $statistics */
        $statistics = $study->statistics;
        $this->assertContains('lower', strtolower($study->studyText->studyTitle),
            "correlation: ".$statistics->correlationCoefficient.
            " value predicting high: ". $statistics->avgDailyValuePredictingHighOutcome. " low: ".
            $statistics->avgDailyValuePredictingLowOutcome);
    }
    public function testOptimalDailyValuesForPredictiveCorrelatedVariables(){
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurementItems = [];
        $effectMeasurementItems = [];
        for($i = 0; $i < 30; $i++){
            $measurementValue = $i;
            $causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
            $effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * ($i + 1), $measurementValue);
        }
        $this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
        $correlation = $this->calculateCorrelation();
        $this->assertEquals(1, $correlation->strongestPearsonCorrelationCoefficient);
        $this->assertGreaterThan(20, $correlation->avgDailyValuePredictingHighOutcome);
        $this->assertLessThan(15, $correlation->avgDailyValuePredictingLowOutcome);
        $this->assertGreaterThan(20, $correlation->averageDailyHighCause);
        $this->assertLessThan(15, $correlation->averageDailyLowCause);
    }
    public function testNegativeLinearCorrelation(): QMUserVariableRelationship{
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurementItems = [];
        $effectMeasurementItems = [];
        for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
            $measurementValue = $i;
            $causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $measurementValue);
            $effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, -$measurementValue);
        }
        $this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
        $correlation = $this->calculateCorrelation();
        return $this->checkNegativeCorrelation($correlation);
    }
    public function testSameQmScoreForPositiveAndNegativeCorrelations(){
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $this->seedWithPositiveLinearCauseEffectMeasurements();
        $cause = $this->getCauseUserVariable();
        $effect = $this->getEffectUserVariable();
        $c = new QMUserVariableRelationship(null, $cause, $effect);
        $pairs = $c->setPairs();
        $actual = collect($pairs)->map(function($pair){
            /** @var Pair $pair */
            return ['cause' => $pair->causeMeasurementValue, 'effect' => $pair->effectMeasurementValue];
        })->all();
        $this->assertEquals($this->getExpectedPositiveLinearPairArray(), array_values($actual));
        $u = QMUser::demo();
        $u->analyzeFully(__FUNCTION__);
        $this->assertEquals(1, UserVariableRelationship::count());
        $positiveCorrelations = QMUserVariableRelationship::getUserVariableRelationships([]);
        $this->checkOptimalValueMessageAndStudyCardsForUserVariables();
        $positiveCorrelation = $positiveCorrelations[0];
        if(EnvOverride::isLocal()){$positiveCorrelation->saveStudyHtmlToResponsesRepo("positive", __FUNCTION__);}
        $this->assertEquals(1, $positiveCorrelation->strongestPearsonCorrelationCoefficient);
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $negativeCorrelation = $this->seedWithNegativeLinearPredictiveCorrelation();
        $data = ['negative' => $negativeCorrelation, 'positive' => $positiveCorrelation];
        if(EnvOverride::isLocal()){$negativeCorrelation->saveStudyHtmlToResponsesRepo("negative", __FUNCTION__);}
        if($negativeCorrelation->qmScore !== $positiveCorrelation->qmScore){
            $m = "negativeCorrelation qmScore $negativeCorrelation->qmScore should be equal to positiveCorrelation qmScore $positiveCorrelation->qmScore.";
            $this->assertEquals($negativeCorrelation->qmScore, $positiveCorrelation->qmScore,$m);
        }
    }
    public function testPositiveBinaryCorrelations(){
        QMBaseTestCase::deleteUserVariablesMeasurementsRemindersAndCorrelations();
        $causeMeasurementItems = [];
        $effectMeasurementItems = [];
        for($i = 0; $i < self::NUMBER_OF_GENERATED_MEASUREMENTS; $i++){
            $causeValue = ($i > self::NUMBER_OF_GENERATED_MEASUREMENTS / 2) ? 1 : 0;
            $measurementValue = $i;
            $causeMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * $i, $causeValue);
            $effectMeasurementItems[] = new QMMeasurement(self::BASE_TIME + self::DAY * ($i + 1), $measurementValue);
        }
        $this->saveCauseAndEffectMeasurementItems($causeMeasurementItems, $effectMeasurementItems);
        $correlation = $this->calculateCorrelation();
        $this->assertEquals(QMVariableRelationship::DIRECTION_HIGHER, $correlation->direction);
        $this->assertEquals(BaseForwardPearsonCorrelationCoefficientProperty::EFFECT_SIZE_strongly_positive, $correlation->effectSize);
        $this->assertEquals(1, $correlation->getGroupedValueOverDurationOfActionClosestToValuePredictingHighOutcome());
        $this->assertEquals(0, $correlation->getGroupedCauseValueClosestToValuePredictingLowOutcome());
        $tagLine = $correlation->getStudyText()->getTagLine();
        $OptimalValueSentenceWithPercentChange = $correlation->getOptimalValueSentenceWithPercentChange();
        $OptimalValueSentence = $correlation->getOptimalValueWithDurationOfActionSentence();
        $PredictorExplanation = $correlation->getStudyText()->getStudyTitle();
        $this->assertGreaterThan(0.7, (float)$correlation->avgDailyValuePredictingHighOutcome);
        $this->assertLessThan(0.3, (float)$correlation->avgDailyValuePredictingLowOutcome);
        $this->assertGreaterThan(0.7, (float)$correlation->averageDailyHighCause);
        $this->assertLessThan(0.3, (float)$correlation->averageDailyLowCause);
        $study = $this->getUserStudyV4();
        $this->assertContains('higher', strtolower($study->studyText->studyTitle));
        $this->assertTrue($correlation->correlationCoefficient > 0);
    }
    public function testPositivePurchaseCorrelationsAndPushNotification(){
        DeviceToken::deleteAll();
        QMDeviceToken::saveTestTokenToDatabase(BasePlatformProperty::PLATFORM_WEB);
        $c = $this->seedWithPositivePurchaseCorrelation();
        $results = $c->sendPushNotification(true);
        /** @var CorrelationPushNotificationData $pushData */
        $pushData = $results[0]["pushData"];
        //$this->assertEquals("Your last Active Time recording was 1 seconds. ", $pushData->title);
        $this->assertEquals("Higher Spending On CauseVariableName Predicts Significantly Higher EffectVariableName", $pushData->title);
        $this->assertEquals("EffectVariableName was 40% higher following above average Spending on CauseVariableName over the previous 30 days. ", $pushData->message);
        $this->assertEquals("https://static.quantimo.do/img/variable_categories/sad-96.png", $pushData->image);
        //$this->assertEquals("https://web.quantimo.do/#/app/study?causeVariableId=1872&effectVariableId=1272&userId=1&apiUrl=local.quantimo.do", $pushData->url);
        $this->assertContains('study/'.$c->getStudyId(),
            //'Id=CauseVariableName&effectVariableId=EffectVariableName&userId=1&clientId',
            $pushData->url);
        $this->assertEquals(QMCommonVariable::PURCHASE_DURATION_OF_ACTION, $c->durationOfAction);
        $this->assertTrue($c->correlationCoefficient > 0);
    }
    /**
     * @param array $requestData
     * @return QMUserStudy
     * @throws NoUserVariableRelationshipsToAggregateException
     */
    public function publishStudyThatHasNotBeenAnalyzedYet(array $requestData): object {
        $requestData['shareUserMeasurements'] = true;
        TestDB::deleteUserAndAggregateData();
        $this->deleteStudiesAndTokens();
        $this->seedWithPositiveLinearCauseEffectMeasurements();
        $numberOfGlobalVariableRelationships = GlobalVariableRelationship::count();
        $this->assertEquals(0, $numberOfGlobalVariableRelationships);
        $user = $this->setAuthenticatedUser(1);
        $publishResponse = $this->postAndGetDecodedBody('/api/v1/study/publish', $requestData);
        /** @var QMUserStudy $studyResponse */
        $studyResponse = $publishResponse->study;
        //if(!$studyResponse->publishedAt){throw new LogicException("No study->publishedAt!");}
        $correlations = GlobalVariableRelationship::all();
        //$this->assertCount(1, $user_variable_relationships, "should have 1 global variable relationship after publishing");
        $numberOfUserVariableRelationships = UserVariableRelationship::count();
        if($numberOfUserVariableRelationships > 1){
            $correlations = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships([]);
            foreach ($correlations as $correlation){$correlation->logInfo("");}
        }
        $this->assertEquals(1, $numberOfUserVariableRelationships, "should have 1 user variable relationship after publishing");
        $this->assertGreaterThanOrEqual(1, Study::count(), "should have 1 study after publishing");
		$s = $user->l()->studies->first();
		$this->assertTrue((bool) $s->is_public);
        // Make sure it got into the DB
        $causeVariable = $this->getCauseUserVariable();
        $this->assertEquals(1, $causeVariable->isPublic);
        $effectVariable = $this->getEffectUserVariable();
        $this->assertEquals(1, $effectVariable->isPublic);
        $this->checkCreatedStudiesHeader();
        return $studyResponse;
    }
    protected function checkCreatedStudiesHeader(){
		$this->setAuthenticatedUser(1);
        $studies = $this->getStudiesCreated();
        $studyHtml = $studies[0]->studyHtml;
        $this->assertContains('CauseVariableName', $studyHtml->studyTitleHtml);
        $this->assertContains('EffectVariableName', $studyHtml->studyHeaderHtml);
    }
    public function testPublishStudyThatsAlreadyBeenAnalyzed(){
		UserVariableRelationship::truncate();
        GlobalVariableRelationship::truncate();
	    Study::truncate();
	    Vote::truncate();
        $this->seedWithPositiveLinearPredictiveCorrelation();
	    $this->assertEquals(0, GlobalVariableRelationship::count());
        $this->setAuthenticatedUser($userId = 1);
        $response = $this->postAndGetDecodedBody('/api/v1/study/publish', [
            'shareUserMeasurements' => true,
            'causeVariableName'     => 'CauseVariableName',
            'effectVariableName'    => 'EffectVariableName'
        ]);
        $this->assertEquals(1, GlobalVariableRelationship::count());
        // Make sure it got into the DB
        $causeVariable = $this->getCauseUserVariable();
        $this->assertEquals(1, $causeVariable->isPublic);
        $effectVariable = $this->getEffectUserVariable();
        $this->assertEquals(1, $effectVariable->isPublic);
        $this->makeSureTheresAnOpenStudyReturned();
        $this->checkOptimalValueMessageForCommonVariables();
    }
    /**
     * @param null $userIdParam
     * @param int $expectedCode
     * @return QMPopulationStudy
     */
    public function getStudyAsUserTwo($userIdParam = null, int $expectedCode = 200): object {
        $requestData = ['causeVariableName' => 'CauseVariableName', 'effectVariableName' => 'EffectVariableName'];
        if($userIdParam){
            $requestData['userId'] = $userIdParam;
        }
        self::deleteAndRecreateAllAggregatedCorrelations();
        $this->setAuthenticatedUser($userId = 2);
        $requestData[QMRequest::PARAM_INCLUDE_CHARTS] = true;
	    if($expectedCode === 401){
		    QMBaseTestCase::setExpectedRequestException(\App\Exceptions\UnauthorizedException::class);
	    }
        $body = $this->getAndDecodeBody('/api/v4/study', $requestData, $expectedCode);
        if($expectedCode !== 401){
            $this->assertStudyVariablesHaveTrackingInstructionsCards($body);
        }
        return $body;
    }
	/**
	 * @param array $params
	 * @return QMStudy
	 */
    public function getStudy(array $params): object {
        $study = $this->getAndDecodeBody('v1/study', $params);
        $this->assertStudyVariablesHaveTrackingInstructionsCards($study);
        return $study;
    }
    public function checkRootCauseAnalysis(){
        $this->makeSureEffectIsPrimaryOutcomeAndReminderGetsCreated();
        $this->setAuthenticatedUser($userId = 1);
        $study = $this->getUserStudyV4();
        $user = $this->getOrSetAuthenticatedUser(1);
        $causePosts = $this->assertNumberOfPostsWithTitleLike($user, 0, "CauseVariableName",
            "We should not have posts because no one published or voted yet");
        $effectPosts = $this->assertNumberOfPostsWithTitleLike($user, 0, "EffectVariableName",
            "We should not have posts because no one published or voted yet");
        $user = $this->getOrSetAuthenticatedUser(1);
        $v = $user->getPrimaryOutcomeQMUserVariable();
        $this->assertEquals("EffectVariableName", $v->getVariableName());
        $strongestCorrelation = $v->getBestCorrelationAsEffect();
        $this->assertEquals("CauseVariableName", $strongestCorrelation->getCauseVariableName());
        $a = $v->getRootCauseAnalysis();
        $v->email();
        $user->analyzeFullyAndSave(__FUNCTION__);
        $user->postToWordPress();
        $overview = $this->assertNumberOfPostsWithTitleLike($user, 1, "Overview");
        $posts = $this->assertNumberOfPostsWithTitleLike($user, 1, $user->displayName,
            "We put the user name in their overview post.  Maybe we shouldn't?  Or maybe we should generate random superhero display names? Let's not put use names in titles to protect privacy");
        $correlations = UserVariableRelationship::all();
        foreach($correlations as $c){$c->validate();}
        try {
            $this->compareHtmlPage('root-cause-analysis', $a->generateHtmlWithHead());
        } catch (\Throwable $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
            $this->compareHtmlPage('root-cause-analysis', $a->generateHtmlWithHead());
        }
    }
    public function makeSureTheresAnOpenStudyReturned(): void{
        $openStudies = $this->getOpenStudies(1);
        $studyHtml = $openStudies[0]->studyHtml;
        $this->assertNotFalse(stripos($studyHtml->studyHeaderHtml, 'higher'));
    }
    /**
     * @return void
     */
    public function makeSureEffectIsPrimaryOutcomeAndReminderGetsCreated(): void{
        $user = $this->getOrSetAuthenticatedUser(1);
        $cause = $this->getCauseUserVariable();
        $effect = $this->getEffectUserVariable();
        $primaryOutcomeVariable = $user->getPrimaryOutcomeQMUserVariable();
        $this->assertTrackingReminderNames([$effect->name]);
        $this->assertEquals("EffectVariableName", $primaryOutcomeVariable->getVariableName());
    }
    /**
     * @param $requestData
     * @return QMUserStudy
     */
    public function unPublishStudyAsUser1($requestData): QMUserStudy{
        $this->setAuthenticatedUser($userId = 1);
        $requestData['shareUserMeasurements'] = false;
        $postData = json_encode($requestData);
        $response = $this->postApiV3('study/publish', $postData);
        // Make sure it got into the DB
        $causeVariable = $this->getCauseUserVariable();
        $this->assertEquals(0, $causeVariable->isPublic);
        $effectVariable = $this->getEffectUserVariable();
        $this->assertEquals(0, $effectVariable->isPublic);
        $study = QMUserStudy::findOrCreateQMStudy('CauseVariableName', 'EffectVariableName',
            1, StudyTypeProperty::TYPE_INDIVIDUAL);
		$id = $study->id;
		$s = Study::find($id);
	    $this->assertFalse((bool)$s->is_public, 'Study should not be public anymore!');
	    $this->assertFalse($study->getIsPublic(), 'Study should not be public anymore!');
        $this->assertFalse((bool)$study->isPublic, 'Study should not be public anymore!');
        return $study;
    }
    /**
     * @param QMCohortStudy|QMUserStudy $data
     */
    private function checkCreatedRemindersAndNotifications($data){
        $this->assertCount(2, $data->trackingReminderNotifications);
        $this->assertCount(2, $data->trackingReminders);
    }
    private function deleteUserFromMailChimp() {
        QMSendgrid::deleteUserFromMailChimpList('m@quantimo.do', '83b517aed3');
    }
    /**
     * @return QMCohortStudy
     */
    private function createCohortStudyAndCheckResponse(): object {
        $study = $this->createStudyTypeAndCheckResponse('group', 201, 1);
        $tokens = QMAccessToken::get();
        $this->checkCreatedRemindersAndNotifications($study);
        $this->assertNotFalse(stripos($study->id, 'cohort'), $study->id);
	    $this->assertStudiesCreated(1, 1);
	    return $study;
    }
    /**
     * @param QMCohortStudy|object $study
     * @return QMClient
     */
    private function checkCohortStudyClient($study): object {
        $this->assertNotNull($study->client);
        /** @var QMClient $client */
        $client = $study->client;
        $this->assertNotFalse(stripos($client->clientId, 'cohort'));
        return $client;
    }
    /**
     * @param array $requestData
     * @return object
     */
    private function joinStudyAndGetDecodedBody(array $requestData): object{
        $response = $this->postAndGetDecodedBody('/api/v1/study/join', $requestData);
        return $response;
    }
    private function deleteStudiesAndTokens(){
        \App\Logging\ConsoleLog::info(__FUNCTION__."...");
        MemoryOrRedisCache::flush();
        QMFileCache::flush();
        QMAccessToken::writable()->delete();
        Collaborator::writable()->delete();
        AppSettings::writable()->where(AppSettings::FIELD_STUDY, 1)->delete();
        Study::deleteAll();
        SentEmail::where(SentEmail::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), "%study%")->forceDelete();
        QMClient::writable()->where(QMClient::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), "%study%")->delete();
		GlobalVariableRelationship::truncate();
    }
    /**
     * @param int $expectedCount
     * @return QMStudy|HasCauseAndEffect[]
     */
    private function getOpenStudies(int $expectedCount): array{
        /** @var StudyListResponseBody $studiesResponse */
        $studiesResponse = $this->getApiV3('studies/open');
        $this->assertNotFalse(stripos($studiesResponse->summary, 'open'));
        $this->assertNotFalse(stripos($studiesResponse->description, 'anyone'));
        $this->assertCount($expectedCount, $studiesResponse->studies);
        return $studiesResponse->studies;
    }
    private function getExpectedPositiveLinearPairArray(): array {
        return [
            0  => [
                'cause'  => 0.0,
                'effect' => 0.0,
            ],
            1  => [
                'cause'  => 1.0,
                'effect' => 1.0,
            ],
            2  => [
                'cause'  => 2.0,
                'effect' => 2.0,
            ],
            3  => [
                'cause'  => 3.0,
                'effect' => 3.0,
            ],
            4  => [
                'cause'  => 4.0,
                'effect' => 4.0,
            ],
            5  => [
                'cause'  => 5.0,
                'effect' => 5.0,
            ],
            6  => [
                'cause'  => 6.0,
                'effect' => 6.0,
            ],
            7  => [
                'cause'  => 7.0,
                'effect' => 7.0,
            ],
            8  => [
                'cause'  => 8.0,
                'effect' => 8.0,
            ],
            9  => [
                'cause'  => 9.0,
                'effect' => 9.0,
            ],
            10 => [
                'cause'  => 10.0,
                'effect' => 10.0,
            ],
            11 => [
                'cause'  => 11.0,
                'effect' => 11.0,
            ],
            12 => [
                'cause'  => 12.0,
                'effect' => 12.0,
            ],
            13 => [
                'cause'  => 13.0,
                'effect' => 13.0,
            ],
            14 => [
                'cause'  => 14.0,
                'effect' => 14.0,
            ],
            15 => [
                'cause'  => 15.0,
                'effect' => 15.0,
            ],
            16 => [
                'cause'  => 16.0,
                'effect' => 16.0,
            ],
            17 => [
                'cause'  => 17.0,
                'effect' => 17.0,
            ],
            18 => [
                'cause'  => 18.0,
                'effect' => 18.0,
            ],
            19 => [
                'cause'  => 19.0,
                'effect' => 19.0,
            ],
            20 => [
                'cause'  => 20.0,
                'effect' => 20.0,
            ],
            21 => [
                'cause'  => 21.0,
                'effect' => 21.0,
            ],
            22 => [
                'cause'  => 22.0,
                'effect' => 22.0,
            ],
            23 => [
                'cause'  => 23.0,
                'effect' => 23.0,
            ],
            24 => [
                'cause'  => 24.0,
                'effect' => 24.0,
            ],
            25 => [
                'cause'  => 25.0,
                'effect' => 25.0,
            ],
            26 => [
                'cause'  => 26.0,
                'effect' => 26.0,
            ],
            27 => [
                'cause'  => 27.0,
                'effect' => 27.0,
            ],
            28 => [
                'cause'  => 28.0,
                'effect' => 28.0,
            ],
            29 => [
                'cause'  => 29.0,
                'effect' => 29.0,
            ],
            30 => [
                'cause'  => 30.0,
                'effect' => 30.0,
            ],
            31 => [
                'cause'  => 31.0,
                'effect' => 31.0,
            ],
            32 => [
                'cause'  => 32.0,
                'effect' => 32.0,
            ],
            33 => [
                'cause'  => 33.0,
                'effect' => 33.0,
            ],
            34 => [
                'cause'  => 34.0,
                'effect' => 34.0,
            ],
            35 => [
                'cause'  => 35.0,
                'effect' => 35.0,
            ],
            36 => [
                'cause'  => 36.0,
                'effect' => 36.0,
            ],
            37 => [
                'cause'  => 37.0,
                'effect' => 37.0,
            ],
            38 => [
                'cause'  => 38.0,
                'effect' => 38.0,
            ],
            39 => [
                'cause'  => 39.0,
                'effect' => 39.0,
            ],
            40 => [
                'cause'  => 40.0,
                'effect' => 40.0,
            ],
            41 => [
                'cause'  => 41.0,
                'effect' => 41.0,
            ],
            42 => [
                'cause'  => 42.0,
                'effect' => 42.0,
            ],
            43 => [
                'cause'  => 43.0,
                'effect' => 43.0,
            ],
            44 => [
                'cause'  => 44.0,
                'effect' => 44.0,
            ],
            45 => [
                'cause'  => 45.0,
                'effect' => 45.0,
            ],
            46 => [
                'cause'  => 46.0,
                'effect' => 46.0,
            ],
            47 => [
                'cause'  => 47.0,
                'effect' => 47.0,
            ],
            48 => [
                'cause'  => 48.0,
                'effect' => 48.0,
            ],
            49 => [
                'cause'  => 49.0,
                'effect' => 49.0,
            ],
        ];
    }
    /**
     * @param QMUser $user
     * @param int $expected
     * @param string $needle
     * @param string|null $message
     * @return WpPost[]
     */
    private function assertNumberOfPostsWithTitleLike(QMUser $user, int $expected, string $needle, string $message = null): array{
        $l = $user->l();
        $l->fresh();
        $posts = $l->wp_posts->fresh();
        $fromDB = WpPost::wherePostAuthor($user->getId())->get();
        $broke = count($fromDB) !== count($posts);
        if($broke){
            $l->fresh();
            $posts = $l->wp_posts->fresh();
        }
        if($broke){le("fresh not working!");}
        $causePosts = $posts->filter(function($post) use ($needle) {
            return stripos($post->post_title, $needle) !== false;
        })->all();
        $this->assertCount($expected, $causePosts, "Should have $expected posts with title like $needle.  $message");
        return $causePosts;
    }
    /**
     * @param HasCauseAndEffect|QMPopulationStudy $study
     */
    public static function assertStudyVariablesHaveTrackingInstructionsCards($study): void{
        self::checkTrackingInstructionButtons($study->effectVariable);
        self::checkTrackingInstructionButtons($study->causeVariable);
    }
    /**
     * @param string $fullHtmlWithHead
     */
    public static function checkFullStudyHtml(string $fullHtmlWithHead): void{
        //TestHelper::assertContains('style.css', $fullHtmlWithHead);
        QMBaseTestCase::assertContains('join-study-button', $fullHtmlWithHead);
    }
	/**
	 * @param $body
	 * @param array $environmentSettings
	 */
	public static function globalStudyChecks($body, array $environmentSettings){
        $path = $environmentSettings["PATH_INFO"];
        $method = $environmentSettings["REQUEST_METHOD"];
        if(stripos($path, '/study/') === false){return;}
        /** @var QMStudy $study */
        $study = $body->data->study ?? $body->study ?? null;
        if(!$study){return;}
        self::assertStudyVariablesHaveTrackingInstructionsCards($study);
        $studyHtml = $study->studyHtml;
        $fullHtmlWithHead = $studyHtml->fullStudyHtml;
        self::checkCard($study->studyCard);
        self::assertNotNull($studyHtml->studyHeaderHtml);
        self::assertNotNull($studyHtml->studyAbstractHtml);
        self::assertNotNull($studyHtml->studyImageHtml);
        self::assertNotNull($studyHtml->studyMetaHtml);
        self::assertContains('class="study-title', $studyHtml->studyHeaderHtml);
        if(!str_contains($path, 'study/join') && !str_contains($path, 'study/create')){
            self::checkFullStudyHtml($fullHtmlWithHead);
        }
    }
	/**
	 * @param QMCard|object $c
	 */
	public static function checkCard($c): void{
		self::assertNotNull($c->content);
		self::assertNotNull($c->image);
		self::assertNotNull($c->ionIcon);
		self::assertNotNull($c->avatar);
		self::assertNotNull($c->title);
		/** @var QMButton $button */
		foreach($c->buttons as $button){
			self::assertNotNull($button->text);
			self::assertNotNull($button->title);
		}
	}
    public function assertCanUnPublish(): void{
        $requestData = $this->getRequestParams();
        $this->unPublishStudyAsUser1($requestData);
        $this->setAuthenticatedUser(1);
        $unPublishedUserStudy = $this->getUserStudyV4($requestData);
        $this->assertFalse((bool)$unPublishedUserStudy->isPublic, "Study should not be public after un-publishing");
        //$this->assertNull($unPublishedUserStudy->publishedAt);
        $this->assertEquals(BasePostStatusProperty::STATUS_PRIVATE, $unPublishedUserStudy->studyStatus);
        $this->setAuthenticatedUser($userId = 2);
        QMBaseTestCase::setExpectedRequestException(UnauthorizedException::class);
        $notUserStudy = $this->getStudyAsUserTwo(1, 401);  // User unpublished
        $populationStudy = $this->getStudyAsUserTwo();
        $this->checkPopulationStudyV4($populationStudy);
    }
    public function assertOtherUsersCanSeePublishedStudy(): void{
        $cause = $this->getCauseUserVariable();
        $effect = $this->getEffectUserVariable();
        $this->setAuthenticatedUser($userId = 2);
        $publishedUserStudy = $this->getUserStudyV4([
            'causeVariableName' => $cause->variableName,
            'effectVariableName' => $effect->variableName,
            'userId' => 1
        ]);
		$s = Study::find($publishedUserStudy->id);
		$url = $s->getDataLabShowUrl();
		$this->assertTrue((bool)$s->is_public, $url);
        $this->assertTrue((bool)$publishedUserStudy->isPublic, $url);
        $this->assertNotNull($publishedUserStudy->publishedAt, $url);
    }
    /**
     * @return array
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws NotEnoughDataException
     */
    public function publishQueueAndAnalyze(): array{
        $fake = Queue::fake();
        Queue::assertNothingPushed(); // Assert that no jobs were pushed...
	    $jobs = Queue::pushedJobs();
        $requestData = $this->getRequestParams();
        $studyFromPublishResponse = $this->publishStudyThatHasNotBeenAnalyzedYet($requestData);
		//$studyId = "cause-6059731-effect-6059732-user-1-user-study";
	    //$this->assertEquals($studyId, $studyFromPublishResponse->id);
        $this->assertNonLoggedInUsersCanSeeSharedStudies($requestData);
        $this->assertEquals("individual", $studyFromPublishResponse->type);
//	    Queue::assertPushed(AnalyzeStudyJob::class, 1);
//        $jobs = $jobs[AnalyzeStudyJob::class];
//        if(count($jobs) !== 1){
//            $this->assertCount(1, $jobs);
//        }
//		/** @var AnalyzeStudyJob $analyzeStudyJob */
//        $analyzeStudyJob = $jobs[0]["job"];
//        $this->assertInstanceOf(AnalyzeStudyJob::class, $analyzeStudyJob);
//        $study = $analyzeStudyJob->study;
//	    //$this->assertEquals($studyId, $study->getId());
//        $this->assertEquals($study->id, $studyFromPublishResponse->id);
//		$this->checkStudyUrls($study);
//        $analyzeStudyJob->handle();
	    $this->assertCount(1, Study::all());
	    $study = Study::first();
//        $wpPost = $study->findWpPost();
//        $this->assertNotNull($wpPost);
//        $content = $wpPost->post_content;
        $cause = $this->getCauseUserVariable();
        $effect = $this->getEffectUserVariable();
        $expectedId = "cause-$cause->variableId-effect-$effect->variableId-user-1-user-study";
//        $this->assertEquals($expectedId, $wpPost->post_name);
        $this->assertEquals($expectedId, $study->id);
        // Direct call for easier debugging
        $study = QMStudy::getStudyIfExists($cause->variableName, $effect->variableName, 1, 'individual');
        $this->compareChartGroup($study->getOrSetCharts());
        $this->assertNotNull($study,"No study!");
	    $this->assertNotNull($study->publishedAt,"No study->publishedAt!");
        $p = $study->firstOrNewWpPost();
        $this->setAuthenticatedUser(1);
        $this->getUserStudyV4($requestData);
        return [$requestData, $cause, $effect];
    }
    public function checkUserBio(): void{
        $user = $this->getOrSetAuthenticatedUser(1);
        $bio = $user->getBioHtml();
        if(stripos($bio, Env::DB_URL) !== false){le('stripos($bio, Env::DB_URL) !== false');}
    }
    /**
     * @return array
     */
    public function getRequestParams(): array{
        $requestData = [
            'causeVariableName'         => 'CauseVariableName',
            'effectVariableName'        => 'EffectVariableName',
            //QMRequest::PARAM_TIME_LIMIT => 5 // Make sure we have to queue
        ];
        return $requestData;
    }
	/**
	 * @param Study $s
	 */
	private function checkStudyUrls(Study $s): void{
		$studyPath = "/study/".$s->getId();
		$showUrl = "http://localhost$studyPath";
		$analyzeUrl = "$showUrl?analyze=1";
		$this->assertEquals(\App\Utils\Env::getAppUrl()."$studyPath?analyze=1", $s->getDebugUrl());
		$this->assertEquals($showUrl, $s->getUrl());
		$this->assertEquals($analyzeUrl, $s->getAnalyzeUrl());
		$dmb = $s->getDBModel();
		$this->assertEquals($showUrl, $s->getUrl());
		$this->assertEquals($analyzeUrl, $dmb->getAnalyzeUrl());
		$c = $s->getHasCorrelationCoefficient();
		$this->assertEquals($showUrl, $c->getUrl());
		$this->assertEquals($showUrl, $c->getUrl());
		$this->assertEquals($analyzeUrl, $c->getAnalyzeUrl());
	}
	private function checkInterestingFactors(): void{
		$cause = $this->getCauseUserVariable();
		$this->assertEquals(1, $cause->getInterestingFactor());
		$this->assertEquals(1, $cause->isPredictor());
		$effect = $this->getEffectUserVariable();
		$this->assertEquals(1, $effect->isOutcome());
		$this->assertEquals(1, $effect->getInterestingFactor());
	}

	public function testPostAndGetUserStudy(): void{
		if(AppMode::isWindows()){
			le("Please run this in Docker because the image is different on Windows");
		}
		$this->assertFalse(UserVariableIsPublicProperty::pluckOrDefault(['shareUserMeasurements' => false]));
		$cause = $this->getCauseUserVariable();
		$this->assertTrue($cause->isPredictor());
		TestDB::deleteWpData();
		TestDB::deleteUserData();
		GlobalVariableRelationship::truncate();
		$this->checkUserBio();
		$this->assertTrackingReminderNames([]);
		$this->publishQueueAndAnalyze();
		$e = $this->getEffectUserVariable();
		$this->assertTrackingReminderNames([$e->name]);
		$this->assertOtherUsersCanSeePublishedStudy();
		$this->assertTrackingReminderNames([$e->name]);
		$this->assertCanUnPublish();
		$this->assertTrackingReminderNames([$e->name]);
		$this->getOpenStudies(1);
		$this->assertTrackingReminderNames([$e->name]);
		$this->checkRootCauseAnalysis();
	}
}
