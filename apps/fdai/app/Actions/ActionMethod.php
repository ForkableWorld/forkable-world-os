<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;

use Illuminate\Support\Str;

class ActionMethod
{
    /**
     * Determine the appropriate "handle" method for the given models.
     *
     * @param  \App\Actions\Action  $action
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    public static function determine(Action $action, $model)
    {
        $method = 'handleFor'.Str::plural(class_basename($model));

        return method_exists($action, $method) ? $method : 'handle';
    }
}
