<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePathProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,191';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'path';
	public $example = '/www/wwwroot/qm-api/tests/UnitTests';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::STACKPATH;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'path';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Path';
	public $type = 'string';

}
