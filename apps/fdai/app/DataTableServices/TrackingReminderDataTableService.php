<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\TrackingReminder;
use Illuminate\Database\Eloquent\Builder;
class TrackingReminderDataTableService extends BaseDataTableService
{
    public $with = [
        'variable',
        'user_variable'
    ];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable{
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->addImageAndNameLink();
        return $dataTable
            ->addVariableImageNameLink()
            ->addMiddleColumn('frequency', function($m) {
                /** @var TrackingReminder $m */
                return $m->getFrequencyDescription();
            })
            ->addMiddleColumns([
                'start_tracking_date' => [
                    'title'      => 'Start Tracking Date',
                    'data'       => 'start_tracking_date',
                    'name'       => 'start_tracking_date',
                    'searchable' => false,
                    'orderable' => true,
                ],
                'stop_tracking_date' => [
                    'title'      => 'Stop Tracking Date',
                    'data'       => 'stop_tracking_date',
                    'name'       => 'stop_tracking_date',
                    'searchable' => false,
                    'orderable' => true,
                ],
            ])
            ->addUserVariableLink()
            ->addVariableCategoryLink()
            ->addMiddleColumns([
                'reminder_start_time' => [
                    'title'      => 'Reminder Start Time',
                    'data'       => 'reminder_start_time',
                    'name'       => 'reminder_start_time',
                    'searchable' => false
                ],
                'reminder_frequency' => [
                    'title'      => 'Reminder Frequency',
                    'data'       => 'reminder_frequency',
                    'name'       => 'reminder_frequency',
                    'searchable' => false
                ],
                'last_tracked' => [
                    'title'      => 'Last Tracked',
                    'data'       => 'last_tracked',
                    'name'       => 'last_tracked',
                    'searchable' => false
                ],
                'instructions' => [
                    'title'      => 'Instructions',
                    'data'       => 'instructions',
                    'name'       => 'instructions',
                    'searchable' => true
                ],
                'image_url' => [
                    'title'      => 'Image Url',
                    'render'     => '"<a href=\""+data+"\">"+data+"</a>"',
                    'data'       => 'image_url',
                    'name'       => 'image_url',
                    'searchable' => true
                ],
                'user_variable_id' => [
                    'title'      => 'User Variable Id',
                    'data'       => 'user_variable_id',
                    'name'       => 'user_variable_id',
                    'searchable' => false
                ],
                'latest_tracking_reminder_notification_notify_at' => [
                    'title'      => 'Latest Tracking Reminder Notification Notify At',
                    'data'       => 'latest_tracking_reminder_notification_notify_at',
                    'name'       => 'latest_tracking_reminder_notification_notify_at',
                    'searchable' => false
                ]
            ]);
    }
    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\TrackingReminder $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(TrackingReminder $model): Builder{
        return $this->buildDataTableQueryFromRequest($model);
    }
	public function getDefaultSortField(): string{
		return TrackingReminder::FIELD_REMINDER_START_TIME;
	}
}
