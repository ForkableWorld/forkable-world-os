<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Types\QMStr;
class CorrelationsOverOnsetDelaysHighchart extends ColumnHighchartConfig {
	/**
	 * @param $c
	 * @param QMChart|null $chart
	 */
	public function __construct($c, QMChart $chart = null) {
		parent::__construct($chart);
		$this->setTitle('VariableRelationships Over Onset Delays');
		$this->setXAxisTitleText('Assumed Onset Delay (in Days)');
		$this->setYAxisTitle('Predictive Coefficient');
		$this->getYAxis()->plotLines = [['value' => 0, 'width' => 1, 'color' => '#EA4335']];
		$this->setLegendEnabled(false);
		$this->getPlotOptions()->line = new Line();
		$effectName = QMStr::escapeSingleQuotes($c->getEffectVariableName());
		$causeName = QMStr::escapeSingleQuotes($c->getCauseVariableName());
		$this->setTooltipFormatter("
            if(this.x > 0){
                return this.y +'<br/>predictive correlation coefficient<br/>is observed when $effectName data'+
                    '<br/>is paired with $causeName data from<br/>'+this.x+' days before';
            } else {
                return this.y +'<br/>predictive correlation coefficient<br/>is observed when $effectName data'+
                    '<br/>is paired with $causeName data from<br/>'+this.x*-1+' later';
            }
        ");
		if($arr = $c->l()->getCorrelationsOverDelays()){
			$this->populate($arr);
		}
		/*       Too long and is truncated
		$config->subtitle = "The onset delay is the amount of time which is assumed to pass after the recorded
					$this->causeVariableName measurement before the maximum change in
					potentially influence $this->effectVariableName is observed.   The larger the
					absolute value of the correlation with a positive onset delay (on the right side of the chart),
					the more likely it is that $c->causeVariableName causally influences
					$c->effectVariableName.
					The larger the absolute value of the correlation with a negative onset delay (on the left side of
					the chart), the more likely it is that $this->effectVariableName causally
					influences $c->causeVariableName.";*/
		$this->type = "VariableRelationships Over Onset Delays";
	}
	public function populate(array $correlations_over_delays){
		$series = new Series();
		$series->name = $this->getTitleAttribute();
		$cats = [];
		foreach($correlations_over_delays as $seconds => $coefficient){
			$series->data[] = $coefficient;
			$cats[] = round($seconds / 86400);
		}
		$this->series = []; // TODO: We might want more series later?
		$this->getXAxis()->setCategories($cats);
		$this->addSeriesAndYAxis($series);
	}
}
