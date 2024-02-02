<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseStandardDeviationProperty;
use App\Utils\Stats;
use App\Types\QMStr;
class VariableStandardDeviationProperty extends BaseStandardDeviationProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param BaseModel|Variable $model
     * @return float
     */
    public static function calculate($model){
        $cv = $model->getDBModel();
        $values = $cv->pluckFromUserVariables(static::NAME);
        $val = ($values) ? Stats::average($values) : null;
        $cv->setAttribute(static::NAME, $val);
        return $val;
    }
}
