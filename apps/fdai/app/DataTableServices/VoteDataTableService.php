<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Vote;
class VoteDataTableService extends BaseDataTableService
{
    protected $with = ['user'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addColumn('description', function(Vote $v) {
                return $v->getDescriptionHtml();
            })
	        ->addTimeSinceColumn(Vote::UPDATED_AT)
            ->addMiddleColumns([
                'description'                  => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Description',
                    'data'       => 'description',
                    'name'       => 'description',
                    'searchable' => false
                ]),
                'value' => [
                    'title'      => 'Value',
                    'data'       => 'value',
                    'name'       => 'value',
                    'searchable' => false
                ],
            ]);
    }
    /**
     * Get query source of dataTable.
     * @param \App\Models\Vote $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Vote $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
