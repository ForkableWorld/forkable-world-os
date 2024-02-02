<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseMessageProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'message';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::INTERNAL_ERROR_MESSAGE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::ERROR_MESSAGE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 16777215;
	public $name = self::NAME;
	public const NAME = 'message';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'nullable|max:16777215';
	public $showOnDetail = true;
	public $title = 'Message';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}