<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\ColumnHighchartConfig;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Utils\Stats;
use App\Variables\QMVariable;
use Illuminate\Support\Arr;
class HourlyColumnChart extends QMColumnChart {
	/**
	 * HourlyColumnChart constructor.
	 * @param QMVariable|null $v
	 */
	public function __construct($v = null){
		$name = "";
		if($v){
			$name = $v->getOrSetVariableDisplayName();
		}
		$this->setTitleAndId('Average ' . $name . ' by Hour of Day');
		$this->setExplanation("Typical $name value each hour of the day.");
		parent::__construct($v);
	}
	/**
	 * @return ColumnHighchartConfig
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$config = new ColumnHighchartConfig(ColumnHighchartConfig::DEFAULT_MIN_MAX_BUFFER, $this);
		try {
			$measurements = $this->getValidDailyMeasurementsWithTagsInUserOrCommonUnit();
		} catch (NotEnoughMeasurementsException $e) {
			return $this->setSubTitleFromInvalidMeasurements($e, $config);
		}
		$distributionArray =
			self::getDistributionArrayFromMeasurements($measurements);
		$config->addSeriesArray('Average ' . $this->getVariableName() . ' By Hour', $distributionArray);
		$config->setXAxisTitleText('Hour');
		$config->setYAxisTitle('Average (' . $this->getUserUnitAbbreviatedName() . ')');
		$this->setPositiveAndNegativeColorsIfNecessary($config);
		return $this->setHighchartConfig($config);
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return array
	 */
	public static function getDistributionArrayFromMeasurements($measurements){
		$hourMeasurementArrays = $hourAverages = [];
		foreach($measurements as $iValue){
			$hourNumber = $iValue->getHourNumber();
			if(!isset($hourMeasurementArrays[$hourNumber])){
				$hourMeasurementArrays[$hourNumber] = [];
			}
			$hourMeasurementArrays[$hourNumber][] = $iValue;
		}
		ksort($hourMeasurementArrays);
		foreach($hourMeasurementArrays as $hourNumber => $hourMeasurementArray){
			$hourName = date("ga", mktime($hourNumber, 0, 0, 1, 1, 2011));
			$hourAverages[$hourName] = Stats::average(Arr::pluck($hourMeasurementArray, 'value'));
		}
		return $hourAverages;
	}
}
