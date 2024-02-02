<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Observers;

use App\Models\UserVariable;
class UserVariableObserver
{
    /**
     * Handle the user variable "created" event.
     *
     * @param  UserVariable  $userVariable
     * @return void
     */
    public function created(UserVariable $userVariable)
    {
        //$userVariable->beforeChange();
    }

    /**
     * Handle the user variable "updated" event.
     *
     * @param  UserVariable  $userVariable
     * @return void
     */
    public function updated(UserVariable $userVariable)
    {
        //$userVariable->beforeChange($userVariable);
    }

    /**
     * Handle the user variable "deleted" event.
     *
     * @param  UserVariable  $userVariable
     * @return void
     */
    public function deleted(UserVariable $userVariable)
    {
        //
    }

    /**
     * Handle the user variable "restored" event.
     *
     * @param  UserVariable  $userVariable
     * @return void
     */
    public function restored(UserVariable $userVariable)
    {
        //
    }

    /**
     * Handle the user variable "force deleted" event.
     *
     * @param  UserVariable  $userVariable
     * @return void
     */
    public function forceDeleted(UserVariable $userVariable)
    {
        //
    }
}
