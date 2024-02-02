<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOutcomeIsAGoalProperty extends BaseProperty
{
    use IsBoolean;
	public $canBeChangedToNull = true;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'outcome_is_a_goal';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'outcome_is_a_goal';
            	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Outcome Is a Goal';
	public $type = self::TYPE_BOOLEAN;

}
