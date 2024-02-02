<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLicenseProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsArray;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,64:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'license';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'license';
	public $order = 99;
	public $phpType = PhpTypes::ARRAY;
	public $showOnDetail = true;
	public $title = 'License';
	public $type = 'string';

}
