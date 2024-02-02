<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Vote;
use App\Exceptions\BadRequestException;
use App\Models\Vote;
use App\Traits\PropertyTraits\VoteProperty;
use App\Properties\Base\BaseValueProperty;
class VoteValueProperty extends BaseValueProperty
{
    use VoteProperty;
    public $table = Vote::TABLE;
    public $parentClass = Vote::class;
    public const SYNONYMS = [
        'vote',
        'userVote',
        'value',
    ];
    /**
     * @param bool $throwException
     * @return int
     */
    public static function fromRequest(bool $throwException = false): ?int{
        $vote = parent::fromRequest();
        if($vote === Vote::UP){
            $vote = 1;
        }
        if($vote === Vote::DOWN){
            $vote = 0;
        }
        if($vote === Vote::NONE){
            return null;
        }
        if($vote !== null && !in_array($vote,
                [
                    Vote::DOWN_VALUE,
                    Vote::UP_VALUE
                ],
                true)){
            throw new BadRequestException('Please provide "none", "up" or "down" as "vote" parameter value');
        }
        return $vote;
    }
}
