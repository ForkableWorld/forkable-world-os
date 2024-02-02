<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfCommonJoinedVariablesProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'number_of_common_joined_variables';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_common_joined_variables';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Common Joined Variables';
	public $type = self::TYPE_INTEGER;

}
