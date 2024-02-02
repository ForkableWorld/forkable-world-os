<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\UI\QMColor;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
class MultivariateHighstock extends DBHighstock {
	protected $variables = [];
	protected $userVariableIds = [];
	/**
	 * MultivariateTimelineHighstockChart constructor.
	 * @param array $userVariableIds
	 * @param QMChart|null $QMChart
	 */
	public function __construct(array $userVariableIds = [], QMChart $QMChart = null){
		if(!$this->qmChart){$this->qmChart = $QMChart;}
		parent::__construct([], $QMChart);
		if($userVariableIds){
			$this->arguments = $this->userVariableIds = $userVariableIds;
			$this->setTitle("Measurements Over Time");
			$this->setSubTitle($this->getVariableNames() . " Measurements");
		}
		$this->setGeneralTooltipFormatter();
	}
	public function inlineNoHeading(): string{
		return parent::inlineNoHeading();
	}
	/**
	 * @return QMVariable[]
	 */
	public function getVariables(): array{
		if(!$this->variables){
			foreach($this->userVariableIds as $id){
				$this->variables[] = QMUserVariable::find($id);
			}
		}
		return $this->variables;
	}
	public function getVariableNames(): string{
		$names = [];
		foreach($this->getVariables() as $v){
			$names[] = $v->getTitleAttribute();
		}
		return implode(" & ", $names);
	}
	/**
	 * @param QMVariable $variable
	 * @param QMMeasurement[]|DailyMeasurement[] $measurements
	 * @param string|null $name
	 */
	public function addVariableSeries(QMVariable $variable, array $measurements, string $name = null){
		if(!$name){
			$name = $variable->getTitleAttribute();
		}
		if(!$measurements){
			le("No measurements provided to " . __FUNCTION__);
		}
		$m = QMMeasurement::getFirst($measurements);
		if(!$m){
			le('!$m');
		}
		$series = new HighstockSeries($name, $this, $m->getUnitAbbreviatedName());
		$max = $variable->getMaximumAllowedValueAttribute();
		foreach($measurements as $m){
			$val = $m->getRoundedValue(3);
			$values[$m->getStartAt()] = $val;
			if($max && $val > $max){
				$m->logError("$val is greater than maximum $max $m->unitAbbreviatedName for $variable->name");
			}
			$series->addDataPoint([$m->getMillis(), $val]);
		}
		$series->yAxis = count($this->getSeries());
		if(isset($this->colors[$series->yAxis])){
			$color = $this->colors[$series->yAxis];
		} else{
			$color = QMColor::randomHexColor();
		}
		$series->setColor($color);
		$this->addSeriesAndYAxis($series);
	}
	public function getHumanizedWhereString(): string{
		$str = "variable is ";
		$names = [];
		foreach($this->getVariables() as $v){
			$names[] = $v->name;
		}
		return $str . implode(" or ", $names);
	}
	public function validate(): void {
		parent::validate();
		if(!$this->navigator || !$this->navigator->enabled){
			le("Need navigator enabled or it won't show in ionic app");
		}
		if(!$this->rangeSelector || !$this->rangeSelector->enabled){
			le("Need rangeSelector enabled or it won't show in ionic app");
		}
		if(is_array($this->yAxis) && count($this->yAxis) > 1){
			$y = $this->yAxis[1];
			if(!$y->getBaseTitle()->getStyle()->color){
				le('!$y->getBaseTitle()->getStyle()->color');
			}
			foreach($this->series as $series){
				/** @var YAxis $y */
				$y = $this->yAxis[$series->getYAxisIndex()];
				if($y->getBaseTitle()->getStyle()->color !== $series->getColor()){
					le('$y->getBaseTitle()->getStyle()->color !== $series->getColor()');
				}
			}
		}
	}
}
