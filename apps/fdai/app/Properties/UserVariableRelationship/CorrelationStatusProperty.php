<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseStatusProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
class CorrelationStatusProperty extends BaseStatusProperty
{
    use CorrelationProperty;
    public const STATUS_UPDATED = 'UPDATED';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_WAITING = 'WAITING';
    public const STATUS_ANALYZING = 'ANALYZING';
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    public $enum = [
        self::STATUS_UPDATED,
        self::STATUS_ANALYZING,
        self::STATUS_ERROR,
        self::STATUS_WAITING,
    ];
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
