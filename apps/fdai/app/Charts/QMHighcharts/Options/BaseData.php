<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseData extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseData.id
	 */
	public $id;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseData.y
	 */
	public $y;
	/**
	 * @var BaseDial
	 * @link https://api.highcharts.com/highcharts/dial
	 */
	public $dial;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseData.name
	 */
	public $name;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseData.table
	 */
	public $table;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseData.startRow
	 */
	public $startRow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseData.endRow
	 */
	public $endRow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseData.endColumn
	 */
	public $endColumn;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseData.hc-key
	 */
	public $hc_key;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseData.value
	 */
	public $value;
	public function __construct(){
		parent::__construct();
		$this->dial = new BaseDial();
	}
}
