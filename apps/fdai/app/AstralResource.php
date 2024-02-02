<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingReturnTypeInspection */

namespace App;

use App\Models\BaseModel;
use ArrayAccess;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Illuminate\Http\Resources\DelegatesToResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonSerializable;
use App\Actions\ActionEvent;
use App\Fields\ID;
use App\Http\Requests\AstralRequest;
use Laravel\Scout\Searchable;
abstract class AstralResource implements ArrayAccess, JsonSerializable, UrlRoutable
{
    use Authorizable,
        ConditionallyLoadsAttributes,
        DelegatesToResource,
        FillsFields,
        PerformsValidation,
        PerformsQueries,
        ResolvesActions,
        ResolvesFields,
        ResolvesFilters,
        ResolvesLenses,
        ResolvesCards;

    /**
     * The underlying model resource instance.
     *
     * @var BaseModel
     */
    public $resource;

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Other';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The relationships that should be eager loaded when performing an index query.
     *
     * @var array
     */
    public static $with = [];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = true;

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = true;

    /**
     * The number of results to display in the global search.
     *
     * @var int
     */
    public static $globalSearchResults = 5;

    /**
     * Where should the global search link to?
     *
     * @var string
     */
    public static $globalSearchLink = 'detail';

    /**
     * The per-page options used the resource index.
     *
     * @var array
     */
    public static $perPageOptions = [25, 50, 100];

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 5;

    /**
     * The cached soft deleting statuses for various resources.
     *
     * @var array
     */
    public static $softDeletes = [];

    /**
     * Indicates whether Astral should check for modifications between viewing and updating a resource.
     *
     * @var bool
     */
    public static $trafficCop = true;

    /**
     * The default displayable pivot class name.
     *
     * @var string
     */
    const DEFAULT_PIVOT_NAME = 'Pivot';

    /**
     * Create a new resource instance.
     * @param  Model  $resource
     * @return void
     */
    public function __construct(Model $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get the fields displayed by the resource.
     * @param  Request $request
     * @return array
     */
    abstract public function fields(Request $request);

    /**
     * Get the underlying model instance for the resource.
     *
     * @return BaseModel
     */
    public function model()
    {
        return $this->resource;
    }

    /**
     * Get the logical group associated with the resource.
     *
     * @return string
     */
    public static function group()
    {
        return static::$group;
    }

    /**
     * Determine if this resource is available for navigation.
     * @param  Request  $request
     * @return bool
     */
    public static function availableForNavigation(Request $request)
    {
        return true;
    }

    /**
     * Determine if this resource uses soft deletes.
     *
     * @return bool
     */
    public static function softDeletes()
    {
        if (isset(static::$softDeletes[static::$model])) {
            return static::$softDeletes[static::$model];
        }

        return static::$softDeletes[static::$model] = in_array(
            SoftDeletes::class, class_uses_recursive(static::newModel())
        );
    }

    /**
     * Determine if this resource is searchable.
     *
     * @return bool
     */
    public static function searchable()
    {
        return ! empty(static::$search) || static::usesScout();
    }

    /**
     * Determine whether the global search links will take the user to the detail page.
     * @param AstralRequest $request
     * @return string
     */
    public function globalSearchLink(AstralRequest $request)
    {
        return static::$globalSearchLink;
    }

    /**
     * Determine if this resource uses Laravel Scout.
     *
     * @return bool
     */
    public static function usesScout()
    {
        return in_array(Searchable::class, class_uses_recursive(static::newModel()));
    }

    /**
     * Get the searchable columns for the resource.
     *
     * @return array
     */
    public static function searchableColumns()
    {
        return empty(static::$search)
                    ? [static::newModel()->getKeyName()]
                    : static::$search;
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return Str::singular(static::label());
    }

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title()
    {
        return $this->{static::$title};
    }

    /**
     * Get the search result subtitle for the resource.
     *
     * @return string|null
     */
    public function subtitle()
    {
        return null;
    }

    /**
     * Get a fresh instance of the model represented by the resource.
     *
     * @return BaseModel|ActionEvent
     */
    public static function newModel()
    {
        $model = static::$model;

        return new $model;
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get meta information about this resource for client side consumption.
     * @param  Request  $request
     * @return array
     */
    public static function additionalInformation(Request $request)
    {
        return [];
    }

    /**
     * The pagination per-page options configured for this resource.
     *
     * @return array
     */
    public static function perPageOptions()
    {
        return static::$perPageOptions;
    }

    /**
     * Indicates whether Astral should check for modifications between viewing and updating a resource.
     * @param  Request  $request
     * @return  bool
     */
    public static function trafficCop(Request $request)
    {
        return static::$trafficCop;
    }

    /**
     * Filter and authorize the given values.
     * @param  AstralRequest  $request
     * @param array $values
     * @return Collection
     */
    protected function filterAndAuthorize(AstralRequest $request, array $values)
    {
        return collect(
            array_values($this->filter($values))
        )->filter->authorize($request, $request->newResource())->values();
    }

    /**
     * Prepare the resource for JSON serialization.
     * @param  AstralRequest  $request
     * @param Collection|null $fields
     * @return array
     */
    public function serializeForIndex(AstralRequest $request, Collection $fields = null)
    {
        return array_merge($this->serializeWithId($fields ?: $this->indexFields($request)), [
            'actions' => $this->availableActions($request),
            'authorizedToView' => $this->authorizedToView($request),
            'authorizedToCreate' => $this->authorizedToCreate($request),
            'authorizedToUpdate' => $this->authorizedToUpdateForSerialization($request),
            'authorizedToDelete' => $this->authorizedToDeleteForSerialization($request),
            'authorizedToRestore' => static::softDeletes() && $this->authorizedToRestore($request),
            'authorizedToForceDelete' => static::softDeletes() && $this->authorizedToForceDelete($request),
            'softDeletes' => static::softDeletes(),
            'softDeleted' => $this->isSoftDeleted(),
        ]);
    }

    /**
     * Prepare the resource for JSON serialization.
     * @param  AstralRequest  $request
     * @return array
     */
    public function serializeForDetail(AstralRequest $request)
    {
        return array_merge($this->serializeWithId($this->detailFieldsWithinPanels($request)), [
            'authorizedToCreate' => $this->authorizedToCreate($request),
            'authorizedToUpdate' => $this->authorizedToUpdate($request),
            'authorizedToDelete' => $this->authorizedToDelete($request),
            'authorizedToRestore' => static::softDeletes() && $this->authorizedToRestore($request),
            'authorizedToForceDelete' => static::softDeletes() && $this->authorizedToForceDelete($request),
            'softDeletes' => static::softDeletes(),
            'softDeleted' => $this->isSoftDeleted(),
        ]);
    }

    /**
     * Determine if the resource may be updated, factoring in attachments.
     * @param  AstralRequest  $request
     * @return bool
     */
    protected function authorizedToUpdateForSerialization(AstralRequest $request)
    {
        if ($request->viaManyToMany()) {
            return $request->findParentResourceOrFail()->authorizedToAttach(
                $request, $this->model()
            );
        }

        return $this->authorizedToUpdate($request);
    }

    /**
     * Determine if the resource may be deleted, factoring in detachments.
     *
     * @param  AstralRequest  $request
     * @return bool
     */
	protected function authorizedToDeleteForSerialization(AstralRequest $request)
    {
        if ($request->viaManyToMany()) {
            return $request->findParentResourceOrFail()->authorizedToDetach(
                $request, $this->model(), $request->viaRelationship
            );
        }

        return $this->authorizedToDelete($request);
    }

    /**
     * Determine if the resource is soft deleted.
     *
     * @return bool
     */
    public function isSoftDeleted()
    {
        return static::softDeletes() &&
               ! is_null($this->resource->{$this->resource->getDeletedAtColumn()});
    }

    /**
     * Prepare the resource for JSON serialization.
     * @return void
     */
    public function jsonSerialize(): mixed
    {
        $this->serializeWithId($this->resolveFields(
            resolve(AstralRequest::class)
        ));
    }

    /**
     * Prepare the resource for JSON serialization using the given fields.
     * @param  Collection $fields
     * @return array
     */
    protected function serializeWithId(Collection $fields)
    {
        return [
            'id' => $fields->whereInstanceOf(ID::class)->first() ?: ID::forModel($this->resource),
            'fields' => $fields->all(),
        ];
    }

    /**
     * Return the location to redirect the user after creation.
     * @param  AstralRequest  $request
     * @param  AstralResource $resource
     * @return string
     */
    public static function redirectAfterCreate(AstralRequest $request, $resource)
    {
        return '/resources/'.static::uriKey().'/'.$resource->getKey();
    }

    /**
     * Return the location to redirect the user after update.
     * @param  AstralRequest  $request
     * @param  AstralResource $resource
     * @return string
     */
    public static function redirectAfterUpdate(AstralRequest $request, $resource)
    {
        return '/resources/'.static::uriKey().'/'.$resource->getKey();
    }
}
