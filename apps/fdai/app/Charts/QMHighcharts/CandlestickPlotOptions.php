<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\UI\QMColor;
class CandlestickPlotOptions extends HighchartOption {
	public $candlestick;
	public function __construct(){
		parent::__construct();
		$this->candlestick = new \stdClass();
		$this->candlestick->upLineColor = QMColor::HEX_GOOGLE_GREEN;
		$this->candlestick->upColor = QMColor::HEX_GOOGLE_GREEN;
		$this->candlestick->downLineColor = QMColor::HEX_GOOGLE_RED;
		$this->candlestick->downColor = QMColor::HEX_GOOGLE_RED;
	}
}
