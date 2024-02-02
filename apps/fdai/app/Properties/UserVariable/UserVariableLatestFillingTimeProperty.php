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
use App\Properties\Base\BaseLatestFillingTimeProperty;
use App\Variables\QMUserVariable;
use App\Slim\View\Request\Pair\GetPairRequest;
class UserVariableLatestFillingTimeProperty extends BaseLatestFillingTimeProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param QMUserVariable $dbm
     * @return int|null
     */
    public static function calculate($dbm){
        if($measurements = $dbm->getMeasurementsWithTags()){
            $at = static::calculateAt($dbm);
            $time = ($at) ? strtotime($at) : null;
        } else {
            $time = null;
        }
        return $dbm->setAttribute(static::NAME, $time);
    }
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        $num = $uv->number_of_raw_measurements_with_tags_joins_children;
        return (bool)$num;
    }
    public function setRawAttribute($processed): void{
        parent::setRawAttribute($processed);
    }
    /**
     * @param QMUserVariable $dbm
     * @return string
     */
    public static function calculateAt($dbm): ?string {
        $l = $dbm->l();
        $month = 86400 * 30;
        $latestMeasurementAt = $dbm->getLatestTaggedMeasurementAt();
        if(!$latestMeasurementAt){
            return $dbm->latestFillingAt = null;
        }
        if(!$dbm->hasFillingValue()){
            return $dbm->latestFillingAt = $latestMeasurementAt;
        }
        $latestSourceAt = $l->latest_source_measurement_start_at;
        if($latestSourceAt < $latestMeasurementAt){ // Happens due to rounding sometimes
            $latestSourceAt = $latestMeasurementAt;
        }
        $latestMeasurementPlusMonthTime = time_or_exception($latestMeasurementAt) + $month;
        $latestMeasurementPlusMonthAt = db_date($latestMeasurementPlusMonthTime);
        $latestFillingAt = (time_or_exception($latestSourceAt) > $latestMeasurementPlusMonthTime) ?
            $latestMeasurementPlusMonthAt : $latestSourceAt;
        if(time_or_exception($latestFillingAt) > time()){$latestFillingAt = now_at();}
		if(!$latestFillingAt){le('!$latestFillingAt');}
        return $dbm->latestFillingAt = db_date($latestFillingAt);
    }
}
