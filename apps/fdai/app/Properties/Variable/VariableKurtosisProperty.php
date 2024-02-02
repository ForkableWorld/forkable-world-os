<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseKurtosisProperty;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
class VariableKurtosisProperty extends BaseKurtosisProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMCommonVariable $model
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
