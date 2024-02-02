<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseIpProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: 134.201.250.155';
	public $example = '24.216.168.142';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::MESSAGES_RECIPIENT;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::MESSAGES_RECIPIENT;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'ip';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'Ip';
	public $type = 'string';
	public $validations = 'required|string|string|string';

}