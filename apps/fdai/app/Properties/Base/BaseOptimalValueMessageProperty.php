<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOptimalValueMessageProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,500:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'optimal_value_message';
	public $example = 'Higher Daily Step Count predicts moderately lower Daily Average Grade.  This individual\'s Daily Average Grade is generally 7.2% lower after 13100 count over the previous 7 days. ';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::INTERNAL_ERROR_MESSAGE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 500;
	public $name = self::NAME;
	public const NAME = 'optimal_value_message';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:500';
	public $title = 'Optimal Value Message';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:500';

}
