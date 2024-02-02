<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Properties\Base\BaseMeanProperty;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Utils\Stats;
class UserVariableMeanProperty extends BaseMeanProperty
{
    use UserVariableProperty, DailyVariableValueTrait, UserVariableValuePropertyTrait;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @return array
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function fixInvalidRecords(): array{
        QMLog::infoWithoutContext('=== ' . __FUNCTION__ . ' ===');
        return array_merge(
            static::fixTooBig(),
        static::fixTooSmall()
        );
    }
    /**
     * @return array
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function fixTooSmall(): array {
        $fixed = [];
        $request = new GetUserVariableRequest([], 'all');
        $variables = $request->getWithFieldTooSmallForUnit(UserVariable::FIELD_MEAN);
        if($variables){
            QMLog::error(count($variables)." user variables have too small means for unit");
            foreach($variables as $v){
                $v->forceAnalyze(__FUNCTION__);
                $fixed[] = $v;
            }
        }
        return $fixed;
    }
    /**
     * @return array
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function fixTooBig(): array{
        $fixed = [];
        $variableRequest = new GetUserVariableRequest([], 'all');
        $variables = $variableRequest->getWithTooBigMeanForUnit(100);
        if (count($variables)) {
            QMLog::error(count($variables) . " user variables have too big means for unit");
            foreach ($variables as $v) {
                $v = UserVariableDefaultUnitIdProperty::changeYesNoToCount($v);
                $v->forceAnalyze(__FUNCTION__);
                $fixed[] = $v;
            }
        }
        return $fixed;
    }
	/**
	 * @return void
	 * @throws \App\Exceptions\InvalidAttributeException
	 */
	public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $val = $this->getDBValue();
        $uv = $this->getUserVariable();
        if($val === null && $uv->number_of_measurements){
            $this->throwException("No mean even though we have measurements");
        }
        // TODO: Rename this to average_daily_value instead of ambiguous mean:  $this->validateBetweenMinMaxRecorded($this->getValue());
    }
    /**
     * @param BaseModel|UserVariable $model
     * @return float
     */
    public static function calculate($model): ?float{
        $uv = $model->getDBModel();
        $values = $uv->getDailyValuesWithTagsAndFilling();
        $mean = null;
        if($values){
            $mean = Stats::average($values, 5);
            $uv->convertValuesToUserUnit();
        }
        $model->setAttribute(static::NAME, $mean);
        return $mean;
    }
}
