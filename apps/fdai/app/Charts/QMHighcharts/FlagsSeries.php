<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\UI\QMColor;
class FlagsSeries extends Series {
	const SHAPE_SQUAREPIN = 'squarepin';
	public $onSeries = 'dataseries';
	public $data = [];
	public $zIndex = 10;
	public $name = 'Flags on series';
	public $shape = self::SHAPE_SQUAREPIN;
	public $type = "flags";
	public $color;
	public $fillColor = QMColor::HEX_WHITE;
	public $width;// i.e. 16;
	public $style;
	public $states;
	public $animationLimit = "Infinity"; // https://api.highcharts.com/highstock/series.flags.animationLimit
	public $allowOverlapX = true; // Keep true or it randomly hides flags https://api.highcharts.com/highstock/series.flags.allowOverlapX
	public $y = -45; // Pixels from bottom.  Gets cut off with default -30
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(?string $onSeries, string $name, string $lineColor, string $fillColor = null){
		$this->color = $lineColor;
		$this->onSeries = $onSeries;
		$this->name = $name;
		//if(!$fillColor){$fillColor = QMColor::STRING_WHITE;}
		$this->fillColor = $fillColor;
		if($fillColor !== QMColor::STRING_WHITE){
			$this->style = new SeriesStyle(QMColor::STRING_WHITE);
		} else{
			$this->style = new SeriesStyle(QMColor::STRING_BLUE);
		}
		$this->states = new SeriesStates();
		$this->validate();
	}
	public function addData(int $x, string $title, float $y = 0){
		$this->data[] = new FlagSeriesDataPoint($x, $title, $y);
	}
	public function getValue(){
		return $this->toArray();
	}
	public function toArray(): array{
		return json_decode(json_encode($this), true);
	}
}
