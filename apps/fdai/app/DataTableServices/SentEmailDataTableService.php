<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\SentEmail;
class SentEmailDataTableService extends BaseDataTableService
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
        return $dataTable
            ->addOpenImageTextLink()
            ->addMiddleColumns([
                'email_address' => [
                    'title'      => 'Email Address',
                    'render'     => '"<a href=\"mailto:"+data+"\">"+data+"</a>"',
                    'data'       => 'email_address',
                    'name'       => 'email_address',
                    'searchable' => true
                ],
                'type' => [
                    'title'      => 'Type',
                    'data'       => 'type',
                    'name'       => 'type',
                    'searchable' => true
                ],
                'content' => [
                    'title'      => 'Content',
                    'data'       => 'content',
                    'name'       => 'content',
                    'searchable' => true
                ]
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\SentEmail $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SentEmail $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
