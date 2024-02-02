<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseXAxis;
class XAxisTimeLine extends BaseXAxis {
	/**
	 * @var DateTimeLabelFormats
	 * @link https://api.highcharts.com/highcharts/dateTimeLabelFormats
	 */
	public $dateTimeLabelFormats;
	public $ordinal;
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(){
		$this->title = new Title();
		$this->dateTimeLabelFormats = new DateTimeLabelFormats();
		$this->type = "datetime";
		$this->ordinal = false;
	}
}
