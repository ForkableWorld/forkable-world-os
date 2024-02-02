<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseCtCorrelation;
use App\Traits\HasCauseAndEffect;
use App\UI\IonIcon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\CtCorrelation
 * @property int $id
 * @property int $user_id
 * @property float|null $correlation_coefficient
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int|null $onset_delay
 * @property int|null $duration_of_action
 * @property int|null $number_of_pairs
 * @property float|null $value_predicting_high_outcome
 * @property float|null $value_predicting_low_outcome
 * @property float|null $optimal_pearson_product
 * @property float|null $vote
 * @property float|null $statistical_significance
 * @property int|null $cause_unit_id
 * @property int|null $cause_changes
 * @property int|null $effect_changes
 * @property float|null $qm_score
 * @property string|null $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CtCorrelation newModelQuery()
 * @method static Builder|CtCorrelation newQuery()
 * @method static Builder|CtCorrelation query()
 * @method static Builder|CtCorrelation whereCauseChanges($value)
 * @method static Builder|CtCorrelation whereCauseUnitId($value)
 * @method static Builder|CtCorrelation whereCauseVariableId($value)
 * @method static Builder|CtCorrelation whereCorrelationCoefficient($value)
 * @method static Builder|CtCorrelation whereCreatedAt($value)
 * @method static Builder|CtCorrelation whereDeletedAt($value)
 * @method static Builder|CtCorrelation whereDurationOfAction($value)
 * @method static Builder|CtCorrelation whereEffectChanges($value)
 * @method static Builder|CtCorrelation whereEffectVariableId($value)
 * @method static Builder|CtCorrelation whereError($value)
 * @method static Builder|CtCorrelation whereId($value)
 * @method static Builder|CtCorrelation whereNumberOfPairs($value)
 * @method static Builder|CtCorrelation whereOnsetDelay($value)
 * @method static Builder|CtCorrelation whereOptimalPearsonProduct($value)
 * @method static Builder|CtCorrelation whereQmScore($value)
 * @method static Builder|CtCorrelation whereStatisticalSignificance($value)
 * @method static Builder|CtCorrelation whereUpdatedAt($value)
 * @method static Builder|CtCorrelation whereUserId($value)
 * @method static Builder|CtCorrelation whereValuePredictingHighOutcome($value)
 * @method static Builder|CtCorrelation whereValuePredictingLowOutcome($value)
 * @method static Builder|CtCorrelation whereVote($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class CtCorrelation extends BaseCtCorrelation {
	use HasCauseAndEffect;
	const CLASS_CATEGORY = Study::CLASS_CATEGORY;

	public function getIonIcon(): string {
		return IonIcon::androidGlobe;
	}
	public function getTagLine(): string{
		return $this->generatePredictorExplanationSentence();
	}
}
