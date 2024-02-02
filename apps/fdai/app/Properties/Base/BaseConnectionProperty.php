<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseConnectionProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'Example: {asn:25876,isp:Los Angeles Department of Water & Power}';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::CONNECTION;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::CONNECTION;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'connection';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Connection';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}