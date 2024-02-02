<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Models\GlobalVariableRelationship;
class BaseAverageDailyHighCauseProperty extends BaseProperty{
	use CauseDailyVariableValueTrait;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = GlobalVariableRelationship::FIELD_AVERAGE_DAILY_HIGH_CAUSE;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::DAILYMOTION;
	public $htmlType = 'text';
	public $image = ImageUrls::ACTIVITIES_HIGHWAY;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'average_daily_high_cause';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Average Daily High Cause';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
}
