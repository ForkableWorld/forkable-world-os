<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\DataTableServices;
use App\Models\MeasurementExport;
class MeasurementExportDataTableService extends BaseDataTableService
{
    protected $includeErrorsColumn = true;
    protected $with = ['user'];
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable
    {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addInternalErrorMessage()
            ->addMiddleColumns([
                'status' => [
                    'title'      => 'Status',
                    'data'       => 'status',
                    'name'       => 'status',
                    'searchable' => true
                ],
                'type' => [
                    'title'      => 'Type',
                    'data'       => 'type',
                    'name'       => 'type',
                    'searchable' => true
                ],
                'output_type' => [
                    'title'      => 'Output Type',
                    'data'       => 'output_type',
                    'name'       => 'output_type',
                    'searchable' => true
                ],
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param MeasurementExport $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MeasurementExport $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
