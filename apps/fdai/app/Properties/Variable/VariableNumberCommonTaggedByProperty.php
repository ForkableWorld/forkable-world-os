<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\CommonTag;
use App\Models\Variable;
use App\Properties\Base\BaseNumberCommonTaggedByProperty;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Variables\QMCommonVariable;
class VariableNumberCommonTaggedByProperty extends BaseNumberCommonTaggedByProperty
{
    use VariableProperty;
    use IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMCommonVariable $dbm
     * @return int
     */
    public static function calculate($dbm): int{
        $rows = $dbm->setCommonTaggedRows();
        $calculated = count($rows);
        $dbm->setAttribute(static::NAME, $calculated);
        return $calculated;
    }
    /**
     * @return bool
     */
    public static function update_number_common_tagged_by(): bool{
        //$this->createNumberOfTagsView();
        $before = QMCommonVariable::readonly()->whereNotNull(Variable::FIELD_NUMBER_OF_COMMON_TAGS)->count();
	    $success = Writable::pdoStatement("
            UPDATE variables
            join " . self::NAME . " on variables.id = " . self::NAME . ".id
            set variables." . self::NAME . " = " . self::NAME . ".total
        ");
        $after = QMCommonVariable::readonly()->whereNotNull(Variable::FIELD_NUMBER_OF_COMMON_TAGS)->count();
        return $success;
    }
    protected static function getRelatedTable():string{return CommonTag::TABLE;}
    public static function getForeignKey():string{return CommonTag::FIELD_TAG_VARIABLE_ID;}
    protected static function getLocalKey():string{return Variable::FIELD_ID;}
}
