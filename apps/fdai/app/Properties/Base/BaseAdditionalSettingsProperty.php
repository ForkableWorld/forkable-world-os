<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\AppSettings\AdditionalSettings;
class BaseAdditionalSettingsProperty extends BaseProperty{
	use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Additional non-design settings for your application.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::ANALYSIS_SETTINGS_MODIFIED;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::AUDIO_AND_VIDEO_CONTROLS_SETTINGS;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'additional_settings';
	public $canBeChangedToNull = true;
	public $phpType = AdditionalSettings::class;
	public $title = 'Additional Settings';
	public $type = AdditionalSettings::class;

}
