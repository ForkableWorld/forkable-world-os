<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\DevOps\XDebug;
use App\Exceptions\QMException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseSynonymsProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Storage\Memory;
use App\Types\QMStr;
use App\Units\CountUnit;
use App\Units\DollarsUnit;
use App\VariableCategories\MiscellaneousVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPagesLikedCommonVariable;
use App\Variables\CommonVariables\SocialInteractionsCommonVariables\FacebookPostsMadeCommonVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
class CommonVariableCleanupJob extends JobTestCase {
    public function testDeletePhysicalActivityProducts(){
        $qb = Variable::whereVariableCategoryId(PhysicalActivityVariableCategory::ID)
            ->whereNotNull(Variable::FIELD_PRODUCT_URL)
            ->whereNull(Variable::FIELD_NUMBER_OF_MEASUREMENTS)
            //->where(Variable::FIELD_DEFAULT_UNIT_ID, CountUnit::ID)
            ->where(Variable::CREATED_AT, "<", db_date("2018-04-05"))
            ->where(Variable::CREATED_AT, ">", db_date("2018-03-29"))
            //->where(Variable::FIELD_INTERNAL_ERROR_MESSAGE, "LIKE", '%No user variables%')
        ;
        $variables = $qb->pluck('name');
        $qb->update([Variable::FIELD_VARIABLE_CATEGORY_ID => MiscellaneousVariableCategory::ID]);
        QMLog::print($variables->all(), "");
        //Variable::logNames($variables);
    }
    public function testFixInvalidNames(){
        VariableNameProperty::fixInvalidRecords();
    }
    public function testCommonVariableCleanup(){
        BaseSynonymsProperty::fixInvalidRecords();
        BaseSynonymsProperty::fixTooLong();
        BaseSynonymsProperty::fixEmptySynonyms();
        BaseSynonymsProperty::fixNulls();
        return;
        CommonVariableCleanupJob::generateHardCodedVariablesForYesNoCount();
        //$v->changeAndConvertToNewDefaultUnitEverywhere(HoursUnit::NAME, 1/60, 0, MinutesUnit::ID);
        VariableNameProperty::replaceVariableNameAndUpdate("Time Asleep", SleepDurationCommonVariable::NAME);
        QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
        $variable = QMCommonVariable::findByNameOrId(1867);
        $variable->deleteMeasurementsOutSideAllowedRange(false, XDebug::active());
        self::fixSpendingVariableTags();
        VariableNameProperty::fixTooShort();
        //RescueTimeConnector::renameRescuetimeVariables();
        //RescueTimeConnector::generateActivitiesHardCodeVariableModels();
        //self::fixValuesGreaterThanMaximumForUnit();
        //self::fixValuesLessThanMinimumForUnit();
        $this->assertTrue(true);
    }
    public static function fixSpendingVariableTags(){
        $rows = Variable::whereVariableCategoryId(PhysicalActivityVariableCategory::ID)
            ->whereLike('name', '%Spending on %')
            ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, "<", 1)
            ->get();
        $variables = [];
        foreach($rows as $row){
            \App\Logging\ConsoleLog::info($row->name);
        }
        foreach($rows as $row){
            $cv = $row->getDBModel();
            $cv->logInfo("");
            $taggedVariables = $cv->getCommonTaggedVariables();
            foreach($taggedVariables as $tagged){
                \App\Logging\ConsoleLog::info("tag $cv => tagged $tagged");
            }
            $tags = $cv->getCommonTagVariables();
            foreach($tags as $tag){
                \App\Logging\ConsoleLog::info("tag $tag => tagged $cv");
            }
        }
    }
    public static function deleteLocationVariablesWithFewMeasurements(){
        //self::renameRescuetimeVariables();
        //UserVariable::whereVariableId(VividDreamsCommonVariable::ID)->update([UserVariable::FIELD_VALENCE => null]);
        $rows = Variable::query()
            ->where(Variable::FIELD_NUMBER_OF_MEASUREMENTS, "<", 1)
            //->whereNotNull(Variable::FIELD_WP_POST_ID)
            ->whereRaw("name like '%Spending on %'")
            ->limit(100)
            ->get();
        $count = $rows->count();
        QMLog::error("Deleting $count location variables");
        QMLog::table($rows);
        foreach($rows as $row){
            $row->hardDeleteWithRelations(__FUNCTION__);
        }
    }
    public function testRenameVariables(){
        $oldNew = [
            "Facebook Likes" => FacebookPagesLikedCommonVariable::NAME,
            "Facebook Posts" => FacebookPostsMadeCommonVariable::NAME
        ];
        foreach($oldNew as $oldName => $newName){
            $oldVariable = QMCommonVariable::findByNameOrId($oldName);
            $newVariable = QMCommonVariable::findByNameOrId($newName);
            $oldVariable->rename($newName, "New name is better?");
        }
    }
    public function testDeleteAllTagsForVariable(){
        $id = -1;
        $v = QMCommonVariable::find($id);
        $v->deleteAllCommonTaggedByMe(__FUNCTION__);
    }
    public function testUpdateConstants(){
        QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
    }
    public function testFixSpendingVariablesThatContainPurchaseInSynonyms(){
        $qb = QMCommonVariable::qb();
        $likeOperator = "LIKE";
        $qb->whereRaw(Variable::TABLE.'.'. Variable::FIELD_NAME. " " . \App\Storage\DB\ReadonlyDB::like() . " '%Spending%'");
        $qb->whereRaw(Variable::TABLE.'.'. Variable::FIELD_SYNONYMS." " . \App\Storage\DB\ReadonlyDB::like() . " '%Purchase%'");
        $rows = $qb->getArray();
        $total = count($rows);
        $i = 0;
        foreach ($rows as $row){
            $i++;
            QMLog::infoWithoutContext("$i of $total");
            $synonyms = str_replace(VariableNameProperty::PURCHASES_OF_VARIABLE_DISPLAY_NAME_PREFIX,
                VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX, $row->synonyms);
            QMCommonVariable::writable()
                ->where(Variable::FIELD_ID, $row->id)
                ->update([Variable::FIELD_SYNONYMS => $synonyms]);
        }
        $after = $qb->getArray();
        $this->assertCount(0, $after);
    }
    public function testChangeOneValuesToPurchasesFromSpending(){
        $qb = QMMeasurement::qb()->whereRaw(Variable::TABLE .
                '.' .
                Variable::FIELD_NAME .
                " " . \App\Storage\DB\ReadonlyDB::like() . " '%" .
            VariableNameProperty::SPENDING_ON_VARIABLE_DISPLAY_NAME_PREFIX .
                "%'")
            ->where(Measurement::TABLE.'.'. Measurement::FIELD_VALUE, 1)
            ->where(Variable::TABLE.'.'. Variable::FIELD_DEFAULT_UNIT_ID, DollarsUnit::ID);
        $rows = $qb->getArray();
        $total = count($rows);
        $i = 0;
        $measurements = QMMeasurement::instantiateArray($rows);
        $toAnalyze = [];
        foreach ($measurements as $m){
            $i++;
            QMLog::infoWithoutContext("$i of $total");
	        $spendingVariable = $m->getQMUserVariable();
            $purchasesVariable = $spendingVariable->getPurchasesVariable();
            $m->changeVariable($purchasesVariable, "name was $m->variableName but was a purchase with value $m->value");
            $toAnalyze[$purchasesVariable->getLogMetaDataSlug()] = $purchasesVariable;
            $toAnalyze[$spendingVariable->getLogMetaDataSlug()] = $purchasesVariable;
        }
        /** @var QMUserVariable $v */
        foreach ($toAnalyze as $v){
            $v->forceAnalyze(__FUNCTION__);
            $numberOfMeasurements = $v->getNumberOfRawMeasurementsWithTagsJoinsChildren();
            if(!$numberOfMeasurements){
                $v->hardDelete("No measurements after updating");
            }
        }
    }
    public function testDeleteStupidBoringVariables() {
        VariableNameProperty::deleteStupidBoringVariables();
    }
    /**
     * @param int $multipleOfStdDev
     * @param int $minimumUsers
     * @return QMCommonVariable[]
     */
    public static function getCommonVariablesWithOutliers(int $multipleOfStdDev = 100, int $minimumUsers = 10): array{
        $rows = QMCommonVariable::getMeasurementJoinedQb()
            ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", $minimumUsers)
            ->whereNull(Measurement::FIELD_DELETED_AT)
            ->whereNotNull(Variable::FIELD_MEAN)
            ->whereNotNull(Variable::FIELD_STANDARD_DEVIATION)
            ->whereRaw(Measurement::FIELD_VALUE.' < '. Variable::FIELD_MEAN.' - '.$multipleOfStdDev.' * '.
	            Variable::FIELD_STANDARD_DEVIATION)
            ->groupBy([Measurement::FIELD_VARIABLE_ID])
            ->limit(100)
            ->getArray();
        QMLog::info(count($rows)." variables have outlier measurements");
        $variables = [];
        foreach($rows as $row){
            $variable = QMCommonVariable::find($row->variable_id);
            QMLog::info($variable->name." has value ".$row->value." ".$variable->getUserOrCommonUnit()->name);
            $variables[] = $variable;
        }
        return $variables;
    }
    public function fixCountPaymentVariables(){
        $rows = QMCommonVariable::readonly()
            ->where(Variable::FIELD_DEFAULT_UNIT_ID, QMUnit::getCount()->id)
            ->where(Variable::FIELD_VARIABLE_CATEGORY_ID, QMVariableCategory::getPayments()->id)
            ->whereNotLike(Variable::FIELD_NAME, "%Purchase%")
            ->whereNotLike(Variable::FIELD_NAME, "%Payment%")
            ->whereNotLike(Variable::FIELD_NAME, "%Order%")
            ->getArray();
        foreach($rows as $row){
            $variable = QMCommonVariable::find($row->id);
            $variable->logInfo("");
        }
    }
    public function fixCountVariablesWithRatingUnitInName(){
        $rows = QMCommonVariable::readonly()
            ->whereLike('name', '%(/5)%')
            ->where(Variable::FIELD_DEFAULT_UNIT_ID, '<>', QMUnit::getOneToFiveRating()->id)
            ->getArray();
        foreach($rows as $row){
            $variable = QMCommonVariable::find($row->id);
            $variable->logInfo("");
        }
    }
    /**
     * @param string $categoryName
     * @param string $clientId
     * @throws QMException
     */
    public static function deleteClientVariablesForCategory(string $categoryName, string $clientId){
        $category = QMVariableCategory::find($categoryName);
        $rows = QMCommonVariable::readonly()
            ->where(Variable::FIELD_CLIENT_ID, $clientId)
            ->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $category->getId())
            ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, '<', 3)
            ->getArray();
        QMLog::info("Deleting ".count($rows)." $clientId variables in $categoryName category");
        foreach($rows as $row){
            $commonVariable = QMCommonVariable::find($row->id);
            $commonVariable->deleteCommonVariableAndAllAssociatedRecords("amazon in $category->name category", true);
        }
    }
    public function testDeleteMeasurementsOutsideAllowedRange(){
        //CommonVariable::updateDatabaseTableFromHardCodedConstants();
        $rows =
            QMCommonVariable::readonly()->whereRaw(Variable::FIELD_MAXIMUM_RECORDED_VALUE .
                    " > " .
                    Variable::FIELD_MAXIMUM_ALLOWED_VALUE)
                ->getArray();
        foreach ($rows as $row) {
            $variable = QMCommonVariable::findByNameOrId($row->id);
            $variable->deleteMeasurementsOutSideAllowedRange(false, XDebug::active());
        }
        $rows =
            QMCommonVariable::readonly()
                ->whereRaw(Variable::FIELD_MINIMUM_RECORDED_VALUE .
                    " < " .
                    Variable::FIELD_MINIMUM_ALLOWED_VALUE .
                    " and " .
                    Variable::FIELD_MINIMUM_RECORDED_VALUE .
                    " is not null  " .
                    " and " .
                    Variable::FIELD_MINIMUM_ALLOWED_VALUE .
                    " is not null  ")
                ->getArray();
        foreach ($rows as $row) {
            $variable = QMCommonVariable::findByNameOrId($row->id);
            $variable->deleteMeasurementsOutSideAllowedRange(false, XDebug::active());
        }
    }
    public static function fixValuesGreaterThanMaximumForUnit(){
        QMLog::infoWithoutContext(__FUNCTION__);
        $valueFields = QMCommonVariable::getCalculatedRawValueFields();
        foreach(QMUnit::getUnits() as $unit){
            if($max = $unit->getMaximumRawValue()){
                $unit->logInfo("Checking $max");
                foreach($valueFields as $valueField){
                    $rows = QMCommonVariable::readonly()
                        ->where($valueField, '>', $max)
                        ->where($valueField, '<>', -1)
                        ->where(Variable::FIELD_DEFAULT_UNIT_ID, $unit->getId())
                        ->getArray();
                    if($rows){
                        foreach($rows as $row){
                            $old = $row->$valueField;
                            $variable = QMCommonVariable::find($row->id);
                            $variable->analyzeFully("$valueField $old is greater than $max");
                            $camel = QMStr::camelize($valueField);
                            $new = $variable->$camel;
                            $error = "$valueField $old is greater than $max";
                            if($new > $max){
                                $variable->analyzeFully($error);
                                le("Was not able to fix $error");
                            }
                            Memory::resetClearOrDeleteAll();
                            $variable = QMCommonVariable::find($row->id);
                            $new = $variable->$camel;
                            if($new > $max){
                                $variable->analyzeFully($error);
                                le("Was not able to fix $error");
                            }
                        }
                    }
                }
            }
        }
    }
    public static function fixValuesLessThanMinimumForUnit(){
        QMLog::infoWithoutContext(__FUNCTION__);
        $valueFields = QMCommonVariable::getCalculatedRawValueFields();
        foreach(QMUnit::getUnits() as $unit){
            if($minimumValue = $unit->getMinimumValue()){
                $unit->logInfo("Checking $minimumValue");
                foreach($valueFields as $valueField){
                    $rows = QMCommonVariable::readonly()
                        ->where($valueField, '<', $minimumValue)
                        ->where($valueField, '<>', -1)
                        ->whereNotNull($valueField)
                        ->where(Variable::FIELD_DEFAULT_UNIT_ID, $unit->getId())
                        ->getArray();
                    if($rows){
                        foreach($rows as $row){
                            $old = $row->$valueField;
                            $variable = QMCommonVariable::find($row->id);
                            $error = "$valueField $old is less than $minimumValue";
                            $variable->analyzeFully($error);
                            $camel = QMStr::camelize($valueField);
                            $new = $variable->$camel;
                            if($new !== null && $new < $minimumValue){
                                $variable->analyzeFully($error);
                                le("Was not able to fix $error");
                            }
                            Memory::resetClearOrDeleteAll();
                            $variable = QMCommonVariable::find($row->id);
                            $new = $variable->$camel;
                            if($new !== null && $new < $minimumValue){
                                $variable->analyzeFully($error);
                                le("Was not able to fix $error");
                            }
                        }
                    }
                }
            }
        }
    }
    public static function generateHardCodedVariablesForYesNoCount(): void{
        $rows = Variable::whereDefaultUnitId(CountUnit::ID)->where(Variable::FIELD_NAME, \App\Storage\DB\ReadonlyDB::like(), '%(yes/no)%')->get();
        /** @var Variable $variable */
        foreach($rows as $variable){
            $variable->generateHardCodedModel();
            $nameWithoutUnit = str_replace(" (yes/no)", "", $variable->name);
            $nameWithOtherUnit = str_replace("(yes/no)", "(count)", $variable->name);
            $withoutUnitInName = Variable::findByName($nameWithoutUnit);
            if($withoutUnitInName){
                $withoutUnitInName->generateHardCodedModel();
            }
            $otherUnitInName = Variable::findByName($nameWithOtherUnit);
            if($otherUnitInName){
                $otherUnitInName->generateHardCodedModel();
            }
        }
    }
}
