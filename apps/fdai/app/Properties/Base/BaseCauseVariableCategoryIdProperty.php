<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\GlobalVariableRelationship;
use App\Storage\DB\Writable;
class BaseCauseVariableCategoryIdProperty extends BaseVariableCategoryIdProperty{
    public const NAME = GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID;
    public $name = self::NAME;
	public $title = 'Predictor Variable Category';
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    public static function updateAll(){
        $table = static::getTable();
        Writable::statementStatic("
            update $table c 
                join variables v on c.cause_variable_id = v.id 
            set c.cause_variable_category_id = v.variable_category_id;
        ");
    }
}
