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
class BaseConversionFactorProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number by which we multiply the tagged variable\'s value to obtain the tag variable\'s value';
	public $example = 0.0085000002384186;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::UNIT_CONVERSION;
	public $htmlType = 'text';
	public $image = ImageUrls::UNIT_CONVERSION;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'conversion_factor';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Conversion Factor';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required';

}
