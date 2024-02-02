<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAttrValueProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsBoolean;
	public $dbInput = 'string';
	public $dbType = 'blob';
	public $default = 'undefined';
	public $description = 'attr_value';
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
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'attr_value';
	public $order = 99;
	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Attr Value';
	public $type = self::TYPE_BOOLEAN;
	public $validations = 'required|string|string|string';

}