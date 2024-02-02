<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Properties\Base\BaseLastProcessedDailyValueProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Types\TimeHelper;
use App\Variables\QMUserVariable;
use Illuminate\Database\Query\JoinClause;
use LogicException;
class UserVariableLastProcessedDailyValueProperty extends BaseLastProcessedDailyValueProperty
{
    use UserVariableProperty, DailyVariableValueTrait, UserVariableValuePropertyTrait;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @throws AlreadyAnalyzingException
     * @throws TooSlowToAnalyzeException
     * @throws UserVariableNotFoundException
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\DuplicateFailedAnalysisException
     * @throws \App\Exceptions\InsufficientVarianceException
     * @throws \App\Exceptions\ModelValidationException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\NotEnoughMeasurementsForCorrelationException
     * @throws \App\Exceptions\StupidVariableNameException
     */
    public static function updateAll(){
        $rows = QMUserVariableRelationship::readonly()
            ->select([
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_USER_ID,
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID,
                UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID,
            ])
            ->leftJoin(UserVariable::TABLE, static function (JoinClause $join) {
                $join->on(UserVariableRelationship::TABLE . '.cause_variable_id', '=', UserVariable::TABLE . '.variable_id')
                    ->on(UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_USER_ID, '=',
                        UserVariable::TABLE . '.' . UserVariable::FIELD_USER_ID);
            })
            //->where(UserVariable::FIELD_NUMBER_OF_CORRELATIONS, ">", 0)
            ->whereNull(UserVariable::TABLE . '.' . UserVariable::FIELD_LAST_PROCESSED_DAILY_VALUE)
            ->getArray();
        $byUser = [];
        foreach ($rows as $row) {
            $byUser[$row->user_id][] = $row;
        }
        foreach ($rows as $row) {
            $c = QMUserVariableRelationship::getExistingUserVariableRelationshipByVariableIds($row->user_id, $row->cause_variable_id, $row->effect_variable_id);
            $v = QMUserVariable::getByNameOrId($row->user_id, $row->cause_variable_id);
            $v->analyzeFully(__FUNCTION__, true);
            if ($v->lastProcessedDailyValue === null) {
                throw new LogicException($v);
            }
            $c->analyzeFully(__FUNCTION__);
        }
    }
    /**
     * @param UserVariable $model
     * @return float
     */
    public static function calculate($model): ?float{
        if ($last = $model->getLastDailyMeasurementWithTagsAndFilling()) {
            $model->lastProcessedDailyValueAt = $last->getStartAt();
            $value = $model->lastProcessedDailyValueInCommonUnit = $last->value;
        } else {
            $model->lastProcessedDailyValueAt = null;
            $value = null;
        }
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
    /**
     * @param string $lastValueAndUnit
     * @param string $variableDisplayName
     * @param string|int $lastProcessedDailyValueAt
     * @return string
     */
    public static function getLastDailyValueSentenceStatic(string $lastValueAndUnit,
                                                           string $variableDisplayName,
                                                           $lastProcessedDailyValueAt = null): string{
        $sentence = "Your last $variableDisplayName recording was $lastValueAndUnit";
        if($lastProcessedDailyValueAt){
            $sentence .= " ".TimeHelper::timeSinceHumanString($lastProcessedDailyValueAt);
        }
        return $sentence.". ";
    }
}
