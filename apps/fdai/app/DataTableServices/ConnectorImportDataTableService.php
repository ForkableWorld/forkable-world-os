<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\ConnectorImport;
class ConnectorImportDataTableService extends BaseDataTableService {
    protected $includeErrorsColumn = true;
    protected $with = ['user', 'connector'];
    /**
     * Get query source of dataTable.
     * @param ConnectorImport $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ConnectorImport $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable->addConnectorLink()->addMiddleColumns([
            'import_started_at'       => [
                'title'      => 'Import Started',
                'data'       => 'import_started_at',
                'name'       => 'import_started_at',
                'searchable' => false
            ],
            'import_ended_at'         => [
                'title'      => 'Import Ended',
                'data'       => 'import_ended_at',
                'name'       => 'import_ended_at',
                'searchable' => false
            ],
            'number_of_measurements'  => new \Yajra\DataTables\Html\Column([
                'title'      => 'Measurements',
                'data'       => 'number_of_measurements',
                'name'       => 'number_of_measurements',
                'searchable' => false
            ]),
            'earliest_measurement_at' => [
                'title'      => 'Earliest Measurement',
                'data'       => 'earliest_measurement_at',
                'name'       => 'earliest_measurement_at',
                'searchable' => false
            ],
            'latest_measurement_at'   => [
                'title'      => 'Latest Measurement',
                'data'       => 'latest_measurement_at',
                'name'       => 'latest_measurement_at',
                'searchable' => false
            ],
        ]);
    }
}
