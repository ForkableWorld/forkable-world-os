<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Contracts\Deletable as DeletableContract;
use App\Contracts\ListableField;
use App\Contracts\RelatableField;
use App\Http\Requests\AstralRequest;
use App\Rules\NotAttached;
use App\Rules\RelatableAttachment;
use App\TrashedStatus;

class BelongsToMany extends Field implements DeletableContract, ListableField, RelatableField
{
    use Deletable, DetachesPivotModels, FormatsRelatableDisplayValues;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'belongs-to-many-field';

    /**
     * The class name of the related resource.
     *
     * @var string
     */
    public $resourceClass;

    /**
     * The URI key of the related resource.
     *
     * @var string
     */
    public $resourceName;

    /**
     * The name of the Eloquent "belongs to many" relationship.
     *
     * @var string
     */
    public $manyToManyRelationship;

    /**
     * The callback that should be used to resolve the pivot fields.
     *
     * @var callable
     */
    public $fieldsCallback;

    /**
     * The callback that should be used to resolve the pivot actions.
     *
     * @var callable
     */
    public $actionsCallback;

    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;

    /**
     * The displayable name that should be used to refer to the pivot class.
     *
     * @var string
     */
    public $pivotName;

    /**
     * Indicates if this relationship is searchable.
     *
     * @var bool
     */
    public $searchable = false;

    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  string|null  $resource
     * @return void
     */
    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;
        $this->deleteCallback = $this->detachmentCallback();

        $this->fieldsCallback = function () {
            return [];
        };

        $this->actionsCallback = function () {
            return [];
        };
    }

    /**
     * Determine if the field should be displayed for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function authorize(Request $request)
    {
        return call_user_func(
            [$this->resourceClass, 'authorizedToViewAny'], $request
        ) && parent::authorize($request);
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolve($resource, $attribute = null)
    {
        //
    }

    /**
     * Get the validation rules for this field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return array
     */
    public function getRules(AstralRequest $request)
    {
        $query = $this->buildAttachableQuery(
            $request, $request->{$this->attribute.'_trashed'} === 'true'
        );

        return array_merge_recursive(parent::getRules($request), [
            $this->attribute => ['required', new RelatableAttachment($request, $query)],
        ]);
    }

    /**
     * Get the creation rules for this field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return array
     */
    public function getCreationRules(AstralRequest $request)
    {
        return array_merge_recursive(parent::getCreationRules($request), [
            $this->attribute => [
                new NotAttached($request, $request->findModelOrFail()),
            ],
        ]);
    }

    /**
     * Build an attachable query for the field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  bool  $withTrashed
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildAttachableQuery(AstralRequest $request, $withTrashed = false)
    {
        $model = forward_static_call([$resourceClass = $this->resourceClass, 'newModel']);

        $query = $request->first === 'true'
                            ? $model->newQueryWithoutScopes()->whereKey($request->current)
                            : $resourceClass::buildIndexQuery(
                                    $request, $model->newQuery(), $request->search,
                                    [], [], TrashedStatus::fromBoolean($withTrashed)
                              );

        return $query->tap(function ($query) use ($request, $model) {
            forward_static_call($this->attachableQueryCallable($request, $model), $request, $query);
        });
    }

    /**
     * Get the attachable query method name.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function attachableQueryCallable(AstralRequest $request, $model)
    {
        return ($method = $this->attachableQueryMethod($request, $model))
                    ? [$request->resource(), $method]
                    : [$this->resourceClass, 'relatableQuery'];
    }

    /**
     * Get the attachable query method name.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    protected function attachableQueryMethod(AstralRequest $request, $model)
    {
        $method = 'relatable'.Str::plural(class_basename($model));

        if (method_exists($request->resource(), $method)) {
            return $method;
        }
    }

    /**
     * Format the given attachable resource.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  mixed  $resource
     * @return array
     */
    public function formatAttachableResource(AstralRequest $request, $resource)
    {
        return array_filter([
            'avatar' => $resource->resolveAvatarUrl($request),
            'display' => $this->formatDisplayValue($resource),
            'value' => $resource->getKey(),
        ]);
    }

    /**
     * Specify the callback to be executed to retrieve the pivot fields.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function fields($callback)
    {
        $this->fieldsCallback = $callback;

        return $this;
    }

    /**
     * Specify the callback to be executed to retrieve the pivot actions.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function actions($callback)
    {
        $this->actionsCallback = $callback;

        return $this;
    }

    /**
     * Set the displayable name that should be used to refer to the pivot class.
     *
     * @param  string  $pivotName
     * @return $this
     */
    public function referToPivotAs($pivotName)
    {
        $this->pivotName = $pivotName;

        return $this;
    }

    /**
     * Specify if the relationship should be searchable.
     *
     * @param  bool  $value
     * @return $this
     */
    public function searchable($value = true)
    {
        $this->searchable = $value;

        return $this;
    }

    /**
     * Set the displayable singular label of the resource.
     *
     * @return string
     */
    public function singularLabel($singularLabel)
    {
        $this->singularLabel = $singularLabel;

        return $this;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return array_merge([
            'belongsToManyRelationship' => $this->manyToManyRelationship,
            'listable' => true,
            'perPage'=> $this->resourceClass::$perPageViaRelationship,
            'resourceName' => $this->resourceName,
            'searchable' => $this->searchable,
            'singularLabel' => $this->singularLabel ?? Str::singular($this->name),
        ], parent::jsonSerialize());
    }
}
