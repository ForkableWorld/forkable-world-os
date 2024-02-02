<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Exceptions\InvalidAttributeException;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseEarliestFillingTimeProperty;
use App\Types\TimeHelper;
use App\Variables\QMUserVariable;
class UserVariableEarliestFillingTimeProperty extends BaseEarliestFillingTimeProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
	/**
     * @param QMUserVariable $dbm
     * @return int|null
     * @throws InvalidAttributeException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($dbm): ?int{
        $at = static::calculateAt($dbm);
        $time = ($at) ? strtotime($at) : null;
        $dbm->validateAttribute(static::NAME, $time);
        return $dbm->setAttribute(static::NAME, $time);
    }
    /**
     * @param QMUserVariable $dbm
     * @return string
     */
    public static function calculateAt($dbm): ?string {
        $month = TimeHelper::DAY * 30;
        $l = $dbm->l();
        $earliestTagged = $dbm->getEarliestTaggedMeasurementAt();
        if(!$earliestTagged){return $dbm->earliestFillingAt = null;}
        if(!$dbm->hasFillingValue()){return $dbm->earliestFillingAt = $earliestTagged;}
        $earliestMeasurementMinusMonthTime = time_or_exception($earliestTagged) - $month;
        $earliestMeasurementMinusMonthAt = db_date($earliestMeasurementMinusMonthTime);
        $earliestSourceAt = $l->getEarliestSourceMeasurementStartAtAttribute();
        if($earliestSourceAt > $earliestTagged){ // Happens due to rounding sometimes
            $earliestSourceAt = $earliestTagged;
        }
        if(!$earliestSourceAt){ // It's probably a tag generated variable with no sources
            return $earliestSourceAt = $earliestTagged;
        }
        if($earliestMeasurementMinusMonthTime > time_or_exception($earliestSourceAt)){
            $earliestFillingAt = $earliestMeasurementMinusMonthAt;
        }else{
            $earliestFillingAt = $earliestSourceAt;
        }
		$dbm->l()->earliest_filling_time = time_or_null($earliestFillingAt);
        return $dbm->earliestFillingAt = $earliestFillingAt;
    }
}
