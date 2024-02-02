<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Properties\Base\BaseStatusProperty;
use App\Traits\PropertyTraits\UserVariableProperty;
class UserVariableStatusProperty extends BaseStatusProperty
{
    use UserVariableProperty;
    public const STATUS_UPDATED = 'UPDATED';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_WAITING = 'WAITING';
    public const STATUS_ANALYZING = 'ANALYZING';
    public const STATUS_CORRELATE = 'CORRELATE';
    public const STATUS_CORRELATING = 'CORRELATING';
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public $enum = [
        self::STATUS_UPDATED,
        self::STATUS_ANALYZING,
        self::STATUS_CORRELATE,
        self::STATUS_CORRELATING,
        self::STATUS_ERROR,
        self::STATUS_WAITING,
    ];
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
