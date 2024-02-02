<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Notification;
use Yajra\DataTables\EloquentDataTable;
class NotificationDataTableService extends BaseDataTableService
{
    protected $with = [];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable->addColumn("body", function(Notification $v) {
            return $v->getBody();
        })
            ->addMiddleColumns([
            //            'title'                  => new \Yajra\DataTables\Html\Column([
            //                'title'      => 'Title',
            //                'data'       => 'title',
            //                'name'       => 'title',
            //                'searchable' => true
            //            ]),
            'body'                  => new \Yajra\DataTables\Html\Column([
                'title'      => 'Body',
                'data'       => 'body',
                'name'       => 'body',
                'searchable' => true
            ]),
            //            'type' => [
            //                'title'      => 'Type',
            //                'data'       => 'type',
            //                'name'       => 'type',
            //                'searchable' => true
            //            ],
            //            'notifiable_type' => [
            //                'title'      => 'Notifiable Type',
            //                'data'       => 'notifiable_type',
            //                'name'       => 'notifiable_type',
            //                'searchable' => true
            //            ],
            //            'notifiable_id' => [
            //                'title'      => 'Notifiable Id',
            //                'data'       => 'notifiable_id',
            //                'name'       => 'notifiable_id',
            //                'searchable' => false
            //            ],
            //            'data' => [
            //                'title'      => 'Data',
            //                'data'       => 'data',
            //                'name'       => 'data',
            //                'searchable' => true
            //            ],
            'read_at' => [
                'title'      => 'Read At',
                'data'       => 'read_at',
                'name'       => 'read_at',
                'searchable' => false
            ],
        ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param Notification $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Notification $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
