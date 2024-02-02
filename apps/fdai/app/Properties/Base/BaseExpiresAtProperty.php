<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseExpiresAtProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsDate;
	public $canBeChangedToNull = true;
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = self::TYPE_DATETIME;
	public $default = 'undefined';
	public $description = 'expires_at';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'expires_at';
	public $order = 99;
	public $phpType = 'date';
	public $rules = 'nullable|datetime';
	public $showOnDetail = true;
	public $title = 'Expires';
	public $type = 'string';
	public $validations = 'nullable|nullable|nullable';

}