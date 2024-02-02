<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\Study;
use App\Traits\PropertyTraits\StudyProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
use App\Types\QMStr;
use App\Slim\Middleware\QMAuth;
class StudyUserIdProperty extends BaseUserIdProperty
{
    use StudyProperty;
    use HasUserFilter;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    /**
     * @param string|null $studyId
     * @return int|null
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function fromId($studyId): ?int{
        if (!$studyId) {
            $studyId = StudyIdProperty::fromRequestDirectly();
            if (!$studyId) {
                return null;
            }
        }
        $suffix = StudyIdProperty::INDIVIDUAL_STUDY_ID_SUFFIX;
        if (stripos($studyId, $suffix) === false) {
            return null;
        }
        $userId = QMStr::before($suffix, $studyId);
        $userId = QMStr::after('-user-', $userId);
        return (int)$userId;
    }
    public static function pluckOrDefault($data){
        $id = StudyIdProperty::pluck($data);
        if($id){return static::fromId($id);}
        return parent::pluckOrDefault($data);
    }
    public static function getDefault($data = null): ?int{
        if($userId = BaseUserIdProperty::fromRequestDirectly()){
            return $userId;
        }
        return QMAuth::id();
    }
}
