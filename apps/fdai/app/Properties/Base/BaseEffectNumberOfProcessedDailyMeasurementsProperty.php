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
class BaseEffectNumberOfProcessedDailyMeasurementsProperty extends BaseProperty{
	use IsInt;
    public const MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN = BaseCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of outcome variable measurements (aggregated daily) used in the analysis. ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 3; // We used to allow 3 so keep that value her for model validation
	public $name = self::NAME;
	public const NAME = 'effect_number_of_processed_daily_measurements';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:5|max:2147483647';
	public $title = 'Effect Processed Daily Measurements';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';

}
