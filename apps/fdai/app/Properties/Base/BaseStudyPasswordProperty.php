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
class BaseStudyPasswordProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'study_password';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CREATE_STUDY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ACTIVITIES_PASSWORD;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 20;
	public $name = self::NAME;
	public const NAME = 'study_password';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:20';
	public $title = 'Study Password';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:20';

}
