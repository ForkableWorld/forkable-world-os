<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\DataSources\QMConnector;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Variable;
use App\Properties\Base\BaseVariableCategoryIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMArr;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
class VariableVariableCategoryIdProperty extends BaseVariableCategoryIdProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public function showOnUpdate(): bool {return QMAuth::isAdmin();}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    public function beforeChange(bool $log = true): void{
        parent::beforeChange();
        $v = $this->getVariable();
        $new = $this->getDBValue();
        $old = $this->getRawOriginalValue();
        $v->logError("Changing variable category from $old to $new");
        $pairs = [
            Measurement::FIELD_VARIABLE_ID => Measurement::FIELD_VARIABLE_CATEGORY_ID,
            UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID => UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID,
            UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID => UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID,
        ];
        foreach($pairs as $variableField => $categoryField){
            $tables = Writable::getTableNamesWithColumns([
                $categoryField, $variableField
            ]);
            foreach($tables as $table){
                $changed = Writable::getBuilderByTable($table)
                    ->where($variableField, $v->getVariableId())
                    ->update([$categoryField => $new])
                ;
                $v->logError("Change $changed $table $categoryField from $old to $new");
            }
        }
    }
    /**
     * @param array $newVariableData
     * @return QMVariableCategory
     * @throws VariableCategoryNotFoundException
     */
    private static function getVariableCategoryFromAmazonProduct(array $newVariableData): ?QMVariableCategory{
        if(isset($newVariableData['ItemAttributes']['ProductGroup'])){
            $variableCategoryObject = QMVariableCategory::findByNameOrSynonym($newVariableData['ItemAttributes']['ProductGroup'], false);
            if($variableCategoryObject){
                return $variableCategoryObject;
            }
            QMLog::error("Variable category not found for " . $newVariableData['ItemAttributes']['Binding']);
        }
        if(isset($newVariableData['ItemAttributes']['Binding'])){
            $variableCategoryObject = QMVariableCategory::findByNameOrSynonym($newVariableData['ItemAttributes']['Binding'], false);
            if($variableCategoryObject){
                return $variableCategoryObject;
            }
            QMLog::error("Variable category not found for " . $newVariableData['ItemAttributes']['Binding']);
        }
        return null;
    }
    /**
     * @param array $newVariableData
     * @param string $name
     * @return QMVariableCategory
     */
    public static function getVariableCategoryFromNewVariableParams(array $newVariableData, string $name): ?QMVariableCategory{
        if ($id = QMArr::getValue($newVariableData, [Variable::FIELD_VARIABLE_CATEGORY_ID])) {
            return QMVariableCategory::find($id);
        }
        if ($categoryFromAmazonProduct = self::getVariableCategoryFromAmazonProduct($newVariableData)) {
            return $categoryFromAmazonProduct;
        }
        $category = QMArr::getValue($newVariableData, [
            'category',
            'categoryName',
            'variableCategoryName',
            'variableCategory'
        ]);
        if ($category) {
            if (is_string($category)) {
                $category = QMVariableCategory::findByNameOrSynonym($category);
            }
            if ($category->isStupidCategory()) {
                QMLog::error("Creating new stupid category $category->name variable with params: " . json_encode($newVariableData));
            }
            return $category;
        }
        if (isset($newVariableData['sourceName'])) {
            if ($connector = QMConnector::getConnectorByNameOrId($newVariableData['sourceName'])) {
                $category = $connector->getDefaultVariableCategory();
                if ($category) {
                    if ($category->isStupidCategory()) {
                        QMLog::error("Creating new stupid category $category->name variable with params: " .
                            json_encode($newVariableData));
                    }
                    return $category;
                }
            }
        }
        throw new CommonVariableNotFoundException(QMCommonVariable::ERROR_NO_VARIABLE_FOUND . " for $name with parameters " .
            \App\Logging\QMLog::print_r($newVariableData, true) .
            '. Please provide variableCategoryName to create a new one.  Available ones are ' .
            QMVariableCategory::getStringListOfVariableCategoryNames());
    }
}
