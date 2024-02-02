<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\Unit;
use App\Models\UserVariable;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipNumberOfCorrelationsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseChangesProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfRawMeasurementsProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Storage\DB\Writable;
use App\Variables\QMUserVariable;
use Exception;
class GlobalVariableRelationshipsCleanUpJobTest extends JobTestCase {
    public function testCalculateUserVariableRelationships(){
        GlobalVariableRelationshipNumberOfCorrelationsProperty::updateAll();
        GlobalVariableRelationshipEffectFollowUpPercentChangeFromBaselineProperty::analyzeWhereNull();
        //GlobalVariableRelationshipNumberOfCorrelationsProperty::fixInvalidRecords();
    }
    public function testDeleteGlobalVariableRelationshipsWithoutUserVariableRelationships() {
        GlobalVariableRelationshipNumberOfCorrelationsProperty::fixInvalidRecords();
        $aggCorrRows = GlobalVariableRelationshipsCleanUpJobTest::getAggregateRowsWithoutUserVariableRelationships();
        QMLog::count($aggCorrRows, "global variable relationships without user variable relationships");
        foreach ($aggCorrRows as $row) {
            $c = QMGlobalVariableRelationship::getByNamesOrIds($row->cause_variable_id, $row->effect_variable_id);
            $message = "No user variable relationships!";
            $c->logInfo($message);
            $c->hardDelete($message);
        }
    }
    public function testDeleteWebsiteGlobalVariableRelationshipsWithoutUserVariableRelationships(){
        $aggCorrRows = GlobalVariableRelationshipsCleanUpJobTest::getAggregateRowsWithoutUserVariableRelationships();
        $total = count($aggCorrRows);
        $i = 0;
        foreach ($aggCorrRows as $aggCorrRow){
            $i++;
            QMLog::infoWithoutContext("$i of $total");
            $c = QMGlobalVariableRelationship::instantiateIfNecessary($aggCorrRow);
            $c->logInfo("");
            $cause = $c->getOrSetCauseQMVariable();
            if($cause->isAppOrWebsite()){
                $c->hardDeleteWithRelations("Cause is $cause->name");
                continue;
            }
            if (stripos($cause->name, VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX) === 0) {
                $c->hardDeleteWithRelations("Cause is $cause->name");
                continue;
            }
            $effect = $c->getOrSetEffectQMVariable();
            if(!$effect->isOutcome()){
                $c->hardDeleteWithRelations("$effect is not an outcome");
                continue;
            }
        }
    }
    public function testCreateUserVariableRelationshipForOrphanAggregates(){
        $aggCorrRows = GlobalVariableRelationshipsCleanUpJobTest::getAggregateRowsWithoutUserVariableRelationships();
        GlobalVariableRelationshipsCleanUpJobTest::createUserVariableRelationshipForOrphanAggregates($aggCorrRows);
    }
    public static function fixAggregatedCorrelationsWithPredictorValueOutsideAllowedRangeForVariable(){
        QMLog::infoWithoutContext('=== '.__FUNCTION__.' ===');
        self::fixForFieldTooBigOrSmallForVariable(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME, 'small');
        self::fixForFieldTooBigOrSmallForVariable(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME, 'big');
        self::fixForFieldTooBigOrSmallForVariable(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME, 'small');
        self::fixForFieldTooBigOrSmallForVariable(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME, 'big');
        self::fixForFieldTooBigOrSmallForUnit(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME, 'small');
        self::fixForFieldTooBigOrSmallForUnit(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_HIGH_OUTCOME, 'big');
        self::fixForFieldTooBigOrSmallForUnit(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME, 'small');
        self::fixForFieldTooBigOrSmallForUnit(GlobalVariableRelationship::FIELD_VALUE_PREDICTING_LOW_OUTCOME, 'big');
    }
    /**
     * @param $field
     * @param $smallOrBig
     */
    public static function fixForFieldTooBigOrSmallForUnit($field, $smallOrBig){
        $qb = QMGlobalVariableRelationship::readonly()
            ->join(Variable::TABLE, Variable::TABLE.'.'.Variable::FIELD_ID, '=', GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID)
            ->join(Unit::TABLE, Unit::TABLE.'.'.Unit::FIELD_ID, '=', Variable::TABLE.'.'.Variable::FIELD_DEFAULT_UNIT_ID)
            ->whereNotNull(GlobalVariableRelationship::TABLE.'.'.$field);
        if($smallOrBig === 'small'){
            $qb->where(GlobalVariableRelationship::TABLE.'.'.$field, '<', Unit::TABLE.'.'.Unit::FIELD_MINIMUM_VALUE)
                ->whereNotNull(Unit::TABLE.'.'.Unit::FIELD_MINIMUM_VALUE);
        }else{
            $qb->where(GlobalVariableRelationship::TABLE.'.'.$field, '>', Unit::TABLE.'.'.Unit::FIELD_MAXIMUM_VALUE)
                ->whereNotNull(Unit::TABLE.'.'.Unit::FIELD_MAXIMUM_VALUE);
        }
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " global variable relationships have too $smallOrBig $field for unit");
            foreach($rows as $row){
                self::recalculateAndRecheckForPair($row->cause_variable_id, $row->effect_variable_id);
            }
        }
    }
    /**
     * @param $field
     * @param $smallOrBig
     */
    public static function fixForFieldTooBigOrSmallForVariable($field, $smallOrBig){
        QMLog::infoWithoutContext("=== Fixing $field too $smallOrBig ===", false);
        $qb = QMGlobalVariableRelationship::readonly()
            ->join(Variable::TABLE, Variable::TABLE.'.'.Variable::FIELD_ID, '=', GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID)
            ->whereNotNull(GlobalVariableRelationship::TABLE.'.'.$field);
        if($smallOrBig === 'small'){
            $qb->where(GlobalVariableRelationship::TABLE.'.'.$field, '<', Variable::TABLE.'.'.Variable::FIELD_MINIMUM_ALLOWED_VALUE)
                ->whereNotNull(Variable::TABLE.'.'.Variable::FIELD_MINIMUM_ALLOWED_VALUE);
        }else{
            $qb->where(GlobalVariableRelationship::TABLE.'.'.$field, '>', Variable::TABLE.'.'.Variable::FIELD_MAXIMUM_ALLOWED_VALUE)
                ->whereNotNull(Variable::TABLE.'.'.Variable::FIELD_MAXIMUM_ALLOWED_VALUE);
        }
        $rows = $qb->getArray();
        if($rows){
            QMLog::error(count($rows) . " global variable relationships have too $smallOrBig $field for variable");
            foreach($rows as $row){
                try {
                    self::recalculateAndRecheckForPair($row->cause_variable_id, $row->effect_variable_id);
                } catch (Exception $exception) {
                    QMLog::error($exception->getMessage(), []);
                    QMGlobalVariableRelationship::deleteByVariableIds($row->cause_variable_id, $row->effect_variable_id);
                }
            }
        }

    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @throws AlreadyAnalyzingException
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws TooSlowToAnalyzeException
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\DuplicateFailedAnalysisException
     * @throws \App\Exceptions\NotEnoughDataException
     */
    public static function recalculateAndRecheckForPair(int $causeVariableId, int $effectVariableId){
        /** @var QMGlobalVariableRelationship $ac */
        $ac = QMGlobalVariableRelationship::getOrCreateByIds($causeVariableId, $effectVariableId);
        if(!$ac->getOrSetCauseQMVariable()){
            QMLog::error("Could not find variable id $causeVariableId!");
            return;
        }
        $ac->getOrSetCauseQMVariable()->recalculateAllCorrelationsWithInvalidValues();
        $ac->analyzeFullyAndSave(__FUNCTION__);
    }
    /**
     * @return array
     */
    private static function getAggregateRowsWithoutUserVariableRelationships(): array{
        $aggCorrRows = Writable::selectStatic("
            select ac.cause_variable_id,
                   ac.effect_variable_id,
                   cv.name as causeName,
                   ev.name as effectName
            from global_variable_relationships ac
            left join user_variable_relationships c on
                (ac.cause_variable_id = c.cause_variable_id and ac.effect_variable_id = c.effect_variable_id)
            join variables cv on cv.id = ac.cause_variable_id
            join variables ev on ev.id = ac.effect_variable_id
            where c.cause_variable_id is null");
        QMLog::count($aggCorrRows, "global variable relationships with no user variable relationships");
        return $aggCorrRows;
    }
    /**
     * @param $aggCorrRows
     * @throws TooSlowToAnalyzeException
     */
    private static function createUserVariableRelationshipForOrphanAggregates($aggCorrRows): void{
        $i = 0;
        $total = count($aggCorrRows);
        foreach ($aggCorrRows as $aggCorrRow) {
            $c = QMGlobalVariableRelationship::instantiateIfNecessary($aggCorrRow);
            $c->logInfo("");
            $i++;
            QMLog::infoWithoutContext("$i of $total");
            $causeId = $aggCorrRow->cause_variable_id;
            $effectId = $aggCorrRow->effect_variable_id;
            $t = UserVariable::TABLE;
            $userIds = QMUserVariable::readonly()
                ->where(UserVariable::FIELD_VARIABLE_ID, $effectId)
                ->where($t .'.'.UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, '>', CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, '>', CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_CHANGES, '>', CorrelationCauseChangesProperty::MINIMUM_CHANGES - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, '>', 1)
                ->pluck(UserVariable::FIELD_USER_ID)
                ->toArray();
            /** @var QMUserVariable[] $causeRows */
            $causeRows = QMUserVariable::readonly()
                ->where(UserVariable::FIELD_VARIABLE_ID, $causeId)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN, '>', CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, '>', CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_CHANGES, '>', CorrelationCauseChangesProperty::MINIMUM_CHANGES - 1)
                ->where($t.'.'.UserVariable::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES, '>', 1)
                ->getDBModels();
            foreach ($causeRows as $cause) {
                $userId = $cause->getUserId();
                if($userId == 1){continue;}
                if (!in_array($userId, $userIds)) {
                    continue;
                }
                try {
                    $effect = QMUserVariable::getByNameOrId($userId, $effectId);
                } catch (UserVariableNotFoundException $e) {
                    QMLog::infoWithoutContext("Effect " . $e->getMessage());
                    continue;
                }
                try {
                    $c = new QMUserVariableRelationship(null, $cause, $effect);
                    $c->analyzeFully(__FUNCTION__);
                    //continue;
                } catch (InsufficientVarianceException $e) {
                    QMLog::infoWithoutContext(__METHOD__.": ".$e->getMessage());
                    continue;
                } catch (NotEnoughMeasurementsForCorrelationException $e) {
                    QMLog::infoWithoutContext(__METHOD__.": ".$e->getMessage());
                    continue;
                }
            }
        }
    }
}
