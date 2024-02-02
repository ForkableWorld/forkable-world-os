<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseHostnameProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: 134.201.250.155';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'hostname';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'max:255';
	public $showOnDetail = true;
	public $title = 'Hostname';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}