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
class BaseAverageReversePearsonCorrelationOverOnsetDelaysProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The average Pearson correlation coefficient when the onset delays are negative. This is can be compared to the forward onset delay correlation.  If the reverse onset delay correlation is higher, then it is unlikely that the considered cause variable is actually a cause of the considered effect variable.';
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
	public const NAME = 'average_reverse_pearson_correlation_over_onset_delays';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Average Reverse Pearson User Variable Relationship Over Onset Delays';
	public $type = 'number';
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';

}
