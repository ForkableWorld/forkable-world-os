<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Builder;
class MeasurementDataTableService extends BaseDataTableService
{
	/**
	 * @param BaseModel $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function buildDataTableQueryFromRequest(BaseModel $model): Builder{
        $this->with = ['variable:'.Variable::getImportantColumnsForRelation(), 'user', 'user_variable'];
        return parent::buildDataTableQueryFromRequest($model);
    }
    /**
     * Get query source of dataTable.
     *
     * @param Measurement $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Measurement $model): Builder{
        return $this->buildDataTableQueryFromRequest($model);
    }
    /**
     * Build DataTable class.
     * @param null $query
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->addOpenImageTextLink();
        $dataTable->skipTotalRecords(); // Too slow with lots of records
        return $dataTable
            ->addPrefixColumn(Measurement::FIELD_VALUE)
            ->addUnitAbbreviatedLink(BaseEloquentDataTable::PREFIX)
            ->addUserVariableLink(BaseEloquentDataTable::PREFIX)
            ->addPrefixColumn(Measurement::FIELD_START_TIME, function(Measurement $m) {
                return $m->getStartSince();
            }, "Recorded")
            ->addVariableCategoryLink()
            ->addDataSourceLink()
            ->addColumn(Measurement::FIELD_NOTE, function(Measurement $m) {
                $meta = $m->getNoteMessage();
                if($meta){
                    return $meta;
                } else{
                    return "N/A";
                }
            })
            ->addInternalErrorMessage();
    }
}
