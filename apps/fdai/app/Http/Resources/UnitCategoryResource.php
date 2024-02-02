<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\UnitCategory */
class UnitCategoryResource extends BaseJsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'title' => $this->getTitleAttribute(),
            'id' => $this->id,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'can_be_summed' => $this->can_be_summed,
            'units_count' => $this->units_count,
            'sort_order' => $this->sort_order,
            'subtitle' => $this->getSubtitleAttribute(),
            'units' => UnitsCollection::collection($this->whenLoaded('units')),
            //'units' => UnitsCollection::collection($this->whenLoaded('units')),
        ];
    }
}
