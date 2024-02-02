<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseGetItUrlProperty extends BaseProperty{
	use IsUrl;
	public $dbInput = 'string,2083:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'URL to a site where one can get this device or application';
	public $example = 'http://www.amazon.com/gp/product/B004H6WTJI/ref=as_li_qf_sp_asin_il?ie=UTF8&camp=1789&creative=9325&creativeASIN=B004H6WTJI&linkCode=as2&tag=quantimodo04-20';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::GET_PREVIEW_BUILDS;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::GET_PREVIEW_BUILDS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'get_it_url';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:2083';
	public $title = 'Get It Url';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:2083';

}
