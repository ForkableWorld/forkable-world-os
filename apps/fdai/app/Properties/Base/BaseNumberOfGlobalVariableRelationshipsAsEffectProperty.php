<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\GlobalVariableRelationship;
use App\Astral\GlobalVariableRelationshipBaseAstralResource;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Fields\Field;
class BaseNumberOfGlobalVariableRelationshipsAsEffectProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of global variable relationships for which this variable is the effect variable';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CORRELATIONS;
	public $htmlType = 'text';
	public $image = ImageUrls::CORRELATIONS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_global_variable_relationships_as_effect';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Causes';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:2147483647';
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return GlobalVariableRelationshipBaseAstralResource::hasMany("Predictors", 'global_variable_relationships_where_cause_variable');
    }
    protected static function getRelationshipClass(): string{return GlobalVariableRelationship::class;}
}
