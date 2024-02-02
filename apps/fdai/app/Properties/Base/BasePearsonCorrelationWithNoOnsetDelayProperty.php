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
class BasePearsonCorrelationWithNoOnsetDelayProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'pearson_correlation_with_no_onset_delay';
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
	public const NAME = 'pearson_correlation_with_no_onset_delay';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Pearson User Variable Relationship With No Onset Delay';
	public $type = 'number';
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
