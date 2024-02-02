<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCauseTreatmentAveragePerDayProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Predictor Average During Treatment (The average high value of the predictor per day considered to be the treatment dosage)';
	public $example = 1009800;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::APPER;
	public $htmlType = 'text';
	public $image = ImageUrls::COLLABORATOR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'cause_treatment_average_per_day';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Cause Treatment Average Per Day';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';

}
