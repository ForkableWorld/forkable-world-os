<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\AdminProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseStripePlanProperty extends BaseProperty{
	use IsString, AdminProperty;
	public $dbInput = 'string,100:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'stripe_plan';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::STRIPE_PLAN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::STRIPE_PLAN;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 100;
	public $name = self::NAME;
	public const NAME = 'stripe_plan';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:100';
	public $title = 'Stripe Plan';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:100';

}
