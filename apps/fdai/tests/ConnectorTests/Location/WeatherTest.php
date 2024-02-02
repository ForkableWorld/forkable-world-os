<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\Location;
use App\DataSources\Connectors\WeatherConnector;
use App\Models\UserVariable;
use App\Storage\DB\TestDB;
use App\Units\DegreesFahrenheitUnit;
use App\Variables\CommonVariables\EnvironmentCommonVariables\AverageDailyOutdoorTemperatureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\BarometricPressureCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\CloudCoverCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\OutdoorHumidityCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PrecipitationCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\UVIndexCommonVariable;
use App\Variables\QMUserVariable;
use Tests\ConnectorTests\LocationBasedConnectorTestCase;
class WeatherTest extends LocationBasedConnectorTestCase {
	public const DISABLED_UNTIL = "2020-12-25";
	public $connectorName = WeatherConnector::NAME;
	public function testCanadaPostalCode(){
		$this->assertEquals(0, UserVariable::whereNotNull(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)->count());
		$location = "H3Z 2Y7";
		$this->credentials = ['zip' => $location];
		$this->fromTime = time() - 3 * 86400;
		$this->variablesToCheck = $this->getCanadaVariablesToCheck();
		$this->connectImportDisconnect();
		$this->getMeasurementsAndCheckLocation();
		$variable = QMUserVariable::getByNameOrId(1, AverageDailyOutdoorTemperatureCommonVariable::NAME);
		$this->assertEquals(DegreesFahrenheitUnit::ABBREVIATED_NAME, $variable->unitAbbreviatedName);
		$this->checkHumidityPngs();
		$this->assertEquals(0, UserVariable::whereNotNull(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)->count());
	}
	public function testImportByZipCode(){
		$this->assertEquals(0, UserVariable::whereNotNull(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)->count());
		$this->credentials = ['location' => self::ZIP_CODE];
		$this->variablesToCheck = $this->getUSVariablesToCheck();
		$this->fromTime = time() - 14 * 86400;
		$this->connectImportDisconnect();
		$this->getMeasurementsAndCheckLocation();
		$variable = QMUserVariable::getByNameOrId(1, AverageDailyOutdoorTemperatureCommonVariable::NAME);
		$this->assertEquals(DegreesFahrenheitUnit::ABBREVIATED_NAME, $variable->unitAbbreviatedName);
		$this->checkHumidityPngs();
		$this->assertEquals(0, UserVariable::whereNotNull(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)->count());
	}
	/**
	 * @return array
	 */
	private function getUSVariablesToCheck(): array{
		return [
			AverageDailyOutdoorTemperatureCommonVariable::NAME,
			BarometricPressureCommonVariable::NAME,
			CloudCoverCommonVariable::NAME,
			OutdoorHumidityCommonVariable::NAME,
			PrecipitationCommonVariable::NAME,
			UVIndexCommonVariable::NAME,
		];
	}
	/**
	 * @return array
	 */
	private function getCanadaVariablesToCheck(): array{
		$arr = [
			BarometricPressureCommonVariable::NAME,
			OutdoorHumidityCommonVariable::NAME,
			PrecipitationCommonVariable::NAME,
			CloudCoverCommonVariable::NAME,
			AverageDailyOutdoorTemperatureCommonVariable::NAME,
		];
		if(time() > strtotime("2019-11-22")){
			$arr[] = UVIndexCommonVariable::NAME; // OWM return 504's
		}
		return $arr;
	}
	private function checkHumidityPngs(){
		$measurements = $this->getMeasurementsForVariable("Outdoor Humidity");
		foreach($measurements as $measurement){
			$this->assertContains("environment", $measurement->pngPath);
		}
	}
	/**
	 * @param array $allMeasurementsByVariableFromFirstImport
	 * @throws \App\Exceptions\TooSlowException
	 */
	protected function deleteMeasurementsAnalyzeAndImportAgain(array $allMeasurementsByVariableFromFirstImport){
		$this->deleteMeasurements();
		$connection = $this->flushConnector()->getConnectionIfExists();
		$connection->import(__METHOD__);
	}
}
