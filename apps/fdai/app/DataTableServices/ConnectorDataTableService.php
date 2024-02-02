<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Connector;
use App\Slim\Middleware\QMAuth;
class ConnectorDataTableService extends BaseDataTableService
{
    protected $with = [];
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->addDisplayNameLink();
        $dataTable->addMiddleColumn(Connector::FIELD_SHORT_DESCRIPTION, null, "Description");
        if(QMAuth::isAdmin()){
            $dataTable->addMiddleColumn(Connector::FIELD_ENABLED);
        }
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     *
     * @param Connector $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Connector $model){
        $qb = $this->buildDataTableQueryFromRequest($model);
        if(!QMAuth::isAdmin()){
            $qb->where(Connector::FIELD_ENABLED, 1);
        }
        return $qb;
    }
}
