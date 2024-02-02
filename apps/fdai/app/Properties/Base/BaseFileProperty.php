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
class BaseFileProperty extends BaseProperty
{
    use IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'file';
	public $example = 'at';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::FILE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVELOPMENT_060_FILE_5;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public $order = '99';
	public $phpType = 'string';
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'File';
	public $type = 'string';
	public $validations = 'required';
	public const NAME = 'file';

}
