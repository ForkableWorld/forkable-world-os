<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseDefaultUnitIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
class VariableDefaultUnitIdProperty extends BaseDefaultUnitIdProperty {
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public static function convertPercentDefaultUnitSymptomsToOutOfFive()
    {
        $percent = QMUnit::getPercent();
        $symptoms = QMVariableCategory::getSymptoms();
        $rows = QMCommonVariable::readonly()
            ->where(self::NAME, $percent->getId())
            ->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $symptoms->getId())
            ->getArray();
        foreach ($rows as $row) {
            $commonVariable = QMCommonVariable::find($row->id);
            $commonVariable->changeDefaultUnitToFiveRatingFromPercent();
        }
    }
    public static function changeUnitsToCountWhereWeightUnitInName(){
        $units = QMUnit::get();
        $dryRun = true;
        foreach($units as $unit){
            //if(!$unit->isWeightCategory()){continue;}
            QMLog::info($unit->abbreviatedName);
            $rows =
                QMCommonVariable::readonly()
                    ->whereLike(Variable::FIELD_NAME, '% '.
                        $unit->abbreviatedName.
                        '%')
                    ->where(self::NAME, $unit->id)
                    ->whereNull(Variable::FIELD_DELETED_AT)
                    ->getArray();
            QMLog::info(count($rows)." rows with $unit->abbreviatedName in name");
            foreach($rows as $row){
                $number = QMStr::getNumberFromStringWithLeadingSpaceOrAtBeginning($row->name);
                if($number){
                    $variable = Variable::find($row->id);
                    $numberOfMeasurements = $variable->getNumberOfMeasurementsAttribute();
                    $variable->logInfo("Number of measurements: ".$numberOfMeasurements);
                    if($numberOfMeasurements < 5){
                        if(!$dryRun){
                            $variable->deleteIfNoAggregatedCorrelationsAndNoMeasurements("Bad name");
                        }
                    }else{
                        $sanitized = VariableNameProperty::sanitizeSlow($variable->name);
                        QMLog::info("Sanitized: ".$sanitized);
                        $variable->addSynonym($variable->name);
                        if(!$dryRun){
                            $variable->update([
                                Variable::FIELD_NAME     => $sanitized,
                                Variable::FIELD_SYNONYMS => $variable->getSynonymsAttribute()
                            ]);
                        }
                    }
                }
            }
        }
    }
    /**
     * @param string $variableName
     * @param string $newDefaultUnitAbbreviatedName
     * @param float $multiplicationFactor
     * @param float $additionFactor
     * @param int|string|QMUnit $oldUnit
     * @throws \App\Exceptions\InvalidAttributeException
     * @throws \App\Exceptions\ModelValidationException
     * @throws \App\Exceptions\NotEnoughDataException
     */
    public static function changeDefaultUnitEverywhere(string $variableName,
                                                       string $newDefaultUnitAbbreviatedName,
                                                       float $multiplicationFactor,
                                                       float $additionFactor,
                                                       $oldUnit){
        if(!$oldUnit instanceof QMUnit){
            $oldUnit = QMUnit::getByNameOrId($oldUnit);
        }
        $newDefaultUnitId = QMUnit::findByNameOrSynonym($newDefaultUnitAbbreviatedName)->id;
        $v = QMCommonVariable::findByNameOrId($variableName);
        $variableId = $v->id;
        if($oldUnit->id !== $v->getCommonUnit()->id){
            le("Wrong old unit id!");
        }
        if($multiplicationFactor){
            Writable::db()->statement("
                UPDATE measurements set value = value * $multiplicationFactor
                    where variable_id = $variableId
                    and unit_id = $oldUnit->id
            ");
        }
        if($additionFactor){
            Writable::db()->statement("
                UPDATE measurements
                    set value = value + $additionFactor
                    where variable_id = $variableId
                    and unit_id = $oldUnit->id
            ");
        }
        Writable::db()->statement("
            UPDATE measurements
                set unit_id = $newDefaultUnitId
                where variable_id = $variableId
                    and unit_id = $oldUnit->id
        ");
        Writable::db()->statement("
            UPDATE variables
                set default_unit_id = $newDefaultUnitId,
                     status = 'WAITING'
            where id = $variableId
              and default_unit_id = $oldUnit->id
        ");
        if($multiplicationFactor || $additionFactor){
            /** @var UserVariable[] $userVariables */
            $userVariables = UserVariable::whereVariableId($variableId)->get();
            foreach($userVariables as $uv){
                QMUserVariable::getOrCreateAndAnalyze($uv->user_id, $variableId);
                try {
                    $uv = QMUserVariable::getByNameOrId($uv->user_id, $variableId);
                } catch (UserVariableNotFoundException $e) {
                }
                try {
                    $uv->calculateCorrelationsIfNecessary();
                } catch (TooSlowToAnalyzeException $e) {
                }
            }
            $cv = QMCommonVariable::find($variableId);
            try {
                $cv->analyzeFullyIfNecessaryAndSave(__FUNCTION__);
            } catch (TooSlowToAnalyzeException $e) {
                $cv->logError(__METHOD__.": ".$e->getMessage());
            } catch (InvalidStringException $e) {
                $cv->logError(__METHOD__.": ".$e->getMessage());
            }
            try {
                QMGlobalVariableRelationship::analyzeAggregatedCorrelationsForVariable($variableId);
            } catch (NoUserVariableRelationshipsToAggregateException $e) {
            } catch (AlreadyAnalyzingException $e) {
            }
        }
    }
    /**
     * @param string $variableName
     * @param array $newVariableData
     * @param QMVariableCategory $variableCategory
     * @param bool $throwException
     * @return QMUnit
     */
    public static function getDefaultUnitFromNewVariableParams($variableName, $newVariableData,
                                                               $variableCategory, bool $throwException = true): ?QMUnit{
        $defaultUnitId = $newVariableArray[self::NAME] =
            QMArr::getValue($newVariableData, [
                self::NAME,
                'unitId'
            ]);
        if ($defaultUnitId && QMUnit::getByNameOrId($defaultUnitId)) {
            return QMUnit::getByNameOrId($defaultUnitId);
        }
        $unitName = QMArr::getValue($newVariableData, [
            'unitName',
            'defaultUnitName',
            'defaultUnitAbbreviatedName',
            'unitAbbreviatedName',
            'unit'
        ]);
        if ($unitName) {
            return QMUnit::getByNameOrId($unitName);
        }
        $defaultUnitId = QMUnit::getUnitIdFromString($variableName);
        if (!$defaultUnitId && isset($newVariableData['ItemAttributes']['Title'])) {
            $defaultUnitId = QMUnit::getUnitIdFromString($newVariableData['ItemAttributes']['Title']);
        }
        if ($defaultUnitId && QMUnit::getByNameOrId($defaultUnitId)) {
            return QMUnit::getByNameOrId($defaultUnitId);
        }
        if (isset($variableCategory->defaultUnitId)) {
            return QMUnit::getByNameOrId($variableCategory->defaultUnitId);
        }
        if ($throwException) {
            throw new BadRequestHttpException("Please provide unit name to create a new " . $variableName . " variable!");
        }
        return null;
    }
    public function showOnUpdate(): bool {return QMAuth::isAdmin();}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
