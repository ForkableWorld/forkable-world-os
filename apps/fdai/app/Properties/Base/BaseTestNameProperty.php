<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseTestNameProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,255:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'test_name';
	public $fieldType = 'string';
	public $minLength = 5;
	public $fontAwesome = FontAwesome::DISPLAY_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AGRICULTURE_TEST_TUBE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'test_name';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Test Name';
	public $type = 'string';
	public function validate(): void {
		parent::validate();
	}
}
