<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\QMConnector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Models\Connection;
use App\Models\TrackingReminder;
use App\Studies\QMCohortStudy;
use App\Types\TimeHelper;
use App\Units\HoursUnit;
use App\VariableCategories\SleepVariableCategory;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\QMVariable;
use Clockwork\Support\Laravel\Tests\UsesClockwork;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class FitBitTest
 * @package Tests\Api\Connectors1
 */
class FitBitTest extends ConnectorTestCase {
	public const DISABLED_UNTIL = "2022-09-01";
	protected $connectorName = FitbitConnector::NAME;
	protected $variablesToCheck = array (
		0 => 'REM Sleep Duration',
		1 => 'Deep Sleep Duration',
		2 => 'Periods of Deep Sleep',
		3 => 'Light Sleep Duration',
		4 => 'Duration of Awakenings During Sleep',
		5 => 'Periods of Light Sleep',
		6 => 'Periods of REM Sleep',
		7 => 'Awakenings',
//		8 => 'Fat Burn Heart Rate Zone Calories Out',
//		9 => 'Fat Burn Heart Rate Zone',
		10 => 'Resting Heart Rate (Pulse)',
//		11 => 'Cardio Heart Rate Zone Calories Out',
//		12 => 'Cardio Heart Rate Zone',
		13 => 'Walk Or Run Distance',
		14 => 'Sleep Duration',
		15 => 'Body Fat',
		16 => 'Calories Burned',
		17 => 'Daily Step Count',
		18 => 'Body Weight',
		19 => 'Body Mass Index Or BMI',
		20 => 'Caloric Intake',
		21 => 'Water (Volume)',
		22 => 'Sleep Start Time',
		23 => 'Time In Bed',
		24 => 'Minutes to Fall Asleep',
		25 => 'Sleep Efficiency From Fitbit',
	);
	public function testMinimumSleepDuration(){
		$c = $this->getQMConnector();
		//$c->importSleepDuration();
		//$c->saveMeasurements();
		try {
			$yesterdayMidnight = TimeHelper::getYesterdayMidnightAt();
			$c->setCurrentUrl("test-url");
			$c->addMeasurement(SleepDurationCommonVariable::NAME, $yesterdayMidnight, 999999, HoursUnit::NAME,
				SleepVariableCategory::NAME);
			$this->fail("Should have thrown InvalidVariableValueException");
		} catch (InvalidVariableValueAttributeException $e) {
			$this->assertContains("maximum", $e->getMessage());
		}
	}
	public function testFitbit(){
		$this->weShouldSkip();
		$this->fromTime = time() - 7 * 86400;
		$this->deleteReminders();
		$this->connectImportCheckDisconnect();
		$this->createAndJoinFitbitStudy();
	}
	/** @noinspection PhpUnreachableStatementInspection */
	public function testErrorMessageGoneAfterConnection(){
		$this->skipTest("TODO: Start requesting import in connect functions?");
		return;
		$this->assertEquals(0, Connection::query()->count());
		$this->connect();
		$c = $this->getConnectionModel();
		$l = $c->l();
		foreach(Connection::ERROR_FIELDS as $field){
			$l->setAttribute($field, "test error");
		}
		$l->save();
		foreach(Connection::ERROR_FIELDS as $field){
			$this->assertEquals(1, Connection::whereNotNull($field)->count());
		}
		$this->connect();
		$this->assertEquals(1, Connection::query()->count());
		foreach(Connection::ERROR_FIELDS as $field){
			$this->assertEquals(0, Connection::whereNotNull($field)->count());
		}
	}
	private function createAndJoinFitbitStudy(){
		$study =
			QMCohortStudy::findOrNewQMStudy(DailyStepCountCommonVariable::NAME, SleepDurationCommonVariable::NAME, 1);
		$instructions = $study->getParticipantInstructions();
		$this->logDebug("instructions", $instructions);
		$cause = $study->getOrSetCauseQMVariable();
		$this->assertDataSourcesCount(1, $cause);
		$dataSources = $cause->getDataSourcesCount();
		if(!isset($dataSources[FitbitConnector::DISPLAY_NAME])){
			le('!isset($dataSources[FitbitConnector::DISPLAY_NAME])');
		}
		$effect = $study->getOrSetEffectQMVariable();
		$dataSources = $effect->getDataSources();
		$this->assertCount(1, $dataSources);
		$this->assertParticipantInstructionsContain($study, ["import", FitbitConnector::DISPLAY_NAME]);
		/** @var QMVariable $cause */
		$cause = $study->causeVariable;
		$card = $cause->getTrackingInstructionCard();
		$this->assertStringContainsString('Import', $card->htmlContent);
	}
	protected function deleteReminders(): void{
		TrackingReminder::deleteAll();
		$number = TrackingReminder::count();
		$this->assertEquals(0, $number);
	}
	/**
	 * @return FitbitConnector
	 */
	public function getQMConnector(): QMConnector{
		return parent::getQMConnector();
	}
}
