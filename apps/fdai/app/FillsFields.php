<?php

namespace App;

use App\Http\Requests\AstralRequest;

trait FillsFields
{
    /**
     * Fill a new model instance using the given request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public static function fill(AstralRequest $request, $model)
    {
        return static::fillFields(
            $request, $model,
            (new static($model))->creationFieldsWithoutReadonly($request)
        );
    }

    /**
     * Fill a new model instance using the given request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    public static function fillForUpdate(AstralRequest $request, $model)
    {
        return static::fillFields(
            $request, $model,
            (new static($model))->updateFieldsWithoutReadonly($request)
        );
    }

    /**
     * Fill a new pivot model instance using the given request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Relations\Pivot  $pivot
     * @return array
     */
    public static function fillPivot(AstralRequest $request, $model, $pivot)
    {
        $instance = new static($model);

        return static::fillFields(
            $request, $pivot,
            $instance->creationPivotFields($request, $request->relatedResource)
        );
    }

    /**
     * Fill the given fields for the model.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Support\Collection  $fields
     * @return array
     */
    protected static function fillFields(AstralRequest $request, $model, $fields)
    {
        return [$model, $fields->map->fill($request, $model)->filter(function ($callback) {
            return is_callable($callback);
        })->values()->all()];
    }
}
