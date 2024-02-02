<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
class ColumnHighchartConfig extends HighchartConfig {
	public const DEFAULT_MIN_MAX_BUFFER = 0.01;
	/**
	 * ColumnHighchartConfig constructor.
	 * @param float $minMaxBuffer
	 * @param QMChart|null $QMChart
	 */
	public function __construct($minMaxBuffer = self::DEFAULT_MIN_MAX_BUFFER, QMChart $QMChart = null){
		$this->minMaxBuffer = $minMaxBuffer;
		parent::__construct($QMChart);
		$this->setLegendEnabled(false);
		$this->chart->type = 'column';
		$this->chart->animation = ['duration' => 0];  // What is this for?
	}
	/**
	 * @param string $seriesName
	 * @param array $labelValueArray
	 * @param float|null $min
	 * @param float|null $max
	 * @deprecated Use addSeries
	 */
	public function addSeriesArray(string $seriesName, array $labelValueArray, float $min = null,
		float $max = null): void{
		$seriesData = array_values($labelValueArray);
		if($min === null){
			$min = min($seriesData);
		}
		if($max === null){
			$max = max($seriesData);
		}
		$this->setYAxisMinMax($min, $max, $this->minMaxBuffer);
		if(!$seriesData){
			le("No data provided to generate column chart. ");
		}
		$xAxisCategories = array_keys($labelValueArray);
		$this->setXAxisCategories($xAxisCategories);
		$column = new ColumnPlotOption(40 * 5 / count($xAxisCategories));
		$this->getPlotOptions()->column = $column;
		$series = new Series();
		$series->name = $seriesName;
		$series->data = $seriesData;
		parent::addSeriesAndYAxis($series);
	}
}
