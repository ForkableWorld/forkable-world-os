<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\Options\BaseNavigator;
use App\Charts\QMHighcharts\Options\BaseRangeSelector;
use App\Types\QMStr;
use App\UI\QMColor;
class BaseHighstock extends HighchartConfig {
	public $useHighStocks = true;
	public $annotations = [];
	/**
	 * @var BaseRangeSelector
	 * @link https://api.highcharts.com/highcharts/rangeSelector
	 */
	public $rangeSelector;
	/**
	 * @var HighstockYAxis[]
	 * @link https://api.highcharts.com/highcharts/yAxis
	 */
	public $yAxis = [];
	/**
	 * @var BaseNavigator
	 */
	public $navigator;
	public function __construct(QMChart $QMChart = null){
		parent::__construct($QMChart);
		$this->yAxis = [];
		$this->xAxis = new XAxisTimeLine();
		$this->chart->type = "spline";
		$this->setLegendEnabled(true);
		$this->unsetPlotOptions();
		$this->rangeSelector = new BaseRangeSelector();
		$this->navigator = new BaseNavigator();
		$this->getChart()->setXZoom();
		// Just use CSS for this $this->setPlotOptions(new CandlestickPlotOptions());
	}
	public function getConstructor(): string{
		return "stockChart";
	}
	/**
	 * @param Series $parentSeries
	 */
	public function addSeriesWithLabels(Series $parentSeries): void{
		$yIndex = $parentSeries->getYAxisIndex();
		if($yIndex === null){
			$yIndex = count($this->yAxis);
		}
		$labelAndDataSeries = $parentSeries->getLabelAndDataSeries();
		/** @var Series $childSeries */
		foreach($labelAndDataSeries as $childSeries){
			$childSeries->validate();
			$childSeries->yAxis = $yIndex;
			if(!$childSeries->color && $yIndex > 0){ // If there's only 1 series, just let the color get set by theme
				$childSeries->setColor($this->colors[$yIndex] ?? QMColor::randomHexColor());
			}
			parent::addSeriesAndYAxis($childSeries);
			if($childSeries->getYAxisIndex() === null){
				if(method_exists($childSeries, 'getYAxis')){
					$yAxisObj = $childSeries->getYAxis();
					$this->addYAxis($yAxisObj, $childSeries->yAxis);
				}
			}
			$this->validate();
		}
	}
	public function renderChart(string $varName = null, string $callback = null, bool $withScriptTag = false): string{
		$this->unsetPlotOptions();
		return parent::renderChart($varName, $callback, $withScriptTag);
	}
	public function setStockTooltipFormatter(): void{
		$this->setTooltipFormatter("
            //debugger;
            function round_it(my_number, precision){
                precision = precision || 3;
                return Number( my_number.toPrecision(precision) )
            }
            var txt = Highcharts.dateFormat('%Y-%m-%d', this.x)+'<br>';
            var points = this.points || [this.point];
            points.forEach(function(point){
                var money = '';
                if(point.point && point.point.close){
                    money += 'open $'+ round_it(point.point.open)+'<br>';
                    money += 'high $'+ round_it(point.point.high)+'<br>';
                    money += 'low $'+ round_it(point.point.low)+'<br>';
                    money += 'close $'+ round_it(point.point.close)+'<br>';
                } else if(!point.y){
                    money = point.title;
                } else {
                     var indicator = 'Indicator';
                    if(point.series.name){
                        indicator = point.series.name;
                    }
                    var unitName = point.series.options.unitName || '';
                    if(unitName === '$'){
                        money = indicator + ' $'+ round_it(point.y);
                    } else {
                        money = indicator + ' '+ round_it(point.y)+''+unitName;
                    }
                }
                txt += money +'<br>';
            });
            return txt;
        ");
	}
	/**
	 * @param string $propertyTitle
	 */
	public function setMoneyTooltipFormatter(string $propertyTitle): void{
		$propertyTitle = QMStr::escapeSingleQuotes($propertyTitle);
		$this->setTooltipFormatter("
            return '<b>$'+ this.y +'</b> " . $propertyTitle . "<br/> at <br/>'+
            Highcharts.dateFormat('%Y-%m-%d', this.x);
        ");
	}
	public function setGeneralTooltipFormatter(): void{
		$this->setTooltipFormatter("
            //debugger;
            function round_it(my_number, precision){
                precision = precision || 3;
                return Number( my_number.toPrecision(precision) )
            }
            var txt = Highcharts.dateFormat('%Y-%m-%d', this.x)+'<br>';
            var points = this.points || [this.point];
            points.forEach(function(point){
                var forPoint = '';
                point = point.point || point;
                if(point && point.close){
                    forPoint += 'open $'+ round_it(point.open)+'<br>';
                    forPoint += 'high $'+ round_it(point.high)+'<br>';
                    forPoint += 'low $'+ round_it(point.low)+'<br>';
                    forPoint += 'close $'+ round_it(point.close)+'<br>';
                } else if(!point.y && point.y !== 0){
                    forPoint = point.title;
                } else {
                    var series = point.series;
                    forPoint = series.name+': '+round_it(point.y);
                    var unitName;
                    if(series.tooltipOptions && series.tooltipOptions.valueSuffix){
                        unitName = series.tooltipOptions.valueSuffix;
                    }
                    if(series.options && series.options.unitName){unitName = series.options.unitName;}
                    if(unitName){forPoint += ''+unitName;}
                }
                txt += forPoint +'<br>';
            });
            return txt;
        ");
	}
	public function useRedAndGreenCandles(){
		$this->setCss(".highcharts-point-down {
    stroke: red;
    fill: red;
}
.highcharts-point-up {
    stroke: green;
    fill: green;
}");
	}
	/**
	 * @param HighstockYAxis[] $yAxis
	 */
	public function setYAxes(array $yAxis): void{
		$this->yAxis = $yAxis;
	}
	public function setOneDaySelected(){
		$this->getRangeSelector()->setOneDaySelected();
	}
	public function setAllSelected(){
		$this->getRangeSelector()->setAllSelected();
	}
	/**
	 * @return BaseRangeSelector
	 */
	public function getRangeSelector(): BaseRangeSelector{
		return $this->rangeSelector;
	}
}
