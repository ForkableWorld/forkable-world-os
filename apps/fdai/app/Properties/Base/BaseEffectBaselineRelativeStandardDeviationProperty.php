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
class BaseEffectBaselineRelativeStandardDeviationProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Outcome Average at Baseline (The average value seen for the outcome without treatment during the previous duration of action time span)';
	public $example = 18;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'effect_baseline_relative_standard_deviation';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Effect Baseline Relative Standard Deviation';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';

}
