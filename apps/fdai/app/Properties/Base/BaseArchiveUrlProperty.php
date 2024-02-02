<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseArchiveUrlProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: https://api.github.com/repos/mikepsinn/qm-api/{archive_format}{/ref}';
	public $example = 'https://api.github.com/repos/codenitive/laravel-oneauth/{archive_format}{/ref}';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::ARCHIVE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DIALOGUE_ASSETS_ARCHIVE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'archive_url';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'max:255';
	public $showOnDetail = true;
	public $title = 'Archive Url';
	public $type = 'string';
	public $validations = 'max:255';

}
