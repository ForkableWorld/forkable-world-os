<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\DevOps\XDebug;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseMeanProperty;
use App\Properties\UserVariable\UserVariableDefaultUnitIdProperty;
use App\Properties\UserVariable\UserVariableEarliestTaggedMeasurementStartAtProperty;
use App\Properties\UserVariable\UserVariableExperimentEndTimeProperty;
use App\Properties\UserVariable\UserVariableFillingValueProperty;
use App\Properties\UserVariable\UserVariableLatestTaggedMeasurementStartAtProperty;
use App\Properties\UserVariable\UserVariableMeanProperty;
use App\Properties\UserVariable\UserVariableMinimumRecordedValueProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\DB\QMQB;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\EnvOverride;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Throwable;
class UserVariableCleanupJobTest extends JobTestCase {
    public function testUserVariableCleanup(){
        UserVariableLatestTaggedMeasurementStartAtProperty::fixNulls();
        UserVariableEarliestTaggedMeasurementStartAtProperty::fixNulls();
        UserVariableExperimentEndTimeProperty::fixInvalidRecords();
        UserVariableFillingValueProperty::fixInvalidRecords();
        //self::deleteUserVariablesWithNoMeasurementsOrReminders();
        return;
        UserVariableDefaultUnitIdProperty::fixIncompatibleUnits();
        self::fixValuesGreaterThanMaximumForVariable();
        self::fixValuesGreaterThanMaximumForUnit();
        UserVariableCleanupJobTest::deleteMinMaxAllowedUserVariablesValuesOutsideUnitRange();
        BaseMeanProperty::fixTooBig();
        UserVariableMeanProperty::fixTooSmall();
        UserVariableMinimumRecordedValueProperty::fixVariablesWithMinRecordedTooSmallForUnit();
    }
    public function testGeneralCleanup(){
        UserVariable::fixInvalidRecords();
        Variable::fixInvalidRecords();
    }
    public function testFixUserVariablesWithValuesBelowMinimum() {
        $qb = GetUserVariableRequest::qb();
        $qb->whereNotNull(Variable::TABLE . '.' . Variable::FIELD_MINIMUM_ALLOWED_VALUE);
        $qb->whereNotNull(UserVariable::TABLE . '.' . UserVariable::FIELD_MINIMUM_RECORDED_VALUE);
        $qb->whereRaw(UserVariable::TABLE .
            '.' .
            UserVariable::FIELD_MINIMUM_RECORDED_VALUE .
            " < " .
            Variable::TABLE .
            '.' .
            Variable::FIELD_MINIMUM_ALLOWED_VALUE);
        $rows = $qb->getArray();
        $variables = QMUserVariable::instantiateNonDBRows($rows);
        foreach ($variables as $v) {
            $min = $v->minimumAllowedValueInCommonUnit;
            $v->logInfo("Min is $min");
            $result =
                QMMeasurement::writable()
                    ->where(Measurement::FIELD_VARIABLE_ID, $v->getVariableIdAttribute())
                    ->where(Measurement::FIELD_VALUE, "<", $min)
                    ->hardDelete("to small");
            $v->forceAnalyze(__FUNCTION__);
        }
    }
    public static function deleteUserVariablesWithNoMeasurementsOrReminders(){
        $qb = QMUserVariable::qb()
            ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS, "<", 1)
            ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS, "<", 1)
            ->where(UserVariable::TABLE.'.'.UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, "<", 1)
            ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_USER_ID, ">", 1)
            //->where(UserVariable::FIELD_STATUS, "<>", UserVariable::STATUS_ANALYZING)K
            //->where(UserVariable::FIELD_USER_ID, 2)
            ;
        $count = $qb->count();
        $rows = $qb->get();
        QMLog::table($rows, __METHOD__);
        QMLog::infoWithoutObfuscation("Got ".count($rows));
        foreach ($rows as $row){
            $v = QMUserVariable::getByNameOrId($row->user_id, $row->variable_id);
            $v->logInfo("");
            try {
                $v->analyzeFully(__FUNCTION__);
                $last = $v->lastProcessedDailyValue;
                if ($last === null) {
                    $v->logError("No lastProcessedDailyValue");
                }
            }  catch (AlreadyAnalyzingException $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
            } catch (Throwable $e) {
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
    }
    public static function fixTimesBefore2000(){
        QMLog::infoWithoutContext('=== '.__FUNCTION__.' ===');
        $fields = QMUserVariable::getUnixTimeFields();
        foreach($fields as $field){
            $rows = QMUserVariable::readonly()->where($field, '<', TimeHelper::YEAR_2000_UNIXTIME)->whereNotNull($field)->getArray();
            $message = count($rows)." user variables with $field before 2000";
            if(count($rows)){
                QMLog::error($message);
            }else{
                QMLog::info($message);
            }
            foreach($rows as $row){
                $userVariable = QMUserVariable::getByNameOrId($row->user_id, $row->variable_id);
                $userVariable->forceAnalyze("$field is ".date('Y-m-d H:i:s', $row->$field));
            }
        }
    }
    public function testDeleteAllUserMinMaxValues(){
        UserVariableCleanupJobTest::deleteUserMinMax();
    }
    private static function deleteMinMaxAllowedUserVariablesValuesOutsideUnitRange(){
        QMLog::infoWithoutContext('=== '.__FUNCTION__.' ===');
        $result = GetUserVariableRequest::qb()
            ->join(QMUnit::TABLE, QMUnit::TABLE.'.'.QMUnit::FIELD_ID, '=', Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID)
            ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_MINIMUM_ALLOWED_VALUE, '<', QMUnit::TABLE.'.'.QMUnit::FIELD_MINIMUM_VALUE)
            ->whereNotNull(QMUnit::TABLE.'.'.QMUnit::FIELD_MINIMUM_VALUE)
            ->whereNotNull(UserVariable::TABLE.'.'. UserVariable::FIELD_MINIMUM_ALLOWED_VALUE)
            ->update([UserVariable::TABLE.'.'. UserVariable::FIELD_MINIMUM_ALLOWED_VALUE => null]);
        QMLog::infoWithoutContext("Fixed $result variables with too small MINIMUM_ALLOWED_VALUE");
        $result = GetUserVariableRequest::qb()
            ->where(UserVariable::TABLE.'.'. UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE, '>', QMUnit::TABLE.'.'.QMUnit::FIELD_MAXIMUM_VALUE)
            ->whereNotNull(QMUnit::TABLE.'.'.QMUnit::FIELD_MAXIMUM_VALUE)
            ->whereNotNull(UserVariable::TABLE.'.'. UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)
            ->update([UserVariable::TABLE.'.'. UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE => null]);
        QMLog::infoWithoutContext("Fixed $result variables with too big MAXIMUM_ALLOWED_VALUE");
    }
    public function testAnalyzeUserVariablesWithPropertiesOutsideAllowedRange(){
        QMLog::infoWithoutContext("=== ".__FUNCTION__." ===");
        if(!EnvOverride::isLocal()){
            $this->assertTrue(true);
            return;
        }
        $userVariableFields = QMUserVariable::getColumns();
        foreach(QMUnit::getUnits() as $unit){
            $max = $unit->getMaximumRawValue();
            if($max !== null){
                foreach($userVariableFields as $field){
                    if(stripos($field, 'original') !== false){
                        continue;
                    }
                    if(stripos($field, '_values') !== false){
                        continue;
                    }
                    if(stripos($field, 'message') !== false){
                        continue;
                    }
                    if(stripos($field, '_value') !== false){
                        $qb = self::commonUserVariableQB();
                        $qb->columns[] = UserVariable::TABLE.'.'.$field;
                        $rows = $qb->where(Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID, $unit->id)
                            ->where(UserVariable::TABLE.'.'.$field, '>', $max)
                            ->getArray();
                        if($rows){
                            foreach($rows as $row){
                                $value = $row->$field;
                                $userVariable = QMUserVariable::getByNameOrId($row->user_id, $row->variable_id);
                                if(!$userVariable){
                                    QMLog::error("Could not get user variable for user $row->user_id and variable $row->variable_id");
                                    continue;
                                }
                                $userVariable->forceAnalyze($field." is ".$value." but unit is $unit->name");
                                $afterAnalysis = $userVariable->getPropertyValueByDbFieldName($field);
                                if($afterAnalysis > $max){
                                    le("Still $afterAnalysis");
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * @return QMQB
     */
    private static function commonUserVariableQB(): QMQB{
        return QMUserVariable::readonly()->select([
                UserVariable::TABLE.'.'. UserVariable::FIELD_VARIABLE_ID,
                UserVariable::TABLE.'.'. UserVariable::FIELD_USER_ID
            ])->join(Variable::TABLE, Variable::TABLE.'.'. Variable::FIELD_ID, '=', UserVariable::TABLE.'.'.
	        UserVariable::FIELD_VARIABLE_ID);
    }
    private static function fixValuesGreaterThanMaximumForUnit(){
        QMLog::infoWithoutContext("=== ".__FUNCTION__." ===");
        $valueFields = QMUserVariable::getCalculatedRawValueFields();
        foreach(QMUnit::getUnits() as $unit){
            if($max = $unit->getMaximumRawValue()){
                foreach($valueFields as $valueField){
                    $rows = GetUserVariableRequest::qb()
                        ->where(UserVariable::TABLE.'.'.$valueField, '>', $max)
                        ->where(UserVariable::TABLE.'.'.$valueField, '<>', -1)
                        ->where(Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID, $unit->getId())
                        ->getArray();
                    if($rows){
                        foreach($rows as $row){
                            self::reAnalyzedAndCheckUserVariable($valueField, $row, $max);
                        }
                    }
                }
            }
        }
    }
    private static function fixValuesGreaterThanMaximumForVariable(){
        $number = QMCommonVariable::writable()
            ->join(QMUnit::TABLE, QMUnit::TABLE.'.'.QMUnit::FIELD_ID, '=', Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID)
            ->whereRaw(QMUnit::TABLE.'.'.QMUnit::FIELD_MAXIMUM_VALUE.' = '. Variable::TABLE.'.'. Variable::FIELD_MAXIMUM_ALLOWED_VALUE)
            ->update([Variable::TABLE.'.'. Variable::FIELD_MAXIMUM_ALLOWED_VALUE => null]);
        QMLog::infoWithoutContext("=== ".__FUNCTION__." ===");
        $rows = QMCommonVariable::readonly()
            ->whereNotNull(Variable::FIELD_MAXIMUM_ALLOWED_VALUE)
            ->whereNull(Variable::FIELD_DELETED_AT)
            ->getArray();
        $valueFields = QMUserVariable::getCalculatedRawValueFields();
        foreach($rows as $row){
            if($max = $row->maximum_allowed_value){
                foreach($valueFields as $valueField){
                    $rows = GetUserVariableRequest::qb()
                        ->where(UserVariable::TABLE.'.'.$valueField, '>', $max)
                        ->where(UserVariable::TABLE.'.'.$valueField, '<>', -1)
                        ->where(Variable::TABLE.'.'. Variable::FIELD_ID, $row->id)
                        ->getArray();
                    if($rows){
                        foreach($rows as $row){
                            self::reAnalyzedAndCheckUserVariable($valueField, $row, $max);
                        }
                    }
                }
            }
        }
    }
    public static function fixValuesLessThanMinimumForUnit(){
        QMLog::infoWithoutContext("=== ".__FUNCTION__." ===");
        $valueFields = QMUserVariable::getCalculatedRawValueFields();
        foreach(QMUnit::getUnits() as $unit){
            if($minimumValue = $unit->getMinimumValue()){
                foreach($valueFields as $valueField){
                    $camel = QMStr::camelize($valueField);
                    $rows = GetUserVariableRequest::qb()
                        ->where(UserVariable::TABLE.'.'.$valueField, '<', $minimumValue)
                        ->where(UserVariable::TABLE.'.'.$valueField, '<>', -1)
                        ->whereNotNull(UserVariable::TABLE.'.'.$valueField)
                        ->where(Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID, $unit->getId())
                        ->getArray();
                    if($rows){
                        foreach($rows as $row){
                            if(!isset($row->$camel)){$camel .= 'InCommonUnit';}
                            $old = $row->$camel;
                            $variable = QMUserVariable::getByNameOrId($row->userId, $row->id);
                            $error = "$camel $old is less than $minimumValue";
                            $variable->forceAnalyze($error);
                            $new = $variable->getProtectedFieldValue($camel);
                            if($new !== null && $new < $minimumValue){
                                $variable->forceAnalyze($error);
                                le("Was not able to fix $error");
                            }
                            Memory::resetClearOrDeleteAll();
                            $variable = QMUserVariable::getByNameOrId($row->userId, $row->id);
                            $new = $variable->getProtectedFieldValue($camel);
                            if($new !== null && $new < $minimumValue){
                                $variable->forceAnalyze($error);
                                le("Was not able to fix $error");
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * @param string $valueField
     * @param QMUserVariable $row
     * @param $max
     * @throws UserVariableNotFoundException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    private static function reAnalyzedAndCheckUserVariable(string $valueField, $row, $max): void{
        $camel = QMStr::camelize($valueField);
        if (!isset($row->$camel)) {
            $camel .= 'InCommonUnit';
        }
        $old = $row->$camel;
        $variable = QMUserVariable::getByNameOrId($row->userId, $row->variableId);
        $unit = $variable->getCommonUnit();
        $variable->forceAnalyze("$valueField $old $unit->abbreviatedName is greater than $max $unit->abbreviatedName");
        $new = $variable->getProtectedFieldValue($camel);
        $error = "$valueField $old is greater than $max";
        if ($new > $max) {
            $variable->forceAnalyze($error);
            le("Was not able to fix $error");
        }
        Memory::resetClearOrDeleteAll();
        $variable = QMUserVariable::getByNameOrId($row->userId, $row->variableId);
        $new = $variable->getProtectedFieldValue($camel);
        if ($new > $max) {
            $variable->forceAnalyze($error);
            le("Was not able to fix $error");
        }
    }
    public static function deleteUserMinMax(): void{
        $result =
            QMUserVariable::writable()
                ->whereNotNull(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE)
                ->update([UserVariable::FIELD_MINIMUM_ALLOWED_VALUE => null,]);
        $result =
            QMUserVariable::writable()
                ->whereNotNull(UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE)
                ->update([UserVariable::FIELD_MAXIMUM_ALLOWED_VALUE => null]);
    }
    public static function deleteUserMinMaxWhereEqualsCommonVariable(): void{
        $fields = QMVariable::getAnalysisSettingsFields();
        foreach($fields as $field){
            $count = QMUserVariable::qb()
                ->whereNotNull(UserVariable::TABLE.'.'.$field)
                ->whereRaw(UserVariable::TABLE.'.'.$field. ' = '. Variable::TABLE.'.'.$field)
                ->count();
            if($count){
                QMLog::error("$count user variables with the same $field as common variable!");
            }
        }
        if(XDebug::active()){
            foreach($fields as $field){
                $result = QMUserVariable::writable()
                    ->join(Variable::TABLE,
                        UserVariable::TABLE.'.'. UserVariable::FIELD_VARIABLE_ID, '=',
                        Variable::TABLE.'.'. Variable::FIELD_ID)
                    ->whereRaw(UserVariable::TABLE.'.'.$field. ' = '. Variable::TABLE.'.'.$field)
                    ->whereNotNull(UserVariable::TABLE.'.'.$field)
                    ->update([UserVariable::TABLE.'.'.$field => null]);
                QMLog::error("Reset $field to null for $result user variables with the same $field as common variable!");
            }
        }
    }
    public static function fixExperimentEndTimes(){
        UserVariableExperimentEndTimeProperty::fixInvalidRecords();
    }
}
