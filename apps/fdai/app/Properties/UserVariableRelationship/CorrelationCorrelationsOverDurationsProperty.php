<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseCorrelationsOverDurationsProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Utils\AppMode;
class CorrelationCorrelationsOverDurationsProperty extends BaseCorrelationsOverDurationsProperty
{
    use CorrelationProperty;
    use IsCalculated;
    const ONSET_DELAY = 0;
	const DAY_OFFSETS = [
		1,
		2,
		4,
		8,
		16,
		32
	];
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
				$correlations[$i] = $model->correlateByHyperParams(self::ONSET_DELAY, 86400 * $i);
			} catch (NotEnoughDataException $e){
				$model->addWarning(__METHOD__.": ".$e->getMessage());
			}
        }
	    if(AppMode::isApiRequest() && count($correlations) < count(self::DAY_OFFSETS)){
		    throw new TooSlowToAnalyzeException(__FUNCTION__, $model);
	    }
        if(!$correlations || count($correlations) < 2){
            throw new NotEnoughDataException($model,
                'Could not calculate correlation over various durations of action. ',
                'This is probably due to insufficient measurements');
        }
        return $correlations;
    }
    public function validate(): void {
        parent::validate();
        if($value = $this->getAccessorValue()){
	        if(count($value) < 2){
				$this->throwException("There should be at least 2 elements");
			}
        }
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
                return $c->getOnsetDelay() === self::ONSET_DELAY;
            });
            if($byOnset){return $byOnset->all();}
        }
        return null;
    }
    /**
     * @param QMUserVariableRelationship|UserVariableRelationship $model
     * @return QMUserVariableRelationship[]
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public static function pluckOrCalculate($model): array{
        $plucked = static::pluckOrDefault($model);
        if($plucked && count($plucked) > 1){return $plucked;}
        return static::calculate($model);
    }
}
