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
class BasePredictorProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = 'boolean:nullable';
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Predictor is true if people would like to know the effects of most variables in the category.';
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::SEND_PREDICTOR_EMAILS;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::SEND_PREDICTOR_EMAILS;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'predictor';
    public $canBeChangedToNull = true;
	public $phpType = PhpTypes::BOOL;
	public $showOnDetail = true;
	public $title = 'Predictor';
	public $type = self::TYPE_BOOLEAN;

}
