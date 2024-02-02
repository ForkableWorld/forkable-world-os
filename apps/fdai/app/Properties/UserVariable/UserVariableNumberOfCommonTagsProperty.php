<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\CommonTag;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfCommonTagsProperty;
class UserVariableNumberOfCommonTagsProperty extends BaseNumberOfCommonTagsProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    protected static function getRelatedTable():string{return CommonTag::TABLE;}
    public static function getForeignKey():string{return CommonTag::FIELD_TAGGED_VARIABLE_ID;}
    protected static function getLocalKey():string{return UserVariable::FIELD_VARIABLE_ID;}
}
