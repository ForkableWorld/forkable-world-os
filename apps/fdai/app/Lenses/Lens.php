<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingReturnTypeInspection */

namespace App\Lenses;

use ArrayAccess;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Illuminate\Http\Resources\DelegatesToResource;
use Illuminate\Support\Str;
use JsonSerializable;
use App\AuthorizedToSee;
use App\Contracts\ListableField;
use App\Fields\FieldCollection;
use App\Fields\ID;
use App\Http\Requests\LensRequest;
use App\Http\Requests\AstralRequest;
use App\Astral;
use App\ProxiesCanSeeToGate;
use App\ResolvesActions;
use App\ResolvesCards;
use App\ResolvesFilters;
use stdClass;

abstract class Lens implements ArrayAccess, JsonSerializable, UrlRoutable
{
    use
        AuthorizedToSee,
        ConditionallyLoadsAttributes,
        DelegatesToResource,
        ProxiesCanSeeToGate,
        ResolvesActions,
        ResolvesCards,
        ResolvesFilters;

    /**
     * The displayable name of the lens.
     *
     * @var string
     */
    public $name;

    /**
     * The underlying model resource instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $resource;

    /**
     * Execute the query for the lens.
     *
     * @param  \App\Http\Requests\LensRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    abstract public static function query(LensRequest $request, $query);

    /**
     * Get the fields displayed by the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    abstract public function fields(Request $request);

    /**
     * Create a new lens instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $resource
     * @return void
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource ?: new stdClass;
    }

    /**
     * Get the displayable name of the lens.
     *
     * @return string
     */
    public function name()
    {
        return $this->name ?: Astral::humanize($this);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey()
    {
        return Str::slug($this->name(), '-', null);
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return $request->newResource()->actions($request);
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return array
     */
    public function serializeForIndex(AstralRequest $request)
    {
        return $this->serializeWithId($this->resolveFields($request)
                ->reject(function ($field) {
                    return $field instanceof ListableField || ! $field->showOnIndex;
                }));
    }

    /**
     * Resolve the given fields to their values.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \App\Fields\FieldCollection
     */
    public function resolveFields(AstralRequest $request)
    {
        $available = $this->availableFields($request);
        $resolved = $available->each->resolve($this->resource);
        $authorized = $resolved->filter->authorize($request);
        $resolveForDisplay = $authorized->each->resolveForDisplay($this->resource);
        return $resolveForDisplay->values();
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @return \App\Fields\FieldCollection
     */
    public function availableFields(AstralRequest $request)
    {
        $all = $this->fields($request);
        $filtered = $this->filter($all);
        $values = array_values($filtered);
        return new FieldCollection($values);
    }

    /**
     * Prepare the lens for JSON serialization using the given fields.
     *
     * @param  \App\Fields\FieldCollection  $fields
     * @return array
     */
    protected function serializeWithId(FieldCollection $fields)
    {
        return [
            'id' => $fields->whereInstanceOf(ID::class)->first() ?: ID::forModel($this->resource),
            'fields' => $fields->all(),
        ];
    }

    /**
     * Prepare the lens for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return [
            'name' => $this->name(),
            'uriKey' => $this->uriKey(),
        ];
    }
}
