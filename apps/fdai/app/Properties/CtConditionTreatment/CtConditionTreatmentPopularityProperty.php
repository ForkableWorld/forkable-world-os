<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtConditionTreatment;
use App\Models\CtConditionTreatment;
use App\Traits\PropertyTraits\CtConditionTreatmentProperty;
use App\Properties\Base\BasePopularityProperty;
class CtConditionTreatmentPopularityProperty extends BasePopularityProperty
{
    use CtConditionTreatmentProperty;
    public $table = CtConditionTreatment::TABLE;
    public $parentClass = CtConditionTreatment::class;
}
