<?php /** @noinspection PhpUnreachableStatementInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Logging\QMLog;
use App\Models\DeviceToken;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Slim\Model\AppEnvironment;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\VitalSignsCommonVariables\CoreBodyTemperatureCommonVariable;
use LogicException;
use Tests\DBUnitTestCase;
class DeviceTokensTest extends \Tests\SlimTests\SlimTestCase {
    public const ANDROID_PUSH_ENABLED = false;
    public function testCombinedAppleNotifications(){
	    $this->skipTest("we don't send apple notifications anymore");
        TestDB::resetUserTables();
        if(AppEnvironment::isCircleCIOrTravis()){
            $this->skipTest("This doesn't work sometimes for some reason");
            /** @noinspection PhpUnreachableStatementInspection */
            return;
        }
        $this->postDeviceTokenAndSendPushNotifications(BasePlatformProperty::PLATFORM_IOS, true);
        $this->assertQueryCountLessThan(100);
    }
    public function testIndividualAppleNotifications(){
		$this->skipTest("we don't send apple notifications anymore");
        if(AppEnvironment::isCircleCIOrTravis()){
            $this->skipTest("This doesn't work sometimes for some reason");
            return;
        }
        $this->postDeviceTokenAndSendPushNotifications(BasePlatformProperty::PLATFORM_IOS, $combined = false);
        $this->assertQueryCountLessThan(102);
    }
    public function testWebNotifications(){
        if(AppEnvironment::isCircleCIOrTravis()){
            $this->skipTest("Can't whitelist Travis IP for pushes");
            /** @noinspection PhpUnreachableStatementInspection */
            return;
        }
        $this->postDeviceTokenAndSendPushNotifications(BasePlatformProperty::PLATFORM_WEB, $combined = false);
        $this->assertQueryCountLessThan(104);
    }
    public function testIndividualAndroidNotifications(){
        if(!self::ANDROID_PUSH_ENABLED){
            $this->skipTest("Android pushes are disabled until I can update app");
            return;
        }
        if(AppEnvironment::isCircleCIOrTravis()){
            $this->skipTest("Can't whitelist Travis IP for pushes");
            return;
        }
        $this->postDeviceTokenAndSendPushNotifications(BasePlatformProperty::PLATFORM_ANDROID, $combined = false);
        $this->assertQueryCountLessThan(4);
    }
    public function testCombinedAndroidNotifications(){
        if(!self::ANDROID_PUSH_ENABLED){
            $this->skipTest("Android pushes are disabled until I can update app");
            return;
        }
        if(AppEnvironment::isCircleCIOrTravis()){
            $this->skipTest("Can't whitelist Travis IP for pushes");
            return;
        }
        $this->postDeviceTokenAndSendPushNotifications(BasePlatformProperty::PLATFORM_ANDROID, $combined = true);
        $this->assertQueryCountLessThan(4);
    }
    /**
     * @param $deviceTokenString
     * @noinspection PhpUndefinedFieldInspection
     */
    public function checkReceivedAt(string $deviceTokenString){
        $tokens = [];
        foreach ($this->getTrackingReminderNotificationsFromApi() as $n){
            $tokens[] = $n->deviceToken = $deviceTokenString;
            $n->additionalData =  ['trackingReminderNotificationId' => $n->id];
            $this->postAndGetDecodedBody("api/v3/trackingReminderNotification/received", $n);
        }
        foreach ($tokens as $str){
            $t = DeviceToken::find($str);
            $this->assertNotNull($t->received_at);
        }
    }
    /**
     * @param string $platform
     * @param bool $combined
     * @throws \App\Exceptions\NoTestDeviceTokenException
     */
    public function postDeviceTokenAndSendPushNotifications(string $platform, bool $combined){
        $this->postTestToken($platform);
        if((bool)User::find(1)->combine_notifications != (bool)$combined){
            try {
                $this->setCombineNotifications($combined);
            } catch (\Throwable $e) {
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
        TrackingReminderNotification::deleteAll();
        TrackingReminder::deleteAll();
        $this->generateOverallMoodAndBodyTempReminderNotifications();
        $u = $this->getOrSetAuthenticatedUser(1);
        $this->assertCount(1, $deviceTokensFromDB = DeviceToken::all());
        $this->assertCount(1, $tokens = $u->getQMDeviceTokens());
        if($tooLateOrEarly = $u->tooLateOrEarlyForNotifications()){
            $this->assertCount(0, $tokens, "It's too late or early for notifications!");
            return;
        }
        if(AppEnvironment::isCircleCIOrTravis() && $platform == BasePlatformProperty::PLATFORM_ANDROID){
            $this->logInfo("Skipping android push on CircleCI because we can't whitelist it");
            return;
        }
        $results = QMTrackingReminderNotification::send();
        $this->assertGreaterThan(0, count($results));
        $token = DeviceToken::first();
        $this->checkReceivedAt($token->device_token);
        $this->assertCount(1, $deviceTokensFromDB = DeviceToken::all());
        $this->assertEquals(1, $deviceTokensFromDB[0]->user_id);
        $this->assertNull($deviceTokensFromDB[0]->error_message);
        $numberNotified = QMDeviceToken::getNumberNotifiedInLastDay();
        $this->assertEquals(1, $numberNotified);
    }
    /**
     * @return QMTrackingReminderNotification[]
     */
    public function getTrackingReminderNotificationsFromApi(): array{
		$this->setAuthenticatedUser(1);
        $response = $this->slimGet('/api/v1/trackingReminderNotifications/past', []);
        $body = json_decode($response->getBody(), false);
        $trackingReminderNotificationsFromApi = $body->data;
        $this->assertIsArray($trackingReminderNotificationsFromApi);
        return $trackingReminderNotificationsFromApi;
    }
    public function generateOverallMoodAndBodyTempReminderNotifications() {
        $reminders[] = [
            'variableId'        => OverallMoodCommonVariable::ID,
            'reminderFrequency' => 60,
            'defaultValue'      => 2
        ];
        $reminders[] = [
            'variableId'        => CoreBodyTemperatureCommonVariable::ID,
            'reminderFrequency' => 60,
            'defaultValue'      => 98
        ];
        $u = $this->getOrSetAuthenticatedUser(1);
        $l = $u->l();
        $l->latest_reminder_time = "23:59:59";
        $l->earliest_reminder_time = "00:00:00";
        $l->save();
        $response = $this->postAndGetDecodedBody('api/v3/trackingReminders',  $reminders);
        /** @var array $trackingReminderNotifications */
        $fromDB = QMTrackingReminderNotification::readonly()->getArray();
        $this->assertGreaterThan(1, count($fromDB));
        foreach ($fromDB as $n) {$this->assertNotNull($n->notify_at);}
        $fromApi = $this->getTrackingReminderNotificationsFromApi();
        $number = count($fromApi);
        $this->assertEquals(2, $number,
            DBUnitTestCase::getErrorMessageFromResponse($response).
            ". We should have gotten 2 notifications from API");
        foreach ($fromApi as $n) {
            $this->assertNotNull($n->trackingReminderNotificationTimeLocal);
        }
        $u = $this->setAuthenticatedUser(1); // Need to setUser again because I updated earliest latest in
	    // generateOverallMoodAndBodyTempReminderNotifications
        $notifications = $u->getPastTrackingRemindersNotifications();
        $this->assertGreaterThan(0, count($notifications));
        $notifications = QMTrackingReminderNotification::getWhereDue();
        $this->assertGreaterThan(0, count($notifications),
            "No TrackingReminderNotification::getWhereDue
            ".TrackingReminderNotification::generateDataLabIndexUrl());
    }
    /**
     * @param bool $combineNotifications
     */
    public function setCombineNotifications(bool $combineNotifications){
		$this->setAuthenticatedUser(1);
        $this->postAndGetDecodedBody('api/v3/userSettings', [
            'latestReminderTime'       => '23:59:00',
            'earliestReminderTime'     => '00:01:00',
            'combineNotifications'     => $combineNotifications,
            'pushNotificationsEnabled' => true,
            'timeZoneOffset'           => 0,
            'clientId'                 => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $user = QMUser::findInDB(1);
        $this->assertEquals($combineNotifications, $user->combineNotifications,
            'Failed to set combineNotifications to ' . $combineNotifications);
    }
    /**
     * @param $deviceTokenRequestBody
     */
    public function deleteDeviceToken($deviceTokenRequestBody){
        $this->postApiV3('deviceTokens/delete', $deviceTokenRequestBody);
        $tokens = Writable::getBuilderByTable('device_tokens')->getArray();
        $this->assertCount(0, $tokens);
    }
    /**
     * @param string $platform
     * @throws \App\Exceptions\NoTestDeviceTokenException
     */
    public function postTestToken(string $platform): void{
        DeviceToken::deleteAll();
        $this->setAuthenticatedUser(1);
        $str = QMDeviceToken::getTestTokenString($platform);
        if(!$str){throw new LogicException("Could not get $platform test device token from Mongo!");}
        $body = [
            'deviceToken' => $str,
            'platform'    => $platform,
            'clientId'    => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ];
        self::assertCount(0, $fromDB = DeviceToken::all());
        $response = $this->postAndGetDecodedBody('api/v3/deviceTokens', $body);
        self::assertCount(1, $fromDB = DeviceToken::all());
        self::assertEquals(1, $fromDB[0]->user_id);
        self::assertNull($fromDB[0]->error_message);
        self::assertEquals($body['deviceToken'], $fromDB[0]->device_token);
        self::assertEquals($body['clientId'], $fromDB[0]->client_id);
    }
}
