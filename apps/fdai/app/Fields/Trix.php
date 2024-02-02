<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use App\Contracts\Deletable as DeletableContract;
use App\Contracts\Storable as StorableContract;
use App\Http\Requests\AstralRequest;
use App\Trix\DeleteAttachments;
use App\Trix\DetachAttachment;
use App\Trix\DiscardPendingAttachments;
use App\Trix\PendingAttachment;
use App\Trix\StorePendingAttachment;

class Trix extends Field implements StorableContract, DeletableContract
{
    use Storable, Deletable, Expandable;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'trix-field';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * Indicates if the field should accept files.
     *
     * @var bool
     */
    public $withFiles = false;

    /**
     * The callback that should be executed to store file attachments.
     *
     * @var callable
     */
    public $attachCallback;

    /**
     * The callback that should be executed to delete persisted file attachments.
     *
     * @var callable
     */
    public $detachCallback;

    /**
     * The callback that should be executed to discard file attachments.
     *
     * @var callable
     */
    public $discardCallback;

    /**
     * Specify the callback that should be used to store file attachments.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function attach(callable $callback)
    {
        $this->withFiles = true;

        $this->attachCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to delete a single, persisted file attachment.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function detach(callable $callback)
    {
        $this->withFiles = true;

        $this->detachCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to discard pending file attachments.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function discard(callable $callback)
    {
        $this->withFiles = true;

        $this->discardCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback that should be used to delete the field.
     *
     * @param  callable  $deleteCallback
     * @return $this
     */
    public function delete(callable $deleteCallback)
    {
        $this->withFiles = true;

        $this->deleteCallback = $deleteCallback;

        return $this;
    }

    /**
     * Specify that file uploads should not be allowed.
     *
     * @param  string  $disk
     * @param  string  $path
     * @return $this
     */
    public function withFiles($disk = null, $path = '/')
    {
        $this->withFiles = true;

        $this->disk($disk)->path($path);

        $this->attach(new StorePendingAttachment($this))
             ->detach(new DetachAttachment($this))
             ->delete(new DeleteAttachments($this))
             ->discard(new DiscardPendingAttachments($this))
             ->prunable();

        return $this;
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void|\Closure
     */
    protected function fillAttribute(AstralRequest $request, $requestAttribute, $model, $attribute)
    {
        $callbacks = [];

        $maybeCallback = parent::fillAttribute($request, $requestAttribute, $model, $attribute);
        if (is_callable($maybeCallback)) {
            $callbacks[] = $maybeCallback;
        }

        if ($request->{$this->attribute.'DraftId'} && $this->withFiles) {
            $callbacks[] = function () use ($request, $model, $attribute) {
                PendingAttachment::persistDraft(
                    $request->{$this->attribute.'DraftId'},
                    $this,
                    $model
                );
            };
        }

        if (count($callbacks)) {
            return function () use ($callbacks) {
                collect($callbacks)->each->__invoke();
            };
        }
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), [
            'shouldShow' => $this->shouldBeExpanded(),
            'withFiles' => $this->withFiles,
        ]);
    }
}
