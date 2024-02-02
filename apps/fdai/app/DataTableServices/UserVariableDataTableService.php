<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\UserVariable;
class UserVariableDataTableService extends BaseDataTableService
{
    protected $with = ['variable', 'user'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->skipTotalRecords(); // Too slow with lots of records
        return $dataTable
            ->addVariableImageNameLink()
            //->addPostLink()
            ->addColumn(UserVariable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, function(UserVariable $v) {
                return $v->latest_tagged_measurement_start_at;
            })
            ->addColumn(UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE, function(UserVariable $v) {
                return $v->getNumberOfUserVariableRelationshipsWhereCauseButton()->getLink();
            })
            ->addColumn(UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT, function(UserVariable $v) {
                return $v->getNumberOfUserVariableRelationshipsWhereEffectButton()->getLink();
            })
            ->addColumn(UserVariable::FIELD_NUMBER_OF_MEASUREMENTS, function(UserVariable $v) {
                return $v->getNumberOfMeasurementsButton()->getLink();
            })
            ->addColumn(UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS, function(UserVariable $v) {
                return $v->getNumberOfTrackingRemindersButton()->getLink();
            })
            ->addAnalysisEndedAt()
            ->addInternalErrorMessage()
            ->addMiddleColumns([
                UserVariable::FIELD_NUMBER_OF_MEASUREMENTS                  => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Measurements',
                    'data'       => UserVariable::FIELD_NUMBER_OF_MEASUREMENTS,
                    'name'       => UserVariable::FIELD_NUMBER_OF_MEASUREMENTS,
                    'searchable' => false
                ]),
                UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS                 => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Reminders',
                    'data'       => UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS,
                    'name'       => UserVariable::FIELD_NUMBER_OF_TRACKING_REMINDERS,
                    'searchable' => false
                ]),
                UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT                 => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Causes',
                    'data'       => UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT,
                    'name'       => UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT,
                    'searchable' => false
                ]),
                UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE                 => new \Yajra\DataTables\Html\Column([
                    'title'      => 'Effects',
                    'data'       => UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE,
                    'name'       => UserVariable::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE,
                    'searchable' => false
                ]),
                'status' => [
                    'title'      => 'Status',
                    'data'       => 'status',
                    'name'       => 'status',
                    'searchable' => true
                ],
                'latest_tagged_measurement_start_at' => [
                    'title'      => 'Latest Tagged Measurement',
                    'data'       => 'latest_tagged_measurement_start_at',
                    'name'       => 'latest_tagged_measurement_start_at',
                    'searchable' => false
                ],

            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param UserVariable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(UserVariable $model)
    {
        return $this->buildDataTableQueryFromRequest($model);
    }
}
