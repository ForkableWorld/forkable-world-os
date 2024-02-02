<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Collaborator;
class CollaboratorDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Collaborator $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Collaborator $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addColumn('app_link', function(Collaborator $v) {
                if($a = $v->application){
                    return $a->getDataLabImageNameDropDown();
                }
                $id = $v->app_id;
                return "Application $id not found!";
            })
            ->addColumn('oa_client_link', function(Collaborator $m) {
                return $m->getClientLink();
            })
            ->addMiddleColumns([
                'app_link'                  => new \Yajra\DataTables\Html\Column([
                    'title'      => 'App',
                    'data'       => 'app_link',
                    'name'       => 'app_link',
                    'searchable' => true
                ]),
                //            'app_id' => [
                //                'title'      => 'App Id',
                //                'data'       => 'app_id',
                //                'name'       => 'app_id',
                //                'searchable' => false
                //            ],
                'type' => [
                    'title'      => 'Type',
                    'data'       => 'type',
                    'name'       => 'type',
                    'searchable' => true
                ]
            ]);
    }
}
