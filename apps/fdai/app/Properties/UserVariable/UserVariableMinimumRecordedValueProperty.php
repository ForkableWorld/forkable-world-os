<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Exceptions\InvalidAttributeException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Properties\Base\BaseMinimumRecordedValueProperty;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
class UserVariableMinimumRecordedValueProperty extends BaseMinimumRecordedValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @return array
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\AlreadyAnalyzingException
     * @throws \App\Exceptions\ModelValidationException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function fixTooSmall(): array{
        $fixed = [];
        $variableRequest = new GetUserVariableRequest([], 'all');
        $variables = $variableRequest->getWithFieldTooSmallForUnit(UserVariable::FIELD_MINIMUM_RECORDED_VALUE);
        if ($variables) {
            QMLog::error(count($variables) . " user variables have too small minimum for unit");
            foreach ($variables as $v) {
                $fixed[] = $v;
                $v->forceAnalyze(__FUNCTION__);
            }
        }
        return $fixed;
    }
    /**
     * @param BaseModel|UserVariable $model
     * @return float|null
     */
    public static function calculate($model){
        $uv = $model->getDBModel();
        $values = $uv->getValuesWithTags();
        $min = ($values) ? min($values) : null;
        $uv->setAttribute(static::NAME, $uv->minimumRecordedValueInCommonUnit = $min);
        $uv->minimumRecordedValueInUserUnit = $uv->toUserUnit($min);
        return $min;
    }
    public function validate(): void {
        try {
            parent::validate();
        } catch (InvalidAttributeException $e){
            $uv = $this->getUserVariable();
            $uv->logError($e->getMessage().
                "\n\tTODO: Figure out how to handle this so we can save variables with invalid data");
            $uv->addValidationError(static::NAME, $this->getDBValue(), $e->getMessage());
            return;
        }
        $uv = $this->getUserVariable();
        $last = $uv->last_value;
        $min = $this->inCommonUnit();
        if($last < $min){
            $this->throwException("lastValueInCommonUnit: $last is less than minimumRecordedValueInCommonUnit: ".$min);
        }
    }
}
