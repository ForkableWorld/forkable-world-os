<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Illuminate\Support\Str;
class BaseNumberOfOutcomePopulationStudiesProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Global Population Studies';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_outcome_population_studies';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Outcome Population Studies';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:0|max:2147483647';
    public static function getRelatedTable(): string{
        return GlobalVariableRelationship::TABLE;
    }
    public static function getForeignKey(): string{
        return "cause_".Str::snake(static::getParentShortClassName())."_id";
    }
}
