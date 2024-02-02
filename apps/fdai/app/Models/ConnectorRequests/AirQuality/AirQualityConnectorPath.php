<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\ConnectorRequests\AirQuality;
use App\DataSources\Connectors\AirQualityConnector;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\TooSlowException;
use App\Models\ConnectorRequests\ConnectorPath;
use App\Slim\Model\Measurement\AdditionalMetaData;
use App\Types\TimeHelper;
use App\Units\IndexUnit;
use App\VariableCategories\EnvironmentVariableCategory;
class AirQualityConnectorPath extends ConnectorPath {
	public function getUserVariables(): array{
		$c = $this->getQMConnector();
		$variables[] = $c->getQMUserVariable(AirQualityConnector::FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,
			IndexUnit::NAME, EnvironmentVariableCategory::NAME, []);
		$variables[] = $c->getQMUserVariable(AirQualityConnector::LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX,
			IndexUnit::NAME, EnvironmentVariableCategory::NAME, []);
		$variables[] = $c->getQMUserVariable(AirQualityConnector::OZONE_POLLUTION_AIR_QUALITY_INDEX, IndexUnit::NAME,
			EnvironmentVariableCategory::NAME, []);
		return $variables;
	}
	/**
	 * @param string $fromAt
	 * @return string
	 */
	public function generateUrl(string $fromAt): string{
		$url = $this->getUrl(['date' => $this->formatDate($fromAt)]);
		return $url;
	}
	/**
	 * @param $response
	 * @return array
	 * @throws InvalidVariableValueAttributeException
	 * @throws TooSlowException
	 */
	public function responseToMeasurements($response): void{
		$c = $this->getQMConnector();
		foreach($response as $item){
			$param = $item->ParameterName;
			$value = $item->AQI;
			$date = $item->DateObserved;
			$note = new AdditionalMetaData(null, "Level of Health Concern: " . $response[0]->Category->Name);
			if($param === "PM2.5"){
				$name = AirQualityConnector::FINE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX;
			} elseif($param === "OZONE"){
				$name = AirQualityConnector::OZONE_POLLUTION_AIR_QUALITY_INDEX;
			} elseif($param === "PM10"){
				$name = AirQualityConnector::LARGE_PARTICULATE_MATTER_POLLUTION_AIR_QUALITY_INDEX;
			} else{
				le("Please implement saving for AQI parameter: " . $item->ParameterName);
			}
			$v = $c->getWeatherUserVariable($name, IndexUnit::NAME);
			$c->addMeasurement($v->name, $date, $value, IndexUnit::NAME, EnvironmentVariableCategory::NAME, [], 86400,
				$note);
		}
	}
	public function formatDate($intOrString){
		return TimeHelper::YYYYmmddd($intOrString) . "T00-0000";
	}
}
