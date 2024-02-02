<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsHtml;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseHtmlProperty extends BaseProperty
{
    use IsHtml;
	public $canBeChangedToNull = true;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'html';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::HTML5;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::HTML;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'html';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Html';
	public $type = 'string';

}
