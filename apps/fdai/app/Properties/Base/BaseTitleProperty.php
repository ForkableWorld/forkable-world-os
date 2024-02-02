<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseTitleProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'title';
	public $example = 'Deep Thought';
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
	public $maxLength = 80;
	public $name = self::NAME;
	public const NAME = 'title';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'nullable|max:80';
	public $showOnDetail = true;
	public $title = 'Title';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}