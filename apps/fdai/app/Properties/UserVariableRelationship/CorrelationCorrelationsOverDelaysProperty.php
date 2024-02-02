<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseCorrelationsOverDelaysProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Utils\AppMode;
class CorrelationCorrelationsOverDelaysProperty extends BaseCorrelationsOverDelaysProperty
{
    use CorrelationProperty;
    use IsCalculated;
	const DAY_OFFSETS        = [-32, -16, -8, -4, -2, -1, 0, 1, 2, 4, 8, 16, 32];
	const DURATION = 86400;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return QMUserVariableRelationship[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public static function calculate($model): array{
        $model->logInfo(__METHOD__);
	    if(AppMode::isApiRequest()){
		    //throw new TooSlowToAnalyzeException(__FUNCTION__, $model);
	    }
        $correlations = [];
        foreach(self::DAY_OFFSETS as $i){
			try {
				$correlations[$i] = $model->correlateByHyperParams(86400 * $i, self::DURATION);
			} catch (NotEnoughDataException $e){
				$model->addWarning(__METHOD__.": ".$e->getMessage());
			}
        }
	    if(AppMode::isApiRequest() && count($correlations) < count(self::DAY_OFFSETS)){
		    throw new TooSlowToAnalyzeException(__FUNCTION__, $model);
	    }
        if(!$correlations){
            throw new NotEnoughDataException($model,
                'Could not calculate correlation over various onset delays. ',
                'This is probably due to insufficient measurements');
        }
        return $correlations;
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return QMUserVariableRelationship[]
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function pluckOrDefault($model): ?array{
        if($correlations = $model->getCorrelationsByHyperParameters()){
            $byOnset = collect($correlations)->filter(function($c){
                /** @var QMUserVariableRelationship $c */
                return $c->getDurationOfAction() === self::DURATION;
            });
            if($byOnset){return $byOnset->all();}
        }
        return null;
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return QMUserVariableRelationship[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public static function pluckOrCalculate($model): array{
        $plucked = static::pluckOrDefault($model);
        if($plucked && count($plucked) > 1){return $plucked;}
        return static::calculate($model);
    }
    public function validate(): void {
        parent::validate();
        if($value = $this->getAccessorValue()){
            if(!is_array($value)){$this->throwException("Should be an array");}
            if(count($value) < 2){$this->throwException("There should be at least 2 elements");}
        }
    }
}
