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
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
class MeasurementOriginalStartAtProperty extends BaseStartAtProperty
{
    use MeasurementProperty;
    public $name = Measurement::FIELD_ORIGINAL_START_AT;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $canBeChangedToNull = false;
    public $required = true;
	public const NAME = Measurement::FIELD_ORIGINAL_START_AT;
    public const SYNONYMS = [
        Measurement::FIELD_ORIGINAL_START_AT,
	    'startTimeEpoch',
        Measurement::FIELD_START_AT,
        'timestamp',
        'startTime',
    ];
	/**
	 * @param \App\Models\BaseModel|\App\Slim\Model\DBModel|array|object $data
	 * @return string|null
	 */
	public static function pluck($data): ?string{
        if(is_string($data)){return $data;}
        if(is_numeric($data)){return db_date($data);}
        $val = parent::pluck($data);
        if(!$val){
            $time = MeasurementStartTimeProperty::pluck($data );
            if($time){return db_date($time);}
        }
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
    public function toDBValue($value): ?string {
        if(!$value){return null;}
        $v = $this->getVariable();
        $rounded = $v->roundStartTime($value);
        return db_date($rounded);
    }
    public function getLatestUnixTime(): int{
        return self::generateLatestUnixTime();
    }
    public function cannotBeChangedToNull(): bool{return true;}
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return string|null
	 */
	public function pluckAndSetDBValue($data, bool $fallback = false): ?string{
        $val = $this->pluck($data);
        if($val !== null){
            return $this->processAndSetDBValue($val);
        }
        return null;
    }
	public static function getSynonyms(): array{
		return parent::getSynonyms();
	}
}
