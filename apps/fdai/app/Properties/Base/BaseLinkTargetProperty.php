<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLinkTargetProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,25:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'The target frame for the link. e.g. _blank, _top, _none.';
	public $example = 'self';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LINK;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ACTIVITIES_TARGET;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 25;
	public $name = self::NAME;
	public const NAME = 'link_target';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:25';
	public $showOnDetail = true;
	public $title = 'Link Target';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:25';

}
