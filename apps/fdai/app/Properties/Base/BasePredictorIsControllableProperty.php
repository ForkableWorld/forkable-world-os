<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePredictorIsControllableProperty extends BaseProperty
{
    use IsBoolean;
	public $canBeChangedToNull = true;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'predictor_is_controllable';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::SEND_PREDICTOR_EMAILS;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::SEND_PREDICTOR_EMAILS;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'predictor_is_controllable';
            	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Predictor Is Controllable';
	public $type = self::TYPE_BOOLEAN;

}
