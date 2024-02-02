<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use App\Http\Requests\AstralRequest;

class PasswordConfirmation extends Password
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'password-field';

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->onlyOnForms();
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttribute(AstralRequest $request, $requestAttribute, $model, $attribute)
    {
        //
    }
}
