<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Vote;
use App\Models\Vote;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\VoteProperty;
use App\Properties\Base\BaseCorrelationIdProperty;
class VoteCorrelationIdProperty extends BaseCorrelationIdProperty
{
    use VoteProperty;
    public $table = Vote::TABLE;
    public $parentClass = Vote::class;
    public static function updateAll(){
        Writable::statementStatic("
            update votes v
                join user_variable_relationships c on v.cause_variable_id = c.cause_variable_id
                    and  v.effect_variable_id = c.effect_variable_id
                    and v.user_id = c.user_id
            set v.correlation_id = c.id
            where c.id is not null;
        ");
    }
}
