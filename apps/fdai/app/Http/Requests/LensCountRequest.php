<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\LensCountException;

class LensCountRequest extends AstralRequest
{
    use CountsResources, InteractsWithLenses;

    /**
     * Get the count of the lens resources.
     *
     * @return int
     */
    public function toCount()
    {
        try {
            return $this->buildCountQuery($this->toQuery())->count();
        } catch (Exception $e) {
            report($e);
        }

        return 0;
    }

    /**
     * Transform the request into a query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function toQuery()
    {
        return tap($this->lens()->query(LensRequest::createFrom($this), $this->newQuery()), function ($query) {
            if (! $query instanceof Builder) {
                throw new LensCountException('Lens must return an Eloquent query instance in order to count lens resources.');
            }
        });
    }
}
