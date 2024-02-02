<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseRecipientUserIdsProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'recipient_user_ids';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::MESSAGES_RECIPIENT;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::MESSAGES_RECIPIENT;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'recipient_user_ids';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Recipient User IDs';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}