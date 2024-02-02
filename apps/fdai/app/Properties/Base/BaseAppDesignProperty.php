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
use App\AppSettings\AppDesign;
use OpenApi\Generator;
class BaseAppDesignProperty extends BaseProperty{
	use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'Various design settings for the app such as the app name, app logo, app favicon, menu definition, intro text, etc.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::HAS_ANDROID_APP;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::DESIGN_TOOL_COLLECTION_3D;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'app_design';
	public $canBeChangedToNull = true;
	public $phpType = AppDesign::class;
	public $title = 'App Design';
	public $type = AppDesign::class;
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
		parent::validate();
	}
}
