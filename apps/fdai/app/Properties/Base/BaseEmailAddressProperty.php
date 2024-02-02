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
class BaseEmailAddressProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'email_address';
	public $example = 'james@doctordurant.com';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LAST_EMAIL;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::EMAIL;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'email_address';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:255';
	public $title = 'Email Address';
	public $type = PhpTypes::STRING;
	public $validations = 'required|max:255';

}
