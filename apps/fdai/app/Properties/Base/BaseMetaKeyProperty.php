<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseMetaKeyProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'An identifying key for the piece of data.';
	public $example = 'first_name';
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
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'meta_key';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'nullable|max:255';
	public $showOnDetail = true;
	public $title = 'Meta Key';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}