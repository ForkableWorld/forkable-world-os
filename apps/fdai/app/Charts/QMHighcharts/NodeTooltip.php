<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseTooltip;
class NodeTooltip extends BaseTooltip {
	/**
	 * @var string
	 */
	public $nodeFormat;
	/**
	 * Tooltip constructor.
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(){
		$this->followPointer = false;
		$this->headerFormat = '<span style="font-size: 10px">{series.name}</span><br/>';
		$this->pointFormat = "{point.fromNode.name} \u2192 {point.toNode.name}: <b>{point.weight}</b><br/>";
		$this->nodeFormat = "{point.name}: <b>{point.sum}</b><br/>";
		$this->useHtml = true;
		$this->enableClickingLinks();
		$this->hideDelay = 2000;
	}
}
