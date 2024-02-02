<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Application;
class ApplicationDataTableService extends BaseDataTableService
{
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Application $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Application $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
    public function getEloquentDataTable($query = null): BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addColumn('oa_client_link',
            function(Application $m){
                $b = $m->getClientLink();
                return $b;
            })
            ->addMiddleColumns([
                Application::FIELD_ID,
                'client_id'        => [
                    'title'      => 'Client ID',
                    'data'       => 'client_id',
                    'name'       => 'client_id',
                    'searchable' => true
                ],
                'app_display_name' => [
                    'title'      => 'App Display Name',
                    'data'       => 'app_display_name',
                    'name'       => 'app_display_name',
                    'searchable' => true
                ],
                'app_description'  => [
                    'title'      => 'App Description',
                    'data'       => 'app_description',
                    'name'       => 'app_description',
                    'searchable' => true
                ],
                'homepage_url'     => [
                    'title'      => 'Homepage Url',
                    'render'     => '"<a href=\""+data+"\">"+data+"</a>"',
                    'data'       => 'homepage_url',
                    'name'       => 'homepage_url',
                    'searchable' => true
                ],
                'app_type'         => [
                    'title'      => 'App Type',
                    'data'       => 'app_type',
                    'name'       => 'app_type',
                    'searchable' => true
                ],
                'study'            => [
                    'title'      => 'Study',
                    'data'       => 'study',
                    'name'       => 'study',
                    'searchable' => false
                ],
                'physician'        => [
                    'title'      => 'Physician',
                    'data'       => 'physician',
                    'name'       => 'physician',
                    'searchable' => false
                ],
            ]);
    }
}
