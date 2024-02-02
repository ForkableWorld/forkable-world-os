<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseHtmlUrlProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,255';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'Example: https://github.com/mikepsinn/qm-api';
	public $example = 'https://github.com/codenitive/laravel-oneauth';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::USER_URL;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVELOPMENT_078_HTML_2;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'html_url';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'max:255|unique:github_repositories,html_url';
	public $showOnDetail = true;
	public $title = 'Html Url';
	public $type = 'string';
	public $validations = 'max:255|unique:github_repositories,html_url';

}
