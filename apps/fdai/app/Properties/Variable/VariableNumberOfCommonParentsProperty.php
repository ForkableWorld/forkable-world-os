<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfCommonParentsProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfCommonParentsProperty extends BaseNumberOfCommonParentsProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMCommonVariable $v
     * @return int|mixed
     */
    public static function calculate($v): int{
        $rows = $v->getParentCommonTagVariables();
        $calculated = count($rows);
        $v->setAttribute(static::NAME, $calculated);
        return $calculated;
    }
}
