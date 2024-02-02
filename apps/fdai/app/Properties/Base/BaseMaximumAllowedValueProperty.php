<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseMaximumAllowedValueProperty extends BaseValueProperty{
	use IsFloat;
    use \App\Traits\PropertyTraits\IsHyperParameter;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Maximum reasonable value for a single measurement this variable in the global default unit for that variable. ';
	public $example = null; // Keep null!
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'maximum_allowed_value';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Maximum Allowed Value';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
	public function toDBValue($value): ?float {
        if(strtolower($value) === "infinity"){return null;}
        return $value;
    }
}
