<?php /** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpUndefinedFieldInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Exceptions\InvalidTimestampException;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Models\UserVariableRelationship;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\TrackingReminder\TrackingReminderReminderStartTimeProperty;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Storage\DB\TestDB;
use App\Storage\DB\Writable;
use App\Types\TimeHelper;
use App\Units\CountUnit;
use App\Units\OneToFiveRatingUnit;
use App\Utils\QMTimeZone;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use Slim\Http\Response;
use Tests\SlimTests\SlimTestCase;
/**
 * Class TrackingRemindersTest
 * @package Tests\Api\Reminders
 */
class TrackingRemindersTest extends \Tests\SlimTests\SlimTestCase {
	protected const DISABLED_UNTIL = "2021-03-16";
	protected $fixtureFiles = [
		'user_variables' => 'common/user_variables_simple.xml',
	];
	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sort_objects_by_id($a, $b){
		/** @noinspection TypeUnsafeComparisonInspection */
		if($a->id == $b->id){
			return 0;
		}
		return ($a->id < $b->id) ? -1 : 1;
	}
	public function deleteNotificationsAndReminders(){
		TrackingReminder::deleteAll();
		TrackingReminderNotification::deleteAll();
	}
	protected function setUp(): void{
		parent::setUp(); // parent::setUp come first
		TestDB::resetUserTables();
		$this->deleteMeasurementsAndReminders();
		UserVariableRelationship::deleteAll();
		TestQueryLogFile::flushTestQueryLog();
	}
	public function testChangeValenceToNeutral(){
		$this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		$valence = 'neutral';
		$this->testPostTrackingReminder(['valence' => $valence]);
		$reminders = QMTrackingReminder::getTrackingReminders($this->getOrSetAuthenticatedUser(1));
		foreach($reminders as $reminder){
			$this->assertEquals($valence, $reminder->valence);
			$notifications = $reminder->getOrCreateNotifications();
			foreach($notifications as $n){
				$buttons = $n->getNotificationActionButtons();
				foreach($buttons as $button){
					$this->assertContains("numeric", $button->image);
				}
			}
		}
	}
	public function testDeleteTrackingReminder(){
		$trackingReminderFromDB = $this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		$response =
			$this->postApiV3('trackingReminders/delete', ['id' => $trackingReminderFromDB->id], 204);
		$this->checkTrackingReminderDeletionResponse($response);
		$this->makeSureWeDoNotHaveRemindersInDB();
	}
	/**
	 * @param $response
	 */
	public function checkTrackingReminderDeletionResponse($response){
		$this->assertResponseBodyContains('"data":{"trackingReminderNotifications":[]', $response);
		$this->assertResponseBodyContains('Tracking reminder deleted successfully', $response);
		$this->checkDeletionResponse($response);
	}
	private function makeSureWeDoNotHaveRemindersInDB(): void{
		$trackingReminders = $this->getAndDecodeBody('api/v1/trackingReminders', []);
		$this->assertCount(0, $trackingReminders->data);
		$trackingReminderNotificationsFromDB = QMTrackingReminderNotification::readonly()->getArray();
		$this->assertCount(0, $trackingReminderNotificationsFromDB);
	}
	public function testDeleteTrackingReminderWithDeleteMethod(){
		$qb = Writable::db();
		$trackingReminderFromDB = $this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		$response = $this->slimDelete('/api/v3/trackingReminders/delete', ['id' => $trackingReminderFromDB->id]);
		$this->checkTrackingReminderDeletionResponse($response);
		$trackingReminders = $this->getAndDecodeBody('api/v1/trackingReminders', []);
		$this->assertCount(0, $trackingReminders->data);
		$trackingReminderNotificationsFromDB = $qb->table('tracking_reminder_notifications')->getArray();
		$this->assertCount(0, $trackingReminderNotificationsFromDB);
	}
	public function testGetTrackingReminderByWrongCategory(){
		$this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		// Make sure category filter works
		$response = $this->slimGet('/api/v1/trackingReminders', ['variableCategoryName' => 'Sleep']);
		$trackingReminders = json_decode($response->getBody(), false);
		$this->assertIsArray($trackingReminders->data);
		$this->assertCount(0, $trackingReminders->data);
	}
	/**
	 * @return mixed
	 */
	public function testGetTrackingReminderFrequency(){
		$this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		// See if we can get it from the API
		$response = $this->slimGet('/api/v1/trackingReminders', ['reminderFrequency' => '86400']);
		/** @var QMTrackingReminder[] $trackingReminders */
		$trackingReminders = json_decode($response->getBody(), false);
		$this->assertIsArray($trackingReminders->data);
		$this->assertCount(1, $trackingReminders->data);
		foreach($trackingReminders->data as $trackingReminder){
			/** @var QMTrackingReminder $trackingReminder */
			$this->checkNewMoodReminder($trackingReminder);
			$this->assertGreaterThan(time(), $trackingReminder->nextReminderTimeEpochSeconds);
			$this->assertNotNull($trackingReminder->trackingReminderId);
			//$this->assertGreaterThan(0, $trackingReminder->numberOfPendingNotifications);
		}
		return $trackingReminders->data;
	}
	/**
	 * @return QMTrackingReminder[]
	 */
	public function testGetTrackingRemindersByRightCategory(){
		$this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		// See if we can get it from the API
		$trackingReminders = $this->getAndCheckTrackingReminders(['variableCategoryName' => 'Emotions']);
		$this->assertCount(1, $trackingReminders);
		foreach($trackingReminders as $trackingReminder){
			$this->checkNewMoodReminder($trackingReminder);
			$this->assertCount(1, $trackingReminder->localDailyReminderNotificationTimes);
			$this->assertCount(1, $trackingReminder->localDailyReminderNotificationTimesForAllReminders);
			//$this->assertGreaterThan(0, $trackingReminder->numberOfPendingNotifications);
		}
		return $trackingReminders;
	}
	/**
	 * @param QMTrackingReminder|\stdClass $tr
	 */
	private function checkNewMoodReminder($tr): void{
		$this->assertEquals(1398, $tr->variableId);
		$this->assertEquals(2, $tr->defaultValue);
		$this->assertEquals(86400, $tr->reminderFrequency);
		$this->assertEquals('/5', $tr->unitAbbreviatedName);
		$this->assertEquals('Overall Mood', $tr->variableName);
		$this->assertEquals(true, $tr->outcome);
		$this->assertEquals('Emotions', $tr->variableCategoryName);
		$this->assertEquals('MEAN', $tr->combinationOperation);
		$this->assertGreaterThan(time() - 86400, $tr->reminderStartEpochSeconds);
	}
	public function testPostAndGetTrackingReminderWithZeroFrequency(){
		$this->setAuthenticatedUser(1);
		$modifiedTrackingReminder = [
			'id' => null,
			'clientId' => null,
			'userId' => null,
			'variableId' => 1398,
			'defaultValue' => 3,
			'reminderStartTime' => null,
			'reminderEndTime' => null,
			'reminderSound' => null,
			'reminderFrequency' => 0,
			'notificationBar' => null,
			'latestTrackingReminderNotificationReminderTime' => null,
			'lastTracked' => null,
			'startTrackingDate' => null,
			'stopTrackingDate' => null,
			'updatedAt' => null,
			'variableName' => null,
			'unitAbbreviatedName' => '/5',
		];
		$this->postAndCheckTrackingRemindersResponse($modifiedTrackingReminder, '/5', false);
		$response = $this->slimGet('/api/v1/trackingReminders', []);
		$updatedTrackingReminders = json_decode($response->getBody(), false);
		$updatedTrackingReminders = $updatedTrackingReminders->data;
		$this->assertIsArray($updatedTrackingReminders);
		$this->assertCount(1, $updatedTrackingReminders);
		$this->assertEquals(1398, $updatedTrackingReminders[0]->variableId);
		$this->assertEquals(3, $updatedTrackingReminders[0]->defaultValue);
		$this->assertEquals(0, $updatedTrackingReminders[0]->reminderFrequency);
		$this->assertEquals('/5', $updatedTrackingReminders[0]->unitAbbreviatedName);
		$this->assertEquals('/5', $updatedTrackingReminders[0]->unitAbbreviatedName);
		$this->assertEquals('Overall Mood', $updatedTrackingReminders[0]->variableName);
		$this->assertEquals('Favorite', $updatedTrackingReminders[0]->valueAndFrequencyTextDescription);
		$this->assertEquals('Never', $updatedTrackingReminders[0]->frequencyTextDescription);
	}
	public function testPostIncompatibleUnit(){
		$newUserVariable = QMUserVariable::getCreateOrUpdateUserVariableByVariableId(1, 1398, [
				'id' => null,
				'clientId' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
				'userId' => null,
				'variableId' => 1398,
				'defaultValue' => 100,
				'reminderStartTime' => null,
				'reminderEndTime' => null,
				'reminderSound' => null,
				'reminderFrequency' => 86400,
				'popUp' => null,
				'sms' => null,
				'email' => null,
				'notificationBar' => null,
				'latestTrackingReminderNotificationReminderTime' => null,
				'lastTracked' => null,
				'startTrackingDate' => null,
				'stopTrackingDate' => null,
				'updatedAt' => null,
				'variableName' => null,
				'variableCategoryName' => 'Foods',
				'unitAbbreviatedName' => 'mL',
				'combinationOperation' => 'SUM',
			]);
		$this->assertNotNull($newUserVariable);
	}
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 * @return mixed|static
	 */
	public function testPostTrackingReminderWithFutureStartDate(){
		$this->setAuthenticatedUser(1);
		$startDate = TimeHelper::YYYYmmddd(time() + 360 * 86400);
		$arr = [
			'variableId' => 1398,
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'instructions' => 'I am an instruction!',
			"startTrackingDate" => $startDate,
		];
		$this->postAndCheckTrackingRemindersResponse($arr, '/5', false);
		$body = $this->getAndDecodeBody('api/v1/trackingReminders', []);
		$trackingRemindersFromApi = $body->data;
		foreach($trackingRemindersFromApi as $r){
			$this->assertEquals('Daily (starts ' . $startDate . ')', $r->frequencyTextDescription);
			$this->assertEquals($arr['variableId'], $r->variableId);
			$this->assertEquals($arr['reminderFrequency'], $r->reminderFrequency);
			$this->assertEquals($arr['defaultValue'], $r->defaultValue);
			$this->assertEquals($arr['instructions'], $r->instructions);
			$this->assertEquals($arr['startTrackingDate'], $r->startTrackingDate);
		}
		$body = $this->getAndDecodeBody('api/v1/trackingReminderNotifications', []);
		$notifications = $body->data;
		$this->assertCount(0, $notifications);
	}
	public function testPostTrackingReminderWithHourlyFrequency(){
		$abbreviation = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation(60);
		$this->assertEquals("Atlantic/Madeira", $abbreviation);
		$db = Writable::db();
		$this->setAuthenticatedUser(1);
		$reminderStartTime = '05:00:00';
		$trackingReminders = [
			'variableId' => 1398,
			'reminderFrequency' => 3600,
			'defaultValue' => 2,
			'reminderStartTime' => $reminderStartTime,
			'instructions' => 'I am an instruction!',
			'timeZone' => QMTimeZone::EUROPE_HELSINKI,
		];
		$postData = json_encode($trackingReminders);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$response = $this->postAndCheckTrackingRemindersResponse($postData);
		// Make sure it got into the DB
		$trackingReminderFromDB = $db->table('tracking_reminders')->first();
		$this->assertEquals(1398, $trackingReminderFromDB->variable_id);
		$this->assertEquals(2, $trackingReminderFromDB->default_value);
		$this->assertEquals(3600, $trackingReminderFromDB->reminder_frequency);
		$this->assertEquals('I am an instruction!', $trackingReminderFromDB->instructions);
		$v = QMUserVariable::getByNameOrId($trackingReminderFromDB->user_id, $trackingReminderFromDB->variable_id);
		$number = $v->getOrCalculateNumberOfTrackingReminders();
		$this->assertEquals(1, $number);
		$v->analyzeFully(__FUNCTION__);
		$response = $this->slimGet('/api/v1/trackingReminders', [
			'limit' => 200,
			'appName' => 'MoodiModo',
			'appVersion' => '2.1.1.0',
			'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
		]);
		/** @var QMTrackingReminder[] $trackingReminders */
		$trackingReminders = json_decode($response->getBody(), false);
		$this->assertIsArray($trackingReminders->data);
		$this->assertCount(1, $trackingReminders->data);
		foreach($trackingReminders->data as $trackingReminder){
			/** @var QMTrackingReminder $trackingReminder */
			$this->assertEquals(1398, $trackingReminder->variableId);
			$this->assertEquals(2, $trackingReminder->defaultValue);
			$this->assertEquals(3600, $trackingReminder->reminderFrequency);
			$this->assertEquals('/5', $trackingReminder->unitAbbreviatedName);
			$this->assertEquals('Overall Mood', $trackingReminder->variableName);
			$this->assertEquals('Emotions', $trackingReminder->variableCategoryName);
			$this->assertEquals('MEAN', $trackingReminder->combinationOperation);
			$this->assertGreaterThan(time() - 86400, $trackingReminder->reminderStartEpochSeconds);
			$this->assertGreaterThan(22, $trackingReminder->localDailyReminderNotificationTimes);
			$this->assertGreaterThan(22, $trackingReminder->localDailyReminderNotificationTimesForAllReminders);
			//$this->assertGreaterThan(0, $trackingReminder->numberOfPendingNotifications);
		}
	}
	/**
	 * @param $response
	 */
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 * @return mixed|static
	 * @throws InvalidTimestampException
	 */
	public function testPostTrackingReminderWithNegativeId(){
		$this->setAuthenticatedUser(1);
		$variableId = 1398;
		QMUserVariable::writable()->where(UserVariable::FIELD_VARIABLE_ID, $variableId)
			->update([UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE => null]);
		$trackingReminders = [
			'unitAbbreviatedName' => '/5',
			'clientId' => null,
			'email' => null,
			'firstDailyReminderTime' => '07:00:00',
			'id' => -1,
			'latestTrackingReminderNotificationReminderTime' => null,
			'lastTracked' => null,
			'notificationBar' => null,
			'popUp' => null,
			'reminderEndTime' => null,
			'reminderFrequency' => 86400,
			'reminderSound' => null,
			'reminderStartTime' => null,
			'secondDailyReminderTime' => null,
			'sms' => null,
			'startTrackingDate' => null,
			'stopTrackingDate' => null,
			'thirdDailyReminderTime' => null,
			'updatedAt' => null,
			'userId' => null,
			'variableId' => $variableId,
			'variableName' => null,
		];
		$postData = json_encode($trackingReminders);
		$this->postAndCheckTrackingRemindersResponse($postData, null);
		// Make sure it got into the DB
		$trackingReminderFromDB = TrackingReminder::first();
		$this->assertEquals($variableId, $trackingReminderFromDB->variable_id);
		$this->assertEquals(86400, $trackingReminderFromDB->reminder_frequency);
		return $trackingReminderFromDB;
	}
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 * @return mixed|static
	 */
	public function testPostTrackingReminderWithNewUnitInSameCategory(){
		$this->setAuthenticatedUser(1);
		$response = $this->postAndCheckTrackingRemindersResponse([
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'variableCategoryName' => 'Emotions',
			'variableName' => 'Overall Mood',
			'unitAbbreviatedName' => '/5',
		]);
		$this->assertCount(1, $response->data->trackingReminders);
		$reminders = $this->postAndGetTrackingReminders([
			'reminderFrequency' => 100,
			'defaultValue' => 2,
			'variableCategoryName' => 'Emotions',
			'variableName' => 'Overall Mood',
			'unitAbbreviatedName' => '/10',
		]);
		$this->assertCount(2, $reminders);
		usort($reminders, ["self", "sort_objects_by_id"]);
		$this->assertEquals('/10', $reminders[1]->unitAbbreviatedName);
		$this->assertEquals('/10', $reminders[0]->unitAbbreviatedName,
			TrackingReminder::generateDataLabShowButton($reminders[0]->id)->getMarkdownLink() .
			UserVariable::generateDataLabShowButton($reminders[0]->userVariableId)->getMarkdownLink());
		$this->assertEquals('Overall Mood', $reminders[1]->variableName);
		$this->assertEquals('Overall Mood', $reminders[0]->variableName);
		$reminders[0]->unitAbbreviatedName = $reminders[1]->unitAbbreviatedName = '%';
		$reminders[0]->abbreviatedUnitName = $reminders[1]->abbreviatedUnitName = $reminders[0]->userUnitId =
		$reminders[1]->userUnitId = $reminders[0]->unitName = $reminders[1]->unitName =
		$reminders[0]->unit = $reminders[1]->unit = $reminders[0]->unitId = $reminders[1]->unitId = null;
		$response = $this->postAndCheckTrackingRemindersResponse($reminders);
		$this->assertCount(2, $response->data->trackingReminders);
		$this->assertCount(1, $response->data->userVariables);
		$this->assertEquals('%', $response->data->userVariables[0]->unitAbbreviatedName);
		$responseBody = $this->getAndDecodeBody('api/v1/trackingReminders', []);
		$reminders = $responseBody->data;
		// Overwrites both previous ones because they have the same variable, time, and frequency
		$this->assertCount(2, $reminders);
		$this->assertEquals('%', $reminders[0]->unitAbbreviatedName);
	}
	/**
	 * @param QMTrackingReminder[] $trackingReminders
	 * @return mixed
	 */
	private function postAndGetTrackingReminders(array $trackingReminders){
		$this->postTrackingReminder($trackingReminders);
		$body = $this->getAndDecodeBody('api/v1//trackingReminders', []);
		if(is_array($body)){
			if(isset($body['data'])){
				$trackingReminders =  $body['data'];
			} else {
				$trackingReminders = $body;
			}
		} else {
			$trackingReminders = $body->data;
		}
		$this->assertGreaterThan(0, count($trackingReminders));
		return $trackingReminders;
	}
	/**
	 * @param $trackingReminders
	 * @return Response|\App\Slim\Model\Reminders\TrackingRemindersResponse
	 */
	public function postTrackingReminder($trackingReminders){
		$postData = json_encode($trackingReminders);
		$response = $this->postAndCheckTrackingRemindersResponse($postData);
		sleep(2); // To verify we're using new created_at even when they happen too fast
		$this->assertCount(1, $response->data->userVariables);
		return $response;
	}
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 */
	public function testPostTrackingReminderWithNewVariable(){
		
		$this->setAuthenticatedUser(1);
		$trackingReminders = $this->postAndGetTrackingReminders([
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'variableCategoryId' => 1,
			'variableName' => 'TestVariableForReminders',
			'unitId' => OneToFiveRatingUnit::ID,
		]);
		foreach($trackingReminders as $r){
			$this->assertEquals(2, $r->defaultValue);
			$this->assertEquals(OneToFiveRatingUnit::ID, $r->unitId);
			$this->assertEquals(1, $r->variableCategoryId);
			$this->assertEquals('TestVariableForReminders', $r->variableName);
			$this->assertEquals('Daily', $r->frequencyTextDescription);
		}
		$this->setAuthenticatedUser(1);
		$trackingReminders = $this->postAndGetTrackingReminders([
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'variableCategoryName' => 'Treatments',
			'variableName' => 'TestVariableForReminders',
			'unitAbbreviatedName' => 'mg',
		]);
		foreach($trackingReminders as $r){
			$this->assertEquals(2, $r->defaultValue);
			$this->assertEquals('mg', $r->unitAbbreviatedName);
			$this->assertEquals('Treatments', $r->variableCategoryName);
			$this->assertEquals('TestVariableForReminders (Weight)', $r->variableName);
			$this->assertEquals('Daily', $r->frequencyTextDescription);
		}
	}
	public function testPostTrackingReminderWithNewVariableAndNullId(){
		$this->setAuthenticatedUser(1);
		
		$name = "aaa test reminder variable";
		$this->assertNull(Variable::findByNameIdOrSynonym($name));
		$trackingReminders = $this->postAndGetTrackingReminders([
			'variableName' => $name,
			'combinationOperation' => null,
			'defaultValue' => 3,
			'variableCategoryName' => "Symptoms",
			'unitAbbreviatedName' => OneToFiveRatingUnit::ABBREVIATED_NAME,
			'reminderFrequency' => 86400,
			'reminderStartTime' => "14:51:16",
		]);
		$this->assertEquals($name, Variable::findByNameIdOrSynonym($name)->name);
		/** @var QMTrackingReminder $r */
		foreach($trackingReminders as $r){
			$this->assertEquals(3, $r->defaultValue);
			$this->assertEquals(OneToFiveRatingUnit::ID, $r->unitId);
			$this->assertEquals(10, $r->variableCategoryId);
			$this->assertEquals('aaa test reminder variable', $r->variableName);
			$this->assertEquals('Aaa Test Reminder Variable', $r->displayName);
			$this->assertEquals('14:51:16', $r->firstDailyReminderTime);
			$this->assertEquals('Daily', $r->frequencyTextDescription);
		}
	}
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 */
	public function testPostTwoTrackingReminders(){
		if($this->weShouldSkip()){
			return;
		}
		$this->setAuthenticatedUser(1);
		$user = $this->getOrSetAuthenticatedUser(1);
		$utc = $user->localToUtcHis(TrackingReminderReminderStartTimeProperty::DEFAULT_LOCAL_REMINDER_TIME);
		$local = $user->utcToLocalHis($utc);
		$this->assertEquals(TrackingReminderReminderStartTimeProperty::DEFAULT_LOCAL_REMINDER_TIME, $local,
			"This might fail on the day that daylight savings time changes");
		$trackingReminders = [
			'variableId' => 1398,
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'instructions' => 'I am an instruction!',
		];
		$response = $this->postAndCheckTrackingRemindersResponse($trackingReminders, '/5');
		/** @var QMUserVariable[] $variables */
		$variables = $response->data->userVariables;
		$this->assertEquals('120 measurements | Recorded 3 years ago | 1 reminders set | Higher Bupropion Sr Intake predicts moderately higher Overall Mood.  Overall Mood was 90.4% higher following above average Bupropion Sr over the previous 21 days. ', $variables[0]->subtitle);
		/** @var QMTrackingReminder $tr */
		$tr = $response->data->trackingReminders[0];
		$local = $tr->reminderStartTimeLocal;
		$this->assertEquals("20:00:00", $local);
		$trackingReminders = [
			'variableId' => 1398,
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'reminderStartTimeLocal' => $local,
			'instructions' => 'I am an instruction!',
		];
		$u = $this->getOrSetAuthenticatedUser(1);
		$utcHis = $u->localToUtcHis($local);
		$this->assertEquals("01:00:00", $utcHis);
		$response = $this->postAndCheckTrackingRemindersResponse($trackingReminders, '/5');
		$reminders = $response->data->trackingReminders;
		$this->assertCount(1, $reminders,
			"Both would have the same time and frequency so there should only be 1 saved");
		$this->assertEquals("20:00:00", $reminders[0]->reminderStartTimeLocal);
		$this->assertEquals("Rate daily at 8PM", $reminders[0]->valueAndFrequencyTextDescriptionWithTime);
		$this->assertEquals("8PM", $reminders[0]->reminderStartTimeLocalHumanFormatted);
		$body = $this->getAndDecodeBody('api/v1/trackingReminders', []);
		$reminders = $body->data;
		$this->assertCount(1, $reminders);
		$this->assertEquals("20:00:00", $reminders[0]->reminderStartTimeLocal);
		$this->assertEquals("Rate daily at 8PM", $reminders[0]->valueAndFrequencyTextDescriptionWithTime);
		$this->assertEquals("8PM", $reminders[0]->reminderStartTimeLocalHumanFormatted);
		/** @var QMTrackingReminder $reminder */
		foreach($reminders as $reminder){
			$this->assertEquals('Daily', $reminder->frequencyTextDescription);
			$this->assertEquals('I am an instruction!', $reminder->instructions);
		}
	}
	public function testUpdateTrackingReminderAndUnit(){
		
		$this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		$newUnitAbbreviatedName = CountUnit::ABBREVIATED_NAME;
		$newVariableCategoryName = 'Foods';
		sleep(1); // Needed so updated_at is later
		$reminders = $this->postAndCheckTrackingRemindersResponse([
			'clientId' => null,
			'combinationOperation' => 'SUM',
			'defaultValue' => 100,
			'email' => null,
			'id' => null,
			'lastTracked' => null,
			'latestTrackingReminderNotificationReminderTime' => null,
			'notificationBar' => null,
			'popUp' => null,
			'reminderEndTime' => null,
			'reminderFrequency' => 86400,
			'reminderSound' => null,
			'reminderStartTime' => null,
			'sms' => null,
			'startTrackingDate' => null,
			'stopTrackingDate' => null,
			'unitAbbreviatedName' => $newUnitAbbreviatedName,
			'updatedAt' => null,
			'userId' => null,
			'variableCategoryName' => $newVariableCategoryName,
			'variableId' => 1398,
			'variableName' => null,
		], $newUnitAbbreviatedName);
		$newUserVariable = UserVariable::whereUserId(1)
		                               ->where(UserVariable::FIELD_VARIABLE_ID, "<>", OverallMoodCommonVariable::ID)
		                               ->orderBy(UserVariable::UPDATED_AT, 'desc')
		                               ->first();
		$actualUnit = $newUserVariable->getUnit();
		$this->assertEquals(CountUnit::ID, $actualUnit->id,
			"Unit wasn't updated to count. " . UserVariable::getDataLabIndexUrl());
		$updatedTrackingReminders = $this->getTrackingReminders();
		$this->assertCount(1, $updatedTrackingReminders);
		$newIncompatible = $updatedTrackingReminders[0];
		$this->assertEquals('Overall Mood (Count)', $newIncompatible->variableName);
		$this->assertEquals($newVariableCategoryName, $newIncompatible->variableCategoryName);
		$this->assertEquals(100, $newIncompatible->defaultValue);
		$this->assertEquals(86400, $newIncompatible->reminderFrequency);
		$this->assertEquals($newUnitAbbreviatedName, $newIncompatible->unitAbbreviatedName);
		// TODO: Should this combination operation be SUM?
		$this->assertEquals(BaseCombinationOperationProperty::COMBINATION_SUM, $newIncompatible->combinationOperation);
	}
	/**
	 * @group api
	 * Test post trackingReminder and search for it afterwards
	 * @param array $additionalData
	 * @return QMTrackingReminder[]
	 * @throws InvalidTimestampException
	 */
	public function testPostTrackingReminder(array $additionalData = []){
		$this->setAuthenticatedUser(1);
		$timeZoneOffset = 300;
		$submittedUtcHis = '00:00:00';
		$u = $this->getOrSetAuthenticatedUser(1);
		$submittedLocalHis = $u->utcToLocalHis($submittedUtcHis);
		$postData = [
			'variableId' => OverallMoodCommonVariable::ID,
			'reminderFrequency' => 86400,
			'defaultValue' => 2,
			'reminderStartTime' => '00:00:00',
			'instructions' => 'I am an instruction!',
			'timeZoneOffset' => $timeZoneOffset,
		];
		$postData = array_merge($postData, $additionalData);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$response = $this->postAndCheckTrackingRemindersResponse($postData);
		// Make sure it got into the DB
		$trackingReminderFromDB = TrackingReminder::first();
		/** @var TrackingReminder[] $trackingReminderFromDB */
		$this->assertEquals(1398, $trackingReminderFromDB->variable_id);
		$this->assertEquals(2, $trackingReminderFromDB->default_value);
		$this->assertEquals(86400, $trackingReminderFromDB->reminder_frequency);
		$this->assertEquals('I am an instruction!', $trackingReminderFromDB->instructions);
		$postData = $this->getAndCheckTrackingReminders([
			'limit' => 200,
			'appName' => 'MoodiModo',
			'appVersion' => '2.1.1.0',
			'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
		]);
		$this->assertCount(1, $postData);
		$trackingReminder = $postData[0];
		$times = $trackingReminder->localDailyReminderNotificationTimes;
		$timesForAll = $trackingReminder->localDailyReminderNotificationTimesForAllReminders;
		$this->assertCount(1, $trackingReminder->localDailyReminderNotificationTimes);
		$this->assertEquals($submittedLocalHis, $times[0]);
		$this->assertCount(1, $timesForAll);
		$this->assertEquals($submittedLocalHis, $timesForAll[0]);
		return $trackingReminderFromDB;
	}
	public function testUpdateTrackingReminderUnitWithId(){
		$this->createTestSymptomRatingMeasurement('Overall Mood');
		$trackingReminderFromDB = $this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(38);
		$trackingReminderToPost = [
			'id' => $trackingReminderFromDB->id,
			'unitAbbreviatedName' => 'kg',
			'clientId' => null,
			'userId' => null,
			'variableId' => 1398,
			'defaultValue' => 100,
			'reminderStartTime' => null,
			'reminderEndTime' => null,
			'reminderSound' => null,
			'reminderFrequency' => 86400,
			'popUp' => null,
			'sms' => null,
			'email' => null,
			'notificationBar' => null,
			'latestTrackingReminderNotificationReminderTime' => null,
			'lastTracked' => null,
			'startTrackingDate' => null,
			'stopTrackingDate' => null,
			'updatedAt' => null,
			'variableName' => null,
			'variableCategoryName' => 'Foods',
			'combinationOperation' => 'SUM',
		];
		$this->postAndCheckTrackingRemindersResponse($trackingReminderToPost, 'kg');
		$response = $this->slimGet('/api/v1/trackingReminders', []);
		$responseBody = json_decode($response->getBody(), false);
		$this->assertIsArray($responseBody->data);
		$this->assertCount(1, $responseBody->data);
		/** @var QMTrackingReminder $tr */
		foreach($responseBody->data as $tr){
			$this->assertNotEquals(1398, $tr->variableId);
			$this->assertEquals(100, $tr->defaultValue);
			$this->assertEquals(86400, $tr->reminderFrequency);
			$this->assertEquals('kg', $tr->unitAbbreviatedName);
			$this->assertEquals('Overall Mood (Weight)', $tr->variableName);
			$this->assertEquals('Overall Mood', $tr->displayName);
			$this->assertEquals('Foods', $tr->variableCategoryName);
			$this->assertEquals('SUM', $tr->combinationOperation);
		}
	}
	public function testUpdateTrackingReminderWithId(){
		$trackingReminderFromDB = $this->testPostTrackingReminder();
		$this->assertQueryCountLessThan(24);
		$this->assertQueryCountLessThan(24);
		// Try updating the existing reminder
		$trackingRemindersToPost = [
			'clientId' => null,
			'combinationOperation' => 'SUM',
			'defaultValue' => 5,
			'email' => null,
			'id' => $trackingReminderFromDB->id,
			'lastTracked' => null,
			'latestTrackingReminderNotificationReminderTime' => null,
			'notificationBar' => null,
			'popUp' => null,
			'reminderEndTime' => null,
			'reminderFrequency' => 86400,
			'reminderSound' => null,
			'reminderStartTime' => null,
			'sms' => null,
			'startTrackingDate' => null,
			'stopTrackingDate' => null,
			'unitAbbreviatedName' => '/5',
			'updatedAt' => null,
			'userId' => null,
			'variableCategoryName' => 'Foods',
			'variableId' => 1398,
			'variableName' => null,
		];
		$this->postAndCheckTrackingRemindersResponse($trackingRemindersToPost, '/5');
		$trackingReminderFromDB = TrackingReminder::first();
		$this->assertEquals(1398, $trackingReminderFromDB->variable_id);
		$this->assertEquals(5, $trackingReminderFromDB->default_value);
		$this->assertEquals(86400, $trackingReminderFromDB->reminder_frequency);
		// Make sure it updated
		$response = $this->slimGet('/api/v1/trackingReminders', []);
		$responseBody = json_decode($response->getBody(), false);
		$this->assertIsArray($responseBody->data);
		$this->assertCount(1, $responseBody->data);
		foreach($responseBody->data as $tr){
			/** @var QMTrackingReminder $tr */
			$this->assertEquals(1398, $tr->variableId);
			$this->assertEquals(5, $tr->defaultValue);
			$this->assertEquals(86400, $tr->reminderFrequency);
			$this->assertEquals($trackingRemindersToPost['unitAbbreviatedName'], $tr->unitAbbreviatedName);
			$this->assertEquals('Overall Mood', $tr->variableName);
			$this->assertEquals($trackingRemindersToPost['variableCategoryName'], $tr->variableCategoryName,
				"\n\tUser variable:\n\t\t" . UserVariable::generateShowUrl($tr->userVariableId));
		}
	}
}
