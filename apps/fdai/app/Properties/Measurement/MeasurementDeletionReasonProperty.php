<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Properties\Base\BaseDeletionReasonProperty;
use App\Traits\PropertyTraits\MeasurementProperty;
class MeasurementDeletionReasonProperty extends BaseDeletionReasonProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
}
