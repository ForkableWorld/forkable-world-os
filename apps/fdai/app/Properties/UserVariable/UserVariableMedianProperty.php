<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Exceptions\InvalidAttributeException;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMedianProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Utils\Stats;
class UserVariableMedianProperty extends BaseMedianProperty
{
    use UserVariableProperty, DailyVariableValueTrait, UserVariableValuePropertyTrait;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        // TODO: Rename this to median daily value instead of ambiguous median:  $this->validateBetweenMinMaxRecorded($this->getValue());
    }
    /**
     * @param UserVariable $model
     * @return float
     * @throws InvalidAttributeException
     */
    public static function calculate($model){
        $uv = $model->getDBModel();
        if($values = $uv->getDailyValuesWithTagsAndFilling()){
            $common = $uv->medianInCommonUnit = Stats::median($values, 5);
            static::validateByValue($common, $model);
            $uv->convertValuesToUserUnit();
        } else {
            $common = null;
        }
        return $common;
    }
}
