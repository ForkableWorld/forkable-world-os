<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseTagVariableIdProperty extends BaseVariableIdProperty {
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'This is the id of the ingredient variable whose value is determined based on the value of the tagged variable.';
	public $example = 1420;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'tag_variable_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'Tag Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
	public $canBeChangedToNull = false;

}
