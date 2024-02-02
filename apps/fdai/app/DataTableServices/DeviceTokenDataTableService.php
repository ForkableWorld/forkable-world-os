<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\DeviceToken;
use App\Slim\Middleware\QMAuth;
use App\Types\TimeHelper;
class DeviceTokenDataTableService extends BaseDataTableService
{
    protected $with = [];
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return BaseEloquentDataTable
     */
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->addMiddleColumns([
            DeviceToken::FIELD_PLATFORM,
        ]);
        if(QMAuth::isAdmin()){
            $dataTable->addUserIdLink();
        }
        $dataTable->addColumn(DeviceToken::FIELD_LAST_NOTIFIED_AT, function(DeviceToken $m) {
                return TimeHelper::timeSinceHumanString($m->last_notified_at);
            }, "Notified")
            ->addColumn(DeviceToken::FIELD_RECEIVED_AT, function(DeviceToken $m) {
                return TimeHelper::timeSinceHumanString($m->received_at);
            }, "Acknowledgement")
            ->addColumn(DeviceToken::FIELD_LAST_CHECKED_AT, function(DeviceToken $m) {
                return TimeHelper::timeSinceHumanString($m->last_checked_at);
            }, "Checked for Notifications")
            ->addMiddleColumns([
                DeviceToken::FIELD_NUMBER_OF_NEW_TRACKING_REMINDER_NOTIFICATIONS,
                DeviceToken::FIELD_NUMBER_OF_NOTIFICATIONS_LAST_SENT,
                DeviceToken::FIELD_NUMBER_OF_WAITING_TRACKING_REMINDER_NOTIFICATIONS,
                DeviceToken::FIELD_SERVER_HOSTNAME,
            ])
            ->addColumn('errors', function(DeviceToken $m) {
                return $m->getInternalErrorMessageLink();
            });
        return $dataTable;
    }
    /**
     * Get query source of dataTable.
     * @param \App\Models\DeviceToken $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DeviceToken $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
}
