<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AstralRequest;

class Password extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'password-field';

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  \App\Http\Requests\AstralRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return mixed
     */
    protected function fillAttributeFromRequest(AstralRequest $request, $requestAttribute, $model, $attribute)
    {
        if (! empty($request[$requestAttribute])) {
            $model->{$attribute} = Hash::make($request[$requestAttribute]);
        }
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return array_merge(
            parent::jsonSerialize(),
            ['value' => '']
        );
    }
}
