<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Imports;
use App\Models\Measurement;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMVariableCategory;
trait ImportsMeasurements
{
    public function variable(array $row): Variable{
        return Variable::firstOrCreate([
            Variable::FIELD_NAME => $this->variableName($row),
        ], $this->getNewVariableData($row));
    }
    public function fillingType(array $row):string{
        return BaseFillingTypeProperty::FILLING_TYPE_NONE;
    }
    abstract public function clientId(array $row):string;
    abstract public function variableName(array $row):string;
    /**
     * @param array $row
     * @return UserVariable
     */
    public function userVariable(array $row): UserVariable{
        $v = $this->variable($row);
        $uv = $v->getOrCreateUserVariable($this->user($row)->id);
        return $uv;
    }
    /**
     * @param array $row
     * @return User
     */
    abstract public function user(array $row): User;
    /**
     * @param array $row
     * @return Measurement
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueException
     */
    public function measurement(array $row): Measurement{
        $uv = $this->userVariable($row);
        $at = $this->at($row);
        $value = $this->value($row);
        $unitId = $this->unitId($row);
        return $uv->newMeasurementByValueTime($at, $value, $unitId);
    }
    abstract public function value(array $row): float;
    abstract public function at(array $row): string;
    abstract public function unitId(array $row): int;
    abstract public function variableCategoryId(array $row): int;
    public function getQMVariableCategory(array $row):QMVariableCategory{
        return QMVariableCategory::find($this->variableCategoryId($row));
    }
    public function variableCategory(array $row):QMVariableCategory{
        return $this->getQMVariableCategory($row);
    }
    /**
     * @param array $row
     * @return array
     */
    protected function getNewVariableData(array $row): array{
        return [
            Variable::FIELD_NAME                 => $this->variableName($row),
            Variable::FIELD_VARIABLE_CATEGORY_ID => $this->variableCategoryId($row),
            Variable::FIELD_CREATOR_USER_ID      => $this->user($row)->id,
            Variable::FIELD_FILLING_TYPE         => $this->fillingType($row),
            Variable::FIELD_CLIENT_ID            => $this->clientId($row),
        ];
    }
}
