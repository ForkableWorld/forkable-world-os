<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\UserTag;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfUserTagsWhereTaggedVariableProperty;
class VariableNumberOfUserTagsWhereTaggedVariableProperty extends BaseNumberOfUserTagsWhereTaggedVariableProperty
{
    use VariableProperty, IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    protected static function getRelatedTable():string{return UserTag::TABLE;}
    public static function getForeignKey():string{return UserTag::FIELD_TAGGED_VARIABLE_ID;}
    protected static function getLocalKey():string{return Variable::FIELD_ID;}
}
