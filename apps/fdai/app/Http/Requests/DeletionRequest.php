<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use Closure;

class DeletionRequest extends AstralRequest
{
    use QueriesResources;

    /**
     * Get the selected models for the action in chunks.
     *
     * @param  int  $count
     * @param  \Closure  $callback
     * @param  \Closure  $authCallback
     * @return mixed
     */
    protected function chunkWithAuthorization($count, Closure $callback, Closure $authCallback)
    {
        $this->toSelectedResourceQuery()->when(! $this->forAllMatchingResources(), function ($query) {
            $query->whereKey($this->resources);
        })->tap(function ($query) {
            $query->getQuery()->orders = [];
        })->chunkById($count, function ($models) use ($callback, $authCallback) {
            $models = $authCallback($models);

            if ($models->isNotEmpty()) {
                $callback($models);
            }
        });
    }

    /**
     * Get the query for the models that were selected by the user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function toSelectedResourceQuery()
    {
        if ($this->forAllMatchingResources()) {
            return $this->toQuery();
        }

        return $this->newQueryWithoutScopes();
    }

    /**
     * Determine if the request is for all matching resources.
     *
     * @return bool
     */
    public function forAllMatchingResources()
    {
        return $this->resources === 'all';
    }
}
