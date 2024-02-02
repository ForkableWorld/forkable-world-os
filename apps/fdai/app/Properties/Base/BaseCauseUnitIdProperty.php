<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Units\MilligramsUnit;
use App\Models\GlobalVariableRelationship;
use OpenApi\Generator;
class BaseCauseUnitIdProperty extends BaseUnitIdProperty{
	public const NAME = 'cause_unit_id';
	public $dbInput = 'smallInteger,false,true';
	public $dbType = 'smallint';
	public $default = Generator::UNDEFINED;
	public $description = 'Unit ID of Cause';
	public $example = MilligramsUnit::ID;
	public $fieldType = 'smallInteger';
	public $fontAwesome = FontAwesome::UNIT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = GlobalVariableRelationship::FIELD_CAUSE_UNIT_ID;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:65535';
	public $title = 'Cause Unit';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:1|max:65535';
    public function shouldShowFilter():bool{return false;}
}
