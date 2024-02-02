<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseRespondingToPhraseIdProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'responding_to_phrase_id';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::PHRASE;
	public $htmlInput = self::TYPE_NUMBER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::PHRASE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'responding_to_phrase_id';
	public $order = 99;
	public $phpType = 'int';
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Responding to Phrase ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|nullable|nullable';

}