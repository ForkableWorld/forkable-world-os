<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseColumn;
class ColumnPlotOption extends BaseColumn {
	public $pointWidth;
	public $enableMouseTracking;
	public $colorByPoint;
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(float $pointWidth = null){
		$this->borderWidth = 0;
		$this->colorByPoint = true;
		$this->enableMouseTracking = true;
		$this->pointPadding = 0.2;
		$this->pointWidth;
	}
}
