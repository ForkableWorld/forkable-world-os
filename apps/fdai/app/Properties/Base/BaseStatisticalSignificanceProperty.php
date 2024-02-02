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
class BaseStatisticalSignificanceProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,4';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'A function of the effect size and sample size';
	public $example = 0.4688;
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
	public const NAME = 'statistical_significance';
	public $phpType = 'float';
	public $rules = 'numeric';
	public $title = 'Statistical Significance';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = false;
	public $validations = 'numeric';

}
