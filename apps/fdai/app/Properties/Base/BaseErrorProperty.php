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
use OpenApi\Generator;
class BaseErrorProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'An error message if there is a problem with the measurement';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::ERROR;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::ERROR_MESSAGE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 65535;
	public $name = self::NAME;
	public const NAME = 'error';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:65535';
	public $title = 'Error';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:65535';
	public function showOnCreate(): bool{return false;}

}
