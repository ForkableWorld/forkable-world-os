<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UserVariableNotFoundException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\Variable;
use App\Astral\Metrics\UserVariableTrendMetric;
use App\Properties\Base\BaseValueProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMUserVariable;
use App\Metrics\Trend;
class MeasurementValueProperty extends BaseValueProperty
{
    use MeasurementProperty, VariableValueTrait;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public const SYNONYMS = [
        'value',
    ];
    /**
     * @param array $params
     */
    public static function deleteMeasurementsOutsideAllowedRangeForUser(array $params){
        $u = QMAuth::getQMUserIfSet();
        $userId = $params['userId'];
        $variableId = $params['variableId'];
        if(!$u->isAdmin() && $userId !== $u->id){
            throw new UnauthorizedException('You do not have this privilege');
        }
        try {
            $v = QMUserVariable::getByNameOrId($userId, $variableId);
        } catch (UserVariableNotFoundException $e) {
            throw new BadRequestException("Variable $variableId for user $userId not found!");
        }
        $v->logError("deleteOutsideAllowedRange");
        $max = $v->maximumAllowedValueInCommonUnit;
        if($max !== null){
            self::softDeleteGreaterThan($userId, $variableId, $max);
        }
        $min = $v->minimumAllowedValueInCommonUnit;
        if($min !== null){
            self::softDeleteLessThan($userId, $variableId, $min);
        }
    }
    /**
     * @param $userId
     * @param $variableId
     * @param float $max
     */
    public static function softDeleteGreaterThan($userId, $variableId, float $max): void{
        Measurement::query()
            ->where('user_id', $userId)
            ->where('variable_id', $variableId)
            ->where('value', '>', $max)
            ->update([
                Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
                Measurement::FIELD_ERROR      => "Too big for variable"
            ]);
    }
    /**
     * @param $userId
     * @param $variableId
     * @param float $min
     */
    public static function softDeleteLessThan($userId, $variableId, float $min): void{
        Measurement::writable()
            ->where('user_id', $userId)
            ->where('variable_id', $variableId)
            ->where('value', '<', $min)
            ->update([
                Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
                Measurement::FIELD_ERROR      => "Too small for variable"
            ]);
    }
    public function getVariable(): Variable{
        return $this->getMeasurement()->getVariable();
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
    }
    /**
     * @throws AlreadyAnalyzingException
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\InvalidAttributeException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function fixTooBigServingMeasurements(){
        $serving = QMUnit::getServing();
        $qb =
            QMMeasurement::writable()
                ->selectRaw('MAX(variable_id) as variable_id, MAX(user_id) as user_id')
                ->where(Measurement::FIELD_UNIT_ID, $serving->getId())
                ->where(Measurement::FIELD_VALUE, '>', $serving->getMaximumRawValue())
                ->groupBy([Measurement::FIELD_USER_ID, Measurement::FIELD_VARIABLE_ID]);
        /** @var QMMeasurement[] $rows */
        $rows = $qb->getDBModels();
        QMLog::info(count($rows) .
            " user variables with values too big for unit serving max " .
            $serving->getMaximumRawValue());
        foreach ($rows as $row) {
            $userVariable = $row->getQMUserVariable();
            $userVariable->analyzeFully("just fixed measurements with too big values", true);
        }
    }
    /**
     * @param $originalValue
     * @return float|mixed
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueException
     */
    public function toDBValue($originalValue): float {
        $m = $this->getMeasurement();
        $commonUnitId = $m->unit_id;
        $originalUnitId = $m->original_unit_id;
        if(!$originalUnitId){
            $originalUnitId = $m->original_unit_id;
        }
		if(!$commonUnitId){le('!$commonUnitId');}
        $convertedValue = QMUnit::convertValueByUnitIds($originalValue, $originalUnitId,
            $commonUnitId);
        return $convertedValue;
    }
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return float|mixed|null
	 */
	public function pluckAndSetDBValue($data, bool $fallback = false){
        $m = $this->getMeasurement();
        if($m->value !== null){
            return $m->value; // We already set in MeasurementOriginalValueProperty
        }
        if(!$m->original_unit_id){
            $m->original_unit_id = MeasurementOriginalUnitIdProperty::pluckOrDefault($data);
        }
        $val = MeasurementOriginalValueProperty::pluckOrDefault($data);
        if($val !== null){
            return $this->processAndSetDBValue($val);
        }
        return null;
    }
    public static function trendMetric(int $userVariableId): Trend{
        $m = new UserVariableTrendMetric();
        $m->setUserVariableId($userVariableId);
        return $m;
    }
}
