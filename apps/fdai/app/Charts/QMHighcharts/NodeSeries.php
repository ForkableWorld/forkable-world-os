<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseStyle;
use App\Logging\QMLog;
use App\UI\QMColor;
use App\Utils\Stats;
abstract class NodeSeries extends Series {
	public const KEYS = ['from', 'to', 'weight', 'tooltip', 'url', 'color'];
	public $keys = self::KEYS;
	public $nodes;
	public function __construct($name){
		parent::__construct();
		$dataLabels = new DataLabels();
		$dataLabels->enabled = true;
		$style = new BaseStyle();
		$style->color = "black";
		$style->textOutline = false;
		$style->fontWeight = "normal";
		$dataLabels->style = $style;
		unset($dataLabels->backgroundColor);
		$this->dataLabels = $dataLabels;
		$this->linkOpacity = 0.9;
		$this->name = $name;
	}
	public function addDataPoint(string $from, string $to, float $weight, string $tooltip, string $url = null,
		string $color = null): array{
		if(!$color){
			$color = QMColor::randomDarkHexColor();
		}
		$point = $this->data[] = [
			$from,
			$to,
			$weight,
			$tooltip,
			$url,
			$color,
		];
		return $point;
	}
	public function getHeight(): int{
		$uniqueEffects = $uniqueCauses = [];
		$data = $this->getData();
		foreach($data as $arr){
			$uniqueCauses[] = $arr[0];
			$uniqueEffects[] = $arr[1];
		}
		$uniqueCauses = array_unique($uniqueCauses);
		$uniqueEffects = array_unique($uniqueEffects);
		$max = max([count($uniqueCauses), count($uniqueEffects)]);
		$height = $max * 100;
		if($height < 200){
			$height = 200;
		}
		return $height;
	}
	public function addNodes(): void{
		$weights = [];
		$data = $this->data;
		if(!$data){
			QMLog::info("No data for nodes in " . static::class);
			return;
		}
		foreach($data as $arr){
			$weights[] = $arr[2];
		}
		$normalized = Stats::normalizeToMinMax($weights, 5, 50);
		$nodes = [];
		foreach($data as $i => $arr){
			$from = $arr[0];
			$to = $arr[1];
			$color = $arr[5];
			$weight = round($normalized[$i]);
			$nodes[$from] = [
				'id' => $from,
				'color' => $color,
				'marker' => [
					'radius' => $weight,
				],
			]; // uses highcharts-color-0 set by setCss()
			$nodes[$to] = [
				'id' => $to,
				'color' => $color,
				'marker' => [
					'radius' => $weight,
				],
			]; // uses highcharts-color-0 set by setCss()
		}
		$this->nodes = array_values($nodes);
	}
}
