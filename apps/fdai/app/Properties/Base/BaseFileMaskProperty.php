<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseFileMaskProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,191';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'file_mask';
	public $example = '*Test.php';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::FILE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVELOPMENT_060_FILE_5;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'file_mask';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'File Mask';
	public $type = 'string';

}
