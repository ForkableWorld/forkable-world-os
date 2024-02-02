<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\ColumnHighchartConfig;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Types\TimeHelper;
use App\Utils\Stats;
use App\Variables\QMVariable;
class MonthlyColumnChart extends QMColumnChart {
	/**
	 * MonthlyColumnChart constructor.
	 * @param QMVariable $v
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		$this->setTitleAndId('Average ' . $v->getOrSetVariableDisplayName() . ' by Month');
		$this->setExplanation("Typical " . $v->getOrSetVariableDisplayName() . " value for each month.");
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
		$unit = $this->getUserUnitAbbreviatedName();
		$distributionArray = self::getDistributionArrayFromMeasurements($measurements);
		$config->addSeriesArray('Daily Average ' . $this->getVariableName() . ' By Month', $distributionArray);
		$config->setXAxisTitleText('Month');
		$config->setYAxisTitle('Daily Average (' . $unit . ')');
		$config->setTooltipFormatter("
            return this.y +'$unit <br/>on average<br/>in '+this.x;
        ");
		$config = $this->setHighchartConfig($config);
		$this->setPositiveAndNegativeColorsIfNecessary($config);
		return $config;
	}
	/**
	 * @param QMMeasurement[] $dailyMeasurements
	 * @return array
	 */
	public static function getDistributionArrayFromMeasurements(array $dailyMeasurements): array{
		$byMonthNumber = $byMonthName = [];
		foreach($dailyMeasurements as $m){
			$month = $m->getMonthNumber();
			if(!isset($byMonthNumber[$month])){
				$byMonthNumber[$month] = [];
			}
			try {
				$allValues[] = $byMonthNumber[$month][] = $m->getValueInUserUnit();
			} catch (IncompatibleUnitException $e) {
				le($e);
			} catch (InvalidVariableValueException $e) {
				le($e);
			}
		}
		ksort($byMonthNumber);
		foreach($byMonthNumber as $monthNumber => $values){
			$monthName = TimeHelper::monthNumberToName($monthNumber);
			$byMonthName[$monthName] = Stats::average($values, 3);
		}
		return $byMonthName;
	}
}
