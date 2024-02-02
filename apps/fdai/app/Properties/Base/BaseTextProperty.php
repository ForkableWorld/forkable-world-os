<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseTextProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'text';
	public $example = 'A funny thing to do is, if you\'re out hiking and your friend gets bitten by a poisonous snake, tell him you\'re going to go for help, then go about ten feet and pretend that *you* got bit by a snake. Then start an argument with him about who\'s going to go get help. A lot of guys will start crying. That\'s why it makes you feel good when you tell them it was just a joke.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::ENVELOPE_OPEN_TEXT_SOLID;
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
	public $maxLength = 65535;
	public $name = self::NAME;
	public const NAME = 'text';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:65535';
	public $showOnDetail = true;
	public $title = 'Text';
	public $type = 'string';
	public $validations = 'required|string|string|string';

}