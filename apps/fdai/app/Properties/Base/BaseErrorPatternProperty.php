<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseErrorPatternProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,191:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'error_pattern';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::ERROR;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ERROR_MESSAGE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'error_pattern';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Error Pattern';
	public $type = 'string';

}
