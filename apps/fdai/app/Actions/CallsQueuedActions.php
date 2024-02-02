<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Astral;

trait CallsQueuedActions
{
    use InteractsWithQueue, SerializesModels;

    /**
     * The action class name.
     *
     * @var \App\Actions\Action
     */
    public $action;

    /**
     * The method that should be called on the action.
     *
     * @var string
     */
    public $method;

    /**
     * The resolved fields.
     *
     * @var \App\Fields\ActionFields
     */
    public $fields;

    /**
     * The batch ID of the action event records.
     *
     * @var string
     */
    public $batchId;

    /**
     * Call the action using the given callback.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function callAction($callback)
    {
        Astral::actionEvent()->markBatchAsRunning($this->batchId);

        $action = $this->setJobInstanceIfNecessary($this->action);

        $callback($action);

        if (! $this->job->hasFailed() && ! $this->job->isReleased()) {
            Astral::actionEvent()->markBatchAsFinished($this->batchId);
        }
    }

    /**
     * Set the job instance of the given class if necessary.
     *
     * @param  mixed  $instance
     * @return mixed
     */
    protected function setJobInstanceIfNecessary($instance)
    {
        if (in_array(InteractsWithQueue::class, class_uses_recursive(get_class($instance)))) {
            $instance->setJob($this->job);
        }

        return $instance;
    }

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName()
    {
        return get_class($this->action);
    }
}
