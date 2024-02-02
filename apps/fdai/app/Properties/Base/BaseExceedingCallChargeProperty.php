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
class BaseExceedingCallChargeProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'decimal,16,2';
	public $dbType = 'decimal';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'exceeding_call_charge';
	public $example = 0;
	public $fieldType = 'decimal';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 1.0E+14;
	public $name = self::NAME;
	public const NAME = 'exceeding_call_charge';
	public $canBeChangedToNull = true;
	public $phpType = 'float';
	public $rules = 'nullable|numeric|max:99999999999999.99';
	public $title = 'Exceeding Call Charge';
	public $type = self::TYPE_NUMBER;
	public $validations = 'nullable|numeric|max:99999999999999.99';

}
