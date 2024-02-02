<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
class TimeLineHighchartConfig
	extends HighchartConfig { // Can't extend Highchart config because plotOptions property breaks rendering
	public $useHighStocks = false;
	public $xAxis = [
		'type' => 'datetime',
		'dateTimeLabelFormats' => [
			'day' => '%m/%e/%Y', // Otherwise it's impossible to know the year in chart images
			'week' => '%m/%e/%Y',
		],
	];
}
