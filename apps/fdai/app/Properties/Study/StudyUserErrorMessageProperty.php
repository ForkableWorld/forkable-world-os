<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\Study;
use App\Traits\PropertyTraits\StudyProperty;
use App\Properties\Base\BaseUserErrorMessageProperty;
class StudyUserErrorMessageProperty extends BaseUserErrorMessageProperty
{
    use StudyProperty;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    public $description = "Errors that the principal investigator should be notified of. ";
}
