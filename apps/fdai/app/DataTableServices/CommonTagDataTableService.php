<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\CommonTag;
class CommonTagDataTableService extends BaseDataTableService
{
	/**
     * Get query source of dataTable.
     * @param \App\Models\CommonTag $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CommonTag $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable{
        $dataTable = (new BaseEloquentDataTable(static::getModelInstance(), $query))
            ->addMiddleColumns([
                'tagged_variable_id' => [
                    'title'      => 'Tagged Variable Id',
                    'data'       => 'tagged_variable_id',
                    'name'       => 'tagged_variable_id',
                    'searchable' => false
                ],
                'tag_variable_id' => [
                    'title'      => 'Tag Variable Id',
                    'data'       => 'tag_variable_id',
                    'name'       => 'tag_variable_id',
                    'searchable' => false
                ],
                'number_of_data_points' => [
                    'title'      => 'Number Of Data Points',
                    'data'       => 'number_of_data_points',
                    'name'       => 'number_of_data_points',
                    'searchable' => false
                ],
                'standard_error' => [
                    'title'      => 'Standard Error',
                    'data'       => 'standard_error',
                    'name'       => 'standard_error',
                    'searchable' => false
                ],
                'tag_variable_unit_id' => [
                    'title'      => 'Tag Variable Unit Id',
                    'data'       => 'tag_variable_unit_id',
                    'name'       => 'tag_variable_unit_id',
                    'searchable' => false
                ],
                'tagged_variable_unit_id' => [
                    'title'      => 'Tagged Variable Unit Id',
                    'data'       => 'tagged_variable_unit_id',
                    'name'       => 'tagged_variable_unit_id',
                    'searchable' => false
                ],
                'conversion_factor' => [
                    'title'      => 'Conversion Factor',
                    'data'       => 'conversion_factor',
                    'name'       => 'conversion_factor',
                    'searchable' => false
                ]
            ]);
        return $dataTable;
    }
}
