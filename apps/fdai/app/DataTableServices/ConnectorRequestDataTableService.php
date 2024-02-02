<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\ConnectorRequest;
class ConnectorRequestDataTableService extends BaseDataTableService
{
    protected $with = [];
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable
    {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addConnectorLink()
            ->addColumn('connection_link', function(ConnectorRequest $v) {
                return $v->connection->getDataLabImageNameDropDown();
            })
            ->addColumn('connector_import_link', function(ConnectorRequest $v) {
                return $v->connection->getDataLabImageNameDropDown();
            })
            ->addColumn(ConnectorRequest::FIELD_REQUEST_HEADERS, function(ConnectorRequest $v) {
                return \App\Logging\QMLog::print_r($v->request_headers, true);
            })
            ->addColumn(ConnectorRequest::UPDATED_AT, function(ConnectorRequest $v) {
                return $v->getTimeSinceUpdated();
            })
            ->addMiddleColumns([
                'connection_link' => [
                    'title'      => 'Connection',
                    'data'       => 'connection_id',
                    'name'       => 'connection_id',
                    'searchable' => false
                ],
                'method' => [
                    'title'      => 'Method',
                    'data'       => 'method',
                    'name'       => 'method',
                    'searchable' => true
                ],
                'code' => [
                    'title'      => 'Code',
                    'data'       => 'code',
                    'name'       => 'code',
                    'searchable' => false
                ],
                'uri' => [
                    'title'      => 'Uri',
                    'data'       => 'uri',
                    'name'       => 'uri',
                    'searchable' => true
                ],
                ConnectorRequest::FIELD_REQUEST_HEADERS => [
                    'title'      => 'Request Headers',
                    'data'       => 'request_headers',
                    'name'       => 'request_headers',
                    'searchable' => true
                ],
                'response_body' => [
                    'title'      => 'Response Body',
                    'data'       => 'response_body',
                    'name'       => 'response_body',
                    'searchable' => true
                ],
                'request_body' => [
                    'title'      => 'Request Body',
                    'data'       => 'request_body',
                    'name'       => 'request_body',
                    'searchable' => true
                ],
                'content_type' => [
                    'title'      => 'Content Type',
                    'data'       => 'content_type',
                    'name'       => 'content_type',
                    'searchable' => true
                ],
                'connector_import_id' => [
                    'title'      => 'Connector Import Id',
                    'data'       => 'connector_import_id',
                    'name'       => 'connector_import_id',
                    'searchable' => false
                ],
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ConnectorRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ConnectorRequest $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
