<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfDataPointsProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The number of data points used to estimate the mean. ';
	public $example = 16;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::HAND_POINT_DOWN_SOLID;
	public $htmlType = 'text';
	public $image = ImageUrls::BUSINESS_COLLECTION_POINT_OF_SERVICE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_data_points';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Data Points';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:0|max:2147483647';

}
