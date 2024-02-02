<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use App\DataSources\Connectors\OuraConnector;
use App\DataSources\QMConnector;
use App\Models\Connection;
use App\Models\TrackingReminder;
use App\Studies\QMCohortStudy;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\QMVariable;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class OuraTest
 * @package Tests\Api\Connectors1
 */
class OuraTest extends ConnectorTestCase {
	public const DISABLED_UNTIL = "2022-08-01";
	protected $connectorName = OuraConnector::NAME;
	protected $variablesToCheck =
		array (
			0 => 'Daily Readiness Score (from Oura)',
			1 => 'Temperature Deviation (from Oura)',
			2 => 'Temperature Trend Deviation (from Oura)',
			3 => 'Calories Burned While Active (from Oura)',
			4 => 'Equivalent Walking Distance (from Oura)',
			5 => 'High Activity Time (from Oura)',
			6 => 'Inactivity Alerts (from Oura)',
			7 => 'Low Activity Time (from Oura)',
			8 => 'Medium Activity Time (from Oura)',
			9 => 'Non Wear Time (from Oura)',
			10 => 'Resting Time Score (from Oura)',
			11 => 'Daily Activity Score (from Oura)',
			12 => 'Sedentary Time (from Oura)',
			13 => 'Daily Step Count (from Oura)',
			14 => 'Calories Burned (from Oura)',
			15 => 'Overall Sleep Score (from Oura)',
			16 => 'Deep Sleep Score (from Oura)',
			17 => 'Sleep Efficiency Score (from Oura)',
			18 => 'Sleep Latency Score (from Oura)',
			19 => 'Sleep Restfulness Score (from Oura)',
			20 => 'Sleep Timing Score (from Oura)',
			21 => 'Sleep Duration Score (from Oura)',
		);
	public function testOura(){
		$this->fromTime = time() - 14 * 86400;
		$this->deleteReminders();
		$this->connectImportCheckDisconnect();
		$this->createAndJoinOuraStudy();
		$QMUser = $this->getUser();
		$metadata = $QMUser->generateNftMetadata();
		$this->assertArrayKeysContain($metadata, []);
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
	private function createAndJoinOuraStudy(){
		$causeName = 'High Activity Time (from Oura)';
		$effectName = 'Overall Sleep Score (from Oura)';
		$study =
			QMCohortStudy::findOrNewQMStudy($causeName, $effectName, 1);
		$instructions = $study->getParticipantInstructions();
		$this->logDebug("instructions", $instructions);
		$cause = $study->getOrSetCauseQMVariable();
		$this->assertDataSourcesCount(1, $cause);
		$dataSources = $cause->getDataSourcesCount();
		if(!isset($dataSources[OuraConnector::DISPLAY_NAME])){
			le('!isset($dataSources[OuraConnector::DISPLAY_NAME])');
		}
		$effect = $study->getOrSetEffectQMVariable();
		$dataSources = $effect->getDataSources();
		$this->assertCount(1, $dataSources);
		$this->assertParticipantInstructionsContain($study, ["import", OuraConnector::DISPLAY_NAME]);
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
	 * @return OuraConnector
	 */
	public function getQMConnector(): QMConnector{
		return parent::getQMConnector();
	}
}
