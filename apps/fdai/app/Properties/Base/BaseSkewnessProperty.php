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
class BaseSkewnessProperty extends BaseProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Skewness';
	public $example = -1.1545446162897;
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
	public const NAME = 'skewness';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'Skewness';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMVariable $model
     * @return mixed
     */
    public static function calculate($model){
        $values = $model->getDailyValuesWithTagsAndFilling();
        if($values){
            Stats::skewnessAndKurtosis($values, $skewness, $kurtosis);
        } else {
            $skewness = null;
        }
        $model->setAttribute(static::NAME, $skewness);
        return $skewness;
    }
}
