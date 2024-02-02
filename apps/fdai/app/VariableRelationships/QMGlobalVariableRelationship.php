<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection ProperNullCoalescingOperatorUsageInspection */
namespace App\VariableRelationships;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipChartGroup;
use App\Charts\ChartGroup;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\Vote;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipAggregateQmScoreProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipAverageEffectProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipAverageVoteProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDataSourceNameProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipDurationOfActionProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipForwardPearsonCorrelationCoefficientProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipIsPublicProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipOnsetDelayProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPopulationTraitPearsonCorrelationCoefficientProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPredictivePearsonCorrelationCoefficientProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPredictsHighEffectChangeProperty;
use App\Properties\GlobalVariableRelationship\GlobalVariableRelationshipPredictsLowEffectChangeProperty;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\UserVariableRelationship\CorrelationAverageEffectProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\GetCommonVariablesRequest;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;
use App\Studies\PairOfAverages;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\StudyLinks;
use App\Traits\HasButton;
use App\Traits\HasCharts;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasFiles;
use App\Traits\HasMany\HasManyCorrelations;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasProperty\HasOnsetAndDuration;
use App\Traits\HasVotes;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\QMAPIValidator;
use App\Utils\UrlHelper;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\LocationsVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;
/** @mixin GlobalVariableRelationship
 */
class QMGlobalVariableRelationship extends QMVariableRelationship {
	use HasButton, HasCorrelationCoefficient, HasManyCorrelations, HasOnsetAndDuration,
		HasGlobalVariableRelationship, HasVotes;
	use HasCharts, HasFiles;
    const EAGER_LOAD_VOTES = true;
    const VIEW_USER_VARIABLE_RELATIONSHIPS_AGGREGATED = "user_variable_relationships_aggregated";
    protected $pairsOfAveragesForAllUsers;
    protected $requireMultipleUsers;
    public $aggregateQMScore;
    public $causeUnitId;
    public $numberOfCorrelations;
    public $numberOfVariablesWhereBestGlobalVariableRelationship;
    public $populationTraitPearsonCorrelationCoefficient;
    public $qmScore;
    public $vote;
    const PROPERTY_ALIASES = ['correlationCoefficient' => 'forwardPearsonCorrelationCoefficient'];
    public const ALGORITHM_MODIFIED_AT = "2020-03-21";
    public const AVERAGE_USER_ID = 'average_user_id';  // Check if only mike's user_variable_relationships
    public const FIELD_AGGREGATE_QM_SCORE = 'aggregate_qm_score';
    public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
    public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
    public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
    public const FIELD_AVERAGE_DAILY_HIGH_CAUSE = 'average_daily_high_cause';
    public const FIELD_AVERAGE_DAILY_LOW_CAUSE = 'average_daily_low_cause';
    public const FIELD_AVERAGE_EFFECT = 'average_effect';
    public const FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE = 'average_effect_following_high_cause';
    public const FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE = 'average_effect_following_low_cause';
    public const FIELD_AVERAGE_VOTE = 'average_vote';
    public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY = 'cause_baseline_average_per_day';
    public const FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION = 'cause_baseline_average_per_duration_of_action';
    public const FIELD_CAUSE_CHANGES = 'cause_changes';
    public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY = 'cause_treatment_average_per_day';
    public const FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION = 'cause_treatment_average_per_duration_of_action';
    public const FIELD_CAUSE_UNIT_ID = 'cause_unit_id';
    public const FIELD_CAUSE_VARIABLE_CATEGORY_ID = 'cause_variable_category_id';
    public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_CONFIDENCE_INTERVAL = 'confidence_interval';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_CRITICAL_T_VALUE = 'critical_t_value';
    public const FIELD_DATA_SOURCE_NAME = 'data_source_name';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_DURATION_OF_ACTION = 'duration_of_action';
    public const FIELD_EFFECT_BASELINE_AVERAGE = 'effect_baseline_average';
    public const FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION = 'effect_baseline_relative_standard_deviation';
    public const FIELD_EFFECT_BASELINE_STANDARD_DEVIATION = 'effect_baseline_standard_deviation';
    public const FIELD_EFFECT_CHANGES = 'effect_changes';
    public const FIELD_EFFECT_FOLLOW_UP_AVERAGE = 'effect_follow_up_average';
    public const FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE = 'effect_follow_up_percent_change_from_baseline';
    public const FIELD_EFFECT_VARIABLE_CATEGORY_ID = 'effect_variable_category_id';
    public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
    public const FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT = 'forward_pearson_correlation_coefficient';
    public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_high_outcome';
    public const FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME = 'grouped_cause_value_closest_to_value_predicting_low_outcome';
    public const FIELD_ID = 'id';
    public const FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR = 'interesting_variable_category_pair';
    public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
    public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
    public const FIELD_NUMBER_OF_CORRELATIONS = 'number_of_correlations';
    public const FIELD_NUMBER_OF_PAIRS = 'number_of_pairs';
    public const FIELD_NUMBER_OF_USERS = 'number_of_users';
    public const FIELD_ONSET_DELAY = 'onset_delay';
    public const FIELD_OPTIMAL_PEARSON_PRODUCT = 'optimal_pearson_product';
    public const FIELD_P_VALUE = 'p_value';
    public const FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT = 'population_trait_pearson_correlation_coefficient';
    public const FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT = 'predictive_pearson_correlation_coefficient';
    public const FIELD_PREDICTS_HIGH_EFFECT_CHANGE = 'predicts_high_effect_change';
    public const FIELD_PREDICTS_LOW_EFFECT_CHANGE = 'predicts_low_effect_change';
    public const FIELD_PUBLISHED_AT = 'published_at';
    public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
    public const FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT = 'reverse_pearson_correlation_coefficient';
    public const FIELD_STATISTICAL_SIGNIFICANCE = 'statistical_significance';
    public const FIELD_STATUS = 'status';
    public const FIELD_T_VALUE = 't_value';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
    public const FIELD_VALUE_PREDICTING_HIGH_OUTCOME = 'value_predicting_high_outcome';
    public const FIELD_VALUE_PREDICTING_LOW_OUTCOME = 'value_predicting_low_outcome';
    public const FIELD_WP_POST_ID = 'wp_post_id';
    public const FIELD_Z_SCORE = 'z_score';
    public const SIGNIFICANT_NUMBER_OF_PAIRS = 100;  // Number above which won't really add much to the significance of the relationship
    public const SIGNIFICANT_NUMBER_OF_USERS = 10;
    public const TABLE = 'global_variable_relationships';
    public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP = [
        self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'avgDailyValuePredictingHighOutcome',
        self::FIELD_VALUE_PREDICTING_LOW_OUTCOME  => 'avgDailyValuePredictingLowOutcome',
        self::FIELD_AGGREGATE_QM_SCORE  => 'qmScore',
        self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT  => 'correlationCoefficient',
    ];
    public static $sqlCalculatedFields = [
        self::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT => 'avg('.UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT.')',
        self::FIELD_ONSET_DELAY => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_DURATION_OF_ACTION => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_NUMBER_OF_PAIRS => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
        self::FIELD_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
        self::FIELD_OPTIMAL_PEARSON_PRODUCT => 'nullable|numeric',
        self::FIELD_AVERAGE_VOTE => 'nullable|numeric',
        self::FIELD_NUMBER_OF_USERS => 'required|integer|min:-2147483648|max:2147483647',
        self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_STATISTICAL_SIGNIFICANCE => 'nullable|numeric',
        self::FIELD_CAUSE_UNIT_ID => 'nullable|integer|min:0|max:65535',
        self::FIELD_CAUSE_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_EFFECT_CHANGES => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_AGGREGATE_QM_SCORE => 'nullable|numeric',
        self::FIELD_STATUS => 'nullable|max:25',
        self::FIELD_REVERSE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
        self::FIELD_PREDICTIVE_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
        self::FIELD_DATA_SOURCE_NAME => 'nullable|max:255',
        self::FIELD_PREDICTS_HIGH_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_PREDICTS_LOW_EFFECT_CHANGE => 'nullable|integer|min:-2147483648|max:2147483647',
        self::FIELD_P_VALUE => 'nullable|numeric',
        self::FIELD_T_VALUE => 'nullable|numeric',
        self::FIELD_CRITICAL_T_VALUE => 'nullable|numeric',
        self::FIELD_CONFIDENCE_INTERVAL => 'nullable|numeric',
        self::FIELD_AVERAGE_EFFECT => 'nullable|numeric',
        self::FIELD_AVERAGE_EFFECT_FOLLOWING_HIGH_CAUSE => 'nullable|numeric',
        self::FIELD_AVERAGE_EFFECT_FOLLOWING_LOW_CAUSE => 'nullable|numeric',
        self::FIELD_AVERAGE_DAILY_LOW_CAUSE => 'nullable|numeric',
        self::FIELD_AVERAGE_DAILY_HIGH_CAUSE => 'nullable|numeric',
        self::FIELD_POPULATION_TRAIT_PEARSON_CORRELATION_COEFFICIENT => 'nullable|numeric',
        self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_LOW_OUTCOME => 'nullable|numeric',
        self::FIELD_GROUPED_CAUSE_VALUE_CLOSEST_TO_VALUE_PREDICTING_HIGH_OUTCOME => 'nullable|numeric',
        self::FIELD_CLIENT_ID => 'nullable|max:255',
        self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
        self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
        self::FIELD_CAUSE_VARIABLE_CATEGORY_ID => 'nullable|boolean',
        self::FIELD_EFFECT_VARIABLE_CATEGORY_ID => 'nullable|boolean',
        self::FIELD_INTERESTING_VARIABLE_CATEGORY_PAIR => 'nullable|boolean',
        self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
        self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
        self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
        self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
        self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
        self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:500',
        self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:1000',
        self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
        self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
        self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DAY => 'nullable|numeric',
        self::FIELD_CAUSE_BASELINE_AVERAGE_PER_DURATION_OF_ACTION => 'nullable|numeric',
        self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DAY => 'nullable|numeric',
        self::FIELD_CAUSE_TREATMENT_AVERAGE_PER_DURATION_OF_ACTION => 'nullable|numeric',
        self::FIELD_EFFECT_BASELINE_AVERAGE => 'nullable|numeric',
        self::FIELD_EFFECT_BASELINE_RELATIVE_STANDARD_DEVIATION => 'nullable|numeric',
        self::FIELD_EFFECT_BASELINE_STANDARD_DEVIATION => 'nullable|numeric',
        self::FIELD_EFFECT_FOLLOW_UP_AVERAGE => 'nullable|numeric',
        self::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE => 'nullable|numeric',
        self::FIELD_Z_SCORE => 'nullable|numeric',
        self::FIELD_CHARTS => 'nullable|json'
    ];
    /**
     * @param QMGlobalVariableRelationship|array|null $l
     * @param string|int|null $causeVariableNameOrId
     * @param string|int|null $effectVariableNameOrId
     * @param bool $requireMultipleUsers
     * @internal param $requestParams
     */
    public function __construct($l = null,
                                $causeVariableNameOrId = null,
                                $effectVariableNameOrId = null,
                                bool $requireMultipleUsers = true){
        $this->requireMultipleUsers = $requireMultipleUsers;
        if(!$l && !$causeVariableNameOrId && !$effectVariableNameOrId){return;}
        if($l){$this->updatePropertiesByDbRow($l);}
        if($causeVariableNameOrId){$this->setCauseVariable($causeVariableNameOrId);
		if(!$this->causeVariableId){le('!$this->causeVariableId');}}
        if($effectVariableNameOrId){$this->setEffectVariable($effectVariableNameOrId);
		if(!$this->causeVariableId){le('!$this->causeVariableId');}
		if(!$this->effectVariableId){le('!$this->effectVariableId');}}
        $this->setType(StudyTypeProperty::TYPE_POPULATION);
        // Must be set after updatePropertiesByDbRow because it will be overwritten if user variable relationship
        parent::__construct($l);
        TimeHelper::convertAllDateTimeValuesToRFC3339($this);
        $this->addToMemory();
        if($l){$this->generateCauseAndEffectVariablesFromCorrelationProperties($l);}
        $this->typeCastNumericAttributes();
        if($this->typeIsIndividual()){le("Type should not be individual!");}
        if(!$this->dataSourceName){$this->setDataSourceName(GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER);}
        $this->calculateIsPublic();
		if($this->id === 1398 && $this->effectVariableId === 1398){le('$this->id === 1398 && $this->effectVariableId === 1398');}
	    if(isset($this->reversePearsonCorrelationCoefficient)){
			$this->getPredictivePearsonCorrelationCoefficient();
		    if(!isset($this->predictivePearsonCorrelationCoefficient)){
			    GlobalVariableRelationshipPredictivePearsonCorrelationCoefficientProperty::calculate($this);
		    }
	    }
    }
    /**
     * @param $aggregatedCorrelationRow
     */
    private function generateCauseAndEffectVariablesFromCorrelationProperties($aggregatedCorrelationRow){
        if(QMVariableRelationship::weShouldDoFullCalculationAndGenerateCharts()){
            return;
        } // We'll just get the actual variables
        if(!$this->causeVariable){
            $this->causeVariable = QMCommonVariable::findInMemory($this->getCauseVariableId());
            if(!$this->causeVariable){
                $this->causeVariable =
                    new QMCommonVariable($this->getCauseVariableProperties($aggregatedCorrelationRow), null);
            }
        }
        if(!$this->effectVariable){
            $this->effectVariable = QMCommonVariable::findInMemory($this->getEffectVariableId());
             if(!$this->effectVariable){
                if(isset($aggregatedCorrelationRow->effectVariableName)){
                    $this->effectVariable =
                        new QMCommonVariable($this->getEffectVariableProperties($aggregatedCorrelationRow), null);
                } else{ // It's laravel data
                    $this->effectVariable = QMCommonVariable::find($this->getEffectVariableId());
                }
            }
        }
    }
    /**
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    private static function limitToInterestingCategoryPairs(\Illuminate\Database\Eloquent\Builder $qb): void{
        $qb->whereIn(self::FIELD_CAUSE_VARIABLE_CATEGORY_ID, [
            //EnvironmentVariableCategory::ID,
            //FoodsVariableCategory::ID,
            //ActivitiesVariableCategory::ID,
            NutrientsVariableCategory::ID,
            PhysicalActivityVariableCategory::ID,
            TreatmentsVariableCategory::ID,
        ]);
        //self::addOutcomeWhereClauses($qb); // Query is even slower without this clause (goes from 15s up to 22s when it's commented)
        $qb->whereIn(self::FIELD_EFFECT_VARIABLE_CATEGORY_ID, [
            EmotionsVariableCategory::ID,
            //PhysiqueVariableCategory::ID,
            SymptomsVariableCategory::ID,
        ]);
    }
    /**
     * @param int|null $userId
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    private static function limitToDownVoted(?int $userId, \Illuminate\Database\Eloquent\Builder $qb): void{
        if(!$userId){
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $userId = QMAuth::getQMUser()->getId();
        }
        $qb->whereHas('votes',
            function($q) use ($userId){
                $q->where(Vote::TABLE.'.'. Vote::FIELD_VALUE, 0)->where(Vote::TABLE.'.'. Vote::FIELD_USER_ID,
                        $userId);
            });
    }
    /**
     * @param string$causeName
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    private static function filterByCauseName(string $causeName, \Illuminate\Database\Eloquent\Builder $qb): void {
        $causeName = SearchVariableRequest::stripAstrix($causeName);
        if(strpos($causeName, "%") === false){
            if($v = Variable::findByName($causeName)){
                $qb->where(self::FIELD_CAUSE_VARIABLE_ID, $v->getId());
                return;
            }
        }
        $causeName = SearchVariableRequest::addWildCardsIfNecessary($causeName);
        $qb->whereHas('cause_variable', function($q) use ($causeName){
            $q->where(Variable::FIELD_NAME, "LIKE", $causeName);
        });
    }
    /**
     * @param string$effectName
     * @param \Illuminate\Database\Eloquent\Builder $qb
     */
    private static function filterByEffectName(string $effectName, \Illuminate\Database\Eloquent\Builder $qb): void {
        $effectName = SearchVariableRequest::stripAstrix($effectName);
        if(strpos($effectName, "%") === false){
            if($v = Variable::findByName($effectName)){
                $qb->where(self::FIELD_EFFECT_VARIABLE_ID, $v->getId());
                return;
            }
        }
        $effectName = SearchVariableRequest::addWildCardsIfNecessary($effectName);
        $qb->whereHas('effect_variable', function($q) use ($effectName){
            $q->where(Variable::FIELD_NAME, "LIKE", $effectName);
        });
    }
    /**
     * @return float
     */
    public function getQmScore(): float {
        if($this->qmScore === null){
            $this->qmScore = $this->aggregateQMScore;
        }
        return parent::getQmScore();
    }
    /**
     * @param int|null $userId
     * @return int|null
     */
    public function getUserVoteValue(int $userId = null): ?int {
        if($this->userVote !== null){
            return $this->userVote;
        }
        if($userId){
            $u = QMUser::find($userId);
        }else{
            $u = QMAuth::getQMUser();
        }
        if($u){
            if(self::EAGER_LOAD_VOTES){
                $l = $this->l();
                $votes = $l->votes;
                foreach($votes as $vote){
                    if($vote->user_id === $u->getId()){
                        return $this->userVote = $vote->value;
                    }
                }
            } else {
                $this->userVote = $u->getVoteValueForCauseAndEffect($this->getCauseVariableId(),
                    $this->getEffectVariableId());
            }
        }
        return $this->userVote;
    }
    /**
     * @return float
     * @throws NoUserVariableRelationshipsToAggregateException
     */
    public function getForwardPearsonCorrelationCoefficient(): float {
        if(!isset($this->forwardPearsonCorrelationCoefficient)){
            GlobalVariableRelationshipForwardPearsonCorrelationCoefficientProperty::calculate($this);
        }
        return parent::getForwardPearsonCorrelationCoefficient();
    }
	/**
	 * @return float|null
	 * @throws NoUserVariableRelationshipsToAggregateException
	 */
    public function getPredictsHighEffectChange(): ?float {
        if($this->predictsHighEffectChange === null){
            GlobalVariableRelationshipPredictsHighEffectChangeProperty::calculate($this);
        }
        return parent::getPredictsHighEffectChange();
    }
    /**
     * @return int
     */
    public function getPredictsLowEffectChange(): ?float {
        if($this->predictsLowEffectChange === null){
            GlobalVariableRelationshipPredictsLowEffectChangeProperty::calculate($this);
        }
        return parent::getPredictsLowEffectChange();
    }
    /**
     * @return array
     */
    private static function getSelectFields(): array{
        $db = ReadonlyDB::db();
        $arr = [
            'cvars.name AS causeVariableName',
            'evars.name AS effectVariableName',
            self::TABLE.'.aggregate_qm_score AS aggregateQMScore',
            $db->raw('COALESCE('.self::TABLE.'.cause_unit_id, cvars.default_unit_id) AS causeVariableCommonUnitId'),
            self::TABLE.'.cause_variable_id AS causeVariableId',
            self::TABLE.'.effect_variable_id AS effectVariableId',
            self::TABLE.'.forward_pearson_correlation_coefficient AS correlationCoefficient',
            self::TABLE.'.value_predicting_high_outcome AS avgDailyValuePredictingHighOutcome',
            self::TABLE.'.value_predicting_low_outcome AS avgDailyValuePredictingLowOutcome',
            'cvars.combination_operation AS causeVariableCombinationOperation',
            'cvars.common_alias AS causeVariableCommonAlias',
            'cvars.common_alias AS causeVariableDisplayName',
            'cvars.image_url AS causeVariableImageUrl',
            'cvars.informational_url AS causeVariableInformationalUrl',
            'cvars.ion_icon AS causeVariableIonIcon',
            'cvars.most_common_connector_id AS causeVariableMostCommonConnectorId',
            'cvars.outcome AS causeVariableIsOutcome',
            'cvars.product_url AS causeVariableProductUrl',
            'cvars.variable_category_id AS causeVariableCategoryId',
            'evars.combination_operation AS effectVariableCombinationOperation',
            'evars.common_alias AS effectVariableCommonAlias',
            'evars.common_alias AS effectVariableDisplayName',
            'evars.default_unit_id AS effectVariableCommonUnitId',
            'evars.image_url AS effectVariableImageUrl',
            'evars.informational_url AS effectVariableInformationalUrl',
            'evars.ion_icon AS effectVariableIonIcon',
            'evars.most_common_connector_id AS effectVariableMostCommonConnectorId',
            'evars.outcome AS effectVariableIsOutcome',
            'evars.product_url AS effectVariableProductUrl',
            'evars.valence AS effectVariableValence',
            'evars.variable_category_id AS effectVariableCategoryId'
        ];
        $arr = static::addSelectFields($arr, self::TABLE);
        return $arr;
    }
    /**
     * @param array $params
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Builder|GlobalVariableRelationship
     */
    private static function complexQb(array $params = [], int $userId = null) {
        $qb = GlobalVariableRelationship::query();
        $sort = $params['sort'] ?? null;
        if(!$sort){$qb->orderByRaw(self::TABLE.'.aggregate_qm_score DESC');}
        if($sort && strtolower($sort) === self::FIELD_P_VALUE){
            $qb->where(self::TABLE.'.'.self::FIELD_P_VALUE, '>', 0);
        }
        if(self::neitherCauseAndEffectNameOrIdAreSet($params)){
            self::limitToInterestingCategoryPairs($qb);
        }
        if(QMRequest::getParam('downvoted')){self::limitToDownVoted($userId, $qb);}
        self::applyFilterParams($params, $qb);
        QMVariableRelationship::applyOffsetLimitSort($qb, $params);
        if(self::bothCauseAndEffectNameOrIdAreSet($params)){$qb->orders = null;}
        return $qb;
    }
    /**
     * @param string|null $reason
     * @return string
     */
    public function getHardDeletionUrl(string $reason = null): string{
        $url = UrlHelper::getApiUrlForPath('sql',
            [
                'sql' => 'DELETE '.
                    self::TABLE.
                    ' from '.
                    self::TABLE.
                    ' where cause_variable_id ='.
                    $this->getCauseVariableId().
                    ' and effect_variable_id='.
                    $this->getEffectVariableId()." /*
                $reason
            */  ",
            ]);
        return $url;
    }
    public static function publishUpVotedStudies(){
        $rows = self::readonly()
            ->select([
                self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_ID,
                self::TABLE.'.'.self::FIELD_EFFECT_VARIABLE_ID,
                self::TABLE.'.'.self::FIELD_DELETED_AT,
            ])
            ->join('votes',
                static function(JoinClause $join){
                    $join->on(self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_ID, '=', 'votes.cause_variable_id')->on(self::TABLE.
                        '.'.
                        self::FIELD_EFFECT_VARIABLE_ID,
                        '=',
                        'votes.effect_variable_id');
                })
            ->where(Vote::TABLE.'.'. Vote::FIELD_VALUE, 1)
            ->where(self::TABLE.'.'.self::FIELD_DATA_SOURCE_NAME,
                '<>',
                GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_MedDRA)
            ->where(self::TABLE.'.'.self::FIELD_DATA_SOURCE_NAME,
                '<>',
                GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_CURE_TOGETHER)
            ->where(self::TABLE.'.'.self::FIELD_NUMBER_OF_CORRELATIONS, '>', 1)
            ->whereNull(self::TABLE.'.'.self::FIELD_PUBLISHED_AT)
            //            ->groupBy(self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_ID,
            //                self::TABLE.'.'.self::FIELD_EFFECT_VARIABLE_ID) Already unique
            ->getArray();
        QMLog::info(count($rows)." studies never published");
        /** @noinspection PhpUnhandledExceptionInspection */
        self::publishByCorrelationRows($rows);
    }
    /**
     * @param bool $dryRun
     */
    public static function unPublishDownVotedStudies(bool $dryRun = true){
        $rows = self::readonly()->select([
            self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_ID,
            self::TABLE.'.'.self::FIELD_EFFECT_VARIABLE_ID,
            self::TABLE.'.'.self::FIELD_DELETED_AT,
            self::TABLE.'.'.self::FIELD_ID,
            self::TABLE.'.'.self::FIELD_DATA_SOURCE_NAME,
        ])->join('votes',
            static function(JoinClause $join){
                $join->on(self::TABLE.'.'.self::FIELD_CAUSE_VARIABLE_ID, '=', 'votes.cause_variable_id')->on(self::TABLE.
                    '.'.
                    self::FIELD_EFFECT_VARIABLE_ID,
                    '=',
                    'votes.effect_variable_id');
            })->where(Vote::TABLE.'.'. Vote::FIELD_VALUE, 0)->where(Vote::TABLE.'.'.
                Vote::FIELD_USER_ID, UserIdProperty::USER_ID_MIKE)
            //->where(self::TABLE.'.'.self::FIELD_DATA_SOURCE_NAME, '<>', self::DATA_SOURCE_NAME_MedDRA)
            //->where(self::TABLE.'.'.self::FIELD_DATA_SOURCE_NAME, '<>', self::DATA_SOURCE_NAME_CURE_TOGETHER)
            //->where(self::TABLE.'.'.self::FIELD_NUMBER_OF_CORRELATIONS, '>', 1)
            //->whereNotNull(self::TABLE.'.'.self::FIELD_published_at)
            ->getArray();
        QMLog::info(count($rows)." studies down-voted by mike");
        foreach($rows as $row){
            $correlation = self::getByNamesOrIds($row->cause_variable_id, $row->effect_variable_id);
            $correlation->logInfo("Un-publishing");
            if(!$dryRun){
                $study = $correlation->findInMemoryOrNewQMStudy();
                $study->unPublish();
            }
        }
    }
	/**
	 * @param $rows
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public static function publishByCorrelationRows($rows): void{
        foreach($rows as $row){
            $causeVariable = QMCommonVariable::find($row->cause_variable_id);
            if(!$causeVariable){
                QMCommonVariable::getCommonVariableRowAndLogDeletionUrl($row->cause_variable_id);
            }
            $effectVariable = QMCommonVariable::find($row->effect_variable_id);
            if(!$effectVariable){
                le("Variable id $row->effect_variable_id not found!");
            }
            $correlation =
                self::getOrCreateByIds($causeVariable->getVariableIdAttribute(),
                    $effectVariable->getVariableIdAttribute());
            if(!$correlation->avgDailyValuePredictingHighOutcome){
                $correlation = self::getOrCreateByIds($causeVariable->getVariableIdAttribute(),
                        $effectVariable->getVariableIdAttribute());
            }
            $correlation->analyzeFullyAndSave(__FUNCTION__);
        }
    }
	/**
	 * @param string $reason
	 * @return \App\VariableRelationships\QMGlobalVariableRelationship
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
	 * @throws \App\Exceptions\StupidVariableException
	 * @throws \App\Exceptions\StupidVariableNameException
	 */
    public function analyzeFully(string $reason): QMGlobalVariableRelationship{
        $this->beforeAnalysis($reason);
        $this->setCauseVariable($this->getCauseVariableNameOrId());
        $this->setEffectVariable($this->getEffectVariableNameOrId());
        $this->exceptionIfStupidVariable();
	    $this->analyzeUserVariableRelationshipsIfNecessary(); // Need to do this or we try to save without new correlation properties like effectFollowUpPercentChangeFromBaseline
        $this->calculateAttributes();
        if(!$this->dataSourceName){$this->setDataSourceName(GlobalVariableRelationshipDataSourceNameProperty::DATA_SOURCE_NAME_USER);}
        $this->getTagLine();
        $this->updateOptimalValueSentencesIfNecessary();
        parent::__construct();
        $this->typeCastNumericAttributes();
        return $this;
    }
    /**
     * @return array
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws InvalidAttributeException
     */
    public function calculateAttributes(): array{
        $correlations = $this->getCorrelations();
        if(!$correlations->count()){
            throw new NoUserVariableRelationshipsToAggregateException($this);
        }
        $res = parent::calculateAttributes();
        return $res;
    }
    public function save(): bool{
        $res = parent::save();
        $id = $this->getId();
        if(!$id){le("no id");}
        //VoteGlobalVariableRelationshipIdProperty::fixNulls();
        Vote::whereCauseVariableId($this->getCauseVariableId())
            ->whereEffectVariableId($this->getEffectVariableId())
            ->update([Vote::FIELD_AGGREGATE_CORRELATION_ID => $id]);
        $this->getCauseQMVariable()->unsetOutcomes();
        $this->getEffectQMVariable()->unsetPredictors();
        return $res;
    }
    /**
     * @param string $reason
     * @param bool $countFirst
     * @return int
     */
    public function hardDeleteWithRelations(string $reason, bool $countFirst = true): int{
        $result = parent::hardDelete($reason, $countFirst);
        $rows =
            QMCommonVariable::writable()
                ->where(Variable::FIELD_ID, $this->getEffectVariableId())
                ->where(Variable::FIELD_BEST_CAUSE_VARIABLE_ID, $this->getCauseVariableId())
                ->getArray();
        $rows2 =
            QMCommonVariable::writable()
                ->where(Variable::FIELD_ID, $this->getCauseVariableId())
                ->where(Variable::FIELD_BEST_EFFECT_VARIABLE_ID, $this->getEffectVariableId())
                ->getArray();
        $both = array_merge($rows, $rows2);
        foreach($both as $row){
            $v = QMCommonVariable::findByNameOrId($row->id);
            $v->setBestGlobalVariableRelationship();
            try {
                $v->analyzeFullyAndSave(__FUNCTION__);
            } catch (Throwable $e) {
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
        return $result;
    }
	/**
	 * @param array $requestParams
	 * @param array $correlations
	 * @return array
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    private static function retryWithoutUserIfNecessary(array $requestParams, array $correlations): array{
        if(isset($requestParams['userId']) && !count($correlations)){
            unset($requestParams['userId']);
            $correlations = self::getOrCreateGlobalVariableRelationships($requestParams);
        }
        return [
            $requestParams,
            $correlations,
        ];
    }
	/**
	 * @param array $requestParams
	 * @param array $correlations
	 * @return QMGlobalVariableRelationship[]
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    private static function retryWithoutUpdatedAtIfNecessary(array $requestParams, array $correlations): array{
        if(isset($requestParams['updatedAt']) && !count($correlations)){
            unset($requestParams['updatedAt']);
            $correlations = self::getOrCreateGlobalVariableRelationships($requestParams);
        }
        return $correlations;
    }
	/**
	 * @param array $params
	 * @param \Illuminate\Database\Eloquent\Builder|GlobalVariableRelationship $qb
	 */
    private static function applyFilterParams(array $params, $qb){
        $filterParamsMap = [
            'causeVariableId'                      => self::TABLE.'.cause_variable_id',
            //'causeVariableName'                    => 'cause_variable.name',
            'correlationCoefficient'               => self::TABLE.'.forward_pearson_correlation_coefficient',
            'createdAt'                            => self::TABLE.'.created_at',
            'deletedAt'                            => self::TABLE.'.deleted_at',
            'durationOfAction'                     => self::TABLE.'.duration_of_action',
            'effectVariableId'                     => self::TABLE.'.effect_variable_id',
            //'effectVariableName'                   => 'effect_variable.name',
            'forwardPearsonCorrelationCoefficient' => self::TABLE.'.forward_pearson_correlation_coefficient',
            'lastUpdated'                          => self::TABLE.'.updated_at',
            'numberOfUsers'                        => self::TABLE.'.'.self::FIELD_NUMBER_OF_USERS,
            'onsetDelay'                           => self::TABLE.'.onset_delay',
            'qmScore'                              => self::TABLE.'.aggregate_qm_score',
            'updatedAt'                            => self::TABLE.'.updated_at',
            'pValue'                               => self::TABLE.'.p_value',
        ];
        if($causeName = $params['causeVariableName'] ?? null){
            self::filterByCauseName($causeName, $qb);
        }
        if($effectName = $params['effectVariableName'] ?? null){
            self::filterByEffectName($effectName, $qb);
        }
        QueryBuilderHelper::applyFilterParamsIfExist($qb, $filterParamsMap, $params);
    }
    /**
     * @param array $requestParams
     * @return bool
     */
    private static function bothCauseAndEffectIdAreSet(array $requestParams): bool{
        return isset($requestParams['causeVariableId'], $requestParams['effectVariableId']);
    }
    /**
     * @param array $requestParams
     * @return bool
     */
    private static function bothCauseAndEffectNameAreSet(array $requestParams): bool{
        return isset($requestParams['causeVariableName'], $requestParams['effectVariableName']);
    }
    /**
     * @param array $requestParams
     * @return bool
     */
    private static function bothCauseAndEffectNameOrIdAreSet(array $requestParams): bool{
        return self::bothCauseAndEffectIdAreSet($requestParams) || self::bothCauseAndEffectNameAreSet($requestParams);
    }
    /**
     * @param array $requestParams
     * @return bool
     */
    private static function neitherCauseAndEffectNameOrIdAreSet(array $requestParams): bool{
        if(isset($requestParams['causeVariableName'])){
            return false;
        }
        if(isset($requestParams['effectVariableName'])){
            return false;
        }
        if(isset($requestParams['causeVariableId'])){
            return false;
        }
        if(isset($requestParams['effectVariableId'])){
            return false;
        }
        return true;
    }
    /**
     * @param array $params
     * @param int|null $userId
     * @return QMGlobalVariableRelationship[]
     */
    public static function getGlobalVariableRelationships(array $params = [], int $userId = null): array {
        [$params, $userId] = self::formatAndValidateRequestParams($params, $userId);
        $causeNameOrId = BaseCauseVariableIdProperty::pluckNameOrId($params);
        $effectNameOrId = BaseEffectVariableIdProperty::pluckNameOrId($params);
        if($causeNameOrId && $effectNameOrId){
            $fromMemory = self::getFromMemoryByCauseAndEffectNameOrId($causeNameOrId, $effectNameOrId);
            if($fromMemory === false){return [];}
            if($fromMemory){return [$fromMemory];}
        }
        $qb = self::complexQb($params, $userId)->with([
            'cause_variable',
            'effect_variable',
            'votes'
        ]);
        return QMGlobalVariableRelationship::toDBModels($qb->get());
    }
	/**
	 * @param array $params
	 * @param int|null $userId
	 * @return QMGlobalVariableRelationship[]
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public static function getOrCreateGlobalVariableRelationships(array $params = [], int $userId = null): array{
        [$params, $userId,] = self::formatAndValidateRequestParams($params, $userId);
        $causeNameOrId = $params ? BaseCauseVariableIdProperty::pluckNameOrId($params) : null;
        $effectNameOrId = $params ? BaseEffectVariableIdProperty::pluckNameOrId($params) : null;
        $correlations = self::getGlobalVariableRelationships($params, $userId);
        if(!isset($params['doNotCreate']) && $causeNameOrId && $effectNameOrId && !$correlations){
            $c = new QMGlobalVariableRelationship(null, $causeNameOrId, $effectNameOrId);
            $c->analyzeFullyAndSave(__FUNCTION__);
            $correlations = [$c];
        }
        [$params, $correlations] = self::retryWithoutUserIfNecessary($params, $correlations);
        $correlations = self::retryWithoutUpdatedAtIfNecessary($params, $correlations);
		if(isset($params['effectVariableName'], $params['causeVariableName'])){
			return QMVariableRelationship::putExactMatchFirst($params['causeVariableName'],
				$params['effectVariableName'], $correlations);
		}
		return $correlations;
    }
	/**
	 * @param string $mostRecentAggregatedCorrelationTimeString
	 * @return array|mixed
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public static function analyzeOldAggregatedCorrelations(string $mostRecentAggregatedCorrelationTimeString = '1970-01-01 00:00:00'): array{
        QMLog::info('Aggregating user_variable_relationships updated since '.
            TimeHelper::YYYYmmddd($mostRecentAggregatedCorrelationTimeString));
        $newCorrelationPairs =
            self::getRecentlyUpdatedUserVariableRelationshipsWithMoreThanTwoUsers($mostRecentAggregatedCorrelationTimeString);
        $newCorrelationPairs =
            self::addCorrelationsWithNewVotes($mostRecentAggregatedCorrelationTimeString, $newCorrelationPairs);
        $newCorrelationPairs = self::addUserVariableRelationshipsForAppsAndWebsites($mostRecentAggregatedCorrelationTimeString,
            $newCorrelationPairs);
        $newCorrelationPairs = self::addUserVariableRelationshipsForPublicVariables($mostRecentAggregatedCorrelationTimeString,
            $newCorrelationPairs);
        $newCorrelationPairs = self::addUserVariableRelationshipsForSharedVariables($mostRecentAggregatedCorrelationTimeString,
            $newCorrelationPairs);
        if(!count($newCorrelationPairs)){
            QMLog::info("No new user variable relationships to aggregate");
            return $newCorrelationPairs;
        }
        QMLog::info("Got ".count($newCorrelationPairs)." new correlation pairs that need aggregation");
        foreach($newCorrelationPairs as $newCorrelationPair){
            $c = new QMGlobalVariableRelationship(null, $newCorrelationPair->cause_variable_id, $newCorrelationPair->effect_variable_id);
            $c->analyzeFullyAndSave(__FUNCTION__);
        }
        QMLog::info('Aggregated '.
            count($newCorrelationPairs).
            ' which were updated since '.
            $mostRecentAggregatedCorrelationTimeString);
        return $newCorrelationPairs;
    }
	/**
	 * @param int $variableId
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public static function analyzeAggregatedCorrelationsForVariable(int $variableId){
        $db = ReadonlyDB::db();
        $causeCorrelationPairs =
            $db->table('user_variable_relationships AS uc')
                ->select($db->raw(' COUNT(DISTINCT(user_id)) AS number_of_users,  cause_variable_id,  effect_variable_id '))
                ->where('uc.cause_variable_id', $variableId)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 1)
                ->getArray();
        $effectCorrelationPairs =
            $db->table('user_variable_relationships AS uc')
                ->select($db->raw('COUNT(DISTINCT(user_id)) AS number_of_users, cause_variable_id, effect_variable_id'))
                ->where('uc.effect_variable_id', $variableId)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 1)
                ->getArray();
        $newCorrelationPairs = array_merge($causeCorrelationPairs, $effectCorrelationPairs);
        $i = 0;
        $numberToReplace = count($newCorrelationPairs);
        foreach($newCorrelationPairs as $newCorrelationPair){
            $start = microtime(true);
            $i++;
            $numberLeft = $numberToReplace - $i;
            $c = new QMGlobalVariableRelationship(null, $newCorrelationPair->cause_variable_id,
                $newCorrelationPair->effect_variable_id);
            $c->analyzeFullyAndSave(__FUNCTION__);
            $time_elapsed_secs = microtime(true) - $start;
            $minutesRemaining = $numberLeft * $time_elapsed_secs / 60;
            QMLog::info(round($minutesRemaining).
                ' MINUTES REMAINING...  '.
                round(100 * $i / $numberToReplace).
                "% complete.  ".
                $numberLeft.
                " pairs left...");
        }
    }
    /**
     * @param $mostRecentAggregatedCorrelationTimeString
     * @param $newCorrelationPairs
     * @return array
     * @internal param $db
     */
    private static function addUserVariableRelationshipsForAppsAndWebsites($mostRecentAggregatedCorrelationTimeString,
                                                                  $newCorrelationPairs): array{
        $newCorrelationPairsForAppsAndWebsitesAsEffect =
            ReadonlyDB::db()
                ->table('user_variable_relationships AS uc')
                ->select(ReadonlyDB::db()
                    ->raw(' COUNT(DISTINCT(c.user_id)) AS number_of_users,  c.cause_variable_id, c.effect_variable_id '))
                ->join('variables AS cvars', 'uc.cause_variable_id', '=', 'cvars.id')
                ->join('variables AS evars', 'uc.effect_variable_id', '=', 'evars.id')
                ->where('evars.variable_category_id', SoftwareVariableCategory::ID)// apps and websites
                ->where('uc.updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 3)// Higher user requirement
                ->getArray();
        $newCorrelationPairs = array_merge($newCorrelationPairs, $newCorrelationPairsForAppsAndWebsitesAsEffect);
        $newCorrelationPairsForAppsAndWebsitesAsCause =
            ReadonlyDB::db()
                ->table('user_variable_relationships AS uc')
                ->select(ReadonlyDB::db()
                    ->raw(' COUNT(DISTINCT(c.user_id)) AS number_of_users,  c.cause_variable_id, c.effect_variable_id '))
                ->join('variables AS cvars', 'uc.cause_variable_id', '=', 'cvars.id')
                ->join('variables AS evars', 'uc.effect_variable_id', '=', 'evars.id')
                ->where('cvars.variable_category_id', SoftwareVariableCategory::ID)// apps and websites
                ->where('uc.updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 3)// Higher user requirement
                ->getArray();
        $newCorrelationPairs = array_merge($newCorrelationPairs, $newCorrelationPairsForAppsAndWebsitesAsCause);
        $newCorrelationPairs = array_unique($newCorrelationPairs, SORT_REGULAR);
        QMLog::info("Got ".count($newCorrelationPairs)." UserVariableRelationshipsForAppsAndWebsites");
        return $newCorrelationPairs;
    }
    /**
     * @param $mostRecentAggregatedCorrelationTimeString
     * @param $newCorrelationPairs
     * @return array
     * @internal param $db
     * @noinspection SpellCheckingInspection
     */
    private static function addUserVariableRelationshipsForPublicVariables($mostRecentAggregatedCorrelationTimeString,
                                                                  $newCorrelationPairs): array{
        $correlationsForPublicVariables =
            ReadonlyDB::getBuilderByTable('user_variable_relationships AS uc')
                ->select(ReadonlyDB::db()
                    ->raw(' COUNT(DISTINCT(c.user_id)) AS number_of_users,  c.cause_variable_id, c.effect_variable_id '))
                ->join('variables AS cvars', 'uc.cause_variable_id', '=', 'cvars.id')
                ->join('variables AS evars', 'uc.effect_variable_id', '=', 'evars.id')
                ->leftJoin('variable_categories as ccats', 'cvars.variable_category_id', '=', 'ccats.id')
                ->leftJoin('variable_categories as ecats', 'evars.variable_category_id', '=', 'ecats.id')
                ->whereRaw('COALESCE(cvars.`is_public`, ccats.`is_public`)= 1')
                ->whereRaw('COALESCE(evars.`is_public`, ecats.`is_public`)= 1')
                ->where('uc.updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 0)
                ->getArray();
        $newCorrelationPairs = array_merge($newCorrelationPairs, $correlationsForPublicVariables);
        $newCorrelationPairs = array_unique($newCorrelationPairs, SORT_REGULAR);
        QMLog::info("Got ".count($newCorrelationPairs)." UserVariableRelationshipsForPublicVariables");
        return $newCorrelationPairs;
    }
    /**
     * @param $mostRecentAggregatedCorrelationTimeString
     * @param $newCorrelationPairs
     * @return array
     * @internal param $db
     */
    private static function addUserVariableRelationshipsForSharedVariables($mostRecentAggregatedCorrelationTimeString,
                                                                  $newCorrelationPairs): array{
        $correlationsForPublicVariables =
            ReadonlyDB::getBuilderByTable('user_variable_relationships AS uc')
                ->select(ReadonlyDB::db()
                    ->raw('COUNT(DISTINCT(uc.user_id)) AS number_of_users, uc.cause_variable_id, uc.effect_variable_id'))
                ->join('user_variables as cuv', 'uc.cause_variable_id', '=', 'cuv.variable_id')
                ->join('user_variables as euv', 'uc.effect_variable_id', '=', 'euv.variable_id')
                ->where('cuv.is_public', 1)
                ->where('euv.is_public', 1)
                ->where('uc.updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 0)
                ->getArray();
        $newCorrelationPairs = array_merge($newCorrelationPairs, $correlationsForPublicVariables);
        $newCorrelationPairs = array_unique($newCorrelationPairs, SORT_REGULAR);
        QMLog::info("Got ".count($newCorrelationPairs)." UserVariableRelationshipsForSharedVariables");
        return $newCorrelationPairs;
    }
    /**
     * @param $mostRecentAggregatedCorrelationTimeString
     * @param $newCorrelationPairs
     * @return array
     */
    private static function addCorrelationsWithNewVotes($mostRecentAggregatedCorrelationTimeString,
                                                        $newCorrelationPairs): array{
        $newVotePairs = ReadonlyDB::db()
            ->table('votes')
            ->select(ReadonlyDB::db()->raw('
                COUNT(DISTINCT(user_id)) AS number_of_users,
                cause_variable_id,
                effect_variable_id
            '))
            ->where('updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
            ->groupBy(['cause_variable_id', 'effect_variable_id'])
            ->getArray();
        $newCorrelationPairs = array_merge($newCorrelationPairs, $newVotePairs);
        $newCorrelationPairs = array_unique($newCorrelationPairs, SORT_REGULAR);
        QMLog::info("Got ".count($newCorrelationPairs)." CorrelationsWithNewVotes");
        return $newCorrelationPairs;
    }
    /**
     * @param $mostRecentAggregatedCorrelationTimeString
     * @return array
     */
    private static function getRecentlyUpdatedUserVariableRelationshipsWithMoreThanTwoUsers($mostRecentAggregatedCorrelationTimeString): array{
        $newCorrelationPairs =
            ReadonlyDB::getBuilderByTable('user_variable_relationships AS uc')
                ->select(ReadonlyDB::db()
                    ->raw('COUNT(DISTINCT(c.user_id)) AS number_of_users, c.cause_variable_id, c.effect_variable_id'))
                ->join('variables AS cvars', 'uc.cause_variable_id', '=', 'cvars.id')
                ->join('variables AS evars', 'uc.effect_variable_id', '=', 'evars.id')
                ->where('cvars.variable_category_id', '<>', 20)// Let's exclude apps and website for now
                ->where('evars.variable_category_id', '<>', 20)// Let's exclude apps and website for now
                ->where('uc.updated_at', '>', $mostRecentAggregatedCorrelationTimeString)
                ->groupBy(['uc.cause_variable_id', 'uc.effect_variable_id'])
                ->having('number_of_users', '>', 1)
                ->getArray();
        QMLog::info("Got ".count($newCorrelationPairs)." RecentlyUpdatedUserVariableRelationshipsWithMoreThanTwoUsers");
        return $newCorrelationPairs;
    }
    /**
     * @param string $causeVariableName
     * @param string $effectVariableName
     * @return QMGlobalVariableRelationship
     */
    public static function getSingleAggregatedCorrelationByVariableNames(string $causeVariableName,
                                                                         string $effectVariableName){
        $fromMemory = self::getFromMemoryByCauseAndEffectNameOrId($causeVariableName, $effectVariableName);
        if($fromMemory !== null){
            return $fromMemory;
        }
        $aggregatedCorrelations = self::getGlobalVariableRelationships([
            'causeVariableName'  => $causeVariableName,
            'effectVariableName' => $effectVariableName,
        ]);
        $c = $aggregatedCorrelations[0] ?? null;
        return $c;
    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @param int|null $userId
     * @return QMGlobalVariableRelationship
     */
    public static function getByIds(int $causeVariableId, int $effectVariableId, int $userId = null): ?QMGlobalVariableRelationship {
        $fromMemory = self::getFromMemoryByCauseAndEffectNameOrId($causeVariableId, $effectVariableId);
        if($fromMemory === false){return null;}
        if($fromMemory !== null){return $fromMemory;}
        $uuid = self::generateStudyId($causeVariableId, $effectVariableId);
        $fromMemory = static::findInMemory($uuid);
        if($fromMemory === false){return null;}
        if($fromMemory !== null){
            if(!$fromMemory->effectVariableId){
                $fromMemory = self::getFromMemoryByCauseAndEffectNameOrId($causeVariableId, $effectVariableId);
                le("No effect id on $fromMemory");
            }
            return $fromMemory;
        }
        $l = GlobalVariableRelationship::findByVariableNamesOrIds($causeVariableId, $effectVariableId);
        if($l){
            $ac = $l->getDBModel();
            if($userId){$ac->getUserVoteValue($userId);} // Need to run again
            return $ac;
        } else {
            static::setFalseInMemoryByUniqueIndex([
                self::FIELD_CAUSE_VARIABLE_ID => $causeVariableId,
                self::FIELD_EFFECT_VARIABLE_ID => $causeVariableId,
            ]);
            return null;
        }
    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @param int|null $userId
     * @return QMGlobalVariableRelationship|null
     */
    public static function getOrCreateByIds(int $causeVariableId,
                                            int $effectVariableId,
                                            int $userId = null): ?QMGlobalVariableRelationship{
        $existing = self::getByIds($causeVariableId, $effectVariableId, $userId);
        if($existing){
            if($userId){$existing->getUserVoteValue($userId);}
            return $existing;
        }
        $c = new QMGlobalVariableRelationship(null, $causeVariableId, $effectVariableId);
        if($userId){$c->getUserVoteValue($userId);}
        try {
            $c->analyzeFullyAndSave(__FUNCTION__);
        } catch (AlreadyAnalyzedException | AlreadyAnalyzingException | DuplicateFailedAnalysisException | NotEnoughDataException | TooSlowToAnalyzeException $e) {
            $c->addException($e);
            return $c;
        } catch (ModelValidationException | StupidVariableNameException $e) {
            le($e);
        }
		$c->getPredictivePearsonCorrelationCoefficient();
		if(!isset($c->predictivePearsonCorrelationCoefficient)){
			$c->logError("No predictivePearsonCorrelationCoefficient");
		}
        return $c;
    }
    /**
     * @param int|string $cause
     * @param int|string $effect
     * @return QMGlobalVariableRelationship
     */
    public static function getByNamesOrIds($cause,
                                           $effect){
        if(is_int($cause)){
            return self::getByIds($cause, $effect);
        }
        return self::getSingleAggregatedCorrelationByVariableNames($cause, $effect);
    }
    /**
     * @param string|int $causeVariableNameOrId
     * @return QMCommonVariable
     */
    public function setCauseVariable($causeVariableNameOrId): QMCommonVariable{
        $v = QMCommonVariable::find($causeVariableNameOrId);
        if(!$v){
            QMCommonVariable::getCommonVariableRowAndLogDeletionUrl($causeVariableNameOrId);
        }
        $this->causeVariable = $v;
        $this->causeVariableCategoryId = $v->variableCategoryId;
        $this->causeVariableCommonUnitId = $v->getCommonUnit()->id;
        $this->causeVariableId = $v->getVariableIdAttribute();
		if(!$this->causeVariableId){le('!$this->causeVariableId');}
        $this->causeVariableName = $v->name;
        return $this->causeVariable;
    }
	/**
	 * @param array $requestParams
	 * @param $userId
	 * @return array
	 */
    private static function formatAndValidateRequestParams(array $requestParams, $userId): array{
        $requestParams =
            QMStr::properlyFormatRequestParams($requestParams, QMUserVariableRelationship::getLegacyRequestParameters());
        if(!$userId && isset($requestParams['userId'])){
            $userId = (int)$requestParams['userId'];
        }
        if(isset($requestParams['causeVariableId'])){
            unset($requestParams['causeVariableName']);
        }
        if(isset($requestParams['effectVariableId'])){
            unset($requestParams['effectVariableName']);
        }
        if(isset($requestParams['sort']) && str_contains($requestParams['sort'], 'qmScore')){
            $requestParams['sort'] = str_replace('qmScore', 'aggregateQMScore', $requestParams['sort']);
        }
        QMAPIValidator::validateParams(QMUserVariableRelationship::getAllowedRequestParameters(),
            array_keys($requestParams),
            'user_variable_relationships/correlations_get');
        return [
            $requestParams,
            $userId,
        ];
    }
    /**
     * @param bool $instantiate
     * @return QMQB
     */
    public static function qb(bool $instantiate = true): QMQB{
        $db = ReadonlyDB::db();
        $qb = $db->table(self::TABLE)
            ->select(self::getSelectFields())
            ->join('variables AS cvars', self::TABLE.'.cause_variable_id', '=', 'cvars.id')
            ->join('variables AS evars', self::TABLE.'.effect_variable_id', '=', 'evars.id')
            ->whereNull(self::TABLE.'.'.self::FIELD_DELETED_AT);
        self::addCauseCommonVariableColumns($qb, $db);
        self::addEffectCommonVariableColumns($qb, $db);
        if($instantiate){$qb->class = self::class;}
        return $qb;
    }
    /**
     * @param string|int $effectVariableNameOrId
     * @return QMCommonVariable
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setEffectVariable($effectVariableNameOrId): QMCommonVariable{
        $v = QMCommonVariable::find($effectVariableNameOrId);
        if(!$v){
            QMCommonVariable::getCommonVariableRowAndLogDeletionUrl($effectVariableNameOrId);
        }
        $this->effectVariable = $v;
        $this->effectVariableCategoryId = $v->variableCategoryId;
        $this->effectVariableCommonUnitId = $v->getCommonUnit()->id;
        $this->effectVariableId = $v->variableId;
        $this->effectVariableName = $v->name;
        return $this->effectVariable;
    }
    /**
     * @return float
     */
    public function getAggregateQMScore(): float {
        return $this->aggregateQMScore;
    }
    /**
     * @param string|int $causeVariableNameOrId
     * @param string|int $effectVariableNameOrId
     * @return QMGlobalVariableRelationship|null|false
     */
    public static function getFromMemoryByCauseAndEffectNameOrId($causeVariableNameOrId, $effectVariableNameOrId) {
        $correlations = static::getAllFromMemoryIndexedByUuidAndId();
        if(!$correlations){return null;}
        $match = collect($correlations)->filter(function($c) use ($causeVariableNameOrId, $effectVariableNameOrId){
            if(!$c){return null;}
            /** @var self $c */
            if($c->causeVariableId === $causeVariableNameOrId && $c->effectVariableId === $effectVariableNameOrId){return $c;}
            if($c->causeVariableName === $causeVariableNameOrId && $c->effectVariableName === $effectVariableNameOrId){return $c;}
            return null;
        })->first();
        return $match;
    }
    /**
     * @param int $causeVariableId
     * @param int $effectVariableId
     * @return int
     */
    public static function deleteByVariableIds(int $causeVariableId, int $effectVariableId): int{
        $causeVariable = QMCommonVariable::find($causeVariableId);
        $effectVariableName = "Non Existent Variable";
        $effectVariable = QMCommonVariable::find($effectVariableId);
        if($effectVariable){
            $effectVariableName = $effectVariable->name;
        }
        $numberUserVariableRelationships =
            QMUserVariableRelationship::readonly()
                ->where(self::FIELD_CAUSE_VARIABLE_ID, $causeVariableId)
                ->where(self::FIELD_EFFECT_VARIABLE_ID, $effectVariableId)
                ->count();
        if(!is_object($causeVariable)){
            le("cause variable not an object!");
        }
        QMLog::error("Deleting global variable relationship for $causeVariable->name and
        $effectVariableName. There are $numberUserVariableRelationships user variable relationships");
        return self::writable()
            ->where(self::FIELD_CAUSE_VARIABLE_ID, $causeVariableId)
            ->where(self::FIELD_EFFECT_VARIABLE_ID, $effectVariableId)
            ->delete();
    }
    /**
     * @return mixed
     */
    public function getPopulationTraitCorrelationPearsonCorrelationCoefficient(): ?float{
        if($this->populationTraitPearsonCorrelationCoefficient !== null){
            return $this->populationTraitPearsonCorrelationCoefficient;
        }
        return GlobalVariableRelationshipPopulationTraitPearsonCorrelationCoefficientProperty::calculate($this);
    }
	/**
	 * @return float
	 */
	protected function calculateAverageEffect(): float {
		return $this->averageEffect =  GlobalVariableRelationshipAverageEffectProperty::calculate($this);
	}
    /**
     * @return PairOfAverages[]
     */
    public function getPairsOfAveragesForAllUsers(): array{
        if($this->pairsOfAveragesForAllUsers !== null){
            return $this->pairsOfAveragesForAllUsers;
        }
        return $this->setPairsOfAveragesForAllUsers();
    }
	/**
	 * @return \Illuminate\Support\Collection|UserVariableRelationship[]
	 */
	public function getCorrelations():Collection{
		return $this->firstOrNewLaravelModel()->getCorrelations();
	}
	/**
	 * @return GlobalVariableRelationship
	 */
	public function firstOrNewLaravelModel(): GlobalVariableRelationship{
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::firstOrNewLaravelModel();
	}
	/**
     * @return PairOfAverages[]
     */
    public function setPairsOfAveragesForAllUsers(): array{
		if($this->pairsOfAveragesForAllUsers !== null){
			return $this->pairsOfAveragesForAllUsers;
		}
        $validPairs = [];
		$correlations = $this->getCorrelations();
	    $pairs = [];
        /** @var UserVariableRelationship $correlation */
        foreach($correlations as $correlation){
            $correlation->setGlobalVariableRelationship($this->l());
			$pairs[] = $correlation->getPairOfAverages();
		}
        foreach($pairs as $pair){
            $cause = $this->getCauseVariable();
            try {
                $cause->validateValueForCommonVariableAndUnit($pair->causeVariableAverageValue,
                    'causeVariableAverageValue', $this->getDurationOfAction());
            } catch (InvalidVariableValueException $e){
                if(!AppMode::isProduction()){le($e);} // If production, we'll have to handle this later
                $show = UserVariable::generateShowUrl($pair->getCauseUserVariableId());
                $this->addWarning("Excluding pair of averages for $show because ".$e->getMessage());
                continue;
            }
            $effect = $this->getEffectVariable();
            try {
                $effect->validateValueForCommonVariableAndUnit($pair->effectVariableAverageValue,
                    'effectVariableAverageValue', $this->getDurationOfAction());
            } catch (InvalidVariableValueException $e){
                if(!AppMode::isProduction()){le($e);} // If production, we'll have to handle this later
                $show = UserVariable::generateShowUrl($pair->getEffectUserVariableId());
                $this->addWarning("Excluding pair of averages for $show because ".$e->getMessage());
                continue;
            }
            $validPairs[] = $pair;
        }
        return $this->pairsOfAveragesForAllUsers = $validPairs;
    }
	/**
	 * @return QMPopulationStudy|QMCohortStudy
	 */
	public function findInMemoryOrNewQMStudy(): QMStudy{
		$study = QMPopulationStudy::findInMemoryOrNewQMStudy($this->causeVariableName,
			$this->effectVariableName,
			QMStudy::DEFAULT_PRINCIPAL_INVESTIGATOR_ID,
			StudyTypeProperty::TYPE_POPULATION);
		$study->setStatistics($this);
		return $study;
	}
    /**
     * @param QMGlobalVariableRelationship|array $row
     */
    private function updatePropertiesByDbRow($row): void{
        if(is_array($row)){
            $row = json_decode(json_encode($row), false);
        }
        /** @noinspection MissingIssetImplementationInspection */
        if(isset($row->cause_variable_id)){
            $row->causeVariableId = $row->cause_variable_id;
        }
        /** @noinspection MissingIssetImplementationInspection */
        if(isset($row->effect_variable_id)){
            $row->effectVariableId = $row->effect_variable_id;
        }
        $this->populateFieldsByArrayOrObject($row);
        if(!$this->causeVariableId){
            le("No cause id!");
        }
        /** @noinspection MissingIssetImplementationInspection */
        if(isset($row->value_predicting_high_outcome)){
            $this->setAvgDailyValuePredictingHighOutcome($row->value_predicting_high_outcome);
            $this->setAvgDailyValuePredictingLowOutcome($row->value_predicting_low_outcome);
        }
        if(isset($this->aggregateQmScore)){
            $this->aggregateQMScore = $this->aggregateQmScore;
            unset ($this->aggregateQmScore);
        }
        if(!isset($this->correlationCoefficient)){
            $this->correlationCoefficient = $this->forwardPearsonCorrelationCoefficient;
        }
        if(isset($this->aggregateQMScore)){
            $this->qmScore = $this->aggregateQMScore;
        }
        $this->vote = $this->averageVote;
    }
    protected function calculateIsPublic(): bool {
        return GlobalVariableRelationshipIsPublicProperty::calculate($this);
    }
    public function pluckFromCorrelations(string $key): array {
        $correlations = $this->getCorrelations();
        return $correlations
            ->where($key, '!=', NULL)
            ->pluck($key)
            ->all();
    }
    /**
     * @return string
     */
    public function getStudyId(): string{
        return QMPopulationStudy::generateStudyId($this->getCauseVariableId(), $this->getEffectVariableId());
    }
    /**
     * @return array
     */
    public static function getFloatAttributes(): array{
        return [
            'averageVote',
            'predictivePearsonCorrelationCoefficient',
            'predictsHighEffectChange',
            'predictsLowEffectChange',
            'reversePearsonCorrelationCoefficient',
        ];
    }
    private function typeCastFloatAttributes(){
        foreach(self::getFloatAttributes() as $floatAttribute){
            if(isset($this->$floatAttribute) && is_string($this->$floatAttribute)){
                $this->$floatAttribute = (float)$this->$floatAttribute;
            }
        }
    }
    /**
     * @return array
     */
    public static function getIntAttributes(): array{
        return [
            'causeChanges',
            'effectChanges',
            'numberOfCorrelations',
            'numberOfUsers',
        ];
    }
    private function typeCastIntAttributes(){
        foreach(self::getIntAttributes() as $intAttribute){
            if(isset($this->$intAttribute) && is_string($this->$intAttribute)){
                $this->$intAttribute = (int)$this->$intAttribute;
            }
        }
    }
    private function typeCastNumericAttributes(){
        $this->typeCastFloatAttributes();
        $this->typeCastIntAttributes();
    }
    /**
     * @param Builder $qb
     * @param Connection $db
     */
    private static function addCauseCommonVariableColumns(Builder $qb, Connection $db){
        $causeCommonVariableColumns = GetCommonVariablesRequest::getCommonVariableColumnsArray($db);
        foreach($causeCommonVariableColumns as $column){
            if(strpos($column, VariableCategory::TABLE.'.') !== false){
                continue;
            }
            $column = str_replace(Variable::TABLE.'.', 'cvars.', $column);
            $column = str_ireplace(' as ', ' as causeVariable_', $column);
            $qb->columns[] = $column;
        }
    }
    /**
     * @param Builder $qb
     * @param Connection $db
     */
    private static function addEffectCommonVariableColumns(Builder $qb, Connection $db){
        $effectCommonVariableColumns = GetCommonVariablesRequest::getCommonVariableColumnsArray($db);
        foreach($effectCommonVariableColumns as $column){
            if(strpos($column, VariableCategory::TABLE.'.') !== false){
                continue;
            }
            $column = str_replace(Variable::TABLE.'.', 'evars.', $column);
            $column = str_ireplace(' as ', ' as effectVariable_', $column);
            $qb->columns[] = $column;
        }
    }
    /**
     * @return int
     */
    public function getNumberOfCorrelations(): int{
        return $this->numberOfCorrelations;
    }
	/**
	 * @param string $reason
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\InvalidAttributeException
	 * @throws \App\Exceptions\NoUserVariableRelationshipsToAggregateException
	 * @throws \App\Exceptions\StupidVariableException
	 * @throws \App\Exceptions\StupidVariableNameException
	 */
    public function analyzePartially(string $reason){
        $this->analyzeFully($reason);
    }
    /**
     * @return QMCommonVariable[]
     */
    public function getSourceObjects(): array{
        return $this->getQMUserVariableRelationships();
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function getOrCreateQMGlobalVariableRelationship(): QMGlobalVariableRelationship{
        return $this;
    }
    public function updateOptimalValueSentencesIfNecessary(): void {
        $sentence = $this->getHigherPredictsAndOptimalValueSentenceWithDurationOfAction();
        $prefix = "Based on data from ".$this->getNumberOfUsers()." participants,";
        $sentence = str_replace('Your', $prefix, $sentence);
        $arr = [Variable::FIELD_OPTIMAL_VALUE_MESSAGE => $sentence];
        $id = $this->id;
        if(!$id){
	        $l = $this->firstOrNewLaravelModel();
            if($l && $l->id){$this->id = $id = $l->id;}
        }
        if($id){$arr[Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID] = $id;}
	    $this->updateBestEffectIfNecessary($arr);
	    $this->updateBestCauseIfNecessary($arr);
    }
    public function getUser(): ?QMUser{
        return QMUser::population();
    }
    /**
     * @return GlobalVariableRelationshipChartGroup
     */
    public function setCharts(): ChartGroup {
        $charts = new GlobalVariableRelationshipChartGroup($this);
        return $this->charts = $charts;
    }
    /**
     * @return GlobalVariableRelationshipChartGroup
     */
    public function getOrSetCharts(): ChartGroup {
        $charts = $this->charts;
        if($charts === null && $this->id){
            $l = $this->l();
            $plucked = $l->pluckCharts();
            $this->charts = $plucked;
        }
        if($this->charts){
            $c = GlobalVariableRelationshipChartGroup::instantiateIfNecessary($this->charts);
            $c->setSourceObject($this);
            return $c;
        }
        return $this->setCharts();
    }
    public static function getViewInsertStatement(): string {
        $def = self::getViewDefinition();
        $table = static::TABLE;
        $allFields = self::getColumns();
        return Writable::convertSelectToInsertStatement($table, $allFields, $def);
    }
    public static function createView(){
        Writable::createOrReplaceView(self::VIEW_USER_VARIABLE_RELATIONSHIPS_AGGREGATED, self::getViewDefinition());
    }
    public static function getViewDefinition(): string{
        $excludedCategories = "(".implode(",", [
            SoftwareVariableCategory::ID,
            LocationsVariableCategory::ID
        ]).")";
        return"
            SELECT
                ANY_VALUE(uc.cause_variable_category_id) as cause_variable_category_id,
                ANY_VALUE(uc.effect_variable_category_id) as effect_variable_category_id,
                MAX(uc.analysis_ended_at) as newest_data_at,
                SUM(uc.average_effect * uc.statistical_significance) / SUM(uc.statistical_significance) AS average_effect,
                SUM(uc.average_daily_low_cause * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS average_daily_low_cause,
                SUM(uc.average_daily_high_cause * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS average_daily_high_cause,
                SUM(uc.forward_pearson_correlation_coefficient * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS forward_pearson_correlation_coefficient,
                SUM(uc.reverse_pearson_correlation_coefficient * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS reverse_pearson_correlation_coefficient,
                SUM(uc.predictive_pearson_correlation_coefficient * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS predictive_pearson_correlation_coefficient,
                GROUP_CONCAT(DISTINCT uc.data_source_name ORDER BY data_source_name ASC SEPARATOR
                            ', ')                                                                      AS data_source_name,
                SUM(uc.value_predicting_high_outcome * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS value_predicting_high_outcome,
                SUM(uc.value_predicting_low_outcome * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS value_predicting_low_outcome,
                SUM(uc.optimal_pearson_product * uc.statistical_significance) /
                SUM(uc.statistical_significance)                                                        AS optimal_pearson_product,
                AVG(uc.average_effect_following_high_cause)                                             AS average_effect_following_high_cause,
                AVG(uc.average_effect_following_low_cause)                                              AS average_effect_following_low_cause,
                AVG(uc.cause_baseline_average_per_day)                                                  AS cause_baseline_average_per_day,
                AVG(uc.cause_baseline_average_per_duration_of_action)                                   AS cause_baseline_average_per_duration_of_action,
                AVG(uc.cause_treatment_average_per_day)                                                 AS cause_treatment_average_per_day,
                AVG(uc.cause_treatment_average_per_duration_of_action)                                  AS cause_treatment_average_per_duration_of_action,
                AVG(uc.confidence_interval)                                                             AS confidence_interval,
                AVG(uc.critical_t_value)                                                                AS critical_t_value,
                AVG(uc.duration_of_action)                                                              AS duration_of_action,
                AVG(uc.effect_baseline_average)                                                         AS effect_baseline_average,
                AVG(uc.effect_baseline_relative_standard_deviation)                                     AS effect_baseline_relative_standard_deviation,
                AVG(uc.effect_baseline_standard_deviation)                                              AS effect_baseline_standard_deviation,
                AVG(uc.effect_follow_up_average)                                                        AS effect_follow_up_average,
                AVG(uc.effect_follow_up_percent_change_from_baseline)                                   AS effect_follow_up_percent_change_from_baseline,
                AVG(uc.grouped_cause_value_closest_to_value_predicting_high_outcome)                    AS grouped_cause_value_closest_to_value_predicting_high_outcome,
                AVG(uc.grouped_cause_value_closest_to_value_predicting_low_outcome)                     AS grouped_cause_value_closest_to_value_predicting_low_outcome,
                AVG(uc.onset_delay)                                                                     AS onset_delay,
                AVG(uc.p_value)                                                                         AS p_value,
                AVG(uc.predicts_high_effect_change)                                                     AS predicts_high_effect_change,
                AVG(uc.predicts_low_effect_change)                                                      AS predicts_low_effect_change,
                AVG(uc.statistical_significance)                                                        AS statistical_significance,
                AVG(uc.t_value)                                                                         AS t_value,
                AVG(uc.z_score)                                                                         AS z_score,
                COUNT(DISTINCT (uc.id))                                                                 AS number_of_correlations,
                COUNT(DISTINCT (uc.user_id))                                                            AS number_of_users,
                FLOOR(AVG(uc.number_of_pairs))                                                          AS number_of_pairs,
                NOW()                                                                                   AS created_at,
                NOW()                                                                                   AS updated_at,
                SUM(uc.cause_changes)                                                                   AS cause_changes,
                SUM(uc.effect_changes)                                                                  AS effect_changes,
                uc.cause_variable_id                                                                    AS cause_variable_id,
                uc.effect_variable_id                                                                   AS effect_variable_id
            from user_variable_relationships as uc
            where uc.cause_variable_category_id not in $excludedCategories and
                  uc.effect_variable_category_id not in $excludedCategories
            group by uc.cause_variable_id and uc.effect_variable_id
            having number_of_users > 1
        ";
    }
    public static function createWhereNecessary(): void{
        self::createView();
        Writable::statementStatic(self::getViewInsertStatement());
        GlobalVariableRelationshipAverageVoteProperty::updateAverageVotes();
        GlobalVariableRelationshipAggregateQmScoreProperty::updateAll();
    }
    /**
     * @return UserVariableRelationship[]|Collection
     */
    public function analyzeUserVariableRelationshipsIfNecessary(): Collection {
        $correlations = $this->getCorrelations();
        $good = [];
        foreach($correlations as $c){
            try {
                $c->analyzeFullyIfNecessaryAndSave(__FUNCTION__);
                $good[$c->getTitleAttribute()] = $c->l();
            } catch (TooSlowToAnalyzeException $e) {
                $this->addInvalidCorrelation($e, $c);
                $good[$c->getTitleAttribute()] = $c->l(); // TODO: Maybe comment this?
            } catch (NotEnoughDataException $e) {
                $this->addInvalidCorrelation($e, $c);
            }
        }
        return $this->correlations = collect($good);
    }
	/**
	 * @return UserVariableRelationship[]|Collection
	 * @throws \App\Exceptions\StupidVariableNameException
	 */
    public function analyzeUserVariableRelationships(): Collection {
        $correlations = $this->getQMUserVariableRelationships();
        $good = [];
        foreach($correlations as $c){
            try {
                $c->analyzeFullyAndSave(__FUNCTION__);
                $good[$c->getTitleAttribute()] = $c->l();
            } catch (TooSlowToAnalyzeException $e) {
                $this->addInvalidCorrelation($e, $c);
                $good[$c->getTitleAttribute()] = $c->l(); // TODO: Maybe comment this?
            } catch (NotEnoughDataException $e) {
                $this->addInvalidCorrelation($e, $c);
            } catch (AlreadyAnalyzedException | AlreadyAnalyzingException | ModelValidationException | DuplicateFailedAnalysisException $e) {
               le($e);
            }
        }
        return $this->correlations = collect($good);
    }
    /**
     * @throws ModelValidationException
     */
    public function validateAnalysisBeforeSave(){
        parent::validateAnalysisBeforeSave();
    }
    public function getTitleAttribute(): string{
        return $this->generateStudyTitle();
    }
    public function getSourceDataUrl(): string {
        return UserVariableRelationship::generateDataLabIndexUrl([
            UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
            UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
        ]);
    }
    /**
     * @return array
     */
    public static function getRequiredAnalysisFields(): array{
        //$fields = array_merge($fields, QMGlobalVariableRelationship::getUserVariableRelationshipFieldsToAverage());
        $fields[] = static::FIELD_EFFECT_FOLLOW_UP_PERCENT_CHANGE_FROM_BASELINE;
        // We can't calculate CONFIDENCE_INTERVAL if there's only one effectValuesExpectedToBeHigherThanAverage
        // $fields[] = static::FIELD_CONFIDENCE_INTERVAL;
        return $fields;
    }
    /**
     * @return GlobalVariableRelationship
     */
    public function l(): GlobalVariableRelationship{
        return parent::l();
    }
    public function getGlobalVariableRelationship(): GlobalVariableRelationship{
        return $this->l();
    }
    public function getAverageVote(): ?float {
        return GlobalVariableRelationshipAverageVoteProperty::calculate($this);
    }
    /**
     * @param Throwable $e
     * @param QMUserVariableRelationship|UserVariableRelationship $c
     */
    public function addInvalidCorrelation(Throwable $e, $c): void{
        $this->addException($e);
        $this->invalidSourceData[$c->getTitleAttribute()] = $c;
    }
    /**
     * @return QMUserVariableRelationship[]
     */
    public function getInvalidSourceData(): array{
        return $this->invalidSourceData;
    }
    public function getStudyType(): string{
        return $this->type = StudyTypeProperty::TYPE_POPULATION;
    }
    public function getUrl(array $params = []): string{
        return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
    }
    /**
     * @return int
     */
    public function getOnsetDelay(): int{
        if($this->onsetDelay === null){
            $this->onsetDelay = GlobalVariableRelationshipOnsetDelayProperty::calculate($this);
        }
        return $this->onsetDelay;
    }
    /**
     * @return int
     */
    public function getDurationOfAction(): int{
        if($this->durationOfAction === null){
            $this->durationOfAction = GlobalVariableRelationshipDurationOfActionProperty::calculate($this);
        }
        return $this->durationOfAction;
    }
    /**
     * @return string
     */
    public function getPHPUnitTestUrl(): string {
        return self::generatePHPUnitTestUrlForAnalyze($this->getCauseVariable(), $this->getEffectVariable());
    }
    public function getQMUserVariableRelationshipWithMostData(): QMUserVariableRelationship{
        $c = $this->getQMUserVariableRelationships();
        return QMArr::getElementWithHighest($c, 'numberOfPairs');
    }
    /**
     * @return int|null
     */
    public function getUserId(): ?int{
        return UserIdProperty::USER_ID_SYSTEM;
    }
    /**
     * @return QMCommonVariable
     */
    public function getOrSetCauseQMVariable(): QMVariable{
        if($v = $this->causeVariable){return $v;}
        return $this->causeVariable = QMCommonVariable::find($this->getCauseVariableId());
    }
    /**
     * @return QMCommonVariable
     */
    public function getOrSetEffectQMVariable(): QMVariable{
        if($v = $this->effectVariable){return $v;}
        return $this->effectVariable = QMCommonVariable::find($this->getEffectVariableId());
    }
    public function findGlobalVariableRelationship(): ?GlobalVariableRelationship{
        return $this->l();
    }
	/**
	 * @return void
	 */
	public function calculateOutcomeBaselineStatistics(){
		$uc = $this->getQMUserVariableRelationships();
		foreach($uc as $correlation){
			try {
				$correlation->calculateOutcomeBaselineStatistics();
			} catch (NotEnoughDataException $e) {
				$correlation->logError(__METHOD__.": ".$e->getMessage());
				continue;
			} catch (TooSlowToAnalyzeException $e) {
				le($e);
			}
		}
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons():array{
		return $this->l()->getInterestingRelationshipButtons();
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function causeValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		return $this->causeValueCommonUnit($inCommonUnit, $precision);
	}
	/**
	 * @param float $inCommonUnit
	 * @param int $precision
	 * @return string|null
	 */
	protected function effectValueUserUnit(float $inCommonUnit, int $precision = QMVariableRelationship::SIG_FIGS): string{
		return $this->effectValueCommonUnit($inCommonUnit, $precision);
	}
	public function getTagLine(): string{
		return $this->l()->getTagLine();
	}
	public function getShowContentView(array $params = []): View{
		return $this->getGlobalVariableRelationship()->getShowContentView($params);
	}
	/**
	 * @return \App\Models\GlobalVariableRelationship|\Illuminate\Database\Eloquent\Builder
	 */
	public static function wherePostable(){
		return GlobalVariableRelationship::wherePostable();
	}
	public function getDataQuantitySentence():string{
		return $this->getCauseVariable()->getMeasurementQuantitySentence()."\n\n".
			$this->getEffectVariable()->getMeasurementQuantitySentence();
	}
	/**
	 * @param array $arr
	 */
	protected function updateBestCauseIfNecessary(array $arr): void{
		$aggQMScore = $this->getQmScore();
		$effect = $this->getEffectVariable();
		if($effect->best_cause_variable_id !== $this->getCauseVariableId()){
			$oldBest = $effect->getBestGlobalVariableRelationship();
			if(!$oldBest || $oldBest->aggregate_qm_score < ($aggQMScore - 0.0001)){
				$effect->best_cause_variable_id = $this->getCauseVariableId();
				$effect->fill($arr);
				try {$effect->save();} catch (ModelValidationException $e) {le($e);}
			}
		}
	}
	/**
	 * @param array $arr
	 */
	protected function updateBestEffectIfNecessary(array $arr): void{
		$aggQMScore = $this->getQmScore();
		$cause = $this->getCauseVariable();
		if($this->getEffectVariableId() !== $cause->best_effect_variable_id){
			$oldBest = $cause->getBestGlobalVariableRelationship();
			if(!$oldBest || $oldBest->aggregate_qm_score < ($aggQMScore - 0.0001)){
				$cause->best_effect_variable_id = $this->getEffectVariableId();
				$cause->fill($arr);
				try {$cause->save();} catch (ModelValidationException $e) {le($e);}
			}
		}
	}
	public function getParentCategoryName(): ?string{
		return static::CLASS_CATEGORY;
	}
	/**
	 * @return \App\Studies\QMStudy|null
	 */
	protected function findQMStudyInMemory(): ?QMStudy{
		return QMPopulationStudy::findInMemoryOrDB($this->getStudyId());
	}
	public function getSortingScore(): float{
		if($this->sortingScore === null){
			$this->sortingScore = $this->getQmScore();
		}
		return parent::getSortingScore();
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttribute($key, $value){
		if($value === null){
			$prev = $this->getAttribute($key);
			if($prev === null){return;}
			$prevStr = QMLog::print_r($prev);
			if($prevStr !== ''){
				$this->logError("Changing $key from $prevStr to null");
			}
		}
		parent::setAttribute($key, $value);
	}
}
