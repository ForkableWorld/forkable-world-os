<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use App\Fields\ActionFields;
use App\Http\Requests\ActionRequest;
use App\Astral;

class DispatchAction
{
    /**
     * Dispatch the given action.
     *
     * @param  \App\Http\Requests\ActionRequest $request
     * @param  \App\Actions\Action $action
     * @param  string $method
     * @param  \Illuminate\Support\Collection $models
     * @param  \App\Fields\ActionFields $fields
     * @return mixed
     */
    public static function forModels(
        ActionRequest $request,
        Action $action,
        $method,
        Collection $models,
        ActionFields $fields
    ) {
        if ($models->isEmpty()) {
            return;
        }

        if ($action instanceof ShouldQueue) {
            return static::queueForModels($request, $action, $method, $models);
        }

        return Transaction::run(function ($batchId) use ($fields, $request, $action, $method, $models) {
            if (! $action->withoutActionEvents) {
                Astral::actionEvent()->createForModels($request, $action, $batchId, $models);
            }

            return $action->withBatchId($batchId)->{$method}($fields, $models);
        }, function ($batchId) {
            Astral::actionEvent()->markBatchAsFinished($batchId);
        });
    }

    /**
     * Dispatch the given action in the background.
     *
     * @param  \App\Http\Requests\ActionRequest  $request
     * @param  \App\Actions\Action  $action
     * @param  string  $method
     * @param  \Illuminate\Support\Collection  $models
     * @return void
     */
    protected static function queueForModels(ActionRequest $request, Action $action, $method, Collection $models)
    {
        return Transaction::run(function ($batchId) use ($request, $action, $method, $models) {
            if (! $action->withoutActionEvents) {
                Astral::actionEvent()->createForModels($request, $action, $batchId, $models, 'waiting');
            }

            Queue::connection(static::connection($action))->pushOn(
                static::queue($action),
                new CallQueuedAction(
                    $action, $method, $request->resolveFields(), $models, $batchId
                )
            );
        });
    }

    /**
     * Extract the queue connection for the action.
     *
     * @param  \App\Actions\Action  $action
     * @return string|null
     */
    protected static function connection($action)
    {
        return property_exists($action, 'connection') ? $action->connection : null;
    }

    /**
     * Extract the queue name for the action.
     *
     * @param  \App\Actions\Action  $action
     * @return string|null
     */
    protected static function queue($action)
    {
        return property_exists($action, 'queue') ? $action->queue : null;
    }
}
