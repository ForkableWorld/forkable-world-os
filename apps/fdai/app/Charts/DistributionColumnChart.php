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
use App\Models\UserVariable;
use App\Properties\Base\BaseValenceProperty;
use App\UI\QMColor;
use App\Utils\Stats;
use App\Variables\QMVariable;
class DistributionColumnChart extends QMColumnChart {
	/**
	 * @param UserVariable|null $v
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		parent::__construct($v);
		$this->setTitleAndId("Daily " . $v->getDisplayNameAttribute() . ' Distribution');
		$this->generateExplanation();
	}
	public static function generateHtmlFromChanges(array $changes, string $title, bool $includeJs = false): string{
		$config = self::generateHighchart($changes, $title);
		return $config->getHtml($includeJs);
	}
	/**
	 * @param array $changes
	 * @param string $title
	 * @param int $bins
	 * @return ColumnHighchartConfig
	 */
	public static function generateHighchart(array $changes, string $title, int $bins = 100): ColumnHighchartConfig{
		$data = DistributionColumnChart::distFromNumberOfBins($changes, $bins);
		//$config = new ColumnHighchartConfig(ColumnHighchartConfig::DEFAULT_MIN_MAX_BUFFER);
		$config = new ColumnHighchartConfig(0);
		$config->addSeriesArray('Occurrences', $data);
		$config->setTitle($title);
		$config->setXAxisTitleText('Value');
		$config->setYAxisTitle('Occurrences');
		return $config;
	}
	public static function valueToDataPoint($value, int $avg, string $valence = null): array{
		$color = DistributionColumnChart::valueToColor($value, $avg, $valence);
		return ['y' => $value, 'color' => $color];
	}
	/**
	 * @param string|null $valence
	 * @param $value
	 * @param int $avg
	 * @return string
	 */
	public static function valueToColor($value, int $avg, string $valence = null): string{
		$color = QMColor::HEX_BLACK;
		if($valence === BaseValenceProperty::VALENCE_POSITIVE){
			$color = ($value > $avg) ? QMColor::HEX_GOOGLE_GREEN : QMColor::HEX_GOOGLE_RED;
		}
		if($valence === BaseValenceProperty::VALENCE_NEGATIVE){
			$color = ($value > $avg) ? QMColor::HEX_GOOGLE_RED : QMColor::HEX_GOOGLE_GREEN;
		}
		return $color;
	}
	/**
	 * @param int $significantFigures
	 * @return array
	 * @throws NotEnoughMeasurementsException
	 */
	public function getDistributionArrayFromMeasurements(int $significantFigures): array{
		$measurements = $this->getValidDailyMeasurementsWithTagsInUserOrCommonUnit();
		$values = [];
		foreach($measurements as $m){
			try {
				$values[] = $m->getValueInUserUnit();
			} catch (IncompatibleUnitException | InvalidVariableValueException|InvalidVariableValueAttributeException $e) {
				le($e);
			}
		}
		$unique = array_unique($values);
		if(count($unique) > 100){
			return DistributionColumnChart::distFromNumberOfBins($values, 50);
		}
		return DistributionColumnChart::distFromSigFigs($values, $significantFigures);
	}
	/**
	 * @return ColumnHighchartConfig
	 * @throws NotEnoughMeasurementsException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$config = new ColumnHighchartConfig(0, $this);
		$distributionArray = $this->getDistributionArrayFromMeasurements(3);
		if(count($distributionArray) > 25){
			$distributionArray = $this->getDistributionArrayFromMeasurements(2);
		}
		if(count($distributionArray) > 25){
			$distributionArray = $this->getDistributionArrayFromMeasurements(1);
		}
		$config->addSeriesArray($this->getVariableName() . ' Distribution', $distributionArray);
		$unit = $this->getUserUnitAbbreviatedName();
		if(stripos($this->getS3BucketAndFilePath(), 'population') !== false){
			$config->setXAxisTitleText('Average Daily Value (' . $unit . ')');
			$config->setYAxisTitle('Number of Days');
		} else{
			$config->setXAxisTitleText('Daily Values (' . $unit . ')');
			$config->setYAxisTitle('Number of Days');
		}
		$config->setTooltipFormatter("
            return this.y +' days where the <br/> average daily value is '+this.x+'$unit';
        ");
		$this->setPositiveAndNegativeColorsIfNecessary($config);
		return $this->setHighchartConfig($config);
	}
	/**
	 * @param float $value
	 * @param int $significantFigures
	 * @return string
	 */
	public static function getDistributionValueLabel($value, int $significantFigures): string{
		if($value === "" || $value === null){
			le("No value provided to getDistributionValueLabel!");
		}
		$valueLabel = (string)$value;
		if(strlen($valueLabel) > 1){
			// Only 1 significant digit results in only 2 columns for weight (100lbs and 200lbs) so we need to loop
			$rounded = Stats::roundByNumberOfSignificantDigits($value, $significantFigures);
			$valueLabel = (string)$rounded;
		}
		return $valueLabel;
	}
	/**
	 * @param array $values
	 * @param int $significantFigures
	 * @return array
	 */
	protected static function distFromSigFigs(array $values, int $significantFigures): array{
		$distributionArray = [];
		foreach($values as $value){
			$valueLabel = self::getDistributionValueLabel($value, $significantFigures);
			if(!isset($distributionArray[$valueLabel])){
				$distributionArray[$valueLabel] = 0;
			}
			++$distributionArray[$valueLabel];
		}
		ksort($distributionArray);
		return $distributionArray;
	}
	/**
	 * @param array $values
	 * @param int $bins
	 * @return array
	 */
	protected static function distFromNumberOfBins(array $values, int $bins): array{
		$distributionArray = [];
		$min = min($values);
		$max = max($values);
		$diff = $max - $min;
		$increment = $diff / $bins;
		$increment = Stats::roundByNumberOfSignificantDigits($increment, 2);
		foreach($values as $value){
			$rounded = Stats::roundToNearestMultipleOf($value, $increment);
			$label = (string)$rounded;
			if($label === "-0"){
				$label = "0";
				//\App\Logging\ConsoleLog::info("$label from ".\App\Logging\QMLog::print_r($rounded, true));
			}
			if(!isset($distributionArray[$label])){
				$distributionArray[$label] = 0;
			}
			++$distributionArray[$label];
		}
		ksort($distributionArray);
		return $distributionArray;
	}
	/**
	 * @param HighchartConfig $highchartConfig
	 */
	protected function setPositiveAndNegativeColorsIfNecessary(HighchartConfig $highchartConfig): void{
		if($this->sourceObject instanceof QMVariable){
			$variable = $this->getQMVariable();
			$avg = null;
			if(strpos($variable->getVariableName(), "Daily Return") !== false){
				$avg = 0;
			}
			if($variable->valenceIsPositive()){
				$highchartConfig->setPositiveAndNegativeColorsByCategory(QMColor::HEX_GOOGLE_GREEN,
					QMColor::HEX_GOOGLE_RED, $avg);
			} elseif($variable->valenceIsNegative()){
				$highchartConfig->setPositiveAndNegativeColorsByCategory(QMColor::HEX_GOOGLE_RED,
					QMColor::HEX_GOOGLE_GREEN, $avg);
			}
		}
	}
	public function getHtml(): string{
		return parent::getHtml();
	}
	public function getOrSetHighchartConfig(): HighchartConfig{
		return parent::getOrSetHighchartConfig();
	}
	protected function generateExplanation(): void{
		$v = $this->getQMVariable();
		$start = $v->getEarliestTaggedMeasurementDate();
		$end = $v->getLatestTaggedMeasurementDate();
		$this->setExplanation("Each column represents the number of days this value occurred between $start and $end.");
	}
}
