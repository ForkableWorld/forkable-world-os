<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\UserVariableNotFoundException;
use App\Models\Measurement;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\PhpUnitJobs\Reminders\ReminderNotificationGeneratorJob;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\TrackingReminder\PostTrackingReminderNotificationsResponse;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Types\TimeHelper;
use App\Units\OneToTenRatingUnit;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\BupropionSrCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Carbon\Carbon;
use Illuminate\Testing\TestResponse;
use LogicException;
use PDO;
use Slim\Http\Response;
use Tests\SlimTests\SlimTestCase;

/**
 * Class TrackingReminderNotificationsTest
 * @package Tests\Api\Reminders
 */
class TrackingReminderNotificationsTest extends \Tests\SlimTests\SlimTestCase {

    public const DISABLED_UNTIL = "2023-04-01";
    protected function setUp(): void{
        parent::setUp();
	    TestDB::deleteUserData();
    }
    public function testPastTrackingReminderNotification(){
        $this->generateOverallMoodReminderNotifications();
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed([],
            '/api/v1/trackingReminderNotifications/past');
        $this->assertCount(1, $trackingReminderNotifications);
        foreach ($trackingReminderNotifications as $trackingReminderNotification){
            $currentTime = time();
            $notificationTime = $trackingReminderNotification->trackingReminderNotificationTime;
            $this->assertLessThan($currentTime, $notificationTime);
        }
    }
    public function testPositive5TimeZone(){
        TestDB::resetUserTables();
        $this->setAuthenticatedUser(1);
        $offsetInHours = 5;
        $u = $this->getOrSetAuthenticatedUser(1);
        $u->setTimeZone(-5 * 60); // Change so it's different and the next change works
        $u->setTimeZone($offsetInHours * 60);
        $abbreviation = $u->getTimezone();
        $this->assertEquals('America/Chicago', $abbreviation);
        $this->generateMoodNotificationsAndCheckFeed();
    }
    public function testNegative5TimeZone(){
        TestDB::resetUserTables();
        $this->setAuthenticatedUser(1);
        $offsetInHours = -5;
        $this->getOrSetAuthenticatedUser(1)->setTimeZone($offsetInHours * 60);
        $abbreviation = $this->getOrSetAuthenticatedUser(1)->getTimezone();
        $this->assertEquals('Asia/Karachi', $abbreviation);
        $this->generateMoodNotificationsAndCheckFeed();
    }
    public function testGetPushNotificationMessage(){
        $this->generateOverallMoodReminderNotifications();
        $user = QMUser::findInDB(1);
        $pushNotificationMessage = QMTrackingReminderNotification::getPushNotificationMessage($user);
        $this->assertEquals('Time to track overall mood', $pushNotificationMessage);
        $this->assertQueryCountLessThan(52);
    }
    public function testFutureTrackingReminderNotification(){
        // Try posting a reminder
        $this->generateOverallMoodReminderNotifications();
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed([], '/api/v1/trackingReminderNotifications/future');
        $this->assertCount(0, $trackingReminderNotifications,
            'A future notification was not created when creating the tracking reminder!');
        foreach ($trackingReminderNotifications as $trn){
            $currentTime = time();
            $notificationTime = strtotime($trn->trackingReminderNotificationTime);
            $this->assertGreaterThan($currentTime, $notificationTime);
        }
        $this->assertQueryCountLessThan(63);
    }
    public function testOneToTenNotifications(){
        TestDB::deleteMeasurementsAndReminders();
        $this->generateOneToTenNotifications();
        $this->getAndCheckNotificationsAndFeed();
    }
    public function testSnoozeTrackingReminderNotification(){
        $this->assertEquals(0, UserVariable::whereUserId(1)->count());
        $this->setAuthenticatedUser(1);
        $notifications = $this->generateOverallMoodReminderNotifications();
        $this->assertEquals(1, UserVariable::whereUserId(1)->count());
        $notifications = ['id' => $notifications[0]->id];
        $this->snoozeAndCheck($notifications);
    }
    public function testSnoozeTrackingReminderNotificationArray(){
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $this->snoozeAndCheck([[
            'id' => $trackingReminderNotifications[0]->id,
            'action' => 'snooze'
       ]]);
    }
    public function testSnoozeTrackingReminderNotificationByTrackingReminderId(){
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $this->setAuthenticatedUser(1);
        $response = $this->getApiV3('trackingReminderNotifications/snooze', [
            'trackingReminderId' => $trackingReminderNotifications[0]->trackingReminderId
        ]);
        $this->checkSnoozedNotification($response);
    }
    /**
     * @group api
     * @throws InvalidTimestampException
     */
    public function testSkipTrackingReminderNotification(){
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $this->skipAndCheck(['id' => $trackingReminderNotifications[0]->id]);
    }
    public function testSkipTrackingReminderNotificationArray(){
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $trackingReminderNotifications[] = ['id' => $trackingReminderNotifications[0]->id, 'action' => 'skip'];
        $this->skipAndCheck($trackingReminderNotifications);
    }
    public function testSkipAllTrackingReminderNotificationsForReminder(){
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('trackingReminderNotifications/skip/all',
            ['trackingReminderId' => $trackingReminderNotifications[0]->trackingReminderId]);
        $this->checkNotificationSkippedSuccessfully($response);
    }
    public function testSkipTrackingReminderNotificationByTrackingReminderId(){
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $trackingReminderNotifications = ['trackingReminderId' => $trackingReminderNotifications[0]->trackingReminderId];
        $this->skipAndCheck($trackingReminderNotifications);
    }
    public function testTrackTrackingReminderNotification(){
        $this->setAuthenticatedUser(1);
        $commonVariable = QMCommonVariable::findByNameOrId(1398);
        $this->assertNotNull($commonVariable);
        $this->assertEquals("Overall Mood", $commonVariable->getVariableName());
        $this->assertEquals(1398, $commonVariable->getVariableIdAttribute());
        $commonVariable = QMCommonVariable::findByNameOrId("Overall Mood");
        $this->assertNotNull($commonVariable, "Could not get common variable by name!");
        $this->assertEquals("Overall Mood", $commonVariable->getVariableName());
        $this->assertEquals(1398, $commonVariable->getVariableIdAttribute());
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $postData = ['id' => $trackingReminderNotifications[0]->id];
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(1, $trackingReminderNotifications);
        $value = 3;
        try {
            $this->postNotificationsAndCheckMeasurements($postData, $value);
            $this->fail("Should have thrown exception");
        } catch (BadRequestException $e){
            ob_end_flush();
        }
        $postData['value'] = $value;
        $this->postNotificationsAndCheckMeasurements($postData,$value);
    }
    /**
     * @param $data
     */
    private function postTrackAndCheckNotificationResponse($data){
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('trackingReminderNotifications/track', $data);
        static::assertResponseBodyContains('Tracking reminder notification tracked successfully', $response);
    }
    public function testTrackTrackingReminderNotificationByTrackingReminderId(){
        $notifications = $this->generateOverallMoodReminderNotifications();
        $value = 3;
        $notifications = [
            'trackingReminderId' => $notifications[0]->trackingReminderId,
            'value' => $value
        ];
        $this->postNotificationsAndCheckMeasurements($notifications, $value);
        $notifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $notifications);
        $measurements = QMMeasurement::writable()->getArray();
        $this->assertCount(1, $measurements);
        $this->assertEquals($value, $measurements[0]->value);
        $measurementFromApi = $this->getAndCheckMeasurementsFromApi();
        $this->assertEquals($value, $measurementFromApi->value);
    }
    public function testCategoryFilterTrackingReminderNotification(){
        $this->setAuthenticatedUser(1);
        $this->generateOverallMoodReminderNotifications();
        $parameters = [ 'variableCategoryName' => 'Emotions' ];
        $notifications = $this->getAndCheckNotificationsAndFeed($parameters);
        $this->assertCount(1, $notifications);
        $parameters = [ 'variableCategoryName' => 'Sleep' ];
        $notifications = $this->getAndCheckNotificationsAndFeed($parameters);
        $this->assertCount(0, $notifications, TrackingReminderNotification::generateDataLabIndexUrl());
    }
    public function testGetTrackingReminderNotification(){
        $this->setAuthenticatedUser(1);
        $this->generateOverallMoodReminderNotifications();
        $this->postEmptyNotifications(1);
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed([]);
        $this->assertCount(1, $trackingReminderNotifications);
        $this->assertNotNull($trackingReminderNotifications[0]->trackingReminderNotificationTimeEpoch);
        $this->checkTrackingReminderNotificationProperties($trackingReminderNotifications, null);
    }
    public function testGetTodayTrackingReminderNotification(){
        $this->setAuthenticatedUser(1);
        $this->generateOverallMoodReminderNotifications();
        $min = date('Y-m-d H:i:s', time() - 2 * 86400);
        $max = date('Y-m-d H:i:s', time());
        $parameters = [
            'minimumReminderTimeUtcString' => $min,
            'maximumReminderTimeUtcString' => $max,
        ];
        $this->setAuthenticatedUser(1);
        $response = $this->getTrackingReminderNotifications( $parameters);
        $notifications = $response->data;
        $u = $this->getOrSetAuthenticatedUser(1);
        $tz = $u->timezone;
        /** @var QMTrackingReminderNotification[] $notifications */
        $this->assertCount(1, $notifications,
            "Should have gotten 1 notification between \n$min (24 hours ago) and \n$max (24 hours from now).
            ".TrackingReminderNotification::getDataLabIndexMDLink().
            TrackingReminder::getDataLabIndexMDLink());
        $this->assertNotNull($notifications[0]->trackingReminderNotificationTimeEpoch);
        $this->checkTrackingReminderNotificationProperties($notifications, null);
    }
    public function testTrackTrackingReminderNotificationWithDifferentValue(){
        TestDB::deleteMeasurementsAndReminders();
        $this->setAuthenticatedUser(1);
        $trackingReminderNotifications = $this->generateOverallMoodReminderNotifications();
        $this->postTrackAndCheckNotificationResponse([
            'id' => $trackingReminderNotifications[0]->id,
            'modifiedValue' => 3
        ]);
        $this->checkThereAreNoMoreNotificationsAndThatMeasurementPosted();
    }
    public function testTrackTrackingReminderNotificationWithZeroValue(){
        $this->setAuthenticatedUser(1);
        TestDB::deleteMeasurementsAndReminders();
        $variableName = BupropionSrCommonVariable::NAME;
        $trackingReminder = ['variableName' => $variableName, 'reminderFrequency' => 86400, 'defaultValue' => 1];
        $this->postAndCheckTrackingRemindersResponse($trackingReminder);
        $this->setAuthenticatedUser(1);
        $responseBody = $this->getTrackingReminderNotifications( [
            'limit' => 200,
            'appName' => 'MoodiModo',
            'appVersion' => '2.1.1.0',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $trackingReminderNotificationsFromApi = $responseBody->data;
        $modifiedValueInDefaultUnit = 0;
        $this->postTrackAndCheckNotificationResponse([
            'trackingReminderNotification' => $trackingReminderNotificationsFromApi[0],
            'modifiedValue' => $modifiedValueInDefaultUnit
        ]);
        $measurementsFromApi = $this->makeSureWeHaveOneMeasurement($modifiedValueInDefaultUnit, $variableName);
        $this->assertEquals(0, $measurementsFromApi[0]->value);
        $this->setAuthenticatedUser(1);
        $measurementsFromApi = $this->getDailyMeasurements(['variableName' => $variableName]);
        $this->assertCount(1, $measurementsFromApi);
        $this->assertEquals(0, $measurementsFromApi[0]->value);
    }
    public function testTrackTrackingReminderNotificationWithTenOutOfTenRating(){
        $modifiedValue = 10;
        $modifiedValueInDefaultUnit = 5;
        $variableName = 'Overall Mood';
        $unitAbbreviatedName = "/10";
        $this->setAuthenticatedUser(1);
        TestDB::deleteMeasurementsAndReminders();
        $this->postAndCheckTrackingRemindersResponse([
            'variableName' => $variableName,
            'reminderFrequency' => 86400,
            'unitAbbreviatedName' => $unitAbbreviatedName
        ]);
        $responseBody = $this->getTrackingReminderNotifications( [
            'limit' => 200,
            'appName' => 'MoodiModo',
            'appVersion' => '2.1.1.0',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $trackingReminderNotificationsFromApi = $responseBody->data;
        $trackingReminderNotificationsFromApi[0]->modifiedValue = $modifiedValue;
        $uv = OverallMoodCommonVariable::getUserVariableByUserId(UserIdProperty::USER_ID_DEMO);
        $this->assertEquals(OneToTenRatingUnit::ID, $uv->getUserUnit()->id);
        $this->postTrackAndCheckNotificationResponse($trackingReminderNotificationsFromApi);
        $measurementsFromApi = $this->makeSureWeHaveOneMeasurement($modifiedValueInDefaultUnit, $variableName);
        $this->assertEquals($modifiedValue, $measurementsFromApi[0]->value);
        $measurementsFromApi = $this->getDailyMeasurements(['variableName' => $variableName]);
        $this->assertCount(1, $measurementsFromApi);
        $this->assertEquals($modifiedValue, $measurementsFromApi[0]->value);
    }
    public function testTrackByTrackingReminderNotificationObjectWithDifferentValue(){
        $notificationFromApi = $this->setupTest();
        $value = 3;
        $postBody = ['trackingReminderNotification' => $notificationFromApi, 'modifiedValue' => $value];
        $this->setAuthenticatedUser(1);
        $this->postApiV3('trackingReminderNotifications/track', $postBody);
        $this->checkThatNotificationTrackedCorrectly($value,
            $notificationFromApi->trackingReminderNotificationTimeEpoch);
    }
    /**
     * @group api
     * @throws InvalidTimestampException
     */
    public function testTrackByTrackingReminderObjectWithDifferentValue(){
        TestDB::deleteMeasurementsAndReminders();
        $this->setAuthenticatedUser(1);
        $this->generateOverallMoodReminderNotifications();
        $trackingRemindersFromApi = $this->getAndCheckTrackingReminders( [
            'limit' => 200,
            'appName' => 'MoodiModo',
            'appVersion' => '2.1.1.0',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $trackingReminderFromApi = $trackingRemindersFromApi[0];
        $trackingReminderFromApi->modifiedValue = 3;
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(1, $trackingReminderNotifications);
        $this->setAuthenticatedUser(1);
        $this->postApiV3('trackingReminderNotifications/track', [
            'trackingReminder' => $trackingReminderFromApi,
            'modifiedValue' => 3
        ]);
        $this->checkThereAreNoMoreNotificationsAndThatMeasurementPosted();
        QMUserVariable::getOrCreateAndAnalyze(1, $trackingReminderFromApi->variableId, true);
        $trackingRemindersFromApi = $this->getTrackingReminders([
            'limit' => 200,
            'appName' => 'MoodiModo',
            'appVersion' => '2.1.1.0',
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $trackingRemindersFromApi =
        /** @var QMTrackingReminder $trackingReminderFromApi */
        $trackingReminderFromApi = $trackingRemindersFromApi[0];
        $this->assertEquals(1, $trackingReminderFromApi->numberOfMeasurements);
    }
    public function testPostTrackNotificationsArray(){
        $trackingReminderNotificationFromApi = $this->setupTest();
        $modifiedValue = 3;
        $postBody[] = [
            'trackingReminderNotification' => $trackingReminderNotificationFromApi,
            'modifiedValue' => $modifiedValue,
            'action' => 'track'
        ];
        $this->postNotificationsAndCheckValue($postBody, $modifiedValue);
        $this->checkThatNotificationTrackedCorrectly($modifiedValue, $trackingReminderNotificationFromApi->trackingReminderNotificationTimeEpoch);
    }
    public function testPostTrackNotificationsArrayWithTrackingReminderNotificationId(){
        $trackingReminderNotificationFromApi = $this->setupTest();
        $modifiedValue = 3;
        $postBody[] = [
            'trackingReminderNotificationId' => $trackingReminderNotificationFromApi->id,
            'modifiedValue' => $modifiedValue,
            'action' => 'track'
        ];
        $this->postNotificationsAndCheckValue($postBody, $modifiedValue);
        $this->checkThatNotificationTrackedCorrectly($modifiedValue, $trackingReminderNotificationFromApi->trackingReminderNotificationTimeEpoch);
    }
    public function testPostTrackNotificationWithoutAction(){
        $trackingReminderNotificationFromApi = $this->setupTest();
        $modifiedValue = 4;
        $this->postNotificationsAndCheckValue([
            'trackingReminderNotificationId' => $trackingReminderNotificationFromApi->id,
            'modifiedValue' => $modifiedValue,
        ], $modifiedValue);
        $this->checkThatNotificationTrackedCorrectly($modifiedValue, $trackingReminderNotificationFromApi->trackingReminderNotificationTimeEpoch);
    }
    public function testPostTrackNotificationsWithoutAuth(){
        $trackingReminderNotificationFromApi = $this->setupTest();
        $this->setAuthenticatedUser(null);
        $modifiedValue = 3;
        $postBody[] = [
            'trackingReminderNotificationId' => $trackingReminderNotificationFromApi->id,
            'modifiedValue' => $modifiedValue,
            'action' => 'track'
        ];
        $this->postNotificationsAndCheckValue($postBody, $modifiedValue);
        $this->setAuthenticatedUser(1);
        $this->checkThatNotificationTrackedCorrectly($modifiedValue,
            $trackingReminderNotificationFromApi->trackingReminderNotificationTimeEpoch);
    }
    /**
     * @throws InvalidTimestampException
     */
    public function testPostMultipleTrackNotificationsArray(){
        $this->setAuthenticatedUser(1);
        TestDB::deleteMeasurementsAndReminders();
        $this->postAndCheckTrackingRemindersResponse([
            'variableId'        => 1398,
            'reminderFrequency' => 86400,
            'defaultValue'      => 1
        ]);
        $this->postAndCheckTrackingRemindersResponse([
            'variableName'      => BupropionSrCommonVariable::NAME,
            'reminderFrequency' => 86400,
            'defaultValue'      => 1
        ]);
        $fromApi = $this->getNotificationsFromApi();
        $this->assertCount(2, $fromApi);
        foreach ($fromApi as $n){
            $n->action = 'track';
            $time = $n->trackingReminderNotificationTimeLocalHumanString;
            $this->assertContains("evening", $time);
        }
        $fromApi[0]->modifiedValue = 1;
        $fromApi[1]->modifiedValue = 2;
        $response = $this->postTrackingReminderNotifications($fromApi);
        $measurements = QMMeasurement::readonly()->getArray();
        $this->assertCount(2, $measurements);
        $userVariable = QMUserVariable::getByNameOrId(1, 1398);
        $this->assertEquals(1, $userVariable->lastValue);
        $this->assertNull($userVariable->secondToLastValue);
        $this->assertNull($userVariable->thirdToLastValue);
    }
    private function generateAndCheckNotifications(){
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
        $notifications = QMTrackingReminderNotification::getTrackingReminderNotifications(null, []);
        foreach($notifications as $n){
            $uv = $n->getQMUserVariable();
            $pushData = $n->getIndividualPushNotificationData();
            if($uv->lastValue !== null){
                $this->assertEquals($uv->lastValue, $pushData->lastValue);
            } else {
                $cv = $n->getCommonVariable();
                if($cv->mostCommonValue !== null){
                    $this->assertEquals($cv->mostCommonValue, $pushData->lastValue);
                }
            }
        }
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
    }
    public function testPostMultipleTrackNotificationsArrayForTheSameVariable() {
        $disabled = true;
        if($disabled){
            $this->skipTest("Disabled because it's flakey");
            return;
        }
        $this->setAuthenticatedUser(1);
        TestDB::deleteMeasurementsAndReminders();
        $trackingReminder = ['variableId' => 1398, 'reminderFrequency' => 60, 'defaultValue' => 1];
        $this->postAndCheckTrackingRemindersResponse($trackingReminder);
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
        $trackingReminderNotificationsFromApi = $this->getNotificationsFromApi();
        foreach ($trackingReminderNotificationsFromApi as $trackingReminderNotificationFromApi){
            $trackingReminderNotificationFromApi->action = 'track';
        }
        $trackingReminderNotificationsFromApi[0]->modifiedValue = 1;
        $trackingReminderNotificationsFromApi[1]->modifiedValue = 2;
        $response = $this->postTrackingReminderNotifications(
            [$trackingReminderNotificationsFromApi[0], $trackingReminderNotificationsFromApi[1]]);
        $measurements = QMMeasurement::readonly()->getArray();
        $this->assertCount(2, $measurements);
        $userVariable = QMUserVariable::getByNameOrId(1, 1398);
        $this->assertEquals(2, $userVariable->lastValue);
        $this->assertEquals(1, $userVariable->secondToLastValue);
        $this->assertNull($userVariable->thirdToLastValue);
        $trackingReminderNotificationsFromApi[2]->modifiedValue = 3;
        $response = $this->postTrackingReminderNotifications([$trackingReminderNotificationsFromApi[2]]);
        $userVariable = QMUserVariable::getByNameOrId(1, 1398);
        $this->assertEquals(3, $userVariable->lastValue);
        $this->assertEquals(2, $userVariable->secondToLastValue);
        $this->assertEquals(1, $userVariable->thirdToLastValue);
    }
    protected function postTrackingReminderNotifications($body){
        $this->setAuthenticatedUser(1);
        $response = $this->postAndGetDecodedBody('/api/v1/trackingReminderNotifications', $body);
        return $response;
    }
    public function generateOneToTenNotifications(){
        $this->setAuthenticatedUser(1);
        QMCommonVariable::writable()->update(['valence' => BaseValenceProperty::VALENCE_POSITIVE]);
        $trackingReminder = [
            'variableId' => 1398,
            'reminderFrequency' => 86400,
            'unitAbbreviatedName' => '/10'
        ];
        $response = $this->postAndCheckTrackingRemindersResponse($trackingReminder);
        /** @var QMTrackingReminderNotification[] $trackingReminderNotifications */
        $trackingReminderNotifications = $response->data->trackingReminderNotifications;
        $this->assertEquals('/10', $trackingReminderNotifications[0]->unitAbbreviatedName);
        $this->assertEquals(BaseValenceProperty::VALENCE_POSITIVE, $trackingReminderNotifications[0]->valence);
        $this->assertEquals(10, $trackingReminderNotifications[0]->maximumAllowedValueInUserUnit);
        $this->assertEquals(1, $trackingReminderNotifications[0]->minimumAllowedValueInUserUnit);
    }
    /**
     * @param float $modifiedValue
     * @param int $trackingReminderNotificationTime
     * @param string $variableName
     * @throws UserVariableNotFoundException
     */
    public function checkThatNotificationTrackedCorrectly(float $modifiedValue, int $trackingReminderNotificationTime,
                                                          string $variableName = 'Overall Mood'){
        $v = QMUserVariable::getByNameOrId(1, $variableName);
        $this->assertDateEquals($trackingReminderNotificationTime, $v->getLatestTaggedMeasurementAt(),
            "trackingReminderNotificationTime", "v->latestTaggedMeasurementTime");
        $this->assertDateEquals($trackingReminderNotificationTime, $v->latestNonTaggedMeasurementTime,
            "trackingReminderNotificationTime", "v->latestNonTaggedMeasurementTime");
        $this->assertDateEquals($trackingReminderNotificationTime, $v->latestMeasurementTime,
            "trackingReminderNotificationTime", "v->latestMeasurementTime");
        //$trackingReminders = $response['trackingReminders'];
        $trackingReminders = $this->getAndCheckTrackingReminders();
        $this->assertEquals($modifiedValue, $trackingReminders[0]->lastValueInUserUnit);
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
        $measurements = QMMeasurement::readonly()->getArray();
        $this->assertCount(1, $measurements);
        $this->assertEquals($modifiedValue, $measurements[0]->value);
        $measurementsFromApi = $this->getMeasurements(['variableName' => 'Overall Mood'], 1);
        $this->assertEquals($modifiedValue, $measurementsFromApi[0]->value);
        $daily = $this->getDailyMeasurements(['variableName' => 'Overall Mood']);
        $this->assertCount(1, $daily);
        $this->assertEquals($modifiedValue, $daily[0]->value);
        $this->assertDateEquals($trackingReminderNotificationTime, $measurementsFromApi[0]->startTime,
            "trackingReminderNotificationTime", "First Measurement Time From API",
            Measurement::generateDataLabUrl().
            "\n".TrackingReminder::generateDataLabUrl().
            "\n".TrackingReminderNotification::generateDataLabUrl());
        QMTrackingReminderNotification::writable()->delete();
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
        $notifications = QMTrackingReminderNotification::getWhereDue();
        foreach ($notifications as $n){
            $pushData = $n->getIndividualPushNotificationData();
            $this->assertEquals($modifiedValue, $pushData->lastValue);
        }
    }
    /**
     * @param $response
     * @throws InvalidTimestampException
     */
    public function checkThatNotificationSnoozedCorrectly($response){
        $this->checkSnoozedNotification($response);
    }
    /**
     * @return QMTrackingReminderNotification[]
     */
    private function getNotificationsFromApi(){
        $params = ['limit' => 200, 'appName' => 'MoodiModo', 'appVersion' => '2.1.1.0', 'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT];
        $response = $this->getTrackingReminderNotifications( $params);
        /** @var QMTrackingReminderNotification[] $notificationsFromApi */
        $notificationsFromApi = $response->data;
        return $notificationsFromApi;
    }
    /**
     * @return QMTrackingReminder[]
     */
    private function getRemindersFromApi(){
        $params = ['limit' => 200, 'appName' => 'MoodiModo', 'appVersion' => '2.1.1.0', 'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT];
        return $this->getTrackingReminders($params);
    }
    public function testReminderNotificationTaskForDailyReminder(){
        $localTimesFromNotifications = [];
        TestDB::deleteMeasurementsAndReminders();
        $this->setAuthenticatedUser(1);
        $trackingReminderPostBody = [
            'variableId' => 1398,
            'reminderFrequency' => 86400,
            'defaultValue' => 2,
            'timeZoneOffset' => 300
        ];
        $this->postApiV3('trackingReminders', $trackingReminderPostBody);
        ReminderNotificationGeneratorJob::deleteOldAndCreateNewNotifications();
        TrackingReminderNotificationsTest::checkLatestNotifyAtOnReminders();
        $notificationsFromDatabase = QMTrackingReminderNotification::readonly()->count();
        $this->assertGreaterThan(4, $notificationsFromDatabase, "We should have 5 notification rows in DB");
        $this->assertLessThan(7, $notificationsFromDatabase, "We should have 5 notification rows in DB");
        $remindersFromApi = $this->getRemindersFromApi();
        $notificationsFromApi = $this->getNotificationsFromApi();
        if(time() < strtotime(self::DISABLED_UNTIL)){
            $this->skipTest('TODO: Fix daylight savings issue');
            return;
        }
        foreach($notificationsFromApi as $n){
            if($n->reminderStartTimeLocal !== $n->trackingReminderNotificationTimeLocal){
                throw new LogicException("notification->reminderStartTimeLocal !== notification->trackingReminderNotificationTimeLocal ".
                    "($n->reminderStartTimeLocal !== $n->trackingReminderNotificationTimeLocal)");
            }
            $localTimesFromNotifications[] = $n->trackingReminderNotificationTimeLocal;
        }
        foreach($remindersFromApi[0]->localDailyReminderNotificationTimesForAllReminders as $time){
            $this->assertContains($time, $localTimesFromNotifications,
                'We have a local notification time from a reminder that is not going to have a matching notification in inbox.');
        }
    }
    public function testReminderNotificationTaskForHourlyReminder(){
        $localDailyReminderNotificationTimesFromNotifications = [];
        $this->setAuthenticatedUser(1);
        TestDB::deleteMeasurementsAndReminders();
        $this->postAndCheckTrackingRemindersResponse([
            'variableId' => 1398,
            'reminderFrequency' => 3600,
            'defaultValue' => 2,
            'timeZoneOffset' => 300
        ]);
        $this->generateAndCheckNotifications();
        /** @var array $trackingReminderNotifications */
        $db = Writable::db();
        $trackingReminderNotifications = $db->table('tracking_reminder_notifications')->getArray();
        $this->assertGreaterThan(27, count($trackingReminderNotifications));
        $this->assertLessThan(46, count($trackingReminderNotifications));
        $trackingReminders = $this->getTrackingReminders(['limit' => 200, 'appName' => 'MoodiModo', 'appVersion' => '2.1.1.0',
                                             'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        $body = $this->getTrackingReminderNotifications(['limit' => 200, 'appName' => 'MoodiModo',
                                                         'appVersion' => '2.1.1.0',
                                                         'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        /** @var QMTrackingReminderNotification[] $trackingReminderNotificationsFromApi */
        $trackingReminderNotificationsFromApi = $body->data;
        $localDailyReminderNotificationTimesForAllReminders =
            $trackingReminders[0]->localDailyReminderNotificationTimesForAllReminders;
        if(time() < strtotime(self::DISABLED_UNTIL)){
            $this->skipTest('TODO: Fix daylight savings issue');
            return;
        }
        foreach($trackingReminderNotificationsFromApi as $trn){
            $this->assertContains( $trn->trackingReminderNotificationTimeLocal, $localDailyReminderNotificationTimesForAllReminders,
                'Reminder notification created for time not present in localDailyReminderNotificationTimesForAllReminders');
            /** @var [] $localDailyReminderNotificationTimesFromNotifications */
            $localDailyReminderNotificationTimesFromNotifications[] = $trn->trackingReminderNotificationTimeLocal;
        }
        foreach($localDailyReminderNotificationTimesForAllReminders as $localDailyReminderNotificationTime){
            $this->assertContains($localDailyReminderNotificationTime, $localDailyReminderNotificationTimesFromNotifications,
                'We have a local notification time that is not going to have a matching notification in inbox.');
        }
    }
    public function testYesNoTwice(){
        $notifications = $this->createYesNoReminderAndNotifications();
        $notifications[0]->modifiedValue = 1;
        $notifications[0]->action = "track";
        $notifications[0]->total = 0;  // This seems stupid to even have this field, but I think it's ignored
        $this->expectQMException();
        $response = $this->postTrackingReminderNotifications($notifications[0]);
        $measurements = Writable::pdo()->query("SELECT * FROM measurements")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($measurements as $measurement){$this->assertEquals(19, $measurement['unit_id']);}
    }
    /**
     * @param Response|TestResponse $response
     * @throws InvalidTimestampException
     */
    public function checkNotificationSkippedSuccessfully($response){
        static::assertResponseBodyContains('Tracking reminder notification skipped successfully', $response);
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
    }
    /**
     * @return QMTrackingReminderNotification
     * @throws InvalidTimestampException
     */
    private function setupTest(){
        $db = Writable::db();
        TestDB::deleteMeasurementsAndReminders();
        $db->table('wp_users')->where('ID', 1)->update(['send_reminder_notification_emails' => 1]);
        $this->setAuthenticatedUser(1);
        $this->generateOverallMoodReminderNotifications();
        $responseBody = $this->getTrackingReminderNotifications(['limit' => 200, 'appName' => 'MoodiModo',
                                                                 'appVersion' => '2.1.1.0',
                                                                 'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        $trackingReminderNotificationsFromApi = $responseBody->data;
        return $trackingReminderNotificationsFromApi[0];
    }
    /**
     * @param Response|TestResponse $response
     * @throws InvalidTimestampException
     */
    private function checkSnoozedNotification($response){
        static::assertResponseBodyContains('Tracking reminder notification snoozed successfully', $response);
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
        $n = TrackingReminderNotification::whereUserId(1)
            ->orderBy(TrackingReminderNotification::FIELD_NOTIFIED_AT, 'asc')
            ->first();
        $afterSnoozeReminderTime = $n->notify_at;
        TimeHelper::checkForDbServerTimeDiscrepancy();
        $this->logInfo("Current system time is " . date('Y-m-d H:i:s'));
        self::assertDateGreaterThan(time(), $afterSnoozeReminderTime, "Current system time is " .
            TimeHelper::getCurrentUtcTimeStringHHMMSS() . " and snoozed trackingReminderNotificationTime is " .
            TimeHelper::convertEpochUnixTimeSecondsToTimeHHMMSS(strtotime($afterSnoozeReminderTime)));
    }
    /**
     * @return QMMeasurement
     */
    private function getAndCheckMeasurementsFromApi(){
        $this->setAuthenticatedUser(1);
        $measurementsFromApi = $this->getMeasurements([], 1);
        return $measurementsFromApi[0];
    }
    /**
     * @param $trackingReminderNotifications
     * @throws InvalidTimestampException
     */
    private function skipAndCheck($trackingReminderNotifications){
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('trackingReminderNotifications/skip', $trackingReminderNotifications);
        $this->checkNotificationSkippedSuccessfully($response);
    }
    /**
     * @param $trackingReminderNotificationsToSnooze
     * @throws InvalidTimestampException
     */
    private function snoozeAndCheck($trackingReminderNotificationsToSnooze){
        $this->setAuthenticatedUser(1);
        $response = $this->postApiV3('trackingReminderNotifications/snooze', $trackingReminderNotificationsToSnooze);
        $this->checkThatNotificationSnoozedCorrectly($response);
    }
    /**
     * @param array $postData
     * @param $value
     */
    private function postNotificationsAndCheckMeasurements(array $postData, $value): void{
        $this->postTrackAndCheckNotificationResponse($postData);
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
        $measurements = QMMeasurement::readonly()->getArray();
        $this->assertCount(1, $measurements);
        $this->assertEquals($value, $measurements[0]->value);
        $measurementFromApi = $this->getAndCheckMeasurementsFromApi();
        $this->assertEquals($value, $measurementFromApi->value);
    }
    private function generateMoodNotificationsAndCheckFeed(): void{
        $carbon = $this->getOrSetAuthenticatedUser(1)->convertToLocalTimezone(time());
        $this->assertInstanceOf(Carbon::class, $carbon);
        $this->generateOverallMoodReminderNotifications();
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed([], '/api/v1/trackingReminderNotifications/past');
        $this->assertCount(1, $trackingReminderNotifications);
	    foreach($trackingReminderNotifications as $trackingReminderNotification){
            $currentTime = time();
            $notificationTime = $trackingReminderNotification->trackingReminderNotificationTime;
            $this->assertLessThan($currentTime, $notificationTime);
        }
    }
    private function checkThereAreNoMoreNotificationsAndThatMeasurementPosted(): void{
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
        $measurements = QMMeasurement::writable()->getArray();
        $this->assertCount(1, $measurements);
        $this->assertEquals(3, $measurements[0]->value);
        $measurementFromApi = $this->getAndCheckMeasurementsFromApi();
        $this->assertEquals(3, $measurementFromApi->value);
    }
    /**
     * @param int $modifiedValueInDefaultUnit
     * @param string $variableName
     * @return QMMeasurement[]
     */
    private function makeSureWeHaveOneMeasurement(int $modifiedValueInDefaultUnit, string $variableName){
        $trackingReminderNotifications = $this->getAndCheckNotificationsAndFeed();
        $this->assertCount(0, $trackingReminderNotifications);
        $measurementsDirectlyFromDatabase = QMMeasurement::writable()->getArray();
        $this->assertCount(1, $measurementsDirectlyFromDatabase);
        $this->assertEquals($modifiedValueInDefaultUnit, $measurementsDirectlyFromDatabase[0]->value);
        $measurementsFromApi = $this->getMeasurements(['variableName' => $variableName]);
        $this->assertCount(1, $measurementsFromApi);
        return $measurementsFromApi;
    }
    public static function checkLatestNotifyAtOnReminders(): void{
        $reminders = TrackingReminder::all();
        foreach($reminders as $reminder){
            $latestReminder = $reminder->latest_tracking_reminder_notification_notify_at;
            $latestNotification =
                TrackingReminderNotification::whereTrackingReminderId($reminder->id)
                    ->max(TrackingReminderNotification::FIELD_NOTIFY_AT);
            self::assertEquals($latestReminder, $latestNotification,
                "MAX Notification NOTIFY_AT is $latestNotification but \n".
                "reminder->latest_tracking_reminder_notification_notify_at is $reminder->latest_tracking_reminder_notification_notify_at \n".
                "Notifications: ".TrackingReminderNotification::generateDataLabIndexUrl()."\n".
                "Reminders: ".TrackingReminder::generateDataLabIndexUrl());
        }
    }
    /**
     * @param $postBody
     * @param int $modifiedValue
     */
    private function postNotificationsAndCheckValue($postBody, int $modifiedValue): void{
        $response = $this->postTrackingReminderNotifications($postBody);
        $measurements = $response->measurements;
        $userVariables = $response->userVariables;
        $uv = QMUserVariable::getFirst($userVariables);
        $this->assertCount(1, $userVariables);
        $m = QMMeasurement::getFirst($measurements->{$uv->name});
        $this->assertEquals($modifiedValue, $m->originalValue);
    }
    /**
     * @param int $expected
     * @return QMTrackingReminderNotification[]
     */
    public function postEmptyNotifications(int $expected): array {
        $response = $this->postAndGetDecodedBody('/api/v1/trackingReminderNotifications', []);
        /** @var PostTrackingReminderNotificationsResponse $response */
        $notifications = $response->trackingReminderNotifications;
        $this->assertCount($expected, $notifications);
        $this->assertNotNull($notifications[0]->trackingReminderNotificationTimeEpoch);
        $this->checkTrackingReminderNotificationProperties($notifications, null);
        return $notifications;
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
        $this->actingAsUserOne();
		$body = $this->getApiV3('trackingReminderNotifications', []);
		$fromApi = $body->data ?? $body;
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
}
