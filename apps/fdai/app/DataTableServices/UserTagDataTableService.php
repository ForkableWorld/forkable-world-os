<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\UserTag;
class UserTagDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
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
                'conversion_factor' => [
                    'title'      => 'Conversion Factor',
                    'data'       => 'conversion_factor',
                    'name'       => 'conversion_factor',
                    'searchable' => false
                ]
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\UserTag $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(UserTag $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
