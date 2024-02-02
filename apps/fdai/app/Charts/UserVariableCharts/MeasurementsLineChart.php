<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\UserVariableCharts;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\HighstockSeries;
use App\Charts\QMHighcharts\MultivariateHighstock;
use App\Charts\QMMeasurementsChart;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Variables\QMUserVariable;
class MeasurementsLineChart extends QMMeasurementsChart {
	/**
	 * @param QMUserVariable|null $v
	 */
	public function __construct(QMUserVariable $v = null){
		if(!$v){
			return;
		}
		parent::__construct($v);
		$this->getTitleAttribute();
		$this->getSubtitleAttribute();
	}
	public function getTitleAttribute(): string{
		$v = $this->getQMVariable();
		$e = $v->getOrSetVariableDisplayName().' Over Time';
		$this->setTitleAndId($e);
		return $e;
	}
	public function getSubtitleAttribute(): string{
		$v = $this->getQMUserVariable();
		$e = $v->getOrSetVariableDisplayName()." measurements over time";
		if($v->getCommonAndUserTaggedVariables()){
			$e .= " as well as those generated from tagged variables";
		}
		if($v->hasFillingValue()){
			$e .= " or generated filler measurements between gaps";
		}
		$e .= ".";
		$this->setExplanation($e);
		return $e;
	}
	protected function getQMUserVariable(): QMUserVariable{
		return $this->getSourceObject();
	}
	/**
	 * @return MultivariateHighstock
	 * @throws NotEnoughMeasurementsException
	 */
	public function getHighchartConfig(): HighchartConfig{
		$c = $this->highchartConfig;
		if(!$c){
			$c = $this->generateHighchartConfig();
		}
		$c = MultivariateHighstock::instantiateIfNecessary($c);
		return $this->highchartConfig = $c;
	}
	/**
	 * @return HighchartConfig
	 * @throws NotEnoughMeasurementsException
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$this->newHighchartConfig();
		$v = $this->getQMUserVariable();
		$byTagName = $v->getMeasurementsByTagVariableName();
		foreach($byTagName as $name => $measurements){
			$this->addMeasurementSeries($name, $measurements);
		}
		if($invalid = $v->getInvalidMeasurements()){
			$this->addMeasurementSeries("Invalid Measurements", $invalid);
		}
		//        if($withTags = $v->getMeasurementsWithTags()){
		//            $raw = $v->getQMMeasurements();
		//            if(count($withTags) !== count($raw)){
		//                $this->addMeasurementSeries("With Tags", $withTags);
		//            }
		//        }
		if($filler = $v->getFillerMeasurements()){
			$this->addMeasurementSeries("Generated Filler Measurements", $filler);
		}
		//        if($daily = $v->getDailyMeasurementsWithoutTagsOrFilling()){
		//            if(count($daily) !== count($withTagsAndFilling)){
		//                $this->addMeasurementSeries("Grouped Daily", $daily);
		//            }
		//        }
		return $this->highchartConfig;
	}
	/**
	 * @return MultivariateHighstock
	 */
	protected function newHighchartConfig(): MultivariateHighstock{
		$c = new MultivariateHighstock([], $this);
		$c->getRangeSelector()->useYearMonthWeekButtons();
		$c->setTitle($this->getTitleAttribute());
		$userUnitName = $this->getUserUnitAbbreviatedName();
		$c->setUnit($userUnitName);
		return $this->highchartConfig = $c;
	}
	/**
	 * @param string $title
	 * @param array $measurements
	 * @return HighstockSeries
	 * @throws NotEnoughMeasurementsException
	 */
	public function addMeasurementSeries(string $title, array $measurements): HighstockSeries{
		$highstock = $this->getHighchartConfig();
		$series = new HighstockSeries($title, $highstock, $this->getUserUnitAbbreviatedName());
		foreach($measurements as $m){
			try {
				$val = $m->getValueInUserUnit();
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				le($e);
				throw new \LogicException();
			}
			$series->addDataPoint([
				                      $m->startTime * 1000,
				                      $val,
			                      ]);
		}
		$series->doNotConnectPoints();
		$highstock->setPointSize(3);
		$highstock->addSeriesAndSetColor($series);
		$highstock->getYAxis()->setTitleText($this->getUserUnit()->getNameAttribute());
		return $series;
	}
	/**
	 * @return \stdClass|HighchartConfig
	 */
	public function getExportableConfig(): \stdClass{
		$config = parent::getExportableConfig();
		if(is_object($config->yAxis) &&
		   empty($config->yAxis->title->text)){ // getUserUnitAbbreviatedName causes get measurements request
			$config->yAxis->title->text = $this->getUserUnitAbbreviatedName();
		}
		$config->plotOptions->series = [];
		return $config;
	}
}
