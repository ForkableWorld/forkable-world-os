<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseInterpretativeConfidenceProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsFloat;
	public $canBeChangedToNull = true;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = 'undefined';
	public $description = 'interpretative_confidence';
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = self::TYPE_NUMBER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'interpretative_confidence';
	public $order = 99;
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $showOnDetail = true;
	public $title = 'Interpretative Confidence';
	public $type = self::TYPE_NUMBER;
	public $validations = 'nullable|nullable|nullable';

}