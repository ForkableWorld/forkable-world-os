<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseSecurityProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'Example: {is_proxy:false,proxy_type:null,is_crawler:false,crawler_name:null,crawler_type:null,is_tor:false,threat_level:low,threat_types:null}';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::DESIGN_TOOL_COLLECTION_SECURITY;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'security';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Security';
	public $type = 'string';
	public $validations = 'nullable|string|nullable|string|nullable|string';

}