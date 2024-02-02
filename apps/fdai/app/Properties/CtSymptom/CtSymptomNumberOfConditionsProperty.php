<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\CtSymptom;
use App\Models\CtConditionSymptom;
use App\Models\CtSymptom;
use App\Traits\PropertyTraits\CtSymptomProperty;
use App\Properties\Base\BaseNumberOfConditionsProperty;
class CtSymptomNumberOfConditionsProperty extends BaseNumberOfConditionsProperty
{
    use CtSymptomProperty;
    public $table = CtSymptom::TABLE;
    public $parentClass = CtSymptom::class;
    protected static function getRelatedTable():string{return CtConditionSymptom::TABLE;}
    public static function getForeignKey(): string{return CtConditionSymptom::FIELD_SYMPTOM_ID;}
}
