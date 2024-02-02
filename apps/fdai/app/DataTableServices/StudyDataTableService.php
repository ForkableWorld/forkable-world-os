<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Study;
class StudyDataTableService extends BaseDataTableService
{
    protected $includeErrorsColumn = true;
    protected $with = ['user', 'cause_variable', 'effect_variable'];
    /**
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addCauseImageNameDropDown()
            ->addEffectImageNameDropDown()
            ->addInternalErrorMessage()
            ->addMiddleColumns([
                'cause_link'                  => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Cause',
                    'data'       => 'cause_link',
                    'name'       => 'cause_link',
                    'searchable' => false,
                    'orderable' => false
                ]),
                'effect_link'                  => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Effect',
                    'data'       => 'effect_link',
                    'name'       => 'effect_link',
                    'searchable' => false,
                    'orderable' => false
                ]),
                'type' => [
                    'title'      => 'Type',
                    'data'       => 'type',
                    'name'       => 'type',
                    'searchable' => false,
                    'orderable' => false
                ],
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param Study $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Study $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
