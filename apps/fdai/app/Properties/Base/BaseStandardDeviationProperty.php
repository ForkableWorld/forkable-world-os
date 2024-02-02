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
use App\Utils\Stats;
use App\Variables\QMVariable;
class BaseStandardDeviationProperty extends BaseProperty{
	use IsFloat;
    use \App\Traits\PropertyTraits\IsCalculated;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Standard deviation';
	public $example = 26.526319287161;
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
	public const NAME = 'standard_deviation';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Standard Deviation';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    /**
     * @param QMVariable $model
     * @return mixed
     */
    public static function calculate($model){
        if($values = $model->getDailyValuesWithTagsAndFilling()){
            $stdDev = Stats::standardDeviation($values);
        } else {
            $stdDev = null;
        }
        $model->setAttribute(static::NAME, $stdDev);
        return $stdDev;
    }
}
