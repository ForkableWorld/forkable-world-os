<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseMetaValueProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'The actual piece of data.';
	public $example = 'Quan T.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'meta_value';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'nullable';
	public $showOnDetail = true;
	public $title = 'Meta Value';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}