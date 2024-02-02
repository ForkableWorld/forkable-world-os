<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOutputHtmlFailExtensionProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,191:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'output_html_fail_extension';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::HAS_CHROME_EXTENSION;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::HAS_CHROME_EXTENSION;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'output_html_fail_extension';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Output Html Fail Extension';
	public $type = 'string';

}
