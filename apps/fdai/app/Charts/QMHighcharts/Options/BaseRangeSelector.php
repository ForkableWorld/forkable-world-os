<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseRangeSelector extends HighchartOption {
	const ONE_DAY = "1D";
	const ALL = 'All';
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseRangeSelector.selected
	 */
	public $selected = 0;
	/**
	 * @var BaseButtons[]
	 * @link https://api.highcharts.com/highcharts/buttons
	 */
	public $buttons;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseRangeSelector.inputEnabled
	 */
	public $inputEnabled = false; // Very crowed on mobile if true
	public $enabled = true;
	public function __construct(){
		parent::__construct();
		$this->useYearMonthWeekButtons();
	}
	/**
	 * @param string $text
	 */
	public function setSelected(string $text): void{
		foreach($this->buttons as $i => $arr){
			if($arr['text'] === $text){
				$this->selected = $i;
			}
		}
	}
	public function setOneDaySelected(){
		$this->setSelected(self::ONE_DAY);
	}
	public function setAllSelected(){
		$this->setSelected(self::ALL);
	}
	public function useYearMonthWeekButtons(){
		$this->buttons = [
			[
				'type' => 'all',
				'text' => self::ALL,
			],
			[
				'type' => 'week',
				'count' => 1,
				'text' => '1w',
			],
			[
				'type' => 'month',
				'count' => 1,
				'text' => '1m',
			],
			[
				'type' => 'year',
				'count' => 1,
				'text' => '1y',
			],
		];
	}
	protected function useLotsOfButtons(): void{
		$this->buttons = [
			[
				'type' => 'all',
				'text' => self::ALL,
			],
			[
				'type' => "hour",
				'count' => 1,
				'text' => "1h",
			],
			[
				'type' => "day",
				'count' => 1,
				'text' => self::ONE_DAY,
			],
			[
				'type' => 'week',
				'count' => 1,
				'text' => '1w',
			],
			[
				'type' => 'month',
				'count' => 1,
				'text' => '1m',
			],
			[
				'type' => 'month',
				'count' => 3,
				'text' => '3m',
			],
			[
				'type' => 'month',
				'count' => 6,
				'text' => '6m',
			],
			[
				'type' => 'ytd',
				'text' => 'YTD',
			],
			[
				'type' => 'year',
				'count' => 1,
				'text' => '1y',
			],
		];
	}
}
