<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty extends BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
	/**
	 * @return void
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public static function fixNulls(){
	    $ids = static::whereNull()
            ->where(Variable::FIELD_NUMBER_OF_MEASUREMENTS, '>', 0)
            ->whereNull(Variable::FIELD_DELETED_AT)
            ->limit(100)
            ->pluck('id');
        foreach ($ids as $id) {
            $v = Variable::findInMemoryOrDB($id);
            $v->analyzeFullyIfNecessary(__FUNCTION__);
        }
    }
}
