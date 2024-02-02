<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOutcomeProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Outcome variables (those with `outcome` == 1) are variables for which a human would generally want to identify the influencing factors.  These include symptoms of illness, physique, mood, cognitive performance, etc.  Generally correlation calculations are only performed on outcome variables';
	public $example = true;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::MANAGE_VARIABLES;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::OUTCOMES;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'outcome';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'nullable|boolean';
	public $title = 'Outcome';
	public $type = self::TYPE_BOOLEAN;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|boolean';
}
