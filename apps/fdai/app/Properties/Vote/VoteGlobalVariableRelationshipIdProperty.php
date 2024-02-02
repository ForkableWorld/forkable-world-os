<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Vote;
use App\Models\Vote;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\VoteProperty;
use App\Properties\Base\BaseGlobalVariableRelationshipIdProperty;
class VoteGlobalVariableRelationshipIdProperty extends BaseGlobalVariableRelationshipIdProperty
{
    use VoteProperty;
    public $table = Vote::TABLE;
    public $parentClass = Vote::class;
    public static function updateAll(){
        Writable::statementStatic("
            update votes v
                join global_variable_relationships ac 
                    on v.cause_variable_id = ac.cause_variable_id
                    and v.effect_variable_id = ac.effect_variable_id
            set v.global_variable_relationship_id = ac.id
            where ac.id is not null;
        ");
    }
    /**
     * @return void
     */
    public static function fixNulls(){
        Vote::whereNull(Vote::FIELD_AGGREGATE_CORRELATION_ID)->update([
            Vote::FIELD_AGGREGATE_CORRELATION_ID => 0
        ]);
        Writable::statementStatic("
            update votes v
                join global_variable_relationships c on v.cause_variable_id = c.cause_variable_id
                    and v.effect_variable_id = c.effect_variable_id
            set v.global_variable_relationship_id = c.id
            where c.id is not null and v.global_variable_relationship_id is null;
        ");
    }
}
