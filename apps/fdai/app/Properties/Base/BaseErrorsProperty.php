<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsArray;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseErrorsProperty extends BaseProperty{
	use IsArray;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Errors that the principal investigator should be notified of. ';
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
	public $name = self::NAME;
	public const NAME = 'errors';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::ARRAY;
	public $title = 'Errors';
	public $type = PhpTypes::ARRAY;

}
