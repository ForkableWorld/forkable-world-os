<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseScatter;
class ScatterPlotOptions extends BaseScatter {
	/**
	 * @var Tooltip
	 */
	public $tooltip;
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->tooltip = new Tooltip();
	}
	/**
	 * @return Tooltip
	 */
	public function getTooltip(): Tooltip{
		return $this->tooltip;
	}
}
