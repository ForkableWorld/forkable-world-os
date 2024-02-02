<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Filters;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use JsonSerializable;
use App\AuthorizedToSee;
use App\Contracts\Filter as FilterContract;
use App\Metable;
use App\Astral;
use App\ProxiesCanSeeToGate;

abstract class Filter implements FilterContract, JsonSerializable
{
    use Metable;
    use AuthorizedToSee;
    use ProxiesCanSeeToGate;

    /**
     * The displayable name of the filter.
     *
     * @var string
     */
    public $name;

    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract public function apply(Request $request, $query, $value);

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    abstract public function options(Request $request);

    /**
     * Get the component name for the filter.
     *
     * @return string
     */
    public function component()
    {
        return $this->component;
    }

    /**
     * Get the displayable name of the filter.
     *
     * @return string
     */
    public function name()
    {
        return $this->name ?: Astral::humanize($this);
    }

    /**
     * Get the key for the filter.
     *
     * @return string
     */
    public function key()
    {
        return get_class($this);
    }

    /**
     * Set the default options for the filter.
     *
     * @return array|mixed
     */
    public function default()
    {
        return '';
    }

    /**
     * Prepare the filter for JSON serialization.
     *
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function jsonSerialize(): array {
        $container = Container::getInstance();
        $req = $container->make(Request::class);
        $opts = $this->options($req);
        $collection = collect($opts);
        $map = $collection->map(function ($value, $key) {
            return is_array($value) ? ($value + ['value' => $key]) : ['name' => $key, 'value' => $value];
        });
        $values = $map->values();
        $all = $values->all();
        return array_merge([
            'class' => $this->key(),
            'name' => $this->name(),
            'component' => $this->component(),
            'options' => $all,
            'currentValue' => $this->default() ?? '',
        ], $this->meta());
    }
}
