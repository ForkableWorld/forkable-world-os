<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseContinentCodeProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: NA';
	public $example = 'NA';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::OAUTH_AUTHORIZATION_CODE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::OAUTH_AUTHORIZATION_CODE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'continent_code';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'Continent Code';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}