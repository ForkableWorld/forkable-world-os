<?php
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserPasswordProperty;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyWeightCommonVariable;
use LogicException;

class UserSettingsTest extends \Tests\SlimTests\SlimTestCase
{
    public function testCreateUser(){
        TestDB::loadUserTables();
        $this->setAuthenticatedUser(null);
        $plainTextPassword = 'qwerty';
        $response = $this->postAndGetDecodedBody('/api/v1/userSettings', [
            'log' => 'new-testuser',
            'email' => 'testuser@test.com',
            'pwdConfirm' => $plainTextPassword,
            'pwd' => $plainTextPassword,
            'register' => true,
            'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        /** @var QMUser $user */
        $user = $response->user;
        $this->assertEquals('testuser@test.com', $user->email);
        $this->assertNotNull($user->accessToken);
        $qmUser = QMUser::findByEmail('testuser@test.com');
        $enc = $qmUser->getEncryptedPasswordHash();
        $match = UserPasswordProperty::check($plainTextPassword, $enc);
        $this->assertTrue($match);
    }
    public function testPostLatestAndEarliestReminderTimes(){
        $db = Writable::db();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $userSettingRequestBody = [
            'latestReminderTime' => '02:00:58',
            'earliestReminderTime' => '01:00:59',
        ];
        $apiUrl = '/api/v1/userSettings';
        $postData = json_encode($userSettingRequestBody);
        $response = $this->postAndGetDecodedBody($apiUrl, $postData);
        // Make sure it got into the DB
        $userSettingFromDB = $db->table('wp_users')->first();
        $this->assertEquals('02:00:00', $userSettingFromDB->latest_reminder_time);
        $this->assertEquals('01:00:00', $userSettingFromDB->earliest_reminder_time);
        $this->assertQueryCountLessThan(9);
    }
    public function testPostEarliestReminderTime()
    {
        $db = Writable::db();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $userSettingRequestBody = [
            'earliestReminderTime' => '02:00:00',
        ];
        $apiUrl = '/api/v1/userSettings';
        $postData = json_encode($userSettingRequestBody);
        $response = $this->postAndGetDecodedBody($apiUrl, $postData);
        // Make sure it got into the DB
        $userSettingFromDB = $db->table('wp_users')->first();
        $this->assertEquals('02:00:00', $userSettingFromDB->earliest_reminder_time);
    }
    public function testPostEarliestReminderTimeWithLeadingZeros(){
        $this->setAuthenticatedUser(1);
        $this->postApiV3('userSettings', ['earliestReminderTime' => '00:01:00',]);
        $userSettingFromDB = QMUser::readonly()->first(); // Make sure it got into the DB
        $this->assertEquals('00:01:00', $userSettingFromDB->earliest_reminder_time);
        $user = $this->getApiV3('user',
	        ['client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        $this->assertEquals('00:01:00', $user->earliestReminderTime);
    }
    public function testClientId(){
        $this->setAuthenticatedUser($userId = 1);
        $user = $this->getApiV3('user',
	        ['client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT]);
        $this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $user->clientId);
    }
    public function testPostLatestReminderTime(){
        $db = Writable::db();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $postData = json_encode(['latestReminderTime' => '19:00:00']);
        $response = $this->postApiV3('userSettings', $postData);
        // Make sure it got into the DB
        $userSettingFromDB = $db->table('wp_users')->first();
        $this->assertEquals('19:00:00', $userSettingFromDB->latest_reminder_time);
        $response = $this->slimGet('/api/v1/user', []);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals('19:00:00', $user->latestReminderTime);
    }
    public function testDisableAndEnablePushNotifications(){
        $this->changeAndCheckBooleanUserSetting('pushNotificationsEnabled');
    }
    public function testDisableAndEnableSmsNotifications()
    {
        $this->changeAndCheckBooleanUserSetting('smsNotificationsEnabled');
    }
    public function testDisableReminderEmailsWithEmailAddress(){
        $response = $this->postAndGetDecodedBody('api/v1/userSettings',
            ['sendPredictorEmails' => true, 'userEmail' => 'test@quantimo.do']);
        $user = $response->user;
        $this->assertNotTrue(isset($user->accessToken));
        $this->assertTrue($user->sendPredictorEmails);
        $response = $this->slimGet('/api/v1/notificationPreferences', [
	        'userEmail' => 'test@quantimo.do',
	        'appVersion' => '2.1.1.0',
	        'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT
        ]);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(true, $user->sendPredictorEmails);
        $response = $this->postApiV3('userSettings', ['sendPredictorEmails' => false, 'userEmail' => 'test@quantimo.do']);
        $response = json_decode($response->getBody(), false);
        $user = $response->user;
        $this->assertNotTrue(isset($user->accessToken));
        $this->assertFalse($user->sendPredictorEmails);
    }
    public function testSetTimeZoneOffset(){
        $enabled = false;
        if(!$enabled){
            $this->skipTest("testSetTimeZoneOffset is fails randomly.  Just use string timezone");
            return;
        }
        $value = random_int(0, 1000);
        $settingName = 'timeZoneOffset';
        $this->changeAndCheckUserSetting($settingName, $value);
    }
    /**
     * @param string $settingName
     * @param $value
     * @return QMUser|object
     */
    public function changeAndCheckUserSetting(string $settingName, $value){
        $this->setAuthenticatedUser($userId = 1);
        $this->postAndGetDecodedBody('api/v3/userSettings', [$settingName => $value]);
        $userSettingFromDB = QMUser::readonly()->first();
        $snake = QMStr::snakize($settingName);
        $camel = QMStr::camelize($settingName);
        $expected = $value;
        if($value === 1){$expected = true;}
        if($value === 0){$expected = false;}
        $this->assertEquals($expected, $userSettingFromDB->$snake);
        $user = $this->getUserRequestWithClientParams();
        $this->assertEquals($expected, $user->$camel);
        return $user;
    }
    public function testSetPhoneNumber(){
        $this->skipTest("I'm sick of getting text messages all the time");
        $this->setAuthenticatedUser($userId = 1);
        $userSettingRequestBody = ['phoneNumber' => "+16183910002"];
        $user = $this->changeAndCheckUserSetting('phoneNumber', $userSettingRequestBody['phoneNumber']);
        $this->assertNotNull($user->phoneVerificationCode);
        $phoneVerificationCodeRequestBody = ['phoneVerificationCode' => $user->phoneVerificationCode];
        $response = $this->postApiV3('userSettings', $phoneVerificationCodeRequestBody);
        $user = $this->getUserRequestWithClientParams();
        $this->assertEquals($userSettingRequestBody['phoneNumber'], $user->phoneNumber);
        $this->assertNull($user->phoneVerificationCode);
    }
    public function testSetTrackLocation(){
        $this->changeAndCheckBooleanUserSetting('trackLocation');
    }
    /**
     * @param $settingName
     */
    public function changeAndCheckBooleanUserSetting($settingName){
        $user = $this->getUserRequestWithClientParams();
        $initialValue = $user->$settingName;
        $value = !$initialValue;
        $this->changeAndCheckUserSetting($settingName, $value);
        $value = !$value;
        $this->changeAndCheckUserSetting($settingName, $value);
        $value = $value ? 0 : 1;
        $this->changeAndCheckUserSetting($settingName, $value);
        $value = $value ? 0 : 1;
        $this->changeAndCheckUserSetting($settingName, $value);
    }
    public function testSetUserPlatforms(){
        TestDB::resetUserTables();
        $userId = 1;
        $this->setAuthenticatedUser($userId);
        $response = $this->slimGet('/api/v1/user', ['platformType' => 'ios']);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(true, $user->hasIosApp);
        $this->slimGet('/api/v1/user', ['platformType' => 'android']);
        $response = $this->slimGet('/api/v1/user', ['platformType' => 'android']);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(true, $user->hasAndroidApp);
        $response = $this->slimGet('/api/v1/user', ['platformType' => 'chrome']);
        $user = json_decode($response->getBody(), false);
        $this->assertEquals(true, $user->hasChromeExtension);
    }
    public function testSetCombineNotifications(){
        $this->changeAndCheckBooleanUserSetting('combineNotifications');
    }
    public function testSetTrackLocationWithInteger(){
        $this->changeAndCheckBooleanUserSetting('trackLocation');
    }
    public function testGoogleIdTokenEndpoint() {
        $googleAccessToken = "1234";
        $userDataFromGoogle = '{"email":"test@quantimo.do", "accessToken":"' . $googleAccessToken . '",
            "idToken":"eyJhbGciOiJSUzI1NiIsImtpZCI6IjAxMjg1OGI1YTZiNDQ3YmY4MDdjNTJkOGJjZGQyOGMwODJmZjc4MjYifQ.eyJpc3MiOiJodHRwczovL2FjY291bnRzLmdvb2dsZS5jb20iLCJpYXQiOjE0ODM4MTM4MTcsImV4cCI6MTQ4MzgxNzQxNywiYXVkIjoiMTA1MjY0ODg1NTE5NC5hcHBzLmdvb2dsZXVzZXJjb250ZW50LmNvbSIsInN1YiI6IjExODQ0NDY5MzE4NDgyOTU1NTM2MiIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJhenAiOiIxMDUyNjQ4ODU1MTk0LWVuMzg1amxua25iMzhtYThvbTI5NnBuZWozaTR0amFkLmFwcHMuZ29vZ2xldXNlcmNvbnRlbnQuY29tIiwiaGQiOiJ0aGlua2J5bnVtYmVycy5vcmciLCJlbWFpbCI6Im1AdGhpbmtieW51bWJlcnMub3JnIiwibmFtZSI6Ik1pa2UgU2lubiIsInBpY3R1cmUiOiJodHRwczovL2xoNi5nb29nbGV1c2VyY29udGVudC5jb20vLUJIcjRoeVVXcVpVL0FBQUFBQUFBQUFJL0FBQUFBQUFFNkw0LzIxRHZnVC1UNVZNL3M5Ni1jL3Bob3RvLmpwZyIsImdpdmVuX25hbWUiOiJNaWtlIiwiZmFtaWx5X25hbWUiOiJTaW5uIiwibG9jYWxlIjoiZW4ifQ.YiHQH3-mBCaFxi9BgXe52S2scgVbMQ_-bMWVYY3d8MJZegQI5rl0IvUr0RmYT1k5bIda1sN0qeRyGkbzBHc7f3uctgpXtzjd02flgl4fNHmRgJkRgK_ttTO6Upx9bRR0ItghS_okM2gjgDWwO5wceTNF1f46vEVFH72GAUHVR9Csh4qs9yjqK66vxOEKN4UqIE9JRSn58dgIW8s6CNlBHiLUChUy1nfd2U0zGQ_tmu90y_76vVw5AYDrHDDPQBJ5Z4K_arzjnVzjhKeHpgOaywS4S1ifrylGkpGt5L2iB9sfdA8tNR5iJcEvEuhzGohnd7HvIWyJJ2-BRHukNYQX4Q","serverAuthCode":"4/3xjhGuxUYJVTVPox8Knyp0xJSzMFteFMvNxdwO5H8jQ",
            "userId":"118444693184829555362","displayName":"Mike Sinn","familyName":"Sinn","givenName":"Mike","imageUrl":"https://lh6.googleusercontent.com/-BHr4hyUWqZU/AAAAAAAAAAI/AAAAAAAE6L4/21DvgT-T5VM/s96-c/photo.jpg"}';
        $userDataFromGoogle = json_decode($userDataFromGoogle, false);
        if(!$userDataFromGoogle){throw new LogicException("Could not decode!");}
        $newUserResponse = $this->postAndGetDecodedBody('/api/v1/googleIdToken', $userDataFromGoogle);
        $new = $newUserResponse->user;
        $this->assertEquals($userDataFromGoogle->email, $new->email);
        $this->assertNotEquals($new->accessToken, $googleAccessToken);
        $this->assertNotNull($new->accessToken);
        $existingUserResponse = $this->postAndGetDecodedBody('/api/v1/googleIdToken', $userDataFromGoogle);
        $existing = $existingUserResponse->user;
        $this->assertEquals($new->id, $existing->id);
        $this->assertEquals($existing->accessToken, $new->accessToken);
    }
    public function testPrimaryOutcomeVariableId() {
        TestDB::resetUserTables();
        User::whereId(1)->update([User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => null]);
        $user = $this->getUserRequestWithClientParams();
        $initialValue = $user->primaryOutcomeVariableId;
        $this->assertNull($initialValue);
        $this->changeAndCheckUserSetting(User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID, BodyWeightCommonVariable::ID);
        $u = $this->getUserRequest(1);
        $this->assertEquals(BodyWeightCommonVariable::NAME, $u->primaryOutcomeVariableName);
        $this->changeAndCheckUserSetting(User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID, OverallMoodCommonVariable::ID);
        $u = $this->getUserRequest(1);
        $this->assertEquals(OverallMoodCommonVariable::NAME, $u->primaryOutcomeVariableName); // hi
    }
}
