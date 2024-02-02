<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtSymptom;
use App\Models\CtSymptom;
use App\Traits\PropertyTraits\CtSymptomProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class CtSymptomIdProperty extends BaseIntegerIdProperty
{
    use CtSymptomProperty;
    public $table = CtSymptom::TABLE;
    public $parentClass = CtSymptom::class;
}
