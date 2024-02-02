<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\ColumnHighchartConfig;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Utils\Stats;
use App\Variables\QMVariable;
class YearlyColumnChart extends QMColumnChart {
	/**
	 * YearlyColumnChart constructor.
	 * @param QMVariable|null $v
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		$this->setTitleAndId('Average ' . $v->getOrSetVariableDisplayName() . ' by Year');
		$this->setExplanation("Typical " . $v->getOrSetVariableDisplayName() . " value for each year.");
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
		}		$unit = $this->getUserUnitAbbreviatedName();
		$distributionArray = self::getDistributionArrayFromMeasurements($measurements);
		$config->addSeriesArray('Daily Average ' . $this->getVariableName() . ' By Year', $distributionArray);
		$config->setXAxisTitleText('Year');
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
		$valuesByYear = $averages = [];
		foreach($dailyMeasurements as $m){
			$year = $m->getYear();
			if(!isset($valuesByYear[$year])){
				$valuesByYear[$year] = [];
			}
			try {
				$allValues[] = $valuesByYear[$year][] = $m->getValueInUserUnit();
			} catch (IncompatibleUnitException|InvalidVariableValueException|InvalidVariableValueAttributeException $e) {
				le($e);
			}
		}
		foreach($valuesByYear as $yearNumber => $values){
			$averages[$yearNumber] = Stats::average($values, 3);
		}
		ksort($averages);
		return $averages;
	}
}
