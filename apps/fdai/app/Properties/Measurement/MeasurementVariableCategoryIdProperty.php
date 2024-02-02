<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseVariableCategoryIdProperty;
class MeasurementVariableCategoryIdProperty extends BaseVariableCategoryIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $required = true;
    public function pluckAndSetDBValue($data, bool $fallback = false){
        $val = static::pluckOrDefault($data);
        if(!$val){
            $variable = $this->getVariable();
            $val = $variable->variable_category_id;
        }
        $this->setRawAttribute($val);
    }
}
