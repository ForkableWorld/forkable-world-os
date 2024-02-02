<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseInterventionTypeProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,4369:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'intervention_type';
	public $example = 'Behavioral';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 4369;
	public $name = self::NAME;
	public const NAME = 'intervention_type';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:4369';
	public $title = 'Intervention Type';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:4369';

}
