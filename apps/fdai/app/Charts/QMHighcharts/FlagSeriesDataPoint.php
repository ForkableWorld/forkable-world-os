<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class FlagSeriesDataPoint {
	public $x;
	public $title;
	/**
	 * @var float
	 */
	public $y;
	/**
	 * FlagSeriesDataPoint constructor.
	 * @param int $milliseconds
	 * @param string $title
	 * @param float|null $y
	 */
	public function __construct(int $milliseconds, string $title, float $y = null){
		$this->x = $milliseconds;
		$this->title = $title;
		$this->y = $y;
	}
}
