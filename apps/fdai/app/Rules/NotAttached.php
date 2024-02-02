<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Http\Requests\AstralRequest;

class NotAttached implements Rule
{
    /**
     * The request instance.
     *
     * @var \App\Http\Requests\AstralRequest
     */
    public $request;

    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $model;

    /**
     * Create a new rule instance.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function __construct(AstralRequest $request, $model)
    {
        $this->model = $model;
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
        return ! in_array(
            $this->request->input($this->request->relatedResource),
            $this->model->{$this->request->viaRelationship}()
                ->withoutGlobalScopes()->get()->modelKeys()
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('astral::validation.attached');
    }
}
