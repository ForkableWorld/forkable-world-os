<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\CommonTag;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfCommonTagsWhereTaggedVariableProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfCommonTagsWhereTaggedVariableProperty extends BaseNumberOfCommonTagsWhereTaggedVariableProperty
{
    use VariableProperty, IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function getRelatedIdField(): string{
        return CommonTag::FIELD_TAGGED_VARIABLE_ID;
    }
    public static function getForeignKey(): string{
        return CommonTag::FIELD_TAGGED_VARIABLE_ID;
    }
    protected static function getRelationshipClass(): string{
        return CommonTag::class;
    }
    /**
     * @param QMCommonVariable $v
     * @return int|mixed
     */
    public static function calculate($v): int{
        $rows = $v->getCommonTagVariables();
        $calculated = count($rows);
        $v->setAttribute(static::NAME, $calculated);
        return $calculated;
    }
}
