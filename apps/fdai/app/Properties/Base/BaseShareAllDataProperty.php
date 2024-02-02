<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseShareAllDataProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = self::TYPE_BOOLEAN;
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'share_all_data';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::CREATIVE_COMMONS_SHARE;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::AUDIO_AND_VIDEO_CONTROLS_SHARE;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'share_all_data';
	public $phpType = PhpTypes::BOOL;
	public $title = 'Share All Data';
	public $type = self::TYPE_BOOLEAN;

}
