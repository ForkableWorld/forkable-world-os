<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAverageDailyLowCauseProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'A realistic daily value (not a fraction from averaging) that typically precedes below average outcome values. ';
	public $example = 4607500;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::DAILYMOTION;
	public $htmlType = 'text';
	public $image = ImageUrls::AGRICULTURE_SUNFLOWER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'average_daily_low_cause';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Average Daily Low Cause';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
}
