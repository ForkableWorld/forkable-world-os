<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseDateTimeLabelFormats;
class DateTimeLabelFormats extends BaseDateTimeLabelFormats {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDateTimeLabelFormats.month
	 */
	public $month;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDateTimeLabelFormats.year
	 */
	public $year;
	public $millisecond; //'%H:%M:%S.%L',
	public $second; //'%H:%M:%S',
	public $minute; //'%H:%M',
	public $hour; //'%H:%M',
	public $day; //'%e. %b',
	public $week; //'%e. %b',
	public function __construct(){
		parent::__construct();
		$this->second = '%Y-%m-%d<br/>%H:%M:%S';
		$this->minute = '%Y-%m-%d<br/>%H:%M';
		$this->hour = '%Y-%m-%d<br/>%H:%M';
		$this->day = '%Y<br/>%b-%d';
		$this->week = '%Y<br/>%b-%d';
		$this->month = "%b %Y";
		$this->year = "%Y";
	}
}
