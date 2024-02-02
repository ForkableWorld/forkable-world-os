<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\PlotOptions;
class NetworkGraphPlotOptions extends PlotOptions {
	public $keys;
	/**
	 * @var array
	 */
	public $layoutAlgorithm;
	public function __construct(){
		parent::__construct();
		$this->keys = NodeSeries::KEYS;
		$this->layoutAlgorithm = [
			'enableSimulation' => true,
			'friction' => -0.9,
		];
	}
}
