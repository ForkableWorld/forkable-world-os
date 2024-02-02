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
class BasePredictivePearsonCorrelationCoefficientProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Pearson correlation coefficient of cause and effect values lagged by the onset delay and grouped based on the duration of action. ';
	public $example = 0.031674562650459;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'predictive_pearson_correlation_coefficient';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Predictive Pearson Correlation Coefficient';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
