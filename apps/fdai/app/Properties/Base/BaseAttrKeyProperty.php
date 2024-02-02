<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAttrKeyProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'attr_key';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::USER_ACTIVATION_KEY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::USER_ACTIVATION_KEY;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 16;
	public $name = self::NAME;
	public const NAME = 'attr_key';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:16';
	public $showOnDetail = true;
	public $title = 'Attr Key';
	public $type = 'string';
	public $validations = 'required|string|string|string';

}