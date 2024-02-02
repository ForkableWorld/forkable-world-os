<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseOriginalValueProperty;
use App\Slim\Model\QMUnit;
use App\Slim\View\Request\QMRequest;
class MeasurementOriginalValueProperty extends BaseOriginalValueProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $canBeChangedToNull = false;
    public $required = true;
    public const SYNONYMS = [
        'original_value',
        'modified_value_in_user_unit',
        'modified_value',
        'value',
    ];
	/**
	 * @param $value
	 * @return float
	 */
	public function toDBValue($value): float {
        $float = static::toFloat($value);
        return $float;
    }
	/**
	 * @param $data
	 * @return float|mixed|null
	 */
	public static function pluckOrDefault($data): ?float {
		$val = parent::pluckOrDefault($data);
		if(is_string($val)){
			return static::toFloat($val);
		}
		return $val;
	}
	/**
     * @param $value
     * @return float|mixed
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueException
     */
    public function processAndSetDBValue($value): float{
        $originalValue = $this->setOriginalValueAndConvertToDBValue($value);
        $measurement = $this->getMeasurement();
        $measurement->setRawAttribute(Measurement::FIELD_ORIGINAL_VALUE, $originalValue);
        $previousValue = $measurement->value;
        $originalUnitId = $measurement->original_unit_id;
        $commonUnitId = $measurement->unit_id;
        if($commonUnitId && $originalUnitId){
            $inCommonUnit = QMUnit::convertValueByUnitIds($originalValue, $originalUnitId, $commonUnitId);
            $measurement->setRawAttribute(Measurement::FIELD_VALUE, $inCommonUnit);
        }
        return $originalValue;
    }
    /**
     * @param bool $throwException
     * @return mixed|null
     */
    public static function fromRequest(bool $throwException = false){
        return QMRequest::getParam(static::NAME);
    }
	public function showOnCreate(): bool{return false;}
}
