<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use Illuminate\Database\Eloquent\Model;

class ID extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'text-field';

    /**
     * Create a new field.
     *
     * @param  string|null  $name
     * @param  string|null  $attribute
     * @param  mixed|null  $resolveCallback
     * @return void
     */
    public function __construct($name = null, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name ?? 'ID', $attribute, $resolveCallback);
    }

    /**
     * Create a new, resolved ID field for the given model.
     *
     * @param  Model  $model
     * @return static
     */
    public static function forModel($model)
    {
        return tap(static::make('ID', $model->getKeyName()))->resolve($model);
    }

    /**
     * Resolve a BIGINT ID field as a string for compatibility with JavaScript.
     *
     * @return $this
     */
    public function asBigInt()
    {
        $this->resolveCallback = function ($id) {
            return (string) $id;
        };

        return $this;
    }
}
