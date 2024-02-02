<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtSideEffect;
use App\Models\CtSideEffect;
use App\Traits\PropertyTraits\CtSideEffectProperty;
use App\Properties\Base\BaseVariableIdProperty;
class CtSideEffectVariableIdProperty extends BaseVariableIdProperty
{
    use CtSideEffectProperty;
    public $table = CtSideEffect::TABLE;
    public $parentClass = CtSideEffect::class;
}
