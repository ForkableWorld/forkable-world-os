<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Vote;
use App\Models\Vote;
use App\Traits\PropertyTraits\VoteProperty;
use App\Properties\Base\BaseClientIdProperty;
class VoteClientIdProperty extends BaseClientIdProperty
{
    use VoteProperty;
    public $table = Vote::TABLE;
    public $parentClass = Vote::class;
}
