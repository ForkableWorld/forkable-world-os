<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\Variable;
use App\Models\VariableCategory;
class VariableDataTableService extends BaseDataTableService
{
    /**
     * Get query source of dataTable.
     * @param Variable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Variable $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        return $dataTable
            ->addOpenImageTextLink() // Open text keeps the image from being shrunk
            ->addNameLink(VariableCategory::FIELD_NAME)
            ->addCategoryLink()
            ->addMiddleColumn(Variable::FIELD_NUMBER_OF_USER_VARIABLES, function(Variable $v) {
                return $v->getNumberOfUserVariablesButton()->getLink();
            })
            ->addAnalysisEndedAt()
            ->addUnitLink()
            ->addMiddleColumn(Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_CAUSE, function(Variable $v) {
                return $v->getNumberOfEffectsButton()->getLink();
            })
            ->addMiddleColumn(Variable::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS_AS_EFFECT, function(Variable $v) {
                return $v->getNumberOfAggregateCausesButton()->getLink();
            })
            ->addMiddleColumn(Variable::FIELD_NUMBER_OF_MEASUREMENTS, function(Variable $v) {
                return $v->getNumberOfMeasurementsButton()->getLink();
            })
            //->addPostLink()
            ;
    }
}
