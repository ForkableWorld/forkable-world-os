<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\MeasurementImport;
use App\DataSources\QMDataSource;
class MeasurementImportDataTableService extends BaseDataTableService
{
    protected $includeErrorsColumn = true;
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
            ->addInternalErrorMessage()
            ->addColumn('source_link', function(MeasurementImport $v) {
                return $v->getDataSourceLink();
            })
            ->addMiddleColumns([
                'file' => [
                    'title'      => 'File',
                    'data'       => 'file',
                    'name'       => 'file',
                    'searchable' => true
                ],
                'status' => [
                    'title'      => 'Status',
                    'data'       => 'status',
                    'name'       => 'status',
                    'searchable' => true
                ],
                'source_name' => [
                    'title'      => 'Source Name',
                    'data'       => 'source_name',
                    'name'       => 'source_name',
                    'searchable' => true
                ]
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param MeasurementImport $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(MeasurementImport $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
