<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtTreatment;
use App\Models\CtTreatment;
use App\Traits\PropertyTraits\CtTreatmentProperty;
use App\Properties\Base\BaseCreatedAtProperty;
class CtTreatmentCreatedAtProperty extends BaseCreatedAtProperty
{
    use CtTreatmentProperty;
    public $table = CtTreatment::TABLE;
    public $parentClass = CtTreatment::class;
}
