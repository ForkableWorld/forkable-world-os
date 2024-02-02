<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Properties\Unit\UnitIdProperty;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseOriginalUnitIdProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
class MeasurementOriginalUnitIdProperty extends BaseOriginalUnitIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public const SYNONYMS = [
        'original_unit_id',
        'user_unit_id',
        'unit_id',
    ];
    /**
     * @param $data
     * @return QMUnit
     */
    public static function findRelated($data): QMUnit{
        $id = static::pluckOrDefault($data);
        return QMUnit::find($id);
    }
    public static function getDefault($data = null){
        return MeasurementUnitIdProperty::pluckOrDefault($data);
    }
    public static function pluckOrDefault($data){
        $id = static::pluck($data);
        if(!$id){$id = static::pluckByName($data);}
        return $id;
    }
    /**
     * @param bool $throwException
     * @return mixed|null
     */
    public static function fromRequest(bool $throwException = false){
        return QMRequest::getParam(static::NAME);
    }
}
