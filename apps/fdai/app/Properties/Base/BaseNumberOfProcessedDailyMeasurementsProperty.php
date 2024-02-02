<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Variables\QMVariable;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseNumberOfProcessedDailyMeasurementsProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'Number of processed measurements';
	public $example = 23;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 7000;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_processed_daily_measurements';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:7000';
	public $title = 'Processed Daily Measurements';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:7000';
    use IsCalculated;
    /**
     * @param QMVariable $v
     * @return mixed
     */
    public static function calculate($v){
        $measurements = $v->getValidDailyMeasurementsWithTagsAndFilling();
        $value = count($measurements);
        $v->setAttribute(static::NAME, $value);
        return $value;
    }
}
