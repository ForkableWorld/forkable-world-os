<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\GlobalVariableRelationship;
use App\Models\Vote;
use App\Properties\Vote\VoteGlobalVariableRelationshipIdProperty;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseNumberOfUpVotesProperty extends BaseProperty
{
    use IsInt, IsCalculated;
	public $dbInput = 'integer,false';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'number_of_up_votes';
	public $example = 0;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CORRELATION_CAUSALITY_VOTE;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CORRELATION_CAUSALITY_VOTE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_up_votes';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Up Votes';
	public $type = self::TYPE_INTEGER;
    /**
     * @param GlobalVariableRelationship $model
     * @return int
     */
    public static function calculate($model): int{
        $ac = $model->l();
	    if($ac->relationLoaded('votes')){
		    $votes = $ac->getVotes();
	    } else {
		    $votes = $ac->votes();
	    }
	    $num = $votes->where(Vote::FIELD_VALUE, "=", Vote::UP_VALUE)->count();
        $model->setAttribute(static::NAME, $num);
        return $num;
    }
    public static function updateAll(){
        VoteGlobalVariableRelationshipIdProperty::updateAll();
        $table = static::getTable();
        Writable::statementStatic("
            update $table ac 
                join votes v on 
                        v.cause_variable_id = ac.cause_variable_id 
                            and v.effect_variable_id = ac.effect_variable_id
                    set ac.number_of_up_votes = sum(v.value)
                    where v.id is not null;
        ");
    }
}
