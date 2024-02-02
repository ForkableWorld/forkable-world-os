<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseMarker;
class HighstockSeries extends Series {
	/**
	 * @var BaseHighstock
	 */
	private $highstock;
	private $flagSeries = [];
	public $data = []; // i.e. [[29.9, '0'], [71.5, '1']]
	public $id; //i.e. 'dataseries';  // Replace if multiple series
	public $keys; // Optional i.e. ['y', 'id'];
	public $lineWidth = 3;
	public $name;
	public $tooltip;
	public $type;
	public $color;
	public $yAxis = 0;
	public $unitName;
	public function __construct(string $name = null, BaseHighstock $highstock = null, string $unitName = null){
		parent::__construct();
		if($highstock){
			$this->highstock = $highstock;
		}
		if($name){
			$this->setNameAndId($name);
		}
		$this->setUnitName($unitName);
	}
	public function setTooltipFormatter(string $unitName): void{
		parent::setTooltipFormatter($unitName);
		if($this->highstock){
			$hs = $this->getHighstock();
			unset($hs->tooltip);
		}
	}
	public function getFlagSeries(string $flagSeriesName, string $labelBackgroundColor,
		string $onSeries = null): FlagsSeries{
		if(isset($this->flagSeries[$flagSeriesName])){
			return $this->flagSeries[$flagSeriesName];
		}
		$s = new FlagsSeries($onSeries, $flagSeriesName, $labelBackgroundColor, $labelBackgroundColor);
		return $this->flagSeries[$flagSeriesName] = $s;
	}
	public function addDataPoint(array $millisAndYValues, string $labelSeriesName = null, string $labelHtml = null,
		string $labelBackgroundColor = null, string $onSeries = null){
		$millis = $millisAndYValues[0];
		$this->highstock->setXAxisMinIfNecessary($millis);
		$this->data[] = $millisAndYValues;
		if(count($millisAndYValues) === 5){
			//$this->keys = ['x', 'open', 'high', 'low', 'close'];
			$this->lineWidth = 1;
		}
		if($labelSeriesName && $labelHtml){
			$flag = $this->getFlagSeries($labelSeriesName, $labelBackgroundColor, $onSeries);
			$flag->addData($millis, $labelHtml, $millisAndYValues[1]);
			$flag->validate();
			$this->validate();
		}
		$this->validate();
	}
	/**
	 * @return Series[]
	 */
	public function getLabelAndDataSeries(): array{
		$arr = [$this];
		/** @var FlagsSeries $fs */
		foreach($this->flagSeries as $fs){
			$fs->validate();
			$arr[] = $fs;
		}
		return $arr;
	}
	public function getYAxis(): HighstockYAxis{
		$this->validate();
		$opposite = $this->getHighstock()->hasMultipleSeries();
		$axis = new HighstockYAxis($this->getTitleAttribute(), $opposite, $this);
		$title = $this->name;
		if(stripos($title, $this->getUnitName()) === false){
			$title .= " (" . $this->getUnitName() . ")";
		}
		$axis->setTitleText($title);
		$axis->setColor($this->getColor());
		$this->validate();
		return $axis;
	}
	/**
	 * @return BaseHighstock
	 */
	public function getHighstock(): BaseHighstock{
		return $this->highstock;
	}
	/**
	 * @return string
	 */
	public function getUnitName(): string{
		if(empty($this->unitName)){
			$this->unitName = $this->getTooltip()->valueSuffix;
		}
		if(empty($this->unitName)){
			le('empty($this->unitName)');
		}
		return $this->unitName;
	}
	/**
	 * @return Tooltip
	 */
	public function getTooltip(): Tooltip{
		return $this->tooltip;
	}
	public function useDotsInsteadOfLine(){
		$this->lineWidth = 0;
		$m = new BaseMarker();
		$m->enabled = true;
		$m->radius = 2;
		$this->marker = $m;
	}
	/**
	 * @param string $unitName
	 */
	public function setUnitName(string $unitName): void{
		$this->unitName = trim($unitName);
	}
	public function disabled(){
		$this->setVisibility(false);
	}
	public function doNotConnectPoints(){
		$this->setLineWidth(0);
	}
	/**
	 * @param int $lineWidth
	 */
	public function setLineWidth(int $lineWidth): void{
		$this->lineWidth = $lineWidth;
	}
}
