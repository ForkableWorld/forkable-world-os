<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCoverageEnabledProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $dbInput = 'string,191';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'coverage_enabled';
	public $example = false;
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::PUSH_NOTIFICATIONS_ENABLED;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::JS_KARMA_COVERAGE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'coverage_enabled';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Coverage Enabled';
	public $type = 'string';

}
