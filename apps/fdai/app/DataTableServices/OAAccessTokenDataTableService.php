<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\OAAccessToken;
class OAAccessTokenDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addColumn('show_link', function(OAAccessToken $m) {
                return $m->getDataLabImageNameDropDown();
            })
            ->addMiddleColumns($this->getModelSpecificColumns());
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\OAAccessToken $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(OAAccessToken $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
    /**
     * Get columns.
     *
     * @return array
     */
    protected function getModelSpecificColumns()
    {
        return [
            'client_id' => [
                'title'      => 'Client Id',
                'data'       => 'client_id',
                'name'       => 'client_id',
                'searchable' => true
            ],
            'user_id' => [
                'title'      => 'User Id',
                'data'       => 'user_id',
                'name'       => 'user_id',
                'searchable' => false
            ],
            'expires' => [
                'title'      => 'Expires',
                'data'       => 'expires',
                'name'       => 'expires',
                'searchable' => false
            ],
            'scope' => [
                'title'      => 'Scope',
                'data'       => 'scope',
                'name'       => 'scope',
                'searchable' => true
            ]
        ];
    }
}
