<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Unit;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\Unit;
use App\Properties\Measurement\MeasurementUserVariableIdProperty;
use App\Traits\PropertyTraits\UnitProperty;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
class UnitIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use UnitProperty;
    public $table = Unit::TABLE;
    public $parentClass = Unit::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'unit_id',
        'id',
    ];
    public const NAME_SYNONYMS = [
        'unit_name',
        'unit_abbreviated_name',
        'abbreviated_unit_name',
        'unit',
    ];
    public static function getDefault($data = null): ?int{
        $val = parent::getDefault($data);
        if($val){return $val;}
        $uvId = MeasurementUserVariableIdProperty::pluckOrDefault($data);
        if($uvId){
            $uv = QMUserVariable::find($uvId);
            return $uv->getUnitIdAttribute();
        }
        $vId = MeasurementUserVariableIdProperty::pluckOrDefault($data);
        if($vId){
            $v = QMCommonVariable::find($uvId);
            return $v->getUnitIdAttribute();
        }
        return null;
    }
}
