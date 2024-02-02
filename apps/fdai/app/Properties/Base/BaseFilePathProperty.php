<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseFilePathProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = false;
	public $dbInput = 'string,1083:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'file_path';
	public $fieldType = 'string';
	public $minLength = 3;
	public $fontAwesome = FontAwesome::FILE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVELOPMENT_060_FILE_5;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'file_path';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'File Path';
	public $type = 'string';

}
