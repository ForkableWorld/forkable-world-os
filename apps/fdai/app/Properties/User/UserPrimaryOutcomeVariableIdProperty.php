<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\ClientNotFoundException;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BasePrimaryOutcomeVariableIdProperty;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\DB\QMQB;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserProperty;
use App\Types\QMArr;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Illuminate\Support\Arr;
class UserPrimaryOutcomeVariableIdProperty extends BasePrimaryOutcomeVariableIdProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
    use IsCalculated;
    /**
     * @param User $model
     * @return QMUserVariable
     */
    public static function calculate($model): QMUserVariable{
        $qmu = $model->getQMUser();
        $v = self::getPrimaryOutcomeFromReminders($qmu);
        if (!$v || $v->getNumberOfMeasurements() < 30) {
            $v = self::getPrimaryOutcomeFromVariablesTable($qmu);
        }
        if(!$v){
            try {
	            $app = $qmu->getAppSettingsForClientThatCreated();
                $searchResult = $app->getPrimaryOutcomeVariable();
                $v = $searchResult->findQMUserVariable($qmu->getId());
            } catch (ClientNotFoundException $e) {
                $qmu->logError(__METHOD__.": ".$e->getMessage());
            }
        }
        if(!$v){
            $v = OverallMoodCommonVariable::instance()->findQMUserVariable($qmu->getId());
            $qmu->logError("Could not determine primary outcome variable so using $v->name");
            $qmu->primaryOutcomeVariableId = $v->getVariableIdAttribute();
            return $v;  // Don't save fallback id so that we allow a better one to be saved later
        }
        self::updatePrimaryOutcomeVariableIdNameAndCreateReminder($v->getVariableIdAttribute(), $qmu);
        return $v;
    }
    /**
     * @param QMUser $qmu
     * @return QMUserVariable
     */
    public static function getPrimaryOutcomeFromVariablesTable(QMUser $qmu): ?QMUserVariable{
        //$qmu = $this;
        $qb = self::outcomeUserVariableQb($qmu);
        $qb->whereIn(Variable::TABLE.
            '.'.
            Variable::FIELD_VARIABLE_CATEGORY_ID,
            [
                QMVariableCategory::getSymptoms()->getId(),
                // We want to give preference to symptoms and emotions over
                QMVariableCategory::getEmotions()->getId()
                // Rescuetime and Fitbit generated data
            ])->whereIn(Variable::TABLE.
            '.'.
           Variable::FIELD_DEFAULT_UNIT_ID,
            [
                QMUnit::getPercent()->getId(),
                QMUnit::getOneToFiveRating()->getId()
            ]);
        /** @var QMUserVariable $row */
        $row = $qb->first();
        if(!$row || $row->numberOfMeasurements < 30){  // Fallback to allow Fitbit and Rescuetime outcomes
            $qb = self::outcomeUserVariableQb($qmu);
            $qb->whereIn(Variable::TABLE.
                '.'.
                Variable::FIELD_DEFAULT_UNIT_ID,
                [
                    QMUnit::getPercent()->getId(),
                    QMUnit::getOneToFiveRating()->getId()
                ]);
            $row = $qb->first();
        }
        if(!$row){
            return null;
        }
        if($row->userId !== $qmu->getId()){
            le("No user id!");
        }
        $v =
            QMUserVariable::findUserVariableByNameIdOrSynonym($row->userId,
                $row->variableId);
        return $v;
    }
    /**
     * @param QMUser $qmu
     * @return QMQB
     */
    public static function outcomeUserVariableQb(QMUser $qmu): QMQB{
        $qb =
            GetUserVariableRequest::qb()
                ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_USER_ID, '=', $qmu->id)
                ->where(Variable::TABLE.'.'. Variable::FIELD_OUTCOME, '=', 1)
                ->orderBy(UserVariable::TABLE.'.'.UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, 'desc');
        return $qb;
    }
    /**
     * @param int $primaryOutcomeVariableId
     * @param QMUser $qmu
     * @return int
     */
    public static function updatePrimaryOutcomeVariableIdNameAndCreateReminder(int $primaryOutcomeVariableId,
                                                                               QMUser $qmu): int{
        if($qmu->primaryOutcomeVariableId === $primaryOutcomeVariableId){
            $qmu->logInfo("Primary outcome was already $primaryOutcomeVariableId");
            return 0;
        }
        $result = $qmu->updateDbRow([User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => $primaryOutcomeVariableId]);
        $qmu->getOrSetPrimaryOutcomeVariableNameFromGlobals(); // Must be done afterwards
        $v = UserVariable::findOrCreateByNameOrId($qmu->getId(), $primaryOutcomeVariableId);
        if($v->getManualTracking()){
            $v->getOrCreateTrackingReminder();
        }
        return $result;
    }
    /**
     * @param QMUser $qmu
     * @return QMUserVariable
     */
    public static function getPrimaryOutcomeFromReminders(QMUser $qmu): ?QMUserVariable{
        $reminders = $qmu->getTrackingReminders();
        if(!$reminders->count()){
            return null;
        }
        $reminders = Arr::where($reminders->all(),
            static function($reminder){
                /** @var QMTrackingReminder $reminder */
                return $reminder->isRating();
            });
        if(!$reminders){
            return null;
        }
        QMArr::sortDescending($reminders, 'numberOfRawMeasurements');
        $most = $reminders[0]->getQMUserVariable();
        return $most;
    }
}
