<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Base\BaseOutcomeProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
class VariableOutcomeProperty extends BaseOutcomeProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    public static function setCommonVariableOutcomesToMatchCategoryOutcomes(){
        foreach (QMVariableCategory::getVariableCategories() as $variableCategory) {
            if ($variableCategory->outcome) {
                $qb = QMCommonVariable::writable()
                    ->where(self::NAME, '<>', 1)
                    ->where(Variable::FIELD_VARIABLE_CATEGORY_ID, $variableCategory->id);
                $doNotMatchBefore = $qb->count();
                if ($doNotMatchBefore) {
                    QMLog::error("$doNotMatchBefore $variableCategory->name variables' outcome is not set to true");
                    $qb->update([self::NAME => $variableCategory->outcome]);
                    $after = $qb->count();
                    $changed = $doNotMatchBefore - $after;
                    QMLog::error("$changed $variableCategory->name variables' outcome were changed to true");
                }
            }
        }
    }
    /**
     * @param QMVariable|Variable $model
     * @return float
     */
    public static function calculate($model){
        $val = self::isOutcome($model);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param $providedParams
     * @param $newVariable
     * @param $variableCategory
     * @return mixed
     */
    public static function setOutcomeInNewVariableArray(array $providedParams,
                                                        array $newVariable,
                                                        QMVariableCategory $variableCategory): array{
        $newVariable[self::NAME] =
            QMArr::getValue($providedParams, [self::NAME]);
        if(!isset($newVariable[self::NAME])){
            $newVariable[self::NAME] = $variableCategory->outcome;
        }
        return $newVariable;
    }
    public static function fixInvalidRecords(){
        self::setNullsToCategoryValue();
        self::fixEconomics();
    }
    public static function fixEconomics(){
        $qb = Variable::whereVariableCategoryId(EconomicIndicatorsVariableCategory::ID)
            ->where(self::NAME, "<>", EconomicIndicatorsVariableCategory::OUTCOME);
        if($invalid = $qb->count()){
            QMLog::error("$invalid economics variables outcome is not null");
            $qb->update([self::NAME => EconomicIndicatorsVariableCategory::OUTCOME]);
        }
    }
    public static function setNullsToCategoryValue(){
        $cats = QMVariableCategory::get();
        foreach($cats as $cat){
            $qb = Variable::whereVariableCategoryId($cat->id)
                ->whereNull(self::NAME);
            $invalid = $qb->count();
            if($invalid){
                QMLog::error("$invalid economics variables outcome is not null");
                $qb->update([self::NAME => $cat->outcome]);
            }
        }
    }
    /**
     * @param QMVariable $model
     * @return bool
     */
    public static function isOutcome(QMVariable $model): bool{
        if($model->causeOnly){
            $val = false;
        }elseif($model->outcome !== null){
            $val = $model->outcome;
        }elseif($model->getQMVariableCategory()->outcome !== null){
            $val = $model->getQMVariableCategory()->outcome;
        }else{
            $val = true;
        }
        return $val;
    }
}
