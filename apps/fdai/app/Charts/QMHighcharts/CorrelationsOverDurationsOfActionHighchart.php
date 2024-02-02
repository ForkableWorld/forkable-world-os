<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Traits\HasCorrelationCoefficient;
use App\Types\QMStr;
class CorrelationsOverDurationsOfActionHighchart extends ColumnHighchartConfig {
	/**
	 * @param HasCorrelationCoefficient $c
	 */
	public function __construct($c){
		parent::__construct();
		$this->setTitle('VariableRelationships Over Durations of Action');
		$this->setXAxisTitleText('Assumed Duration Of Action (in Days)');
		$this->setYAxisTitle('Predictive Coefficient');
		$this->getYAxis()->plotLines = [['value' => 0, 'width' => 1, 'color' => '#EA4335']];
		$this->setLegendEnabled(false);
		$this->getPlotOptions()->line = new Line();
		$effectName = QMStr::escapeSingleQuotes($c->getEffectVariableName());
		$causeName = QMStr::escapeSingleQuotes($c->getCauseVariableName());
		$this->setTooltipFormatter("
            return this.y +'<br/>predictive correlation coefficient<br/>is observed when $effectName data'+
                '<br/>is paired with $causeName data<br/>aggregated over the previous<br/>'+this.x+' days';
        ");
		if($arr = $c->getCorrelationsOverDurations()){
			$this->populate($arr);
		}
		$this->type = "VariableRelationships Over Durations of Action";
	}
	public function populate(array $correlations_over_durations){
		$series = new Series();
		$series->name = $this->getTitleAttribute();
		foreach($correlations_over_durations as $seconds => $coefficient){
			$series->data[] = $coefficient;
			$this->getXAxis()->categories[] = round($seconds / 86400);
		}
		$this->series = []; // TODO: We might want more series later?
		$this->addSeriesAndYAxis($series);
	}
}
