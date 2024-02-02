<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseTagVariableUnitIdProperty extends BaseUnitIdProperty {
	public $dbInput = 'smallInteger,false,true';
	public $dbType = 'smallint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The id for the unit of the tag (ingredient) variable.';
	public $example = 6;
	public $fieldType = 'smallInteger';
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
	public $maximum = 65535;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'tag_variable_unit_id';
	public $canBeChangedToNull = false;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:65535';
	public $title = 'Tag Variable Unit';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:1|max:65535';
    public function shouldShowFilter():bool{return false;}

}
