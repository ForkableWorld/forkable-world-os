<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfUserVariablesProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of User Variables';
	public $example = 2;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlType = 'text';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_user_variables';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'integer|min:0|max:2147483647';
	public $title = 'User Variables';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    protected static function getRelationshipClass(): string{return UserVariable::class;}
}
