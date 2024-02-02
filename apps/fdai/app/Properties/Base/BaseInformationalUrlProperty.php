<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseInformationalUrlProperty extends BaseProperty{
	use IsUrl;
	public $dbInput = 'string,2000:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Wikipedia url';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::USER_URL;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::USER_URL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2000;
	public $name = self::NAME;
	public const NAME = 'informational_url';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2000';
	public $title = 'Informational Url';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:2000';

}
