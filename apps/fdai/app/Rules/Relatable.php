<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Fields\HasOne;
use App\Fields\MorphOne;
use App\Http\Requests\AstralRequest;
use App\Astral;

class Relatable implements Rule
{
    /**
     * The request instance.
     *
     * @var \App\Http\Requests\AstralRequest
     */
    public $request;

    /**
     * The query builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    public $query;

    /**
     * Create a new rule instance.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function __construct(AstralRequest $request, $query)
    {
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $model = $this->query->select('*')->whereKey($value)->first();

        if (! $model) {
            return false;
        }

//        dd($this->relationshipIsFull($model, $attribute, $value));

        if ($this->relationshipIsFull($model, $attribute, $value)) {
            return false;
        }

        if ($resource = Astral::resourceForModel($model)) {
            return $this->authorize($resource, $model);
        }

        return true;
    }

    /**
     * Determine if the relationship is "full".
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    protected function relationshipIsFull($model, $attribute, $value)
    {
        $inverseRelation = $this->request->newResource()
                    ->resolveInverseFieldsForAttribute($this->request, $attribute)->first(function ($field) {
                        return $field instanceof HasOne || $field instanceof MorphOne;
                    });

        if ($inverseRelation && $this->request->resourceId) {
            $modelBeingUpdated = $this->request->findModelOrFail();

            if (is_null($modelBeingUpdated->{$attribute})) {
                return false;
            }

            if ($modelBeingUpdated->{$attribute}->getKey() == $value) {
                return false;
            }
        }

        return $inverseRelation &&
               $model->{$inverseRelation->attribute}()->count() > 0;
    }

    /**
     * Authorize that the user is allowed to relate this resource.
     *
     * @param  string  $resource
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function authorize($resource, $model)
    {
        return (new $resource($model))->authorizedToAdd(
            $this->request, $this->request->model()
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('astral::validation.relatable');
    }
}
