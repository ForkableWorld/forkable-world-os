<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Types\TimeHelper;
class TrackingReminderNotificationDataTableService extends BaseDataTableService
{
    protected $with = ['user', 'variable'];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): \App\DataTableServices\BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable
            ->addVariableImageNameLink()
            ->addMiddleColumn(TrackingReminderNotification::FIELD_NOTIFY_AT,
                function(TrackingReminderNotification $m){
                    return TimeHelper::timeSinceHumanStringHtml($m->notify_at);
            }, "Due")
            ->addMiddleColumn(TrackingReminderNotification::FIELD_NOTIFIED_AT,
                function(TrackingReminderNotification $m){
                return TimeHelper::timeSinceHumanStringHtml($m->notified_at);
            })
            ->addMiddleColumn('tracking_reminder_link',
                function(TrackingReminderNotification $m) {
                $id = $m->tracking_reminder_id;
                return TrackingReminder::generateDataLabShowLink($id);
            })
            ->addUserVariableLink()
            ->addVariableCategoryLink();
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\TrackingReminderNotification $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(TrackingReminderNotification $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
