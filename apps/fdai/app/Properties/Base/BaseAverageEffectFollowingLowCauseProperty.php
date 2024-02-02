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
class BaseAverageEffectFollowingLowCauseProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'average_effect_following_low_cause';
	public $example = 3.1817;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::LOW_VISION_SOLID;
	public $htmlType = 'text';
	public $image = ImageUrls::AGRICULTURE_SUNFLOWER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'average_effect_following_low_cause';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Average Effect Following Low Cause';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
