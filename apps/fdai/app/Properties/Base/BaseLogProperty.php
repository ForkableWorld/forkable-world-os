<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLogProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'text,16777215:nullable';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'log';
	public $example = '<span style=\"background-color: black; color: white\">sh: 1: vendor/bin/phpunit: not found<br></span>';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::TRACKER_LOG;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::TRACKER_LOG;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'log';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Log';
	public $type = 'string';

}
