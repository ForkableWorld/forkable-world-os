<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Observers;

use App\Models\GithubRepositories;

class GithubRepositoriesObserver
{
    /**
     * Handle the item "created" event.
     *
     * @param  GithubRepositories $item
     * @return void
     */
    public function created(GithubRepositories $item)
    {
        //
    }

    /**
     * Handle the item "updated" event.
     *
     * @param  GithubRepositories $item
     * @return void
     */
    public function updated(GithubRepositories $item)
    {
        //
    }

    /**
     * Handle the item "deleted" event.
     *
     * @param  GithubRepositories $item
     * @return void
     */
    public function deleted(GithubRepositories $item)
    {
        //
    }

    /**
     * Handle the item "restored" event.
     *
     * @param  GithubRepositories $item
     * @return void
     */
    public function restored(GithubRepositories $item)
    {
        //
    }

    /**
     * Handle the item "force deleted" event.
     *
     * @param  GithubRepositories $item
     * @return void
     */
    public function forceDeleted(GithubRepositories $item)
    {
        //
    }
}
