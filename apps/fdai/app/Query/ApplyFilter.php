<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Query;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class ApplyFilter
{
    /**
     * The filter instance.
     *
     * @var \App\Filters\Filter
     */
    public $filter;

    /**
     * The value of the filter.
     *
     * @var mixed
     */
    public $value;

    /**
     * Create a new invokable filter applier.
     *
     * @param  \App\Filters\Filter  $filter
     * @param  mixed  $value
     * @return void
     */
    public function __construct($filter, $value)
    {
        $this->value = $value;
        $this->filter = $filter;
    }

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|HasMany  $query
     * @return \Illuminate\Database\Eloquent\Builder|HasMany
     */
    public function __invoke(Request $request, $query)
    {
        $this->filter->apply(
            $request, $query, $this->value
        );

        return $query;
    }
}
