<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Fields;

use Illuminate\Http\Request;

class MorphToActionTarget extends MorphTo
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'morph-to-action-target-field';

    /**
     * Determine if the field is not redundant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    public function isNotRedundant(Request $request)
    {
        return true;
    }
}
