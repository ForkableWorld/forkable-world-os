<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariableRelationship;
use App\Astral\CorrelationBaseAstralResource;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfUserVariableRelationshipsAsCauseProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of user variable relationships for which this variable is the cause variable';
	public $example = 43;
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
	public const NAME = 'number_of_user_variable_relationships_as_cause';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Effects';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:2147483647';
    public static function getAstralRelatedResourceClass(): string{return CorrelationBaseAstralResource::class;}
    protected static function getRelationshipClass(): string{return UserVariableRelationship::class;}
    public static function getForeignKey(): string{return UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID;}
    public static function getRelatedTable(): string{return UserVariableRelationship::TABLE;}
}
