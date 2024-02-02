<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Rules;

use App\Astral;

class RelatableAttachment extends Relatable
{
    /**
     * Authorize that the user is allowed to relate this resource.
     *
     * @param  string  $resource
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function authorize($resource, $model): bool
    {
        $parentResource = Astral::resourceForModel(
            $parentModel = $this->request->findModelOrFail()
        );

        return (new $parentResource($parentModel))->authorizedToAttachAny(
            $this->request, $model
        ) || (new $parentResource($parentModel))->authorizedToAttach(
            $this->request, $model
        );
    }
}
