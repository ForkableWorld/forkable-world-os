<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCountryNameProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: United States';
	public $example = 'United States';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::COUNTRY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::COUNTRY;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'country_name';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'Country Name';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}