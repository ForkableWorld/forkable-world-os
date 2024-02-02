<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseUserActivationKeyProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Used for resetting passwords.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::OLD_USER;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATOR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'user_activation_key';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'User Activation Key';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';

}
