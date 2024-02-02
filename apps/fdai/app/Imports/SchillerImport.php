<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Imports;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Units\IndexUnit;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
abstract class SchillerImport implements ToModel, WithMultipleSheets
{
    use ImportsMeasurements, Importable;
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueException
     */
    public function model(array $row): ?\Illuminate\Database\Eloquent\Model{
        $value = $this->value($row);
        if(!is_float($value)){
            return null;
        }
        return $this->measurement($row);
    }
    public function user(array $row): User{
        return User::econ();
    }
    abstract public function value(array $row): float;
    public function at(array $row): string{
        $date = $row[0];
        $year = Str::before($date, '.');
        $month = Str::after($date, '.');
        $at = db_date("$year-$month-01");
        return $at;
    }
    public function unitId(array $row): int {
        return IndexUnit::ID;
    }
    public function clientId(array $row): string{
        return BaseClientIdProperty::CLIENT_ID_MONEYMODO;
    }
    public function variableCategoryId(array $row): int{
        return EconomicIndicatorsVariableCategory::ID;
    }
    public function sheets(): array {
        return [
            'Data' => new static()
        ];
    }
}
