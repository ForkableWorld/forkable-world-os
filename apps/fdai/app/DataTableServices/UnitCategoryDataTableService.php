<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\UnitCategory;
class UnitCategoryDataTableService extends BaseDataTableService
{
    /**
     * Get query source of dataTable.
     * @param UnitCategory $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(UnitCategory $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = new BaseEloquentDataTable($this->model, $query, false);
        return $dataTable->addNameLink('name', BaseEloquentDataTable::PREFIX);
    }
}
