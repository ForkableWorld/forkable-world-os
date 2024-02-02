<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Properties\Base\BaseStatusProperty;
use App\Traits\PropertyTraits\UserProperty;
class UserStatusProperty extends BaseStatusProperty
{
    use UserProperty;
    public const STATUS_UPDATED = 'UPDATED';
    public const STATUS_WAITING = 'WAITING';
    public const STATUS_ANALYZING = 'ANALYZING';
    public const STATUS_ERROR = 'ERROR';
    public $table = User::TABLE;
    public $parentClass = User::class;
    public $enum = [
        self::STATUS_UPDATED,
        self::STATUS_ANALYZING,
        self::STATUS_ERROR,
        self::STATUS_WAITING,
    ];
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
