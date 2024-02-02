<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCloneUrlProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: https://github.com/mikepsinn/qm-api.git';
	public $example = 'https://github.com/codenitive/laravel-oneauth.git';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::CLONE_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::USER_URL;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'clone_url';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'Clone Url';
	public $type = 'string';
	public $validations = 'required|max:255';

}
