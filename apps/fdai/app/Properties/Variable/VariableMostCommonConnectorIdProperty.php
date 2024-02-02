<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMostCommonConnectorIdProperty;
class VariableMostCommonConnectorIdProperty extends BaseMostCommonConnectorIdProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param Variable $model
     * @return int
     */
    public static function calculate($model): ?int{
        $cv = $model->getDBModel();
        $val = $model->mostCommonFromUserVariablesBasedOnNumberOfMeasurements(self::NAME);
        if(!$val){
            $original = $model->getOriginal(self::NAME);
            if($original){
                $model->throwLogicException(self::NAME.
                    ": No value from mostCommonFromUserVariablesBasedOnNumberOfMeasurements but we used to have $original");
            }
        }
        $model->setAttribute(static::NAME, $val);
        $cv->setAttribute(static::NAME, $val);
        return $val;
    }
    public function validate(): void {
        parent::validate();
    }
}
