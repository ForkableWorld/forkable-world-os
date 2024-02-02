<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BasePredictorVariableIdProperty extends BaseVariableIdProperty {
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'predictor_variable_id';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::SEND_PREDICTOR_EMAILS;
	public $htmlType = 'text';
	public $image = ImageUrls::SEND_PREDICTOR_EMAILS;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'predictor_variable_id';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Predictor Variable';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:1|max:2147483647';

}
