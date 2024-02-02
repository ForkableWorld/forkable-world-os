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
class WeekdayColumnChart extends QMColumnChart {
	/**
	 * WeekdayColumnChart constructor.
	 * @param QMVariable|null $v
	 * @internal param QMVariable $userVariable
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		$this->setTitleAndId('Average ' . $v->getOrSetVariableDisplayName() . ' by Day of Week');
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
		$distributionArray = self::getDistributionArrayFromMeasurements($measurements);
		$unit = $this->getUserUnitAbbreviatedName();
		$config->addSeriesArray('Average ' . $this->getVariableName() . ' By Day of Week', $distributionArray);
		$this->setExplanation("Typical " . $this->getVariableName() . " value on each day of the week.");
		$config->setXAxisTitleText('Day');
		$config->setYAxisTitle('Average (' . $unit . ')');
		$config->setTooltipFormatter("
            return this.y +'$unit <br/>on average<br/>on '+this.x;
        ");
		$config = $this->setHighchartConfig($config);
		$this->setPositiveAndNegativeColorsIfNecessary($config);
		return $config;
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return array
	 */
	public static function getDistributionArrayFromMeasurements(array $measurements): array{
		$dayOfWeekMap = [
			'Sun',
			'Mon',
			'Tue',
			'Wed',
			'Thu',
			'Fri',
			'Sat',
		];
		$weekdayMeasurementArrays = $weekdayAverages = [];
		foreach($measurements as $iValue){
			if(!isset($weekdayMeasurementArrays[$iValue->getWeekdayNumber()])){
				$weekdayMeasurementArrays[$iValue->getWeekdayNumber()] = [];
			}
			$weekdayMeasurementArrays[$iValue->getWeekdayNumber()][] = $iValue;
		}
		ksort($weekdayMeasurementArrays);
		foreach($weekdayMeasurementArrays as $dayNumber => $weekdayMeasurementArray){
			$dayOfWeekName = $dayOfWeekMap[$dayNumber];
			$values = collect($weekdayMeasurementArray)->map(function($m){
				/** @var QMMeasurement $m */
				return $m->getValueInUserUnit();
			})->all();
			$weekdayAverages[$dayOfWeekName] = Stats::average($values, 3);
		}
		return $weekdayAverages;
	}
	public function getSubtitleAttribute(): string{
		$explanation = "Typical " . $this->getVariableName() . " value seen on each day of the week. ";
		return $this->explanation = $explanation;
	}

}
