<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Unit;
class UnitDataTableService extends BaseDataTableService
{
    /**
     * Get query source of dataTable.
     * @param Unit $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Unit $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = new BaseEloquentDataTable($this->model, $query, false);
        $dataTable->addNameLink('name', BaseEloquentDataTable::PREFIX);
        $dataTable->addCategoryLink(BaseEloquentDataTable::PREFIX);
        $dataTable->addPrefixColumn(Unit::FIELD_ABBREVIATED_NAME);
        $dataTable->addSuffixColumn(Unit::FIELD_ADVANCED);
        $dataTable->addIdLink();
        return $dataTable;
    }

}
