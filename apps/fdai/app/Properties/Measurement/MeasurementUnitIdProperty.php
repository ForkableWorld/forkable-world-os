<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\Variable;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseUnitIdProperty;
use Illuminate\Database\Eloquent\Builder;
use App\Slim\Model\QMUnit;
use App\Units\CountUnit;
use App\Units\YesNoUnit;
class MeasurementUnitIdProperty extends BaseUnitIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public static function fixInvalidRecords(): array{
        self::setUnitIdToCountWhereYesNo();
        return array_merge(
    self::fixNotEqualToVariableUnitId(),
        parent::fixInvalidRecords()
        );
    }
    public static function setUnitIdToCountWhereYesNo(): void {
        $qb = Measurement::query()
            ->withTrashed()
            ->join(Variable::TABLE, Variable::TABLE.'.'.Variable::FIELD_ID, "=", Measurement::TABLE.'.'.Measurement::FIELD_VARIABLE_ID)
            ->where(Variable::TABLE.'.'.Variable::FIELD_DEFAULT_UNIT_ID, CountUnit::ID)
            ->where(Measurement::TABLE.'.'.Measurement::FIELD_UNIT_ID, YesNoUnit::ID)
        ;
        $sql = $qb->toSql();
        $all = $qb->count();
        if($all){
            QMLog::error("Setting $all yes/no measurements to count unit");
            $qb->update([Measurement::TABLE.'.'.Measurement::FIELD_UNIT_ID => CountUnit::ID]);
        } else{
            QMLog::info("No measurements with yes/no unit where variable is count");
        }
    }
    public static function fixNotEqualToVariableUnitId(): array {
        $qb = self::whereNotEqualToVariableUnitId();
        $sql = $qb->toSql();
        $badIds = $qb->pluck(Measurement::TABLE.'.'.Measurement::FIELD_ID);
        $num = count($badIds);
        \App\Logging\ConsoleLog::info("$num measurements where unit_id does not match common variable unit");
        $arr = [];
        foreach($badIds as $id){
            $arr[] = $m = Measurement::find($id);
            $v = $m->getVariable();
            $m->logInfo("measurements.unit_id is ".$m->getUnit()->name." but variable unit is ".$v->getUnit()->name."
            ".$m->getEditUrl());
        }
        return $arr;
    }
    /**
     * @return \App\Models\Base\BaseMeasurement|Builder|\Illuminate\Database\Query\Builder
     */
    public static function whereNotEqualToVariableUnitId(){
        $qb = Measurement::query()
            ->withTrashed()
            ->join(Variable::TABLE,
                Variable::TABLE.'.'.Variable::FIELD_ID,
                "=",
                Measurement::TABLE.'.'.Measurement::FIELD_VARIABLE_ID)
            ->whereRaw("variables.default_unit_id <> measurements.unit_id");
        return $qb;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $m = $this->getMeasurement();
        $unitId = $this->getDBValue();
        $unit = QMUnit::find($unitId);
        $v = $m->getVariable();
        if($unitId !== $v->default_unit_id){
            $this->throwException("measurement unit must equal common variable unit ".$v->getUnit()->name." but is ".$unit->name);
        }
    }
    public static function getInvalidUrl():string{
        return Measurement::generateDataLabIndexUrl(["whereRaw" => "variables.default_unit_id <> measurements.unit_id"]);
    }
    /**
     * @param $data
     * @return QMUnit
     */
    public static function findRelated($data): QMUnit{
        $unitId = MeasurementVariableIdProperty::findUnitId($data);
        $unit = QMUnit::find($unitId);
		if(!$unit){le('!$unit');}
        return $unit;
    }
    public static function applyRequestParamsToQuery(\Illuminate\Database\Query\Builder $qb): void{
        $disabled = true;
        if(!$disabled){  // Unit is only provided for conversions, not filtering
            parent::applyRequestParamsToQuery($qb);
        }
    }
	public function showOnCreate(): bool{return true;}
}
