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
class BaseShortDescriptionProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text,65535';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Short description of the service (such as the categories it tracks)';
	public $example = 'Tracks diet.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::AUDIO_DESCRIPTION_SOLID;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 65535;
	public $name = self::NAME;
	public const NAME = 'short_description';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:65535';
	public $title = 'Short Description';
	public $type = PhpTypes::STRING;
	public $validations = 'required';

}
