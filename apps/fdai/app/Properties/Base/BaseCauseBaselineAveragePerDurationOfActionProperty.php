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
class BaseCauseBaselineAveragePerDurationOfActionProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Predictor Average at Baseline (The average low non-treatment value of the predictor per duration of action)';
	public $example = 4600500;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::APPER;
	public $htmlType = 'text';
	public $image = ImageUrls::JETBRAINS_ACTIONS_CHANGEVIEW_;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'cause_baseline_average_per_duration_of_action';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Cause Baseline Average Per Duration of Action';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';

}
