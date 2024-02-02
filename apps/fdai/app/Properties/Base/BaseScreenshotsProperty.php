<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseScreenshotsProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'screenshots';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'screenshots';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Screenshots';
	public $type = 'string';

}
