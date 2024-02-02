<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Logging\QMLog;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfUserTagsProperty;
use App\Variables\QMUserVariable;
class UserVariableNumberOfUserTagsProperty extends BaseNumberOfUserTagsProperty
{
    use UserVariableProperty, IsNumberOfRelated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    protected static function getRelatedTable():string{return UserTag::TABLE;}
    public static function getForeignKey():string{return UserTag::FIELD_TAGGED_USER_VARIABLE_ID;}
    protected static function getLocalKey():string{return UserVariable::FIELD_ID;}
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model): int {
        $vars = $model->getUserTagVariables();
        $val = count($vars);
        try {
            $model->setAttribute(static::NAME, $val);
        } catch (\Throwable $e){
            QMLog::error(__METHOD__.": ".$e->getMessage());
            $model->setAttribute(static::NAME, $val);
        }
        return $val;
    }
}
