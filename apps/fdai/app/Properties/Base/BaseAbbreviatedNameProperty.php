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
class BaseAbbreviatedNameProperty extends BaseNameProperty{
	use IsString;
    const MIN_LENGTH = 1;
    const MAX_LENGTH = 40;
	public $dbInput = 'string,40';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Unit abbreviation';
	public $example = 's';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CREDENTIAL;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
    public $minLength = self::MIN_LENGTH;
	public $maxLength = self::MAX_LENGTH;
	public $name = self::NAME;
	public const NAME = 'abbreviated_name';
	public $rules = 'required|max:40|unique:units,abbreviated_name';
	public $title = 'Abbreviated Name';
	public $validations = 'required';

}
