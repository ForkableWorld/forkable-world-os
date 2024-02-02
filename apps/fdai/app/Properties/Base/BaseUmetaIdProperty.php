<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseUmetaIdProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $dbInput = 'bigInteger,true';
	public $dbType = 'bigint';
	public $default = 'undefined';
	public $description = 'Unique number assigned to each row of the table.';
	public $example = 113;
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlInput = self::TYPE_NUMBER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CLIENT_ID;
	public $importance = false;
	public $isOrderable = true;
	public $isPrimary = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'umeta_id';
	public $order = 99;
	public $phpType = 'int';
	public $showOnDetail = true;
	public $title = 'Umeta ID';
	public $type = self::TYPE_INTEGER;

}