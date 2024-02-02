<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Requests;
class ResourceIndexRequest extends AstralRequest{
    use CountsResources, QueriesResources;
    /**
     * Get the count of the resources.
     *
     * @return int
     */
    public function toCount(): int{
        return $this->buildCountQuery($this->toQuery())->count();
    }
    /**
     * @return static
     */
    public static function req(): AstralRequest{
        return parent::req();
    }
}
