<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;
use App\Astral\BaseAstralAstralResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Contracts\RelatableField;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceIndexRequest;
use App\PerformsQueries;
use App\Rules\Relatable;
use App\TrashedStatus;
class BelongsTo extends Field implements RelatableField
{
    use FormatsRelatableDisplayValues;
    use ResolvesReverseRelation;
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'belongs-to-field';
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
     * The name of the Eloquent "belongs to" relationship.
     *
     * @var string
     */
    public $belongsToRelationship;
    /**
     * The key of the related Eloquent model.
     *
     * @var string
     */
    public $belongsToId;
    /**
     * The column that should be displayed for the field.
     *
     * @var \Closure
     */
    public $display;
    /**
     * Indicates if the related resource can be viewed.
     *
     * @var bool
     */
    public $viewable = true;
    /**
     * Indicates if this relationship is searchable.
     *
     * @var bool
     */
    public $searchable = false;
    /**
     * The callback that should be run when the field is filled.
     *
     * @var \Closure
     */
    public $filledCallback;
    /**
     * The attribute that is the inverse of this relationship.
     *
     * @var string
     */
    public $inverse;
    /**
     * The displayable singular label of the relation.
     *
     * @var string
     */
    public $singularLabel;
    /**
     * Indicates whether the field should display the "With Trashed" option.
     *
     * @var bool
     */
    public $displaysWithTrashed = true;
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
        /** @var BaseAstralAstralResource $resource */
        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);
        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->belongsToRelationship = $this->attribute;
        $this->singularLabel = $name;
    }
    /**
     * Determine if the field should be displayed for the given request.
     * @param  Request $request
     * @return bool
     */
    public function authorize(Request $request): bool
    {
        return $this->isNotRedundant($request) && call_user_func(
            [$this->resourceClass, 'authorizedToViewAny'], $request
        ) && parent::authorize($request);
    }
    /**
     * Determine if the field is not redundant.
     * Ex: Is this a "user" belongs to field in a blog post list being shown on the "user" detail page.
     * @param  Request  $request
     * @return bool
     */
    public function isNotRedundant(Request $request): bool
    {
        return ! $request instanceof ResourceIndexRequest || ! $this->isReverseRelation($request);
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
        $value = null;
        if ($resource->relationLoaded($this->attribute)) {
            $value = $resource->getRelation($this->attribute);
        }
        if (! $value) {
            /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $bt */
            $bt = $resource->{$this->attribute}();
            /** @var BelongsTo|Builder $qb */
            $bt = $bt->withoutGlobalScopes();
            try {
                $value = $bt->getResults();
            } catch (\Throwable $e){
                le("make sure you're providing the right relationship method to \App\Astral\Resource::belongsTo like in \App\Properties\Base\BaseCauseVariableIdProperty::getDetailsField. ".$e->getMessage());
                $value = $bt->getResults();
            }
        }
        /** @var Model $value */
        if ($value) {
            $resource->setRelationAndAddToMemory($this->attribute, $value);
            $this->belongsToId = $value->getKey();
            /** @var BaseAstralAstralResource $resource */
            $resource = new $this->resourceClass($value);
            $this->value = $this->formatDisplayValue($resource);
            $this->viewable = $this->viewable
                && $resource->authorizedToView(request());
        }
    }
    /**
     * Define the callback that should be used to resolve the field's value.
     *
     * @param  callable  $displayCallback
     * @return $this
     */
    public function displayUsing(callable $displayCallback): BelongsTo
    {
        return $this->display($displayCallback);
    }
    /**
     * Get the validation rules for this field.
     * @param  AstralRequest $request
     * @return array
     */
    public function getRules(AstralRequest $request): array
    {
        $query = $this->buildAssociatableQuery(
            $request, $request->{$this->attribute.'_trashed'} === 'true'
        );
        return array_merge_recursive(parent::getRules($request), [
            $this->attribute => array_filter([
                $this->nullable ? 'nullable' : 'required',
                new Relatable($request, $query),
            ]),
        ]);
    }
    /**
     * Hydrate the given attribute on the model based on the incoming request.
     * @param  AstralRequest $request
     * @param  object  $model
     * @return void
     */
    public function fill(AstralRequest $request, $model)
    {
        $foreignKey = $this->getRelationForeignKeyName($model->{$this->attribute}());
        parent::fillInto($request, $model, $foreignKey);
        if ($model->isDirty($foreignKey)) {
            $model->unsetRelation($this->attribute);
        }
        if ($this->filledCallback) {
            call_user_func($this->filledCallback, $request, $model);
        }
    }
    /**
     * Hydrate the given attribute on the model based on the incoming request.
     * @param  AstralRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(AstralRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            $value = $request[$requestAttribute];
            $relation = Relation::noConstraints(function () use ($model) {
                return $model->{$this->attribute}();
            });
            if ($this->isNullValue($value)) {
                $relation->dissociate();
            } else {
                $relation->associate($relation->getQuery()->withoutGlobalScopes()->find($value));
            }
        }
    }
    /**
     * Build an associatable query for the field.
     * @param  AstralRequest  $request
     * @param  bool  $withTrashed
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function buildAssociatableQuery(AstralRequest $request, bool $withTrashed = false)
    {
        $model = forward_static_call(
            [$resourceClass = $this->resourceClass, 'newModel']
        );
        /** @var PerformsQueries $resourceClass */
        $query = $request->first === 'true'
                        ? $model->newQueryWithoutScopes()->whereKey($request->current)
                        : $resourceClass::buildIndexQuery(
                                $request, $model->newQuery(), $request->search,
                                [], [], TrashedStatus::fromBoolean($withTrashed)
                          );
        return $query->tap(function ($query) use ($request, $model) {
            forward_static_call($this->associatableQueryCallable($request, $model), $request, $query);
        });
    }
    /**
     * Get the associatable query method name.
     * @param  AstralRequest  $request
     * @param Model $model
     * @return array
     */
    protected function associatableQueryCallable(AstralRequest $request, $model): array
    {
        return ($method = $this->associatableQueryMethod($request, $model))
                    ? [$request->resource(), $method]
                    : [$this->resourceClass, 'relatableQuery'];
    }
    /**
     * Get the associatable query method name.
     * @param  AstralRequest  $request
     * @param Model $model
     * @return string
     */
    protected function associatableQueryMethod(AstralRequest $request, $model): string
    {
        $method = 'relatable'.Str::plural(class_basename($model));
        if (method_exists($request->resource(), $method)) {
            return $method;
        }
        le("associatableQueryMethod $method does not exist on resource:".$request->resource());throw new \LogicException();
    }
    /**
     * Format the given associatable resource.
     * @param  AstralRequest  $request
     * @param  mixed  $resource
     * @return array
     */
    public function formatAssociatableResource(AstralRequest $request, $resource): array
    {
        return array_filter([
            'avatar' => $resource->resolveAvatarUrl($request),
            'display' => $this->formatDisplayValue($resource),
            'value' => $resource->getKey(),
        ]);
    }
    /**
     * Specify if the relationship should be searchable.
     * @param  bool $value
     * @return $this
     */
    public function searchable(bool $value = true): BelongsTo
    {
        $this->searchable = $value;
        return $this;
    }
    /**
     * Specify if the related resource can be viewed.
     * @param  bool $value
     * @return $this
     */
    public function viewable(bool $value = true): BelongsTo
    {
        $this->viewable = $value;
        return $this;
    }
    /**
     * Specify a callback that should be run when the field is filled.
     * @param  \Closure  $callback
     * @return $this
     */
    public function filled(\Closure $callback): BelongsTo
    {
        $this->filledCallback = $callback;
        return $this;
    }
    /**
     * Set the attribute name of the inverse of the relationship.
     * @param  string  $inverse
     * @return $this
     */
    public function inverse(string $inverse): BelongsTo
    {
        $this->inverse = $inverse;
        return $this;
    }
    /**
     * Set the displayable singular label of the resource.
     *
     * @return $this
     */
    public function singularLabel($singularLabel): BelongsTo
    {
        $this->singularLabel = $singularLabel;
        return $this;
    }
    /**
     * hides the "With Trashed" option.
     *
     * @return $this
     */
    public function withoutTrashed(): BelongsTo
    {
        $this->displaysWithTrashed = false;
        return $this;
    }
    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'belongsToId' => $this->belongsToId,
            'belongsToRelationship' => $this->belongsToRelationship,
            'label' => forward_static_call([$this->resourceClass, 'label']),
            'resourceName' => $this->resourceName,
            'reverse' => $this->isReverseRelation(app(AstralRequest::class)),
            'searchable' => $this->searchable,
            'singularLabel' => $this->singularLabel,
            'viewable' => $this->viewable,
            'displaysWithTrashed' => $this->displaysWithTrashed,
        ], parent::jsonSerialize());
    }
}
