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
class BaseLongDescriptionProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Longer paragraph description of the data provider';
	public $example = 'Lose weight with MyFitnessPal, the fastest and easiest-to-use calorie counter for iPhone and iPad. With the largest food database of any iOS calorie counter (over 3,000,000 foods), and amazingly fast food and exercise entry.';
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
	public $name = self::NAME;
	public const NAME = 'long_description';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required';
	public $title = 'Long Description';
	public $type = PhpTypes::STRING;
	public $validations = 'required';

}
