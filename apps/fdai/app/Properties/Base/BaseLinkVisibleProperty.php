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
class BaseLinkVisibleProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,20:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'Control if the link is public or private.';
	public $example = true;
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LINK;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 20;
	public $name = self::NAME;
	public const NAME = 'link_visible';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:20';
	public $showOnDetail = true;
	public $title = 'Link Visible';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:20';

}
