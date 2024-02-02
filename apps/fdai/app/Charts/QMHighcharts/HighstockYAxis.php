<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class HighstockYAxis extends YAxis {
	public $labels;
	public $gridLineWidth;
	public $title;
	public $opposite;
	/**
	 * YAxis constructor.
	 * @param string $title
	 * @param bool $opposite
	 * @param HighstockSeries $series
	 */
	public function __construct(string $title, bool $opposite, HighstockSeries $series){
		parent::__construct();
		$this->gridLineWidth = 0;
		$this->setTitleText($title);
		//This should be set in theme $this->setColor($series->getColor());
		//$this->labels = new HighchartLabels($unit, $series);
		$this->opposite = $opposite;
	}
}
