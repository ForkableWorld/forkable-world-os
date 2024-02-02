<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseMaximumAllowedDailyValueProperty extends BaseValueProperty{
	use IsFloat;
    use \App\Traits\PropertyTraits\IsHyperParameter;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The maximum allowed value in the default unit for measurements aggregated over a single day. ';
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::DAILYMOTION;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::QUESTION_MARK;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'maximum_allowed_daily_value';
	public $phpType = 'float';
	public $title = 'Maximum Allowed Daily Value';
	public $type = self::TYPE_NUMBER;
}
