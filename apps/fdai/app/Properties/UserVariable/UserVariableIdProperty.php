<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class UserVariableIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'user_variable_id',
        'id',
    ];
}
