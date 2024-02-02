<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\UserVariableRelationship\CorrelationPredictivePearsonCorrelationCoefficientProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BasePredictivePearsonCorrelationCoefficientProperty;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipPredictivePearsonCorrelationCoefficientProperty extends BasePredictivePearsonCorrelationCoefficientProperty
{
    use GlobalVariableRelationshipProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
	/**
	 * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
	 * @return float
	 */
    public static function calculate($model){
	    try {
//		    /** @var UserVariableRelationship[] $userVariableRelationships */
//		    $userVariableRelationships = $model->getCorrelations();
//			foreach($userVariableRelationships as $userVariableRelationship){
//				$correlation = $userVariableRelationship->getQMUserVariableRelationship();
//				try {
//					$predictive = $correlation->getPredictivePearsonCorrelationCoefficient();
//				} catch (NotEnoughDataException $e) {
//					$model->addWarning("Could not calculate predictive pearson correlation coefficient for user variable relationship {$correlation->id} because: ".
//					                   $e->getMessage());
//				} catch (TooSlowToAnalyzeException $e) {
//					le($e);
//				}
//			}
		    $val = $model->weightedAvgFromUserVariableRelationships(static::NAME);
		    $model->setAttribute(static::NAME, $val);
		    return $val;
	    } catch (NoUserVariableRelationshipsToAggregateException $e) {
			$model->logError(__METHOD__.": ".$e->getMessage());
		    return null;
	    }
    }
}
