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
class BaseReversePearsonCorrelationCoefficientProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'User Variable Relationship when cause and effect are reversed. For any causal relationship, the forward correlation should exceed the reverse correlation';
	public $example = -0.14779745524745;
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
	public const NAME = 'reverse_pearson_correlation_coefficient';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Reverse Pearson Correlation Coefficient';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
