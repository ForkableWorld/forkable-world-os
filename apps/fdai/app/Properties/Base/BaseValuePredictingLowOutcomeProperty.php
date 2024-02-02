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
class BaseValuePredictingLowOutcomeProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'cause value that predicts a below average effect value (in default unit for cause variable)';
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::CREATE_STUDY;
	public $htmlType = 'text';
	public $image = ImageUrls::OUTCOMES;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'value_predicting_low_outcome';
	public $phpType = 'float';
	public $rules = 'numeric';
	public $title = 'Value Predicting Low Outcome';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = false;
	public $validations = 'numeric';
}
