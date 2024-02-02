<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsArray;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseMetaProperty extends BaseProperty
{
    use IsArray;
	public $canBeChangedToNull = true;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'meta';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::COMMENTMETUM;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'meta';
            	public $order = '99';
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Meta';
	public $type = 'string';

}
