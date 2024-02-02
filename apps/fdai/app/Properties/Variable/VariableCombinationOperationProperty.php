<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Exceptions\BadRequestException;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Controller\Variable\PostVariableController;
use App\Slim\Model\QMUnit;
use App\Traits\PropertyTraits\VariableProperty;
use Database\Seeders\DatabaseSeeder;
class VariableCombinationOperationProperty extends BaseCombinationOperationProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMUnit|int $unit
     * @param string $combinationOperation
     * @return string
     */
    public static function getValidCombinationOperation(QMUnit $unit, string $combinationOperation): ?string
    {
        if ($combinationOperation === BaseCombinationOperationProperty::COMBINATION_SUM &&
            !$unit->canBeSummed()) {
            $combinationOperation = BaseCombinationOperationProperty::COMBINATION_MEAN;
            QMLog::info('Cannot use SUM combination operation for unit ' . $unit->name .
                '. Using MEAN instead');
        }
        if ($combinationOperation === Variable::FIELD_MEAN && $unit->isCountCategory()) {
            $combinationOperation = BaseCombinationOperationProperty::COMBINATION_SUM;
            QMLog::error('Cannot use MEAN combination operation for count unit ' .
                $unit->name . '. Using SUM instead');
        }
        if (!in_array(strtoupper($combinationOperation), [
            BaseCombinationOperationProperty::COMBINATION_SUM,
            BaseCombinationOperationProperty::COMBINATION_MEAN
        ], true)) {
            throw new BadRequestException(
                PostVariableController::ERROR_INVALID_COMBINATION_OPERATION, [$combinationOperation]);
        }
        return $combinationOperation;
    }
    public function validate(): void {
        parent::validate();
        $v = $this->getVariable();
        $unit = $v->getQMUnit();
        $co = $this->getDBValue();
        if(!$co){return;}
        $unitCO = $unit->combinationOperation; // Don't use getter!
        if ($unitCO && $unitCO !== $co) {
            if(DatabaseSeeder::isReprocessingSeed()){
                $this->setValue($unitCO);
                return;
            }
            $this->throwException("Cannot use $co combination operation for unit " .
                $unit->name.". Use $unitCO instead. ");
        }
        if ($co === BaseCombinationOperationProperty::COMBINATION_SUM && !$unit->canBeSummed()) {
            if(DatabaseSeeder::isReprocessingSeed()){
                $this->setValue(self::COMBINATION_MEAN);
                return;
            }
            $this->throwException('Cannot use SUM combination operation for unit ' .
                $unit->name . '. Use MEAN instead. ');
        }
        if ($co === BaseCombinationOperationProperty::COMBINATION_MEAN && $unit->isCountCategory()) {
            // Why not?  $this->throwException('Cannot use MEAN combination operation for count unit ' . $unit->name . '. Use SUM instead');
        }
    }
}
