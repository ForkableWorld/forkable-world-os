<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseStartAtProperty;
use App\Traits\PropertyTraits\IsTemporalTwin;
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
class MeasurementStartAtProperty extends BaseStartAtProperty
{
    use MeasurementProperty, IsTemporalTwin;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public function validate(): void {
        parent::validate();
        $m = $this->getMeasurement();
        $time = $m->getRawAttribute(Measurement::FIELD_START_TIME);
        $this->assertEquals(db_date($time),
            Measurement::FIELD_START_TIME);
    }
    public static function pluck($data): ?string{
        if(is_string($data)){return $data;}
        if(is_numeric($data)){return db_date($data);}
        $val = parent::pluck($data);
        return $val;
    }
    /**
     * @param BaseModel|array|object|DBModel|string $data
     * @return string
     */
    public static function getDefault($data = null): ?string{
        $time = MeasurementStartTimeProperty::pluck($data);
        if($time){return db_date($time);}
        return null;
    }
    /**
     * @param bool $throwException
     * @return mixed|null
     */
    public static function fromRequest(bool $throwException = false){
        return QMRequest::getParam(static::NAME);
    }
    /**
     * @param $value
     * @return string
     */
    public function toDBValue($value): ?string{
        if(!$value){return null;}
        $v = $this->getVariable();
        $rounded = $v->roundStartTime($value);
        return db_date($rounded);
    }
    public function getLatestUnixTime(): int{
        return self::generateLatestUnixTime();
    }
    public function cannotBeChangedToNull(): bool{return true;}
    public function getTwinTimeAttribute(): string{return Measurement::FIELD_START_TIME;}
    /**
     * @param $data
     * @return int
     */
    public static function pluckRounded($data): int{
        return db_date(MeasurementStartTimeProperty::pluckRounded($data));
    }
    public function pluckAndSetDBValue($data, bool $fallback = false): ?float{
        $val = $this->pluck($data);
        if($val !== null){
            return $this->processAndSetDBValue($val);
        }
        return null;
    }
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
