<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataTableServices;
use App\Models\User;
use App\UI\HtmlHelper;
class UserDataTableService extends BaseDataTableService
{
    protected $with = [];
    /**
     * Get query source of dataTable.
     *
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model){
        return $this->buildDataTableQueryFromRequest($model);
    }
    public function getEloquentDataTable($query = null): BaseEloquentDataTable {
        $dataTable = parent::getEloquentDataTable($query);
        $dataTable->addOpenImageTextLink();
        $dataTable
            ->addNameLink(User::FIELD_DISPLAY_NAME)
            ->addAnalysisEndedAt(BaseEloquentDataTable::MIDDLE)
            ->addMiddleColumn(User::FIELD_NUMBER_OF_MEASUREMENTS, function(User $v) {
                $b = $v->getMeasurementsButton();
                return $b->getLink();
            }, "Measurements")
            ->addMiddleColumn(User::FIELD_LAST_LOGIN_AT)
            ->addMiddleColumn(User::FIELD_USER_REGISTERED, function(User $v) {
                return $v->getTimeSinceRegistered();
            }, "Registered")
            ->addMiddleColumn(User::FIELD_NUMBER_OF_CONNECTIONS, function(User $v) {
                return $v->getNumberOfConnectionsButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_NUMBER_OF_CORRELATIONS, function(User $v) {
                return $v->getNumberOfCorrelationsButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_NUMBER_OF_STUDIES, function(User $v) {
                return $v->getNumberOfStudiesButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_NUMBER_OF_TRACKING_REMINDERS, function(User $v) {
                return $v->getNumberOfTrackingRemindersButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_NUMBER_OF_USER_VARIABLES, function(User $v) {
                return $v->getNumberOfUserVariablesButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_NUMBER_OF_VOTES, function(User $v) {
                return $v->getNumberOfVotesButton()->getLink();
            })
            ->addMiddleColumn(User::FIELD_USER_URL, function(User $v) {
                if($v->user_url){
                    return HtmlHelper::generateLink($v->user_url, $v->user_url, true);
                }
                return "N/A";
            })
            ->addAnalysisStartedAt(BaseEloquentDataTable::MIDDLE)
            //->addPostLink()
        ;
        $arr = [];
        $arr = array_merge($arr, [
            'user_login' => [
                'title'      => 'Username',
                'data'       => 'user_login',
                'name'       => 'user_login',
                'searchable' => true,
                'orderable' => false
            ],
            'user_email' => [
                'title'      => 'Email',
                'render'     => '"<a href=\"mailto:"+data+"\">"+data+"</a>"',
                'data'       => 'user_email',
                'name'       => 'user_email',
                'searchable' => true,
                'orderable' => false
            ],
        ]);
        $dataTable->addMiddleColumns($arr);
        $dataTable->addUserIdLink();
        $dataTable->addMiddleColumn(User::FIELD_ROLES, function(User $v) {
            return $v->getRolesString();
        });
        return $dataTable;
    }
}
