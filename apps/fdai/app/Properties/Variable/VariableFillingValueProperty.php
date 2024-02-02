<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseFillingValueProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMVariable;
class VariableFillingValueProperty extends BaseFillingValueProperty
{
    use DailyVariableValueTrait, VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMVariable $model
     * @return float
     */
    public static function calculate($model): ?float{
        $type = $model->getFillingTypeAttribute();
        $val = BaseFillingValueProperty::fromType($type, $model->fillingValue);
	    $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param array $newVariable
     * @return mixed
     */
    public static function setFillingValueInNewVariableArray(array $newVariable): array{
        if (!array_key_exists(self::NAME, $newVariable)) {
            $newVariable[self::NAME] = -1; // -1 means undefined so we'll fall back to unit filling value
        }
        return $newVariable;
    }
	protected function validateMin(): void{
		$val = $this->getDBValue();
		if($val != -1){
			parent::validateMin();
		}
	}
}
