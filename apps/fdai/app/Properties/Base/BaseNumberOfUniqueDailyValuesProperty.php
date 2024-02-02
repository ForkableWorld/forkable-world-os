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
use App\Variables\QMVariable;
class BaseNumberOfUniqueDailyValuesProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of unique daily values';
	public $example = 18;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::DAILYMOTION;
	public $htmlType = 'text';
	public $image = ImageUrls::QUESTION_MARK;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_unique_daily_values';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:0|max:2147483647';
	public $title = 'Unique Daily Values';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:0|max:2147483647';
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMVariable $v
     * @return mixed
     */
    public static function calculate($v){
        $values = $v->getDailyValuesWithTagsAndFilling();
        $value = count(array_unique($values));
        $v->setAttribute(static::NAME, $value);
        return $value;
    }
}
