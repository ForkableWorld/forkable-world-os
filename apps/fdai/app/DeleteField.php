<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App;

use App\Contracts\Storable;
use App\Http\Requests\AstralRequest;

class DeleteField
{
    /**
     * Delete the given field.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \App\Fields\Field|\App\Contracts\Deletable  $field
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function forRequest(AstralRequest $request, $field, $model)
    {
        $arguments = [
            $request,
            $model,
        ];

        if ($field instanceof Storable) {
            array_push($arguments, $field->getStorageDisk(), $field->getStoragePath());
        }

        $result = call_user_func_array($field->deleteCallback, $arguments);

        if ($result === true) {
            return $model;
        }

        if (! is_array($result)) {
            $model->{$field->attribute} = $result;
        } else {
            foreach ($result as $key => $value) {
                $model->{$key} = $value;
            }
        }

        return $model;
    }
}
