<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Study;
use App\Models\Study;
use App\Properties\Base\BaseStatusProperty;
use App\Studies\QMStudy;
use App\Traits\PropertyTraits\StudyProperty;
class StudyStatusProperty extends BaseStatusProperty
{
    use StudyProperty;

    const STATUS_UPDATED = QMStudy::STATUS_UPDATED;
    const STATUS_WAITING = QMStudy::STATUS_WAITING;
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    public $enum = [
        self::STATUS_UPDATED,
        self::STATUS_WAITING,
    ];
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{return $this->enum;}
}
