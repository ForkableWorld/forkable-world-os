<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\MeasurementImport;
use App\Models\MeasurementImport;
use App\Properties\Base\BaseStatusProperty;
use App\Traits\PropertyTraits\MeasurementImportProperty;
class MeasurementImportStatusProperty extends BaseStatusProperty
{
    use MeasurementImportProperty;
	public $enum = [self::STATUS_WAITING, self::STATUS_ERROR, self::STATUS_FULFILLED];
	public const STATUS_FULFILLED = 'FULFILLED';
	public const STATUS_WAITING = 'WAITING';
	public const STATUS_ERROR = 'ERROR';
	public $table = MeasurementImport::TABLE;
    public $parentClass = MeasurementImport::class;
	protected function isLowerCase(): bool{
		return false;
	}
	public function getEnumOptions(): array{return $this->enum;}
}
