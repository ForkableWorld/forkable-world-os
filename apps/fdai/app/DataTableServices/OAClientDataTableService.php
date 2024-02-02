<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\DataTableServices;
use App\Models\OAClient;
class OAClientDataTableService extends BaseDataTableService
{
    /**
     * Build DataTable class.
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable
            ->addOpenImageTextLink()
            ->addColumn(OAClient::FIELD_CLIENT_ID, function(OAClient $m) {
                return $m->getDataLabDisplayNameLink();
            })
            ->addRelatedDataDropDown();
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\OAClient $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(OAClient $model){
        return $this->buildDataTableQueryFromRequest($model);
    }

}
