<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Formulas;
use App\Models\Variable;
use App\Variables\QMCommonVariable;
class BestCauseVariableIdCommonVariableFormula extends BaseFormula
{
    public const SQL = 'select cause_variable_id as calculatedValue
            from global_variable_relationships ac
            where cause_variable_id = $this->id
                and ac.deleted_at is null
            order by ac.aggregate_qm_score desc
            limit 1';
    public const TABLE = Variable::TABLE;
    public const MODEL = QMCommonVariable::class;
    public const AVERAGE_DURATION = null;
}
