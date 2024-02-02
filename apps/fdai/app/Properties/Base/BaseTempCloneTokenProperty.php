<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseTempCloneTokenProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: AAVNV2SECRETYTBOS7ZI';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::DEVICE_TOKEN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVICE_TOKEN;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'temp_clone_token';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'max:255';
	public $showOnDetail = true;
	public $title = 'Temp Clone Token';
	public $type = 'string';
	public $validations = 'max:255';

}
