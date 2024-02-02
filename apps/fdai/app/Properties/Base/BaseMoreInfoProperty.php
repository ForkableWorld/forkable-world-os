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
class BaseMoreInfoProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'More information displayed when the user is adding reminders and going through the onboarding process.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::INFO_SOLID;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ESSENTIAL_COLLECTION_MORE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'more_info';
    public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'More Info';
	public $type = PhpTypes::STRING;

}
