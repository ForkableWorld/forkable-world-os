<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Connection;
use App\Types\TimeHelper;
class ConnectionDataTableService extends BaseDataTableService
{
    protected $includeErrorsColumn = true;
    protected $with = ['user', 'connector'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable
    {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addDisplayNameLink()
            ->addConnectorLink()
            ->addColumn(Connection::FIELD_TOTAL_MEASUREMENTS_IN_LAST_UPDATE, function(Connection $v) {
                return $v->getMeasurementsInLastUpdateLink();
            })
            ->addInternalErrorMessage()
            ->addColumn(Connection::FIELD_IMPORT_ENDED_AT, function(Connection $v) {
                return TimeHelper::timeSinceHumanString($v->import_ended_at);
            })
            ->addColumn(Connection::FIELD_IMPORT_STARTED_AT, function(Connection $v) {
                return TimeHelper::timeSinceHumanString($v->import_started_at);
            })
            ->addMiddleColumns( [
                'connect_status' => [
                    'title'      => 'Connect Status',
                    'data'       => 'connect_status',
                    'name'       => 'connect_status',
                    'searchable' => true
                ],
                'update_status' => [
                    'title'      => 'Update Status',
                    'data'       => 'update_status',
                    'name'       => 'update_status',
                    'searchable' => true
                ],
                'import_started_at' => [
                    'title'      => 'Import Started At',
                    'data'       => 'import_started_at',
                    'name'       => 'import_started_at',
                    'searchable' => false
                ],
                'import_ended_at' => [
                    'title'      => 'Import Ended At',
                    'data'       => 'import_ended_at',
                    'name'       => 'import_ended_at',
                    'searchable' => false
                ],
                'total_measurements_in_last_update' =>  new \Yajra\DataTables\Html\Column([
                    'title'      => 'Measurements In Last Import',
                    'data'       => 'total_measurements_in_last_update',
                    'name'       => 'total_measurements_in_last_update',
                    'searchable' => false
                ]),
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param Connection $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Connection $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
