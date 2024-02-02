<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAvailableOutsideUsProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsBoolean;
	public $dbInput = self::TYPE_BOOLEAN;
	public $dbType = self::TYPE_BOOLEAN;
	public $default = 'undefined';
	public $description = 'Whether the connector is available outside the US.';
	public $example = 1;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::DOCUMENTATION;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::MUSICAL_NOTES_96_PNG;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'available_outside_us';
	public $order = 99;
	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Available Outside Us';
	public $type = self::TYPE_BOOLEAN;

}
