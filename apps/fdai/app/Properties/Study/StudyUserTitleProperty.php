<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\Study;
use App\Traits\PropertyTraits\StudyProperty;
use App\Properties\Base\BaseUserTitleProperty;
class StudyUserTitleProperty extends BaseUserTitleProperty
{
    use StudyProperty;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    public const SYNONYMS = [
        'study_title',
        'user_title',
        'study_name',
        'title',
    ];
}
