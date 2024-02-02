<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseTestsPathProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,191';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'tests_path';
	public $example = 'UnitTests';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::STACKPATH;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::JETBRAINS_MODULES_TESTSOURCEFOLDER_;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'tests_path';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Tests Path';
	public $type = 'string';

}
