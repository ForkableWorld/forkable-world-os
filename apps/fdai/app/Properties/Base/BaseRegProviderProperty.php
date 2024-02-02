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
class BaseRegProviderProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,25:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Registered via';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 25;
	public $name = self::NAME;
	public const NAME = 'reg_provider';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:25';
	public $title = 'Reg Provider';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:25';

}
