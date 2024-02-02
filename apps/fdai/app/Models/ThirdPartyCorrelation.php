<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Base\BaseThirdPartyCorrelation;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasDBModel;
use App\UI\FontAwesome;
use App\UI\InternalImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class ThirdPartyCorrelation
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property float $qm_score
 * @property float $forward_pearson_correlation_coefficient
 * @property float $value_predicting_high_outcome
 * @property float $value_predicting_low_outcome
 * @property int $predicts_high_effect_change
 * @property int $predicts_low_effect_change
 * @property float $average_effect
 * @property float $average_effect_following_high_cause
 * @property float $average_effect_following_low_cause
 * @property float $average_daily_low_cause
 * @property float $average_daily_high_cause
 * @property float $average_forward_pearson_correlation_over_onset_delays
 * @property float $average_reverse_pearson_correlation_over_onset_delays
 * @property int $cause_changes
 * @property float $cause_filling_value
 * @property int $cause_number_of_processed_daily_measurements
 * @property int $cause_number_of_raw_measurements
 * @property string $cause_unit
 * @property int $cause_unit_id
 * @property float $confidence_interval
 * @property float $critical_t_value
 * @property Carbon $created_at
 * @property string $data_source_name
 * @property string $deleted_at
 * @property int $duration_of_action
 * @property int $effect_changes
 * @property float $effect_filling_value
 * @property int $effect_number_of_processed_daily_measurements
 * @property int $effect_number_of_raw_measurements
 * @property string $error
 * @property float $forward_spearman_correlation_coefficient
 * @property int $id
 * @property int $number_of_days
 * @property int $number_of_pairs
 * @property int $onset_delay
 * @property int $onset_delay_with_strongest_pearson_correlation
 * @property float $optimal_pearson_product
 * @property float $p_value
 * @property float $pearson_correlation_with_no_onset_delay
 * @property float $predictive_pearson_correlation_coefficient
 * @property float $reverse_pearson_correlation_coefficient
 * @property float $statistical_significance
 * @property float $strongest_pearson_correlation_coefficient
 * @property float $t_value
 * @property Carbon $updated_at
 * @property int $user_id
 * @property float $grouped_cause_value_closest_to_value_predicting_low_outcome
 * @property float $grouped_cause_value_closest_to_value_predicting_high_outcome
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property string $status
 * @property int $cause_variable_category_id
 * @property int $effect_variable_category_id
 * @property bool $interesting_variable_category_pair
 * @property Variable $variable
 * @package App\Models
 * @method static bool|null forceDelete()
 * @method static Builder|ThirdPartyCorrelation newModelQuery()
 * @method static Builder|ThirdPartyCorrelation newQuery()
 * @method static \Illuminate\Database\Query\Builder|ThirdPartyCorrelation onlyTrashed()
 * @method static Builder|ThirdPartyCorrelation query()
 * @method static bool|null restore()
 * @method static Builder|ThirdPartyCorrelation whereAverageDailyHighCause($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageDailyLowCause($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageEffect($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageEffectFollowingHighCause($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageEffectFollowingLowCause($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageForwardPearsonCorrelationOverOnsetDelays($value)
 * @method static Builder|ThirdPartyCorrelation whereAverageReversePearsonCorrelationOverOnsetDelays($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseChanges($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseFillingValue($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseId($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseNumberOfProcessedDailyMeasurements($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseNumberOfRawMeasurements($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseUnit($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseUnitId($value)
 * @method static Builder|ThirdPartyCorrelation whereCauseVariableCategoryId($value)
 * @method static Builder|ThirdPartyCorrelation whereClientId($value)
 * @method static Builder|ThirdPartyCorrelation whereConfidenceInterval($value)
 * @method static Builder|ThirdPartyCorrelation whereCreatedAt($value)
 * @method static Builder|ThirdPartyCorrelation whereCriticalTValue($value)
 * @method static Builder|ThirdPartyCorrelation whereDataSourceName($value)
 * @method static Builder|ThirdPartyCorrelation whereDeletedAt($value)
 * @method static Builder|ThirdPartyCorrelation whereDurationOfAction($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectChanges($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectFillingValue($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectId($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectNumberOfProcessedDailyMeasurements($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectNumberOfRawMeasurements($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectVariableCategoryId($value)
 * @method static Builder|ThirdPartyCorrelation whereError($value)
 * @method static Builder|ThirdPartyCorrelation whereExperimentEndTime($value)
 * @method static Builder|ThirdPartyCorrelation whereExperimentStartTime($value)
 * @method static Builder|ThirdPartyCorrelation whereForwardPearsonCorrelationCoefficient($value)
 * @method static Builder|ThirdPartyCorrelation whereForwardSpearmanCorrelationCoefficient($value)
 * @method static Builder|ThirdPartyCorrelation whereGroupedCauseValueClosestToValuePredictingHighOutcome($value)
 * @method static Builder|ThirdPartyCorrelation whereGroupedCauseValueClosestToValuePredictingLowOutcome($value)
 * @method static Builder|ThirdPartyCorrelation whereId($value)
 * @method static Builder|ThirdPartyCorrelation whereInterestingVariableCategoryPair($value)
 * @method static Builder|ThirdPartyCorrelation whereJsonEncoded($value)
 * @method static Builder|ThirdPartyCorrelation whereNumberOfDays($value)
 * @method static Builder|ThirdPartyCorrelation whereNumberOfPairs($value)
 * @method static Builder|ThirdPartyCorrelation whereOnsetDelay($value)
 * @method static Builder|ThirdPartyCorrelation whereOnsetDelayWithStrongestPearsonCorrelation($value)
 * @method static Builder|ThirdPartyCorrelation whereOptimalPearsonProduct($value)
 * @method static Builder|ThirdPartyCorrelation wherePValue($value)
 * @method static Builder|ThirdPartyCorrelation wherePearsonCorrelationWithNoOnsetDelay($value)
 * @method static Builder|ThirdPartyCorrelation wherePredictivePearsonCorrelationCoefficient($value)
 * @method static Builder|ThirdPartyCorrelation wherePredictsHighEffectChange($value)
 * @method static Builder|ThirdPartyCorrelation wherePredictsLowEffectChange($value)
 * @method static Builder|ThirdPartyCorrelation wherePublishedAt($value)
 * @method static Builder|ThirdPartyCorrelation whereQmScore($value)
 * @method static Builder|ThirdPartyCorrelation whereReversePearsonCorrelationCoefficient($value)
 * @method static Builder|ThirdPartyCorrelation whereStatisticalSignificance($value)
 * @method static Builder|ThirdPartyCorrelation whereStatus($value)
 * @method static Builder|ThirdPartyCorrelation whereStrongestPearsonCorrelationCoefficient($value)
 * @method static Builder|ThirdPartyCorrelation whereTValue($value)
 * @method static Builder|ThirdPartyCorrelation whereUpdatedAt($value)
 * @method static Builder|ThirdPartyCorrelation whereUserId($value)
 * @method static Builder|ThirdPartyCorrelation whereValuePredictingHighOutcome($value)
 * @method static Builder|ThirdPartyCorrelation whereValuePredictingLowOutcome($value)
 * @method static Builder|ThirdPartyCorrelation whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|ThirdPartyCorrelation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ThirdPartyCorrelation withoutTrashed()
 * @mixin Eloquent
 * @method static Builder|ThirdPartyCorrelation whereCauseVariableId($value)
 * @method static Builder|ThirdPartyCorrelation whereEffectVariableId($value)
 * @property-read OAClient|null $oa_client
 * @property-read Variable $cause
 * @property-read VariableCategory|null $cause_variable_category
 * @property-read Variable $effect
 * @property-read VariableCategory|null $effect_variable_category
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int $cause_id variable ID of the cause variable for which the user desires user_variable_relationships
 * @property int $effect_id variable ID of the effect variable for which the user desires user_variable_relationships
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property string|null $json_encoded
 * @property-read OAClient|null $client
 */
class ThirdPartyCorrelation extends BaseThirdPartyCorrelation {
	use HasCauseAndEffect;
	public const CLASS_DESCRIPTION = 'Calculated Correlation Coefficients';
	public const ANALYZABLE = true;
	public const COLOR = QMColor::HEX_RED;
	public const DEFAULT_IMAGE = InternalImageUrls::BETTER_WORLD_THROUGH_DATA_PEOPLE_1200_630;
	public const DEFAULT_LIMIT = 20;
	public const DEFAULT_SEARCH_FIELD = 'effect_variable.' . Variable::FIELD_NAME;
	public const DEFAULT_ORDERINGS = [self::FIELD_QM_SCORE => self::ORDER_DIRECTION_DESC];
	public const FONT_AWESOME = FontAwesome::VIAL_SOLID;
	public static function getSlimClass(): string{ return QMUserVariableRelationship::class; }
	use SoftDeletes, HasDBModel;
	protected $casts = [
		'cause_variable_id' => 'int',
		'effect_variable_id' => 'int',
		'qm_score' => 'float',
		'forward_pearson_correlation_coefficient' => 'float',
		'value_predicting_high_outcome' => 'float',
		'value_predicting_low_outcome' => 'float',
		'predicts_high_effect_change' => 'int',
		'predicts_low_effect_change' => 'int',
		'average_effect' => 'float',
		'average_effect_following_high_cause' => 'float',
		'average_effect_following_low_cause' => 'float',
		'average_daily_low_cause' => 'float',
		'average_daily_high_cause' => 'float',
		'average_forward_pearson_correlation_over_onset_delays' => 'float',
		'average_reverse_pearson_correlation_over_onset_delays' => 'float',
		'cause_changes' => 'int',
		'cause_filling_value' => 'float',
		'cause_number_of_processed_daily_measurements' => 'int',
		'cause_number_of_raw_measurements' => 'int',
		'cause_unit_id' => 'int',
		'confidence_interval' => 'float',
		'critical_t_value' => 'float',
		'duration_of_action' => 'int',
		'effect_changes' => 'int',
		'effect_filling_value' => 'float',
		'effect_number_of_processed_daily_measurements' => 'int',
		'effect_number_of_raw_measurements' => 'int',
		'forward_spearman_correlation_coefficient' => 'float',
		'number_of_days' => 'int',
		'number_of_pairs' => 'int',
		'onset_delay' => 'int',
		'onset_delay_with_strongest_pearson_correlation' => 'int',
		'optimal_pearson_product' => 'float',
		'p_value' => 'float',
		'pearson_correlation_with_no_onset_delay' => 'float',
		'predictive_pearson_correlation_coefficient' => 'float',
		'reverse_pearson_correlation_coefficient' => 'float',
		'statistical_significance' => 'float',
		'strongest_pearson_correlation_coefficient' => 'float',
		't_value' => 'float',
		'user_id' => 'int',
		'grouped_cause_value_closest_to_value_predicting_low_outcome' => 'float',
		'grouped_cause_value_closest_to_value_predicting_high_outcome' => 'float',
		'wp_post_id' => 'int',
		'cause_variable_category_id' => 'int',
		'effect_variable_category_id' => 'int',
		'interesting_variable_category_pair' => 'bool',
	];
	protected array $rules = [
		self::FIELD_QM_SCORE => 'nullable|numeric',
		self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_AVERAGE_EFFECT => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'nullable|numeric',
		self::FIELD_AVERAGE_FORWARD_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_AVERAGE_REVERSE_PEARSON_CORRELATION_OVER_ONSET_DELAYS => 'nullable|numeric',
		self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_CAUSE_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_CONFIDENCE_INTERVAL => 'nullable|numeric',
		self::FIELD_CRITICAL_T_VALUE => 'nullable|numeric',
		self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
		self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:60|max:8640000',
		self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_FILLING_VALUE => 'nullable|numeric',
		self::FIELD_EFFECT_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_EFFECT_NUMBER_OF_RAW_MEASUREMENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_ERROR => 'nullable|max:65535',
		self::FIELD_FORWARD_SPEARMAN_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_NUMBER_OF_DAYS => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PAIRS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ONSET_DELAY => 'nullable|integer|min:0|max:8640000',
		self::FIELD_ONSET_DELAY_WITH_STRONGEST_PEARSON_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
		self::FIELD_P_VALUE => 'nullable|numeric',
		self::FIELD_PEARSON_CORRELATION_WITH_NO_ONSET_DELAY => 'nullable|numeric',
		self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
		self::FIELD_STRONGEST_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
		self::FIELD_T_VALUE => 'nullable|numeric',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
		self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_WP_POST_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'required|integer|min:1|max:300',
		self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'nullable|boolean',
		self::FIELD_CAUSE_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_EFFECT_VARIABLE_ID => 'nullable|integer|min:1|max:2147483647',
	];

	public function getIonIcon(): string {
		return IonIcon::androidGlobe;
	}
	public function getTagLine(): string{
		return $this->generatePredictorExplanationSentence();
	}
}
