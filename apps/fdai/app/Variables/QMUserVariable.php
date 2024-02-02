<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Buttons\QMButton;
use App\Buttons\Sharing\EmailSharingButton;
use App\Buttons\Sharing\FacebookSharingButton;
use App\Buttons\Sharing\TwitterSharingButton;
use App\Buttons\States\StudyStateButton;
use App\Buttons\States\VariableStates\ChartsStateButton;
use App\Buttons\States\VariableStates\HistoryAllVariableStateButton;
use App\Buttons\States\VariableStates\MeasurementAddVariableStateButton;
use App\Buttons\States\VariableStates\VariableSettingsVariableNameStateButton;
use App\Buttons\Tracking\NotificationButton;
use App\Buttons\Tracking\RatingNotificationButton;
use App\Buttons\Tracking\SkipNotificationButton;
use App\Buttons\Tracking\SnoozeNotificationButton;
use App\Cards\StartTrackingQMCard;
use App\Cards\StudyCard;
use App\Charts\ChartGroup;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\API\MeasurementAPIController;
use App\Models\UserTag;
use App\Models\Vote;
use App\DataSources\Connectors\GoogleCalendarConnector;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\QMConnector;
use App\DevOps\XDebug;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\DuplicateDataException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidTagCategoriesException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooManyMeasurementsException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UserVariableNotFoundException;
use App\Jobs\UserVariableCorrelationJob;
use App\Logging\QMLog;
use App\Mail\RootCauseAnalysisEmail;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\CommonTag;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Study;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\WpPost;
use App\PhpUnitJobs\Analytics\UserVariableAnalysisJob;
use App\Products\ProductHelper;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseNameProperty;
use App\Properties\Base\BaseNumberOfDaysProperty;
use App\Properties\Base\BaseNumberOfMeasurementsProperty;
use App\Properties\Base\BaseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\Base\BaseUnitIdProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseChangesProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfProcessedDailyMeasurementsProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseNumberOfRawMeasurementsProperty;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Properties\UserVariable\UserVariableDataSourcesCountProperty;
use App\Properties\UserVariable\UserVariableEarliestFillingTimeProperty;
use App\Properties\UserVariable\UserVariableEarliestSourceMeasurementStartAtProperty;
use App\Properties\UserVariable\UserVariableLastProcessedDailyValueProperty;
use App\Properties\UserVariable\UserVariableLatestFillingTimeProperty;
use App\Properties\UserVariable\UserVariableLatestSourceMeasurementStartAtProperty;
use App\Properties\UserVariable\UserVariableLatestTaggedMeasurementStartAtProperty;
use App\Properties\UserVariable\UserVariableNumberOfMeasurementsProperty;
use App\Properties\UserVariable\UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty;
use App\Properties\UserVariable\UserVariableNumberOfUserVariableRelationshipsAsCauseProperty;
use App\Properties\UserVariable\UserVariableNumberOfUserVariableRelationshipsAsEffectProperty;
use App\Properties\UserVariable\UserVariableOptimalValueMessageProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Reports\RootCauseAnalysis;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\DailyMeasurement;
use App\Slim\Model\Measurement\FillerMeasurement;
use App\Slim\Model\Measurement\ProcessedQMMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\RawQMMeasurement;
use App\Slim\Model\Notifications\IndividualPushNotificationData;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Traits\HasModel\HasUserVariable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\Units\ServingUnit;
use App\Utils\APIHelper;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\QMProfile;
use App\Utils\Stats;
use App\Utils\UrlHelper;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\InvestmentStrategiesVariableCategory;
use App\VariableCategories\VitalSignsVariableCategory;
use Carbon\CarbonInterface;
use Dialogflow\Action\Surface;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LogicException;
use RuntimeException;
use stdClass;
use Tests\TestGenerators\StagingJobTestFile;
use Throwable;
/**
 * @mixin UserVariable
 */
class QMUserVariable extends QMVariable {
	use HasUserVariable;
	//public const FIELD_ID = 'id'; // TODO: use actual id
	//protected $isPublic;
	private $combinedNewMeasurementItems;
	private $validDailyMeasurementsWithTagsAndFilling;
	private $validDailyMeasurementsWithTagsInUserUnit;
	private $downVotesAsCause;
	private $downVotesAsEffect;
	private $filteredMeasurementsWithTags;
	private $groupedValues;
	private $invalidMeasurements = [];
	private $measurementRequest;
	private $measurementsIndexedByStartTimeRoundedToMinimumSecondsBetween;
	private $newMeasurements = [];
	private $relatedRecords;
	private $requestParams;
	private $rootCauseAnalysis;
	private $savedMeasurements = [];
	private $trackingReminder;
	private $trackingReminders;
	private $userTaggedVariableRows;
	private $userVariableIdsToCorrelateWith;
	private static $userVariableButtons;
	protected $bestUserVariableRelationship;
	protected $commonVariableFillingValue;
	protected $commonVariableValence;
	protected $dailyValuesWithTagsAndFilling;
	protected $joinedUserTagVariableIds;
	protected $joinWith;
	protected $lastOriginalUnitId;
	protected $lastOriginalValue;
	protected $lastUnitId;
	protected $latitude;
	protected $location;
	protected $longitude;
	protected $measurementsAtLastAnalysis;
	protected $measurementsInUserUnit;
	protected $mostCommonOriginalUnitId;
	protected $mostCommonValueInUserUnit;
	protected $newUserUnitId;
	protected $numberOfUserVariableRelationships;
	protected $processedMeasurements;
	protected $processedMeasurementsInUserUnit;
	protected $trackingReminderNotifications;
	protected $bestCorrelationWhereCause;
	protected $bestCorrelationWhereEffect;
	protected $userVariableRelationshipsAsCause;
	protected $userVariableRelationshipsAsEffect;
	protected $userMaximumAllowedDailyValue;
	protected $userMinimumAllowedDailyValue;
	protected $userMinimumAllowedNonZeroValue;
	protected $userNumberOfUniqueValues;
	protected $userVariableFillingValue;
	protected $userVariableMostCommonConnectorId;
	protected $userVariableValence;
	protected $userVariableWikipediaTitle;
	protected $wpPostId;
	public $actionArray;
	public $alias;
	public $bestUserVariableRelationshipId;
	public $bestUserStudyCard;
	public $bestUserStudyLink;
	public $chartsLinkDynamic;
	public $chartsLinkEmail;
	public $chartsLinkFacebook;
	public $chartsLinkGoogle;
	public $chartsLinkStatic;
	public $chartsLinkTwitter;
	public $chartsMailToLink;
	public $childUserTagVariables;
	public $commonAdditionalMetaData;
	public $commonNumberOfUniqueValues;
	public $commonVariableCategoryId;
	public $earliestFillingAt;
	public $earliestFillingTime;
	public $earliestMeasurementTime;
	public $earliestSourceMeasurementStartAt;
	public $earliestSourceTime;
	public $earliestUserMeasurementAt;
	public $earliestUserMeasurementTime;
	public $experimentEndAt;
	public $experimentEndTime;
	public $experimentEndTimeSeconds;
	public $experimentEndTimeString;
	public $experimentStartAt;
	public $experimentStartTime;
	public $experimentStartTimeSeconds;
	public $experimentStartTimeString;
	public ?float $fillingValueInUserUnit;
	public $id;
	public $ingredientOfUserTagVariables;
	public $ingredientUserTagVariables;
	public $ionIcon;
	public $joinedUserTagVariables;
	public $lastCorrelatedAt;
	public $lastProcessedDailyValue;
	public $lastProcessedDailyValueAt;
	public $lastProcessedDailyValueInCommonUnit;
	public $lastProcessedDailyValueInUserUnit;
	public $lastValue;
	public $lastValueInCommonUnit;
	public $lastValueInUserUnit;
	public $lastValuesInCommonUnit;
	public $lastValuesInUserUnit;
	public $latestFillingAt;
	public $latestFillingTime;
	public $latestSourceMeasurementStartAt;
	public $latestSourceTime;
	public $latestUserMeasurementAt;
	public $latestUserMeasurementTime;
	public $maximumAllowedValueInCommonUnit;
	public $maximumAllowedValueInUserUnit;
	public $maximumRecordedValueInCommonUnit;
	public $maximumRecordedValueInUserUnit;
	public $meanInCommonUnit;
	public $meanInUserUnit;
	public $medianInCommonUnit;
	public $medianInUserUnit;
	public $minimumAllowedValueInCommonUnit;
	public $minimumAllowedValueInUserUnit;
	public $minimumRecordedValueInCommonUnit;
	public $minimumRecordedValueInUserUnit;
	public $mostCommonConnectorId;
	public $mostCommonValueInCommonUnit;
	public $numberOfChanges;
	public $numberOfCommonChildren;
	public $numberOfCommonFoods;
	public $numberOfCommonIngredients;
	public $numberOfCommonJoinedVariables;
	public $numberOfCommonParents;
	public $numberOfCorrelations;
	public $numberOfMeasurements;
	public $numberOfMeasurementsWithTagsAtLastCorrelation;
	public $numberOfOutcomeCaseStudies;
	public $numberOfPredictorCaseStudies;
	public $numberOfProcessedDailyMeasurements;
	public $numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis;
	public $numberOfSoftDeletedMeasurements;
	public $numberOfTrackingReminderNotifications;
	public $numberOfTrackingReminders;
	public $numberOfUserChildren;
	public $numberOfUserFoods;
	public $numberOfUserIngredients;
	public $numberOfUserJoinedVariables;
	public $numberOfUserParents;
	public $numberOfUserTags;
	public $numberUserTaggedBy;
	public $outcomeOfInterest;
	public $parentUserTagVariables;
	public $predictorOfInterest;
	public $secondMostCommonValueInCommonUnit;
	public $secondMostCommonValueInUserUnit;
	public $secondToLastValue;
	public $secondToLastValueInCommonUnit;
	public $secondToLastValueInUserUnit;
	public $shareUserMeasurements;
	public $thirdMostCommonValueInCommonUnit;
	public $thirdMostCommonValueInUserUnit;
	public $thirdToLastValue;
	public $thirdToLastValueInCommonUnit;
	public $thirdToLastValueInUserUnit;
	public $userAdditionalMetaData;
	public $userBestCauseVariableId;
	public $userBestEffectVariableId;
	public $userId;
	public $userMaximumAllowedValueInCommonUnit; // Can't be private or won't be populated by row for some reason
	public $userMinimumAllowedValueInCommonUnit; // Can't be private or won't be populated by row for some reason
	public string $userOptimalValueMessage;
	public $userTaggedVariables;
	public $userTagVariables;
	public $userUnitId;
	public $userVariableId;
	public $userVariableVariableCategoryId;
	public $variableSettingsUrl;
	public $wikipediaExtract;
	public const ALGORITHM_MODIFIED_AT                                      = "2020-09-10";
	public const ALL_USERS                                                  = 'all';
	public const CACHE_LIFETIME                                             = 86400;
	public const CLASS_PARENT_CATEGORY                                      = Variable::CLASS_CATEGORY;
	public const COLLECTION_NAME                                            = "UserVariable";
	public const CREATE_TRACKING_REMINDER_IF_NEW                            = 'createTrackingReminderIfNew';
	public const DEFAULT_SORT_FIELD                                         = '-' .
	Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT;
	public const FIELD_ALIAS                                                = 'alias';
	public const FIELD_ANALYSIS_ENDED_AT                                    = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT                                = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT                        = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT                                  = 'analysis_started_at';
	public const FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS                 = 'average_seconds_between_measurements';
	public const FIELD_BEST_CAUSE_VARIABLE_ID                               = 'best_cause_variable_id';
	public const FIELD_BEST_EFFECT_VARIABLE_ID                              = 'best_effect_variable_id';
	public const FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID                             = 'best_user_variable_relationship_id';
	public const FIELD_CAUSE_ONLY                                           = 'cause_only';
	public const FIELD_CLIENT_ID                                            = 'client_id';
	public const FIELD_COMBINATION_OPERATION                                = 'combination_operation';
	public const FIELD_CREATED_AT                                           = 'created_at';
	public const FIELD_DATA_SOURCES_COUNT                                   = 'data_sources_count';
	public const FIELD_DEFAULT_UNIT_ID                                      = 'default_unit_id';
	public const FIELD_DELETED_AT                                           = 'deleted_at';
	public const FIELD_DESCRIPTION                                          = 'description';
	public const FIELD_DURATION_OF_ACTION                                   = 'duration_of_action';
	public const FIELD_EARLIEST_FILLING_TIME                                = 'earliest_filling_time';
	public const FIELD_EXPERIMENT_END_TIME                                  = 'experiment_end_time';
	public const FIELD_EXPERIMENT_START_TIME                                = 'experiment_start_time';
	public const FIELD_FILLING_TYPE                                         = 'filling_type';
	public const FIELD_FILLING_VALUE                                        = 'filling_value';
	public const FIELD_ID                                                   = 'id';
	public const FIELD_INFORMATIONAL_URL                                    = 'informational_url';
	public const FIELD_INTERNAL_ERROR_MESSAGE                               = 'internal_error_message';
	public const FIELD_JOIN_WITH                                            = 'join_with';
	public const FIELD_KURTOSIS                                             = 'kurtosis';
	public const FIELD_LAST_CORRELATED_AT                                   = 'last_correlated_at';
	public const FIELD_LAST_ORIGINAL_UNIT_ID                                = 'last_original_unit_id';
	public const FIELD_LAST_ORIGINAL_VALUE                                  = 'last_original_value';
	public const FIELD_LAST_PROCESSED_DAILY_VALUE                           = 'last_processed_daily_value';
	public const FIELD_LAST_UNIT_ID                                         = 'last_unit_id';
	public const FIELD_LAST_VALUE                                           = 'last_value';
	public const FIELD_LATEST_FILLING_TIME                                  = 'latest_filling_time';
	public const FIELD_LATITUDE                                             = 'latitude';
	public const FIELD_LOCATION                                             = 'location';
	public const FIELD_LONGITUDE                                            = 'longitude';
	public const FIELD_MAXIMUM_ALLOWED_VALUE                                = 'maximum_allowed_value';
	public const FIELD_MAXIMUM_RECORDED_VALUE                               = 'maximum_recorded_value';
	public const FIELD_MEAN                                                 = 'mean';
	public const FIELD_MEASUREMENTS_AT_LAST_ANALYSIS                        = 'measurements_at_last_analysis';
	public const FIELD_MEDIAN                                               = 'median';
	public const FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS                  = 'median_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS         = 'minimum_allowed_seconds_between_measurements';
	public const FIELD_MINIMUM_ALLOWED_VALUE                                = 'minimum_allowed_value';
	public const FIELD_MINIMUM_RECORDED_VALUE                               = 'minimum_recorded_value';
	public const FIELD_MOST_COMMON_CONNECTOR_ID                             = 'most_common_connector_id';
	public const FIELD_MOST_COMMON_ORIGINAL_UNIT_ID                         = 'most_common_original_unit_id';
	public const FIELD_MOST_COMMON_SOURCE_NAME                              = 'most_common_source_name';
	public const FIELD_MOST_COMMON_VALUE                                    = 'most_common_value';
	public const FIELD_NEWEST_DATA_AT                                       = 'newest_data_at';
	public const FIELD_NUMBER_OF_CHANGES                                    = 'number_of_changes';
	public const FIELD_NUMBER_OF_CORRELATIONS                               = 'number_of_correlations';
	public const FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION = 'number_of_measurements_with_tags_at_last_correlation';
	public const FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS               = 'number_of_processed_daily_measurements';
	public const FIELD_NUMBER_OF_MEASUREMENTS                               = 'number_of_measurements';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN  = 'number_of_raw_measurements_with_tags_joins_children';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS                         = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES                        = 'number_of_unique_daily_values';
	public const FIELD_NUMBER_OF_UNIQUE_VALUES                              = 'number_of_unique_values';
	public const FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE                 = 'number_of_user_variable_relationships_as_cause';
	public const FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT                = 'number_of_user_variable_relationships_as_effect';
	public const FIELD_ONSET_DELAY                                          = 'onset_delay';
	public const FIELD_OPTIMAL_VALUE_MESSAGE                                = 'optimal_value_message';
	public const FIELD_OUTCOME                                              = 'outcome';
	public const FIELD_OUTCOME_OF_INTEREST                                  = 'outcome_of_interest';
	public const FIELD_PARENT_ID                                            = 'parent_id';
	public const FIELD_PREDICTOR_OF_INTEREST                                = 'predictor_of_interest';
	public const FIELD_IS_PUBLIC                                            = 'is_public';
	public const FIELD_REASON_FOR_ANALYSIS                                  = 'reason_for_analysis';
	public const FIELD_SECOND_TO_LAST_VALUE                                 = 'second_to_last_value';
	public const FIELD_SKEWNESS                                             = 'skewness';
	public const FIELD_STANDARD_DEVIATION                                   = 'standard_deviation';
	public const FIELD_STATUS                                               = 'status';
	public const FIELD_THIRD_TO_LAST_VALUE                                  = 'third_to_last_value';
	public const FIELD_UPDATED_AT                                           = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE                                   = 'user_error_message';
	public const FIELD_USER_ID                                              = 'user_id';
	public const FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE                     = 'user_maximum_allowed_daily_value';
	public const FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE                     = 'user_minimum_allowed_daily_value';
	public const FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE                  = 'user_minimum_allowed_non_zero_value';
	public const FIELD_VALENCE                                              = 'valence';
	public const FIELD_VARIABLE_CATEGORY_ID                                 = 'variable_category_id';
	public const FIELD_VARIABLE_ID                                          = 'variable_id';
	public const FIELD_VARIANCE                                             = 'variance';
	public const FIELD_WIKIPEDIA_TITLE                                      = 'wikipedia_title';
	public const LARAVEL_CLASS                                              = UserVariable::class;
	public const TABLE                                                      = 'user_variables';
	public const USE_SOURCE_TIMES_FOR_FILLING_TIMES                         = false;
	public const MYSQL_COLUMN_TYPES                                         = [
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => QMDB::TYPE_INT,
	];
	public const DB_FIELD_NAME_TO_PROPERTY_NAME_MAP                         = [
		'id' => 'userVariableId',
		'last_processed_daily_value' => 'lastProcessedDailyValueInCommonUnit',
		'last_value' => 'lastValueInCommonUnit',
		'maximum_recorded_value' => 'maximumRecordedValueInCommonUnit',
		'mean' => 'meanInCommonUnit',
		'measurements_at_last_analysis' => 'numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis',
		'median' => 'medianInCommonUnit',
		'minimum_recorded_value' => 'minimumRecordedValueInCommonUnit',
		'most_common_connector_id' => 'userVariableMostCommonConnectorId',
		'number_of_correlations' => 'numberOfUserVariableRelationships',
		'number_of_unique_values' => 'userNumberOfUniqueValues',
		'number_of_user_variable_relationships_as_cause' => 'numberOfCorrelationsAsCause',
		'number_of_user_variable_relationships_as_effect' => 'numberOfCorrelationsAsEffect',
		'second_to_last_value' => 'secondToLastValueInCommonUnit',
		'third_to_last_value' => 'thirdToLastValueInCommonUnit',
		'valence' => 'userVariableValence',
		'variable_category_id' => 'userVariableVariableCategoryId',
		'wikipedia_title' => 'userVariableWikipediaTitle',
		self::FIELD_BEST_CAUSE_VARIABLE_ID => 'userBestCauseVariableId',
		self::FIELD_BEST_EFFECT_VARIABLE_ID => 'userBestEffectVariableId',
		self::FIELD_DEFAULT_UNIT_ID => 'userUnitId',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'userOptimalValueMessage',
	];
	public static $sqlCalculatedFields = [
		self::FIELD_BEST_CAUSE_VARIABLE_ID => [
			'table' => UserVariableRelationship::TABLE,
			'foreign_key' => UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID,
			'duration' => 0,
			'sql' => 'select cause_variable_id as calculatedValue
                                from user_variable_relationships ac
                                where effect_user_variable_id = $this->id
                                    and ac.deleted_at is null
                                order by ac.qm_score desc
                                limit 1',
		],
		self::FIELD_BEST_EFFECT_VARIABLE_ID => [
			'table' => UserVariableRelationship::TABLE,
			'foreign_key' => UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID,
			'duration' => 0,
			'sql' => 'select effect_variable_id as calculatedValue
                                from user_variable_relationships ac
                                where cause_user_variable_id = $this->id
                                    and ac.deleted_at is null
                                order by ac.qm_score desc
                                limit 1',
		],
		self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'FROM_UNIXTIME(min(' . Measurement::FIELD_START_TIME . '))',
			'duration' => 1,
		],
		self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'FROM_UNIXTIME(max(' . Measurement::FIELD_START_TIME . '))',
			'duration' => 1,
		],
		self::FIELD_MAXIMUM_RECORDED_VALUE => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'max(' . Measurement::FIELD_VALUE . ')',
			'duration' => 1,
		],
		self::FIELD_MEAN => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'avg(' . Measurement::FIELD_VALUE . ')',
			'duration' => 1,
		],
		self::FIELD_MINIMUM_RECORDED_VALUE => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'min(' . Measurement::FIELD_VALUE . ')',
			'duration' => 1,
		],
		self::FIELD_NUMBER_OF_SOFT_DELETED_MEASUREMENTS => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'duration' => 300,
			'sql' => 'update user_variables v
                                inner join (
                                    select measurements.user_variable_id, count(measurements.id) as number_of_soft_deleted_measurements
                                    from measurements
                                    where measurements.deleted_at is not null
                                    group by measurements.user_variable_id
                                    ) m on v.id = m.user_variable_id
                                set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements;
            ',
		],
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_CAUSE => [
			'table' => UserVariableRelationship::TABLE,
			'foreign_key' => UserVariableRelationship::FIELD_CAUSE_USER_VARIABLE_ID,
			'sql' => 'count(' . UserVariableRelationship::FIELD_ID . ')',
			'duration' => 1,
		],
		self::FIELD_NUMBER_OF_USER_VARIABLE_RELATIONSHIPS_AS_EFFECT => [
			'table' => UserVariableRelationship::TABLE,
			'foreign_key' => UserVariableRelationship::FIELD_EFFECT_USER_VARIABLE_ID,
			'sql' => 'count(' . UserVariableRelationship::FIELD_ID . ')',
			'duration' => 1,
		],
		self::FIELD_NUMBER_OF_MEASUREMENTS => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'count(' . Measurement::FIELD_ID . ')',
			'duration' => 1,
		],
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => [
			'table' => TrackingReminder::TABLE,
			'foreign_key' => TrackingReminder::FIELD_USER_VARIABLE_ID,
			'sql' => 'count(' . TrackingReminder::FIELD_ID . ')',
			'duration' => 18,
		],
		self::FIELD_NEWEST_DATA_AT => [
			'table' => Measurement::TABLE,
			'foreign_key' => Measurement::FIELD_USER_VARIABLE_ID,
			'sql' => 'max(' . Measurement::UPDATED_AT . ')',
			'duration' => 1,
		],
		self::FIELD_NUMBER_OF_CORRELATIONS => [
			'table' => null,
			'foreign_key' => null,
			'duration' => 28,
			'sql' => 'update user_variables v
            set v.number_of_correlations = v.number_of_user_variable_relationships_as_cause + v.number_of_user_variable_relationships_as_effect',
		],
		self::FIELD_ANALYSIS_ENDED_AT => 'php',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'php',
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'php',
		self::FIELD_ANALYSIS_STARTED_AT => 'php',
		self::FIELD_AVERAGE_SECONDS_BETWEEN_MEASUREMENTS => 'php',
		self::FIELD_CAUSE_ONLY => 'const',
		self::FIELD_CLIENT_ID => 'const',
		self::FIELD_COMBINATION_OPERATION => 'const',
		self::FIELD_DATA_SOURCES_COUNT => 'php',
		self::FIELD_DEFAULT_UNIT_ID => 'const',
		self::FIELD_DESCRIPTION => 'php',
		self::FIELD_DURATION_OF_ACTION => 'const',
		self::FIELD_EARLIEST_FILLING_TIME => 'php',
		self::FIELD_FILLING_VALUE => 'const',
		self::FIELD_ID => 'const',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'php',
		self::FIELD_JOIN_WITH => 'php',
		self::FIELD_KURTOSIS => 'php',
		self::FIELD_LAST_ORIGINAL_UNIT_ID => 'php',
		self::FIELD_LAST_ORIGINAL_VALUE => 'php',
		self::FIELD_LAST_PROCESSED_DAILY_VALUE => 'php',
		self::FIELD_LAST_UNIT_ID => 'php',
		self::FIELD_LAST_VALUE => 'php',
		self::FIELD_LATEST_FILLING_TIME => 'php',
		self::FIELD_LATITUDE => 'php',
		self::FIELD_LONGITUDE => 'php',
		self::FIELD_MAXIMUM_ALLOWED_VALUE => 'const',
		self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 'php',
		self::FIELD_MEDIAN => 'php',
		self::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS => 'php',
		self::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 'const',
		self::FIELD_MINIMUM_ALLOWED_VALUE => 'const',
		self::FIELD_MOST_COMMON_CONNECTOR_ID => 'php',
		self::FIELD_MOST_COMMON_ORIGINAL_UNIT_ID => 'php',
		self::FIELD_MOST_COMMON_SOURCE_NAME => 'php',
		self::FIELD_MOST_COMMON_VALUE => 'php',
		self::FIELD_NAME => 'const',
		self::FIELD_NUMBER_OF_CHANGES => 'php',
		self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'php',
		self::FIELD_NUMBER_OF_PROCESSED_DAILY_MEASUREMENTS => 'php',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => 'php',
		self::FIELD_NUMBER_OF_UNIQUE_DAILY_VALUES => 'php',
		self::FIELD_NUMBER_OF_UNIQUE_VALUES => 'php',
		self::FIELD_ONSET_DELAY => 'const',
		self::FIELD_OPTIMAL_VALUE_MESSAGE => 'php',
		self::FIELD_OUTCOME => 'const',
		self::FIELD_OUTCOME_OF_INTEREST => 'php',
		self::FIELD_PARENT_ID => 'const',
		self::FIELD_PREDICTOR_OF_INTEREST => 'php',
		self::FIELD_IS_PUBLIC => 'const',
		self::FIELD_REASON_FOR_ANALYSIS => 'php',
		self::FIELD_SECOND_TO_LAST_VALUE => 'php',
		self::FIELD_SKEWNESS => 'php',
		self::FIELD_STANDARD_DEVIATION => 'php',
		self::FIELD_STATUS => 'php',
		self::FIELD_THIRD_TO_LAST_VALUE => 'php',
		self::FIELD_USER_ERROR_MESSAGE => 'php',
		self::FIELD_USER_ID => 'const',
		self::FIELD_USER_MAXIMUM_ALLOWED_DAILY_VALUE => 'const',
		self::FIELD_USER_MINIMUM_ALLOWED_DAILY_VALUE => 'const',
		self::FIELD_USER_MINIMUM_ALLOWED_NON_ZERO_VALUE => 'const',
		self::FIELD_VALENCE => 'php',
		self::FIELD_VARIABLE_CATEGORY_ID => 'const',
		self::FIELD_VARIABLE_ID => 'const',
		self::FIELD_VARIANCE => 'php',
		self::FIELD_WIKIPEDIA_TITLE => 'php',
	];
	/**
	 * @var array
	 */
	private $aggregatedValues = [];
	/**
	 * @var array
	 */
	private $byTagName;
	/**
	 * @var QMMeasurement[]
	 */
	private $fillerMeasurements;
	/**
	 * @param object|null $row
	 * @param int|QMUser|null $userId
	 * @param int|null $variableId
	 * @param array|GetUserVariableRequest $params
	 * @param null|QMUserVariable $cachedObject
	 */
	public function __construct($row = null, $userId = null, int $variableId = null, $params = [],
		QMUserVariable $cachedObject = null){
		$this->requestParams = $params;
		if($userId && !is_int($userId)){
			$userId = $userId->id;
		}
		if(!$userId && isset($row->userId)){
			$userId = $row->userId;
		}
		$this->userId = $userId;
		if(!$variableId && isset($row->variableId)){
			$variableId = $row->variableId;
		}
		if($cachedObject){
			$this->populateFieldsByArrayOrObject($cachedObject);
			if(isset($cachedObject->unit)){
				$this->setUserUnit(new QMUnit($cachedObject->unit));
			}
			return;
		}
		if(!$userId || !$variableId){
			return;
		}
		$this->setId($variableId);
		$this->variableId = $variableId;  // Needed for saving variable in database maybe?
		if($row){
			$this->setDbRow($row);
			foreach($this as $key => $value){
				if($key === "name" && isset($this->name)){
					continue;
				}
				if(property_exists($row, $key) && isset($row->$key)){
					$this->$key = $row->$key;
				}
			}
			if(isset($row->unit)){
				$this->setUserUnit(new QMUnit($row->unit));
			}
			if($id = $this->userVariableId){
				$this->id = $id;
			}
		}
		try {
			$this->populateDefaultFields();
		} catch (\Throwable $e) {
			$this->populateDefaultFields();
		}
	}
	/**
	 * @param bool $throwException
	 * @return QMUserVariable|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function fromRequest(bool $throwException = false){
		$nameOrId = VariableIdProperty::nameOrIdFromRequest();
		if(!$nameOrId){
			return null;
		}
		$userId = QMAuth::id(true);
		$v = QMUserVariable::findByNameIdSynonymOrSpending($userId, $nameOrId);
		if(!$v && $throwException){
			throw new BadRequestException("Variable $nameOrId not found for user $userId! Please include valid variableId in the request. ");
		}
		return $v;
	}
	public function populateDefaultFields(){
		$this->setUserUnit($this->userUnitId ?? $this->unitId ?? $this->commonUnitId);
		parent::populateDefaultFields();
		$this->getUserVariableId();
		$this->setValence();
		$this->getDurationOfAction();
		$this->getOnsetDelay();
		$this->setExperimentStartTimeSeconds();
		$this->setExperimentEndTimeSeconds();
		$this->getMostCommonConnectorId();
		$this->setVariableCategory($this->getVariableCategoryId());
		$this->setChartLinks();
		$this->getVariableSettingsUrl();
		$this->setNotificationButtonsIfNecessary();
		if($public = $this->getIsPublic()){
			$this->setIsPublic($public);
		}
		$this->setTagsIfRequested();
		$this->setChartsIfRequested();
		TimeHelper::convertAllDateTimeValuesToRFC3339($this);
		$this->getCommonAdditionalMetaData();
		$this->getUserAdditionalMetaData();
		$uv = $this->getUserVariable();
		if(!$this->subtitle){
			$this->subtitle = $uv->getSubtitleAttribute();
		}
		$this->fillingValueInUserUnit = $uv->getFillingValueInUserUnit();
		$this->setMinimumAllowedValue($uv->getMinimumAllowedValueAttribute());
		$this->setMaximumAllowedValue($uv->getMaximumAllowedValueAttribute());
		$this->convertValuesToUserUnit();
		$this->validateRequiredProperties();
		if($t = $this->earliestSourceTime){
			$this->earliestSourceMeasurementStartAt = db_date($t);
		}
		if($t = $this->latestSourceTime){
			$this->latestSourceMeasurementStartAt = db_date($t);
		}
		if(!$this->latestMeasurementTime){
			$this->setLatestMeasurementTime($this->latestNonTaggedMeasurementStartAt);
		}
		if(!$this->earliestMeasurementTime){
			$this->setEarliestMeasurementTime($this->earliestNonTaggedMeasurementStartAt);
		}
		if(!$this->variableName){
			le("variableName");
		}
		if($this->onsetDelay === null){
			le("onsetDelay");
		}
		$this->url = $this->getUrl();
		$this->subtitle = $this->description = $uv->getSubtitleAttribute();
	}
	/**
	 * @param QMUserVariable|UserVariable $cause
	 * @param QMUserVariable|UserVariable $effect
	 * @return bool
	 */
	private static function shouldCorrelate(QMUserVariable|UserVariable $cause, QMUserVariable|UserVariable $effect): bool{
		if($cause->getVariableCategoryId() === InvestmentStrategiesVariableCategory::ID &&
			$effect->getVariableCategoryId() !== InvestmentStrategiesVariableCategory::ID){
			return false;
		}
		return true;
	}
	public function addToMemory(): void{
		$this->validateUnit();
		/** @var static $global */
		$global = $this->getMeFromMemory();
		if($global && $global->measurements && $global !== $this){
			le("Why did we get this variable again when one with measurements was
            already in globals? We don't want to overwrite variables with measurements already. ");
		}
		parent::addToMemory();
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return object|QMUserVariable
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){
		if($arrayOrObject instanceof static){
			return $arrayOrObject;
		}
		$model = new static();
		$maybeFromMemory = $model->populateFieldsByArrayOrObjectAndMemory($arrayOrObject);
		try {
			$maybeFromMemory->populateDefaultFields();
		} catch (\Throwable $e) {
			$maybeFromMemory->populateDefaultFields();
		}
		return $maybeFromMemory;
	}
	/**
	 * @param array|object $arrayOrObject
	 */
	public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void{
		if(!$this->variableCategoryId && is_object($arrayOrObject)){
			try {
				/** @var self $arrayOrObject */
				$this->variableCategoryId = $arrayOrObject->userVariableVariableCategoryId ??
					$arrayOrObject->commonVariableCategoryId ?? $arrayOrObject->variableCategoryId ?? $arrayOrObject->variable_category_id;
			} catch (\Throwable $e){
			    QMLog::info(__METHOD__.": ".$e->getMessage());
			    le($e);
			}
		}
		parent::populateFieldsByArrayOrObject($arrayOrObject);
	}
	/**
	 * @param int|null $variableId
	 * @param string $reason
	 * @return int
	 */
	public static function setAllUserVariablesToReCorrelate(int $variableId, string $reason): int{
		return QMUserVariable::scheduleAnalysisWhere(self::FIELD_VARIABLE_ID, $variableId, $reason, [
			self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 0,
			self::FIELD_LAST_CORRELATED_AT => null,
		]);
	}
	/**
	 * @param int $userId
	 * @param int $commonVariableId
	 * @return QMUserVariable|null
	 */
	private static function unDeleteIfNecessary(int $userId, int $commonVariableId): ?QMUserVariable{
		$v = null;
		$qb = self::writable()->where(self::FIELD_USER_ID, $userId)->where(self::FIELD_VARIABLE_ID, $commonVariableId);
		$row = $qb->getArray();
		if($row){
			QMLog::info("user $userId variable $commonVariableId row already exists");
			$qb->update([self::FIELD_DELETED_AT => null]);
			try {
				$v = self::getByNameOrId($userId, $commonVariableId);
			} catch (UserVariableNotFoundException $e) {
				le($e);
				throw new \LogicException();
			}
		}
		return $v;
	}
	public function getAllCommonAndUserTagVariableTypes(){
		$this->getAllUserTagVariableTypes();
		$this->getAllCommonTagVariableTypes();
	}
	public function setAllCommonAndUserTagVariableTypes(){
		$this->setAllUserTagVariableTypes();
		$this->setAllCommonTagVariableTypes();
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @return QMUserVariable
	 */
	public static function findInMemoryByVariableId(int $userId, int $variableId): ?QMUserVariable{
		if(!$variableId){
			le("No variableId provided to getByVariableIdFromGlobals");
		}
		$forUser = self::fromMemoryWhereUserId($userId);
		if(!$forUser){
			return null;
		}
		$matches = QMArr::where('variableId', $variableId, $forUser);
		return $matches[0] ?? null;
	}
	/**
	 * @param int $userId
	 * @param string $name
	 * @return QMUserVariable
	 */
	private static function findInMemoryByName(int $userId, string $name): ?QMUserVariable{
		$variableName = strtolower($name);
		$globals = self::fromMemoryWhereUserId($userId);
		foreach($globals as $variable){
			if(strtolower($variable->name) === $variableName){
				return $variable;
			}
		}
		return null;
	}
	/**
	 * @param int $userId
	 * @param string $nameOrSynonym
	 * @param array $newVariableParams
	 * @return QMUserVariable
	 */
	private static function findByNameOrSynonymInMemory(int $userId, string $nameOrSynonym,
		array $newVariableParams = []): ?QMUserVariable{
		$fromMemory = self::findInMemoryByName($userId, $nameOrSynonym);
		if(!$fromMemory){
			$forUser = self::fromMemoryWhereUserId($userId);
			foreach($forUser as $v){
				if($v->isNameOrSynonym($nameOrSynonym)){
					$fromMemory = $v;
				}
			}
		}
		if($fromMemory){
			$unitInNameIfDifferent = $fromMemory->updatePropertiesIfNecessary($newVariableParams);
			return $unitInNameIfDifferent;
		}
		return $fromMemory;
	}
	/**
	 * @param array|object $body
	 * @return QMUserVariable
	 */
	public function createNewCommonAndUserVariableWithUnitInName($body): QMUserVariable{
		$common = $this->getVariable()->toNonNullArray();
		QMUserVariable::flushAllFromMemory();
		QMCommonVariable::flushAllFromMemory();
		$newUnit = BaseUnitIdProperty::pluckParentDBModel($body);
		$nameWithUnit = VariableNameProperty::withUnit($this->name, $newUnit);
		if(QMCommonVariable::findByNameOrId($nameWithUnit)){
			return self::findOrCreateByNameOrId($this->userId, $nameWithUnit);
		}
		$common[Variable::FIELD_CLIENT_ID] = BaseClientIdProperty::fromDataOrRequest($body);
		$common[Variable::FIELD_COMBINATION_OPERATION] = null;
		$common[Variable::FIELD_CREATOR_USER_ID] = $this->userId;
		$common[Variable::FIELD_DEFAULT_UNIT_ID] = $newUnit->id;
		$common[Variable::FIELD_NAME] = $nameWithUnit;
		$common[Variable::FIELD_NUMBER_OF_USER_VARIABLES] = 1;
		$common[Variable::FIELD_IS_PUBLIC] = 0;
		unset($common[Variable::FIELD_ID]);
		$v = new Variable();
		$v->forceFill($common);
		$v->synonyms = [$v->name, $v->getTitleAttribute()];
		try {
			$v->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return self::createOrUnDeleteById($this->userId, $v->id);
	}
	/**
	 * @param string $itemName
	 * @param int $userId
	 * @param string|null $variableCategoryName
	 * @return QMUserVariable
	 */
	public static function findOrCreateWithReminderFromAmazon(string $itemName, int $userId,
		string $variableCategoryName = null): QMUserVariable{
		try {
			$userVariable = self::getByNameOrId($userId, $itemName);
		} catch (UserVariableNotFoundException $e) {
			$userVariable = false;
		}
		if(!$userVariable){
			$amazonProduct = ProductHelper::getByKeyword($itemName, $variableCategoryName);
			if($amazonProduct){
				$newVariableParams = $amazonProduct->getNewVariableParametersForExactMatch();
				$newVariableParams[self::CREATE_TRACKING_REMINDER_IF_NEW] = true;
				$userVariable = self::findOrCreateByNameOrId($userId, $itemName, [], $newVariableParams);
				if(!$userVariable){
					QMLog::error("Could not create user variable for $itemName");
				}
			} else{
				$userVariable = self::findOrCreateByNameOrId($userId, $itemName, [],
					['variableCategoryName' => $variableCategoryName]);
				QMLog::error("Could not get Amazon product for $itemName");
			}
		}
		return $userVariable;
	}
	/**
	 * @param int $userId
	 * @param $nameOrId
	 * @param array $params
	 * @param array $newVariableParams
	 * @return QMUserVariable
	 */
	private static function createByNameOrId(int $userId, $nameOrId, array $params,
		array $newVariableParams): QMUserVariable{
		if(is_int($nameOrId)){
			$cv = QMCommonVariable::find($nameOrId);
		} elseif(!isset($params['doNotCreateNewCommonVariables']) || !$params['doNotCreateNewCommonVariables']){
			$cv = QMCommonVariable::findOrCreateByName($nameOrId, $newVariableParams);
		} else{
			$cv = QMCommonVariable::findByNameOrId($nameOrId, $newVariableParams);
		}
		if(!$cv){
			le("Could not create COMMON variable for $nameOrId");
		}
		return self::createOrUnDeleteById($userId, $cv->variableId, $newVariableParams);
	}
	/**
	 * @return void
	 */
	private function createUserVariablesForWeatherCommonTags(){
		if($this->getVariableCategoryName() !== EnvironmentVariableCategory::NAME){
			return;
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Temperature at';
		$commonTagVariableName = 'Outdoor Temperature';
		$newVariableData = [
			'onsetDelay' => $this->onsetDelay,
			'durationOfAction' => $this->durationOfAction,
		];
		$newVariableData['variableCategoryId'] = 17;
		$newVariableData['unitId'] = 46;
		$newVariableData['combinationOperation'] = 'MEAN';
		$newVariableData['clientId'] = 'Common Tag';
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Pressure at';
		$commonTagVariableName = 'Outdoor Pressure';
		$newVariableData['unitId'] = 47;
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Humidity at';
		$commonTagVariableName = 'Outdoor Humidity';
		$newVariableData['unitId'] = 21;
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Visibility at';
		$commonTagVariableName = 'Outdoor Visibility';
		$newVariableData['unitId'] = 16;
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Precipitation at';
		$commonTagVariableName = 'Precipitation';
		$newVariableData['unitId'] = 48;
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
		$conversionFactor = 1;
		$partialTaggedUserVariableName = 'Cloud cover at';
		$commonTagVariableName = 'Cloud Cover';
		$newVariableData['unitId'] = 21;
		if(stripos($this->name, $partialTaggedUserVariableName) !== false){
			$this->createCommonTagAndUserVariable($commonTagVariableName, $newVariableData, $conversionFactor);
		}
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return bool
	 */
	private function weShouldMergeOverlappingMeasurements(array $measurements): bool{
		return $this->mergeOverlappingMeasurements;
	}
	/**
	 * @return string
	 */
	public function getEarliestNonTaggedMeasurementStartAt(): ?string{
		$earliest = $this->earliestNonTaggedMeasurementStartAt;
		if($new = $this->getCombinedNewQMMeasurements()){
			$earliestNew = collect($new)->min('startAt');
			if($earliest === null || $earliestNew < $earliest){
				$earliest = $earliestNew;
			}
		}
		return $this->earliestNonTaggedMeasurementTime = $earliest;
	}
    /**
     * @param bool $indexByDateString
     * @return Measurement[]|Collection
     */
    public function getCombinedNewMeasurements(bool $indexByDateString = false): Collection{
        return Measurement::instantiateArray($this->getCombinedNewQMMeasurements($indexByDateString));
    }
	/**
	 * @param bool $indexByDateString
	 * @return QMMeasurement[]
	 */
	public function getCombinedNewQMMeasurements(bool $indexByDateString = false): array{
		/** @var QMMeasurement[] $ungrouped */
		$ungrouped = $this->newMeasurements;
		if(!$ungrouped){
			return [];
		}
		$numberNew = count($ungrouped);
		$alreadyCombined = $this->combinedNewMeasurementItems;
		if($alreadyCombined !== null && count($alreadyCombined) >= $numberNew){
			if($indexByDateString){
				return QMMeasurement::indexMeasurementsByStartAt($alreadyCombined);
			}
			return $alreadyCombined;
		}
		$grouped = [];
		foreach($ungrouped as $m){
			$rounded = $m->getRoundedStartTime();
			$grouped[$rounded][] = $m;
		}
		$combined = [];
		$minSeconds = $this->getMinimumAllowedSecondsBetweenMeasurements();
		/** @var QMMeasurement[] $measurements */
		foreach($grouped as $rounded => $measurements){
			$date = db_date($rounded);
			$m = QMMeasurement::getFirst($measurements);
			$merged = clone $m;
			if(count($measurements) > 1){
				$values = Arr::pluck($measurements, 'value');
				$duplicateMessage = "We have " . count($measurements) . " $this measurements measurements within same $minSeconds second time range on $date.
                    The values are " . implode(", ", $values) . ". ";
				if(count(array_unique($values)) > 1){
					if($this->weShouldMergeOverlappingMeasurements($measurements)){
						$merged->value = $this->combineValues($values);
						$merged->getAdditionalMetaData()->addMergedMeasurements($measurements, $merged);
						$merged->logInfo("MinimumAllowedSecondsBetweenMeasurements is $minSeconds so grouping these measurements: " .
						                 QMLog::var_export($merged->getAdditionalMetaData()->getMergedMeasurements(), true));
					} else{
						if($this->isSum()){
							$duplicateMessage .= " Just summing values... ";
						} else{
							$duplicateMessage .= " Just averaging values... ";
						}
						$merged->value = $this->combineValues($values);
						$this->logError($duplicateMessage);
					}
				} else{
					$this->logInfo($duplicateMessage);
				}
			}
			$merged->setStartTime($rounded);
			if(isset($combined[$merged->startTime])){
				le($merged);
			}
			if($indexByDateString){
				$combined[$merged->getStartAt()] = $merged;
			} else{
				$combined[$merged->startTime] = $merged;
			}
		}
		return $this->combinedNewMeasurementItems = $combined;
	}
	/**
	 * @return string
	 */
	public function getOptimalValueMessage(): ?string{
		if(isset($this->userOptimalValueMessage)){
			return $this->userOptimalValueMessage;
		}
		return $this->getCommonOptimalValueMessage();
	}
	/**
	 * @param bool $allowDBQueries
	 * @return string
	 */
	public function getOrCalculateUserOptimalValueMessage(bool $allowDBQueries = true): string{
		if(!isset($this->userOptimalValueMessage)){
			$this->calculateUserOptimalValueMessage();
		}
		return $this->userOptimalValueMessage;
	}
	/**
	 * @return string
	 */
	public function calculateUserOptimalValueMessage(): string{
		return $this->userOptimalValueMessage = UserVariableOptimalValueMessageProperty::calculate($this);
	}
	/**
	 * @return QMTrackingReminder
	 */
	public function getQMTrackingReminder(): ?QMTrackingReminder{
		if($this->trackingReminder){
			return $this->trackingReminder;
		}
		$reminders = $this->getQMTrackingReminders();
		return $this->trackingReminder = $reminders[0] ?? null;
	}
	/**
	 * @param mixed $daily
	 */
	private function setValidDailyMeasurementsWithTagsAndFilling(?array $daily): void{
		if($daily){
			$this->validateDailyMeasurements($daily);
		}
		$this->validDailyMeasurementsWithTagsAndFilling = $daily;
	}
	/**
	 * @param string $attribute
	 * @param $value
	 */
	public function setAttributeIfLessThan(string $attribute, $value){
		$existing = $this->getAttribute($attribute);
		if($existing === null || $value < $existing){
			$this->setAttribute($attribute, $value);
		}
	}
	/**
	 * @param string $attribute
	 * @param $value
	 */
	public function setAttributeIfGreaterThan(string $attribute, $value){
		$existing = $this->getAttribute($attribute);
		if($existing === null || $value > $existing){
			$this->setAttribute($attribute, $value);
		}
	}
	/**
	 * @param QMMeasurement $m
	 * @throws InvalidVariableValueAttributeException
	 */
	public function updateFromMeasurement(QMMeasurement $m){
		$startTime = $m->getRoundedStartTime();
		$startAt = $m->getStartAt();
		try {
			$value = $m->getValueInCommonUnit();
		} catch (IncompatibleUnitException $e) {
			/** @var LogicException $e */
			throw $e;
		}
		$this->setAttributeIfLessThan(self::FIELD_EARLIEST_FILLING_TIME, $startTime);
		$this->setAttributeIfLessThan(self::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT, $startAt);
		$this->setAttributeIfLessThan(self::FIELD_EARLIEST_TAGGED_MEASUREMENT_START_AT, $startAt);
		$this->setAttributeIfGreaterThan(self::FIELD_LATEST_FILLING_TIME, $startTime);
		$this->setAttributeIfGreaterThan(self::FIELD_LATEST_NON_TAGGED_MEASUREMENT_START_AT, $startAt);
		$this->setAttributeIfGreaterThan(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT, $startAt);
		$this->newestDataAt = now_at();
		$this->setAttributeIfGreaterThan(self::FIELD_NUMBER_OF_UNIQUE_VALUES, 1);
		$this->setAttributeIfNotSet(self::FIELD_MOST_COMMON_CONNECTOR_ID, $m->connectorId);
		$this->setAttributeIfNotSet(self::FIELD_MOST_COMMON_SOURCE_NAME, $m->sourceName);
		$this->setAttributeIfLessThan(self::FIELD_MINIMUM_RECORDED_VALUE, $value);
		$this->setAttributeIfGreaterThan(self::FIELD_MAXIMUM_RECORDED_VALUE, $value);
		$this->setAttribute(self::FIELD_LAST_VALUE, $value);
		$this->setAttribute(self::FIELD_LAST_ORIGINAL_VALUE, $m->getOriginalValue());
		$this->addToLastValuesInCommonUnitArray($value);
	}
	public function setTagsIfRequested(){
		$params = QMVariable::convertVariableRequestToRequestParamsIfNecessary($this->requestParams);
		if(isset($params['includeTags']) && $params['includeTags']){
			try {
				$this->getAllCommonAndUserTagVariableTypes();
			} catch (InsufficientMemoryException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	public function setChartsIfRequested(): void{
		$params = QMVariable::convertVariableRequestToRequestParamsIfNecessary($this->requestParams);
		if(isset($params[QMRequest::PARAM_INCLUDE_CHARTS])){
			try {
				$this->getOrSetHighchartConfigs();
			} catch (NotEnoughMeasurementsException $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
				$this->logError(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	public function setNotificationButtonsIfNecessary(): void{
		if($this->getManualTracking() !== false && AppMode::isApiRequest()){ // To slow to do all the time
			$this->getNotificationActionButtons();
		}
	}
	/**
	 * @param int|string $latestFillingTime
	 * @return int
	 */
	public function setLatestFillingTime($latestFillingTime): int{
		$time = TimeHelper::universalConversionToUnixTimestamp($latestFillingTime);
		$at = db_date($latestFillingTime);
		$this->latestFillingAt = $at;
		return $this->setAttribute(self::FIELD_LATEST_FILLING_TIME, $time);
	}
	/**
	 * @param int|string $earliestFillingTime
	 * @return int
	 */
	public function setEarliestFillingTime($earliestFillingTime): int{
		$time = TimeHelper::universalConversionToUnixTimestamp($earliestFillingTime);
		$at = db_date($earliestFillingTime);
		$this->earliestFillingAt = $at;
		try {
			return $this->setAttribute(self::FIELD_EARLIEST_FILLING_TIME, $time);
		} catch (\Throwable $e) {
			return $this->setAttribute(self::FIELD_EARLIEST_FILLING_TIME, $time);
		}
	}
	/**
	 * @return array
	 */
	public function getMeasurementsByTagVariableName(): array{
		$byName = $this->byTagName;
		if($byName === null){
			try {
				$this->setMeasurementsWithTags();
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				le($e);
				throw new \LogicException();
			}
			$byName = $this->byTagName;
		}
		return $byName;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getFillerMeasurements(): array{
		$measurements = $this->fillerMeasurements;
		if($measurements === null){
			$this->getValidDailyMeasurementsWithTagsAndFilling();
			$measurements = $this->fillerMeasurements;
			if($measurements === null){
				$measurements = [];
			}
		}
		return $measurements;
	}
	/**
	 * @return RawQMMeasurement[]
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function setMeasurementsWithTags(): array{
		$allWithTags = $rawMeasurements = $this->getQMMeasurements();
		$this->byTagName[$this->name] = $rawMeasurements;
		$allWithTags = array_values($allWithTags); // Might have duplicate dates with tags
		$needToSort = false;
		$tagged = $this->getCommonAndUserTaggedVariables();
		foreach($tagged as $v){
			$v->inGlobalsWithDifferentMeasurements();
			$raw = $v->getQMMeasurements();
			if($raw){
				$needToSort = true;
			}
			$converted = $this->convertTaggedMeasurements($v->tagConversionFactor, $raw, $v);
			$this->byTagName["From " . $v->name] = $converted;
			$allWithTags = array_merge($allWithTags, $converted);
		}
		if($needToSort){
			$allWithTags = MeasurementStartTimeProperty::sortMeasurementsChronologically($allWithTags);
		}
		return $this->measurementsWithTags = $allWithTags;
	}
    public function getVariableId(): int{
        return $this->variableId;
    }
	public function addInvalidMeasurement(Measurement|QMMeasurement|DailyMeasurement $m, string $error){
		$this->addWarning($error, ['measurement' => $m]);
		$m->setError($error);
		$this->invalidMeasurements[] = $m;
	}
	/**
	 * @param string $name
	 * @param array $meta
	 * @param bool $obfuscate
	 * @param string|null $message
	 */
	public function logErrorOrInfoIfTesting(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		if($this->getQMUser() && $this->getQMUser()->isTestUser()){
			$this->logInfo($name, $meta);
			return;
		}
		parent::logErrorOrInfoIfTesting($name, $meta, $obfuscate, $message);
	}
	/**
	 * @param array $rows
	 * @param GetUserVariableRequest|null $req
	 * @param string|null $sortBy
	 * @return QMUserVariable[]
	 */
	public static function convertRowsToVariables(array $rows, GetUserVariableRequest $req = null,
		string $sortBy = null): array{
		$variables = [];
		foreach($rows as $row){
			$v = self::findInMemoryByVariableId($row->userId, $row->variableId);
			if(!$v){
				self::setNumberOrUniqueValuesOnRow($row);
				$v = new QMUserVariable($row, $row->userId, $row->variableId, $req);
			}
			$variables[] = $v;
		}
		if($sortBy){
			GetUserVariableRequest::addSubTitles($variables, $sortBy);
		}
		return $variables;
	}
	/**
	 * Returns an array of variables this user has measurements for
	 * @param int $userId
	 * @param array $params
	 * @param bool $exactMatch
	 * @param bool $useWritableConnection
	 * @return QMUserVariable[]|QMCommonVariable[]
	 * @internal param string $searchPhrase
	 */
	public static function getUserVariables(int $userId, array $params = [], bool $exactMatch = false,
		bool $useWritableConnection = false): array{
		$params['exactMatch'] = $exactMatch;
		$params['useWritableConnection'] = $useWritableConnection;
		$params['userId'] = $userId;
		$req = new GetUserVariableRequest($params, $userId);
		if($req->getTagSearch()){
			return $req->getEligibleTagVariables();
		}
		$qb = $req->complicatedQb();
		$ids = $qb->pluck(UserVariable::TABLE . '.' . UserVariable::FIELD_ID);
		$userVariables = UserVariable::find($ids);
		$variableIds = [];
		foreach($userVariables as $userVariable){
			$variableIds[] = $userVariable->variable_id;
		}
		$variables = Variable::find($variableIds);
		$dbms = UserVariable::toDBModels($userVariables);
		if($sort = $req->getSort()){
			GetUserVariableRequest::addSubTitles($dbms, $sort);
		}
		if($dbms && ($req->getNameAttribute() || $req->getVariableId())){
			return $dbms;
		}
		if($dbms && $req->gotExactMatchIncludingSynonyms($dbms)){
			return $dbms;
		}
		$dbms = self::getFallbackVariablesIfNecessary($req, $dbms);
		$dbms = self::postProcessVariableQueryResults($dbms, $req);
		return $dbms;
	}
	/**
	 * @param int|QMUnit|string $userUnit
	 * @return QMUnit
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function setUserUnit($userUnit): QMUnit{
		$userUnit = parent::setUserUnit($userUnit);
		QMUnit::setInputType($this);
		$this->validateUnit();
		$this->userUnitId = $userUnit->id;
		$this->unsetUserUnitIfItNotCompatibleWithCommonUnit();
		//$this->convertValuesToUserUnit();
		if(AppMode::isApiRequest()){
			$this->getAvailableUnits();
		}
		return $userUnit;
	}
	/**
	 * @param GetUserVariableRequest $variableRequest
	 * @param QMUserVariable[] $userVariables
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	public static function fallbackToVariablesWithAggregatedCorrelationsIfNecessary(GetUserVariableRequest $variableRequest,
		array $userVariables): array{
		if(!count($userVariables) && $variableRequest->getFallbackToAggregatedCorrelations()){
			$userVariables = QMCommonVariable::getCommonVariables($variableRequest->getCommonVariableRequest());
		}
		return $userVariables;
	}
	/**
	 * @param QMCommonVariable[]|QMUserVariable[] $combinedVariables
	 * @param string $searchPhrase
	 * @return QMCommonVariable[]
	 */
	public static function includePrivateExactMatchIfNotPresent(array $combinedVariables, string $searchPhrase): array{
		$combinedVariables = QMVariable::putExactMatchFirst($combinedVariables, $searchPhrase);
		if(isset($combinedVariables[0]) && !QMVariable::isExactMatch($searchPhrase, $combinedVariables[0]->name)){
			$exactRequestParams = [
				'includePrivate' => true,
				'name' => str_replace('%', '', $searchPhrase),
				'exactMatch' => true,
			];
			$exactMatches = QMCommonVariable::getCommonVariables($exactRequestParams);
			if(isset($exactMatches[0])){
				$combinedVariables = QMCommonVariable::mergeAndRemoveDuplicateNames($exactMatches, $combinedVariables);
			}
		}
		return $combinedVariables;
	}
	/**
	 * @param GetUserVariableRequest $variableRequest
	 * @param QMUserVariable[] $userVariables
	 * @return array
	 */
	private static function putExactMatchFirstIfWeDoNotHaveSortParam(GetUserVariableRequest $variableRequest,
		array $userVariables): array{
		if(!$variableRequest->getSort() && $variableRequest->getSearchPhrase()){
			$userVariables = QMVariable::putExactMatchFirst($userVariables, $variableRequest->getSearchPhrase());
		}
		return $userVariables;
	}
	/**
	 * @param GetUserVariableRequest $variableRequest
	 * @param QMUserVariable[] $userVariables
	 * @return mixed
	 */
	private static function removeExtrasIfExceedingLimit(GetUserVariableRequest $variableRequest,
		array $userVariables): array{
		if($variableRequest->getLimit()){
			while(count($userVariables) > $variableRequest->getLimit()){
				array_pop($userVariables);
			}
		}
		return $userVariables;
	}
	/**
	 * @param GetUserVariableRequest $variableRequest
	 * @param QMUserVariable[] $userVariables
	 * @return QMCommonVariable[]
	 */
	private static function addPrivateExactMatchIfWeHaveSearchPhraseAndCanIncludePublic(GetUserVariableRequest $variableRequest,
		array $userVariables): array{
		if(!$variableRequest->isFallbackToAggregatedCorrelations() && $variableRequest->getSearchPhrase() &&
			$variableRequest->isIncludePublic()){
			$userVariables =
				self::includePrivateExactMatchIfNotPresent($userVariables, $variableRequest->getSearchPhrase());
		}
		return $userVariables;
	}
	/**
	 * @param GetUserVariableRequest $variableRequest
	 * @param QMUserVariable[] $variables
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	private static function getFallbackVariablesIfNecessary(GetUserVariableRequest $variableRequest,
		array $variables): array{
		$variables = self::fallbackToVariablesWithAggregatedCorrelationsIfNecessary($variableRequest, $variables);
		if($variableRequest->getSearchPhrase() && !$variableRequest->isFallbackToAggregatedCorrelations() &&
			$variableRequest->isIncludePublic() && count($variables) < $variableRequest->getLimit()){
			$variables =
				QMCommonVariable::backFillWithCommonVariablesPrivateExactMatchOrWithoutCategoryFilter($variableRequest,
					$variables);
		}
		$variables = self::addPrivateExactMatchIfWeHaveSearchPhraseAndCanIncludePublic($variableRequest, $variables);
		if(!count($variables) && $variableRequest->getSearchPhrase()){
			$variables = $variableRequest->getVariablesWithMatchingSynonyms();
		}
		return $variables;
	}
	/**
	 * @param QMUserVariable[]|QMCommonVariable[] $userVariables
	 * @param GetUserVariableRequest $variableRequest
	 * @return QMCommonVariable[]|QMUserVariable[]
	 */
	private static function postProcessVariableQueryResults(array $userVariables,
		GetUserVariableRequest $variableRequest): array{
		$userVariables = QMVariable::filterByUnitCategoryName($userVariables, $variableRequest);
		SwaggerDefinition::addOrUpdateSwaggerDefinition($userVariables, __CLASS__);
		$userVariables = self::putExactMatchFirstIfWeDoNotHaveSortParam($variableRequest, $userVariables);
		$userVariables = self::removeExtrasIfExceedingLimit($variableRequest, $userVariables);
		if(!$userVariables){
			$userVariables = [];
		}
		return $userVariables;
	}
	/**
	 * @return float[]
	 */
	public function getUniqueValuesWithTagsInReverseOrder(): array{
		$values = $this->getValuesWithTags();
		return array_values(array_unique(array_reverse($values)));
	}
	/**
	 * @return float[]
	 */
	public function getValidUniqueValuesWithTagsInReverseOrder(): array{
		$validValuesWithTags = $this->getValidValuesWithTags();
		return array_values(array_unique(array_reverse($validValuesWithTags)));
	}
	public function getSubtitleAttribute(): string{
		return $this->subtitle = $this->getUserVariable()->getSubtitleAttribute();
	}
	/**
	 * @param float|null $userMinimumAllowedValueInCommonUnit
	 */
	public function setUserMinimumAllowedValueInCommonUnit(?float $userMinimumAllowedValueInCommonUnit): void{
		$this->userMinimumAllowedValueInCommonUnit = $userMinimumAllowedValueInCommonUnit;
	}
	/**
	 * @param float|null $userMaximumAllowedValueInCommonUnit
	 */
	public function setUserMaximumAllowedValueInCommonUnit(?float $userMaximumAllowedValueInCommonUnit): void{
		$this->userMaximumAllowedValueInCommonUnit = $userMaximumAllowedValueInCommonUnit;
	}
	/**
	 * @param DailyMeasurement[] $all
	 * @return DailyMeasurement[]
	 */
	private function removeInvalidDailyMeasurements(array $all): array{
		$keep = [];
		$minDaily = $this->getMinimumAllowedDailyValue();
		$this->getCommonMinimumAllowedValue();
		$maxDaily = $this->getCommonMaximumAllowedDailyValue();
		foreach($all as $date => $m){
			if($minDaily !== null && $m->value < $minDaily){
				$tooSmall[] = $m;
				$this->addInvalidMeasurement($m, "Value $m->value is below minimum allowed daily value $minDaily");
				continue;
			}
			if($maxDaily !== null && $m->value > $maxDaily){
				$tooBig[] = $m;
				$this->addInvalidMeasurement($m, "Value $m->value is above maximum allowed daily value $maxDaily");
				continue;
			}
			$keep[$date] = $m;
		}
		if($keep){$this->validateDailyMeasurements($keep);}
		return $keep;
	}
	/**
	 * @param string|int $startDate
	 * @param string|int $endDate
	 * @return ProcessedQMMeasurement[]|DailyMeasurement[]
	 */
	public function getDailyMeasurementsWithTagsAndFillingInTimeRange($startDate, $endDate): array{
		$startDate = TimeHelper::YYYYmmddd($startDate);
		$endDate = TimeHelper::YYYYmmddd($endDate);
		if($startDate > $endDate){
			le('$startDate > $endDate');
		}
		$all = $this->getValidDailyMeasurementsWithTagsAndFilling();
		$keep = [];
		foreach($all as $date => $m){
			if($date < $startDate){
				$tooEarly[] = $m;
				continue;
			}
			if($date > $endDate){
				$tooLate[] = $m;
				break;
			}
			$keep[$date] = $m;
			if(!$m->userId){
				le("no user id", $m);
			}
		}
		$keep = $this->removeInvalidDailyMeasurements($keep);
		return $keep;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function generateValidDailyMeasurementsWithTags(): array{
		try {
			$all = $this->getMeasurementsWithTags();
			$daily = DailyMeasurement::aggregateDaily($all, $this);
		} catch (InsufficientMemoryException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			if($this->isSum()){
				$daily = QMMeasurement::getSumByDate($this->variableId, $this->userId);
			} else{
				$daily = DailyMeasurement::getAveragedByDate($this->variableId, $this->userId);
			}
			$unit = $this->getCommonUnit();
			foreach($daily as $one){
				$one->userId = $this->getUserId();
				$one->variableId = $this->getVariableIdAttribute();
				$one->unitId = $unit->id;
				$one->userVariableId = $this->getUserVariableId();
			}
		}
		$valid = $this->removeInvalidDailyMeasurements($daily);
		$this->setDailyMeasurementsWithTags($valid);
		return $valid;
	}
	/**
	 * @param QMMeasurement[] $inCommonUnit
	 * @return QMMeasurement[]
	 */
	public function convertMeasurementsToUserUnit(array $inCommonUnit): array{
		$userUnit = $this->getUserUnit();
		if($userUnit->id === $this->getCommonUnit()->id){
			return $inCommonUnit;
		}
		$inUserUnit = [];
		foreach($inCommonUnit as $m){
			$m = clone $m;
			try {
				$commonVal = $m->value;
				$m->convertUnit($userUnit);
				$m->valueInUserUnit = $m->value;
				$m->valueInCommonUnit = $commonVal;
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				le($e);
			}
			$inUserUnit[] = $m;
		}
		return $inUserUnit;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getValidDailyMeasurementsWithTagsInUserUnit(): array{
		$measurements = $this->validDailyMeasurementsWithTagsInUserUnit;
		if($measurements !== null){
			return $measurements;
		}
		$inCommonUnit = $this->getValidDailyMeasurementsWithTags();
		$inUserUnit = $this->convertMeasurementsToUserUnit($inCommonUnit);
		return $this->validDailyMeasurementsWithTagsInUserUnit = $inUserUnit;
	}
	/**
	 * @param bool $includeConcatenatedStringFields
	 * @return QMMeasurement[]
	 */
	public function setDailyMeasurements(bool $includeConcatenatedStringFields = false): array{
		if($all = $this->measurements){
			$daily = DailyMeasurement::aggregateDaily($all, $this);
			return $this->dailyMeasurements = $daily;
		}
		$combinationOperation = $this->getOrSetCombinationOperation();
		if($combinationOperation === BaseCombinationOperationProperty::COMBINATION_MEAN){
			$combinationOperation = 'AVG';
		}
		$qb = QMMeasurement::readonly()->where(static::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
			->where(static::FIELD_USER_ID, $this->getUserId());
		$qb = GetMeasurementRequest::addAggregatedSelectStatements($qb, $includeConcatenatedStringFields);
		$qb->groupBy(['startDate']);
		$qb->orderBy('startDate');
		$qb->columns[] = ReadonlyDB::db()->raw($combinationOperation . "(value) AS value");
		$rows = $qb->getArray();
		$measurements = QMMeasurement::instantiateArray($rows);
		$byDate = [];
		foreach($measurements as $m){
			$m->setUserVariable($this);
			$m->setUnitId($this->getCommonUnitId());
			$byDate[$m->getDate()] = $m;
		}
		return $this->dailyMeasurements = $byDate;
	}
	/**
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function trackingReminderQb(): Builder {
		return TrackingReminder::whereUserId($this->userId)
			->where(TrackingReminder::FIELD_VARIABLE_ID, $this->getVariableIdAttribute());
	}
	/**
	 * @return array
	 */
	private static function getRelatedDbFields(): array{
		$array = [];
		$array[] = [
			'table' => Measurement::TABLE,
			'field' => 'variable_id',
		];
		$array[] = [
			'table' => UserVariableRelationship::TABLE,
			'field' => 'cause_variable_id',
		];
		$array[] = [
			'table' => UserVariableRelationship::TABLE,
			'field' => 'effect_variable_id',
		];
		$array[] = [
			'table' => 'variable_user_sources',
			'field' => 'variable_id',
		];
		$array[] = [
			'table' => 'tracking_reminders',
			'field' => 'variable_id',
		];
		$array[] = [
			'table' => 'user_tags',
			'field' => 'tagged_variable_id',
		];
		$array[] = [
			'table' => 'user_tags',
			'field' => 'tag_variable_id',
		];
		$array[] = [
			'table' => Study::TABLE,
			'field' => Study::FIELD_CAUSE_VARIABLE_ID,
		];
		$array[] = [
			'table' => Study::TABLE,
			'field' => Study::FIELD_EFFECT_VARIABLE_ID,
		];
		$array[] = [
			'table' => Vote::TABLE,
			'field' => Vote::FIELD_CAUSE_VARIABLE_ID,
		];
		$array[] = [
			'table' => Vote::TABLE,
			'field' => Vote::FIELD_EFFECT_VARIABLE_ID,
		];
		return $array;
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int
	 */
	public function hardDelete(string $reason, bool $countFirst = true): int{
		$measurements = $this->getMeasurementsWithTags();
		if($measurements){
			le("Cannot delete because there are " . count($measurements));
		}
		$this->getRelatedRecords();
		return parent::hardDelete($reason, $countFirst);
	}
	/**
	 * @return QMTrackingReminder[]
	 */
	public function getQMTrackingReminders(): array{
		$trackingReminders = $this->getQMUser()->getTrackingRemindersByVariableId($this->getVariableIdAttribute());
		if(count($trackingReminders) !== $this->numberOfTrackingReminders){
			$this->updateDbRow([self::FIELD_NUMBER_OF_TRACKING_REMINDERS => count($trackingReminders)]);
		}
		return $trackingReminders;
	}
	/**
	 * @return UserVariable[]
	 */
	public function getCauseVariablesToCorrelateWith(): array{
		$qb = $this->getUser()->user_variables();
		$qb = $qb->whereHas('variable', function($qb){
			$catId = $this->getVariableCategoryId();
			if($catId === EconomicIndicatorsVariableCategory::ID){
				$qb->where(Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID, "<>",
					InvestmentStrategiesVariableCategory::ID);
			}
		});
		$userVariables = $qb->get();
		$keep = [];
		foreach($userVariables as $cause){
			if(!self::shouldCorrelate($cause, $this)){
				continue;
			}
			if($cause->getId() === $this->getId()){
				$l = $this->l();
				$keep[$this->getVariableName()] = $l;
			} else{
				$keep[$cause->getVariableName()] = $cause;
			}
		}
		return $keep;
	}
	/**
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function correlateAsEffect(): array{
		try {
			$this->analyzeFullyIfNecessary("we're calculating all user_variable_relationships with this variable");
		} catch (TooSlowToAnalyzeException $e) {
			le($e);
		}
		$correlations = [];
		$variables = $this->getCauseVariablesToCorrelateWith();
		$i = 0;
		$total = count($variables);
		foreach($variables as $cause){
			$causes[$cause->getVariableName()] = $cause;
			$i++;
			QMLog::infoWithoutContext("=== Correlating with cause {$cause->getVariableName()} ($i of $total causes) ===");
			try {
				$cause->analyzeFullyIfNecessary("we're correlating it with $this->name");
			} catch (TooSlowToAnalyzeException $e) {
				le($e);
			}
			try {
				$c = QMUserVariableRelationship::findOrCreate($this->getUserId(), $cause->getVariableIdAttribute(),
					$this->getVariableIdAttribute());
				$c->analyzeIfNecessary(__FUNCTION__);
				$correlations[$cause->getVariableName()] = $c;
			} catch (UserVariableNotFoundException $e) {
				le($e);
			}
			$this->charts = null;
			$this->unsetCorrelations();
			$get = $this->getOutcomesOrPredictors(); // This excludes self-user_variable_relationships
			$createdCount = count($correlations);
			$fromDBCount = $get->count();
			if($createdCount > ($fromDBCount + 1)){  // This excludes self-user_variable_relationships so we add +1
				$causeNames = array_keys($correlations);
				$fromDB = BaseNameProperty::listValues($get->all());
				le("Why did we create $createdCount user_variable_relationships for effect $this".
				 "\n\tcauses: ".QMStr::list($causeNames).
					"\nbut only get these $fromDBCount user_variable_relationships from the database:\n$fromDB"
					."\n\t".$this->getDataLabShowUrl());
			}
		}
		return $correlations;
	}
	/**
	 * @param string $reason
	 * @return PendingDispatch
	 */
	public function queueCorrelation(string $reason): ?PendingDispatch{
		if(UserVariableCorrelationJob::alreadyQueued($this)){
			return null;
		}
		$this->saveAnalysisStatus($reason, UserVariableStatusProperty::STATUS_CORRELATE);
		return UserVariableCorrelationJob::queueModel($this->l(), $reason);
	}
	/**
	 * @param array $userVariableIds
	 * @return QMUserVariableRelationship[]
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function correlate(array $userVariableIds = []): array{
		if($this->status !== UserVariableStatusProperty::STATUS_CORRELATING){
			$this->setStatusInDatabase(UserVariableStatusProperty::STATUS_CORRELATING, []);
		}
		$this->analyzeFullyIfNecessary("we're calculating all user_variable_relationships with this variable");
		if(!$userVariableIds){
			$userVariableIds = $this->getUserVariableIdsToCorrelateWith();
		}
		$total = count($userVariableIds);
		$i = 0;
		$correlations = [];
		foreach($userVariableIds as $id){
			if($profile = false){
				QMProfile::startLiveProf(__METHOD__);
			}
			$toCorrelateWith[] = $b = QMUserVariable::find($id);
			if($b->analyzedInLastXHours(24)){
				$b->alreadyAnalyzed = true;
			}
			$i++;
			$this->logInfo("=== Correlating $i of $total variables ===");
			try {
				$b->analyzeFully("we're correlating it with $this->name", false);
			} catch (AlreadyAnalyzingException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
			} catch (AlreadyAnalyzedException $e) {
				$this->logInfo(__METHOD__.": ".$e->getMessage());
			} // Don't worry, it won't update if we don't have new measurements
			$bDownVotedAsCause = $this->downVotedAsCause($b);
			$bDownVotedAsEffect = $this->downVotedAsEffect($b);
			if($bDownVotedAsCause && $bDownVotedAsEffect){
				$this->logInfo("$b downvoted as both cause and effect so skipping it...");
				continue;
			}
			if(!$bDownVotedAsEffect && QMUserVariableRelationship::shouldWeCalculate($this, $b)){
				$c = $this->tryToCalculateForPair($this, $b);
			}
			if(!$bDownVotedAsCause && QMUserVariableRelationship::shouldWeCalculate($b, $this)){
				$c = $this->tryToCalculateForPair($b, $this);
			}
			if(isset($c)){
				$correlations[$c->getTitleAttribute()] = $c;
			}
			if($profile){
				QMProfile::endProfile();
			}
		}
		$this->afterCorrelation($correlations);
		return $correlations;
	}
	/**
	 * @return string
	 */
	public function getPHPUnitTestUrlForCorrelateAll(): ?string{
		$userId = $this->getUserId();
		$variableId = $this->getVariableIdAttribute();
		$testName = 'CalculateCorrelationsForVariableAndTagVariablesFor' . $variableId . 'User' . $userId;
		$body = "\$v = QMUserVariable::findByNameOrId($userId, $variableId);" . PHP_EOL;
		$body .= "\t\$v->calculateCorrelations();" . PHP_EOL;
		return StagingJobTestFile::getUrl($testName, $body, __CLASS__);
	}
	/**
	 * @param string|null $reason
	 * @param bool $updateTags
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 */
	public function analyzeFully(string $reason = null, bool $updateTags = true){
		$this->beforeAnalysis($reason);
		if($updateTags){
			$this->verifyJsonEncodableAndNonRecursive();
			$this->createUserVariablesForWeatherCommonTags();
			$this->verifyJsonEncodableAndNonRecursive();
			$this->analyzeTagVariables();
			$this->verifyJsonEncodableAndNonRecursive();
		}
		$withTags = $this->getMeasurementsWithTags();
		$l = $this->l();
		if(!$withTags){
			$this->debugReasonForExistence();
		}
		UserVariableClient::updateByUserVariable($l); // Analyze even if we don't have measurements in case we deleted them all
		UserVariableLatestSourceMeasurementStartAtProperty::calculate($l); // Filling is dependent on this
		UserVariableEarliestSourceMeasurementStartAtProperty::calculate($l); // Filling is dependent on this
		$this->setAttribute(self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS, count($withTags));
		$this->setNumberOfMeasurementsWithTagsAtLastAnalysis(count($withTags));
		try {
			$calculated = $this->calculateAttributes();
		} catch (InvalidAttributeException $e) {
			le($e);
		}
		if(AppMode::isApiRequest()){
			$clientId = BaseClientIdProperty::fromRequest(false);
			if($clientId && $clientId !== BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
				$l->client_id = $clientId;
			}
		}
		$l->status =
			($this->weShouldCalculateCorrelations()) ? UserVariableStatusProperty::STATUS_CORRELATE : UserVariableStatusProperty::STATUS_UPDATED;
		$l->user_error_message = $l->internal_error_message = $err ?? null;
		$this->updateVariable();
		$l->analysis_ended_at = $this->setAnalysisEndedAtAndStatusUpdated();
		$this->logInfo("Saving full analysis to DB. " . Memory::getDurationInSecondsString("$this Analysis") .
			".  Last analysis " . $this->getTimeSinceAnalysisEndedAt());
		try {
			$l->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param string $reason
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function forceAnalyze(string $reason){
		$this->setAlreadyAnalyzed(false);
		$this->analyzeFully($reason);
	}
	/**
	 * @return string
	 */
	public function getLastCorrelatedAt(): ?string{
		return $this->lastCorrelatedAt;
	}
	/**
	 * @param int $userId
	 */
	public function setUserId(int $userId): void{
		$this->userId = $userId;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getPurchasesVariable(): QMUserVariable{
		$common = $this->getCommonVariable();
		$commonPurchase = $common->getPurchasesVariable();
		$userPurchase = $commonPurchase->findQMUserVariable($this->getUserId());
		return $userPurchase;
	}
	/**
	 * @return int
	 */
	public function getNumberOfMeasurementsWithTagsAtLastCorrelation(): int{
		return $this->numberOfMeasurementsWithTagsAtLastCorrelation ?: 0;
	}
	/**
	 * @return array
	 */
	protected function getRelatedRecords(): array{
		if($this->relatedRecords !== null){
			return $this->relatedRecords;
		}
		$related = [];
		foreach(self::getRelatedDbFields() as $arr){
			$table = $arr['table'];
			$foreignVariableKey = $arr['field'];
			$rows = ReadonlyDB::getBuilderByTable($table)->where($foreignVariableKey, $this->getVariableIdAttribute())
				->where(self::FIELD_USER_ID, $this->getUserId())->getArray();
			if(count($rows)){
				$related[$table] = $rows;
				$this->logError(count($rows) . " $table rows where $foreignVariableKey is $this->variableId");
			} else{
				$this->logDebug("No deleted or soft-deleted $table records where $foreignVariableKey is $this->variableId");
			}
		}
		return $this->relatedRecords = $related;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getNewMeasurements(): array{
		$new = $this->newMeasurements;
		return $new ?? [];
	}
	/**
	 * @return float
	 */
	public function getLastValueInUserUnit(): ?float{
		$common = $this->getLastValueInCommonUnit();
		if($common === null){
			return null;
		}
		return $this->lastValueInUserUnit = $this->toUserUnit($common);
	}
	/**
	 * @return float
	 */
	public function getSecondToLastValueInUserUnit(): ?float{
		$common = $this->getSecondToLastValueInCommonUnit();
		if($common === null){
			return null;
		}
		return $this->secondToLastValueInUserUnit = $this->toUserUnit($common);
	}
	/**
	 * @return float
	 */
	public function getThirdToLastValueInUserUnit(): ?float{
		$common = $this->getThirdToLastValueInCommonUnit();
		if($common === null){
			return null;
		}
		return $this->thirdToLastValueInUserUnit = $this->toUserUnit($common);
	}
	public function validateUnit(){
		$name = $this->unitAbbreviatedName;
		if(!$name){
			le("no unitAbbreviatedName");
		}
		$fromName = QMUnit::find($name);
		$unit = $this->unit;
		if($unit && $unit->id !== $fromName->id){
			le("unitAbbreviatedName $name does not match unit object $unit->abbreviatedName!");
		}
	}
	/**
	 * @param UserVariable $uv
	 */
	public function populateByLaravelUserVariable(UserVariable $uv): void{
		$this->setUserUnit($uv->getUnitIdAttribute());
		$attributes = $uv->attributesToArray();
		foreach($attributes as $key => $value){
			if(static::class !== QMUserVariable::class && $key === 'id'){
				continue;
			}
			if($value !== null){
				$camel = QMStr::camelize($key);
				$this->$camel = $value;
			}
		}
		$this->dataSourcesCount = $uv->getDataSourcesCountAttribute();
		$this->setDurationOfAction($uv->getDurationOfActionAttribute());
		if($earliest = $uv->earliest_filling_time){
			$this->setEarliestFillingTime($earliest);
		}
		$earliest_non_tagged_measurement_start_at = $uv->earliest_non_tagged_measurement_start_at;
		$this->setEarliestMeasurementTime($earliest_non_tagged_measurement_start_at);
		$this->setEarliestNonTaggedMeasurementStartAtAttribute($earliest_non_tagged_measurement_start_at);
		$this->earliestSourceTime =
			time_or_null($this->earliestSourceMeasurementStartAt = $uv->earliest_source_measurement_start_at);
		$this->setEarliestTaggedMeasurementStartAtAttribute($uv->earliest_tagged_measurement_start_at ?? null);
		if($str = $this->experimentEndTime = $uv->experiment_end_time){
			$this->experimentEndTimeSeconds = strtotime($str);
		}
		if($str = $this->experimentStartTime = $uv->experiment_start_time){
			$this->experimentStartTimeSeconds = strtotime($str);
		}
		$this->fillingType = $uv->getFillingTypeAttribute();
		$this->setFillingValue($this->userVariableFillingValue = $uv->getFillingValueAttribute());
		$this->lastValueInCommonUnit = $uv->last_value;
		if($latest = $uv->latest_filling_time){
			$this->setLatestFillingTime($latest);
		}
		$this->setLatestMeasurementTime($uv->latest_non_tagged_measurement_start_at);
		if($at = $uv->latest_non_tagged_measurement_start_at ?? null){
			$this->setLatestNonTaggedMeasurementStartAtAttribute($at);
		}
		$this->latestSourceTime =
			time_or_null($this->latestSourceMeasurementStartAt = $uv->latest_source_measurement_start_at);
		$this->setLatestTaggedMeasurementStartAtAttribute($uv->latest_tagged_measurement_start_at ?? null);
		$this->setUserMaximumAllowedValueInCommonUnit($uv->getMaximumAllowedValueAttribute());
		$this->maximumRecordedValueInCommonUnit = $this->maximumRecordedValue;
		$this->meanInCommonUnit = $this->mean;
		$this->minimumAllowedSecondsBetweenMeasurements = $uv->minimum_allowed_seconds_between_measurements;
		$this->setUserMinimumAllowedValueInCommonUnit($uv->getMinimumAllowedValueAttribute());
		$this->minimumRecordedValueInCommonUnit = $this->minimumRecordedValue;
		$this->mostCommonValueInCommonUnit = $this->mostCommonValue;
		$this->setOnsetDelay($uv->onset_delay);
		$this->setOutcome($uv->getOutcomeAttribute());
		$this->secondToLastValueInCommonUnit = $this->secondToLastValue;
		$this->thirdToLastValueInCommonUnit = $this->thirdToLastValue;
		if($msg = $uv->optimal_value_message){
			$this->userOptimalValueMessage = $msg;
		}
		$this->userUnitId = $uv->getUnitIdAttribute();
		$this->userVariableMostCommonConnectorId = $this->mostCommonConnectorId;
		$this->valence = $this->userVariableValence = $uv->getValenceAttribute();
		$this->setVariableCategory($this->userVariableVariableCategoryId = $uv->getVariableCategoryIdAttribute());
		$v = $uv->getVariable();
		$this->commonVariableCategoryId = $v->variable_category_id;
	}
	/**
	 * @param float|null $inCommonUnit
	 * @return float|null
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function setMinimumAllowedValue(?float $inCommonUnit): ?float{
		$this->minimumAllowedValueInCommonUnit = $inCommonUnit;
		$this->minimumAllowedValueInUserUnit = $this->toUserUnit($inCommonUnit);
		return parent::setMinimumAllowedValue($inCommonUnit);
	}
	/**
	 * @param float|null $inCommonUnit
	 * @return float|null
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function setMaximumAllowedValue(?float $inCommonUnit): ?float{
		$this->maximumAllowedValueInCommonUnit = $inCommonUnit;
		$this->maximumAllowedValueInUserUnit = $this->toUserUnit($inCommonUnit);
		return parent::setMaximumAllowedValue($inCommonUnit);
	}
	/**
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 */
	private function analyzeTagVariables(){
		$this->verifyJsonEncodableAndNonRecursive();
		$commonTagVariables = $this->getCommonTagVariables();
		$this->verifyJsonEncodableAndNonRecursive();
		if($commonTagVariables){
			foreach($commonTagVariables as $commonTagVariable){
				if($commonTagVariable->variableId === $this->variableId){
					$commonTagVariable->logError("Skipping tag analysis because it's the same id as the parent being analyzed");
				}
				$this->throwExceptionAndDeleteTagVariableIfCategoryNotValid($commonTagVariable);
				$this->verifyJsonEncodableAndNonRecursive();
				$commonTagVariable->verifyJsonEncodableAndNonRecursive();
				try {
					$commonTagVariable->analyzeFully(__FUNCTION__, false);
				} catch (AlreadyAnalyzedException $e) {
					$this->logInfo("Skipping common tag analysis for $commonTagVariable because " . $e->getMessage());
				} catch (TooSlowToAnalyzeException $e) {
					le($e);
				}  // Avoid infinite loop in joined variables
				$commonTagVariable->verifyJsonEncodableAndNonRecursive();
				$this->verifyJsonEncodableAndNonRecursive();
			}
		}
		$this->verifyJsonEncodableAndNonRecursive();
		$userTagVariables = $this->getUserTagVariables();
		$this->verifyJsonEncodableAndNonRecursive();
		if($userTagVariables){
			foreach($userTagVariables as $userTagVariable){
				try {
					$userTagVariable->analyzeFully(__METHOD__, false);
				} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
					$this->logInfo("Skipping user tag analysis for $userTagVariable because " . $e->getMessage());
				} catch (TooSlowToAnalyzeException $e) {
					le($e);
				}  // Avoid infinite loop in joined variables
			}
		}
	}
	/**
	 * @param string|null $reason
	 * @return int
	 */
	public function deleteUserVariableAndMeasurements(string $reason = null){
		if($this->calculateNumberOfTrackingReminders()){
			QMLog::error("Not deleting $this->name because we have $this->numberOfTrackingReminders TrackingReminders");
			return false;
		}
		$this->logError("Deleting $this->name for because $reason");
		$this->getBaseMeasurementTableQuery()->update([
			Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
			Measurement::FIELD_ERROR => $reason,
		]);
		return $this->delete($reason, false);
	}
	/**
	 * @param string $reason
	 * @param bool $hard
	 * @return int
	 */
	public function delete(string $reason, bool $hard = false): int{
		if($hard){ // TODO: Delete related records
			le("Hard user variable deletion not yet implemented");
		}
		$this->logError("Deleting because $reason");
		return $this->softDelete([], $reason);
	}
	/**
	 * @return QMQB
	 */
	private function getBaseMeasurementTableQuery(): QMQB{
		return QMMeasurement::writable()->whereNull(Measurement::FIELD_DELETED_AT)
			->where(Measurement::FIELD_USER_ID, $this->userId)
			->where(Measurement::FIELD_VARIABLE_ID, $this->variableId);
	}
	/**
	 * @return int
	 * @deprecated Use setExperimentStartAt
	 */
	public function setExperimentStartTimeSeconds(): ?int{
		$time = $this->experimentStartTime;
		if(TimeHelper::isZeroTime($time)){
			return $this->experimentStartTimeSeconds = false;
		}
		$s = $this->experimentStartTimeSeconds;
		if(time_or_null($time) > $this->earliestTaggedMeasurementTime){
			$s = strtotime($time);
		}
		if($s){
			$this->experimentStartTimeString = date('Y-m-d H:i:s', $s);
		}
		return $this->experimentStartTimeSeconds = $s;
	}
	/**
	 * @return int
	 * @deprecated Use setExperimentEndAt
	 */
	public function setExperimentEndTimeSeconds(): ?int{
		$s = false;
		$experimentEnd = $this->experimentEndTime;
		if(TimeHelper::isZeroTime($experimentEnd)){
			return $this->experimentEndTimeSeconds = false;
		}
		$latestAt = $this->getLatestTaggedMeasurementAt();
		if(time_or_null($experimentEnd) < time_or_null($latestAt)){
			$s = strtotime($experimentEnd);
			if($s < 0){
				$this->logError("experimentEndTimeSeconds $s less than 0!");
				$s = false;
			}
		}
		if($s){
			$this->experimentEndTimeString = date('Y-m-d H:i:s', $s);
		}
		return $this->experimentEndTimeSeconds = $s;
	}
	/**
	 * @return string
	 */
	public function getEarliestFillingAt(): ?string{
		if($start = QMRequest::getParam('startTime')){
			return db_date($start);
		}
		return UserVariableEarliestFillingTimeProperty::calculateAt($this);
	}
	/**
	 * @return string|null
	 */
	public function getLatestFillingAt(): ?string{
		if($end = QMRequest::getParam('endTime')){
			return db_date($end);
		}
		return $this->latestFillingAt = UserVariableLatestFillingTimeProperty::calculateAt($this);
	}
	/**
	 * @return array
	 */
	public function getLastValuesInCommonUnit(): array{
		$arr = $this->lastValuesInCommonUnit;
		if($arr !== null){
			return $arr;
		}
		$arr = [];
		$val = $this->lastValueInCommonUnit;
		if($val !== null){
			$arr[] = $val;
		}
		$val = $this->secondToLastValueInCommonUnit;
		if($val !== null){
			$arr[] = $val;
		}
		$val = $this->thirdToLastValueInCommonUnit;
		if($val !== null){
			$arr[] = $val;
		}
		return $this->lastValuesInCommonUnit = $arr;
	}
	/**
	 * @param float $value
	 * @return array
	 */
	public function addToLastValuesInCommonUnitArray(float $value): array{
		$arr = $this->getLastValuesInCommonUnit();
		array_unshift($arr, $value);
		return $this->lastValuesInCommonUnit = array_unique($arr);
	}
	/**
	 * @return string
	 */
	public function getNewestDataAt(): ?string{
		$newest = $this->newestDataAt;
		if($new = $this->getCombinedNewQMMeasurements()){
			$max = collect($new)->max('updatedAt');
			if($max > $newest){
				return $this->newestDataAt = $max;
			}
		}
		return $this->newestDataAt = $newest;
	}
	/**
	 * @return QMUserVariable
	 */
	public function resetAnalysisSettings(): QMUserVariable{
		$resetSettings = [
			'duration_of_action' => null,
			'filling_value' => -1,
			self::FIELD_FILLING_TYPE => null,
			'join_with' => null,
			'maximum_allowed_value' => null,
			'minimum_allowed_value' => null,
			'onset_delay' => null,
			'experiment_start_time' => null,
			'experiment_end_time' => null,
			'alias' => null,
			self::FIELD_CLIENT_ID => BaseClientIdProperty::fromRequest(false),
			self::FIELD_ANALYSIS_REQUESTED_AT => now_at(),
			self::FIELD_REASON_FOR_ANALYSIS => __FUNCTION__,
			self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => now_at(),
		];
		$success = $this->updateDbRow($resetSettings);
		if(!$success){
			$this->logError("Could not " . __FUNCTION__);
		}
		$this->deleteFromMemory();
		$uv = UserVariable::find($this->getUserVariableId());
		return $uv->getDBModel();
	}
	/**
	 * @param array $arr
	 * @param string|null $reason
	 * @return int
	 * @deprecated Use Eloquent model save directly
	 */
	public function updateDbRow(array $arr, string $reason = null): int{
		if(isset($arr[self::FIELD_FILLING_VALUE])){
			$arr[self::FIELD_FILLING_TYPE] = BaseFillingTypeProperty::valueToType($arr[self::FIELD_FILLING_VALUE]);
		}
		$arr = $this->validateValuesInUpdateArray($arr);
		if($id = $arr[self::FIELD_DEFAULT_UNIT_ID] ?? null){
			try {
				$this->validateAttribute(self::FIELD_DEFAULT_UNIT_ID, $id);
			} catch (InvalidAttributeException $e) {
				le($e);
			}
			$this->setUserUnit($id);
		}
		return parent::updateDbRow($arr, $reason);
	}
	/**
	 * @return array
	 */
	public static function getUnixTimeFields(): array{
		$fields = self::getColumns();
		$timeFields = [];
		foreach($fields as $field){
			if($field !== self::FIELD_EXPERIMENT_START_TIME && $field !== self::FIELD_EXPERIMENT_END_TIME &&
				$field !== 'last_successful_update_time' && stripos($field, '_time') !== false){
				$timeFields[] = $field;
			}
		}
		return $timeFields;
	}
	/**
	 * @return string
	 */
	public function getLatestNonTaggedMeasurementStartAt(): ?string{
		$at = null;
		if($all = $this->measurements){
			/** @var QMMeasurement $last */
			$last = end($all); // Faster than collect($combined)->max('startTime');
			$times[] = $last->getStartAt();
			$at = $last->getStartAt();
		}
		if($combined = $this->getCombinedNewQMMeasurements()){
			$combinedLatestAt = collect($combined)->max('startAt');
			$roundedTime = $this->roundStartTime($combinedLatestAt);
			$roundedAt = db_date($roundedTime);
			if($combinedLatestAt !== $roundedAt){
				le("Combined measurements should be rounded!");
			}
			if(!$at || $combinedLatestAt > $at){
				$at = $combinedLatestAt;
			}
		}
		if($at){
			$this->setLatestNonTaggedMeasurementStartAtAttribute($at);
		}
		return $this->latestNonTaggedMeasurementStartAt;
	}
	/**
	 * @return string
	 */
	public function getLatestTaggedMeasurementAt(): ?string{
		$times = [];
		$latestTagged = $this->getAttribute(self::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT);
		if(!$latestTagged && $this->latestTaggedMeasurementTime){
			$latestTagged = db_date($this->latestTaggedMeasurementTime);
		}
		if($latestTagged){
			$times[] = db_date($latestTagged);
		}
		if($nonTagged = $this->getLatestNonTaggedMeasurementStartAt()){
			$times[] = $nonTagged;
		}
		if($measurements = $this->measurementsWithTags){
			/** @var QMMeasurement $last */
			$last = end($measurements);  // Faster than collect($combined)->max('startTime');
			$at = $last->getStartAt();
			$this->setLatestTaggedMeasurementStartAtAttribute($at);
			return $at;
		}
		$latest = ($times) ? max($times) : null;
		if(is_numeric($latest)){
			le('is_numeric($latest)');
		}
		return $latest;
	}
	/**
	 * @return string
	 */
	public function getEarliestTaggedMeasurementAt(): ?string{
		$earliest = $this->l()->earliest_tagged_measurement_start_at;
		if($earliest instanceof CarbonInterface){
			$earliest = db_date($earliest);
		}
		/** @var QMMeasurement[] $measurements */
		if($measurements = $this->measurements){
			$m = AnonymousMeasurement::getFirst($measurements);
			$fromMeasurements = $m->getStartAt();
			if(!$earliest || $fromMeasurements < $earliest){
				$earliest = $fromMeasurements;
			}
		}
		/** @var QMMeasurement[] $withTagged */
		if($withTagged = $this->measurementsWithTags){
			$m = QMMeasurement::getFirst($withTagged);
			$fromTagged = $m->getStartAt();
			if(!$earliest || $fromTagged < $earliest){
				$earliest = $fromTagged;
			}
		}
		$newEarliest = $this->getNewEarliestTaggedMeasurementAt();
		if($newEarliest && $newEarliest < $earliest){
			$earliest = $newEarliest;
		}
		if(!$earliest && $newEarliest){
			$earliest = $newEarliest;
		}
		if(!$earliest && $this->getNumberOfMeasurements()){
			$this->logError("No earliest measurement at!");
		}
		return $this->earliestTaggedMeasurementAt = $earliest;
	}
	/**
	 * @return string|null
	 */
	public function getNewEarliestTaggedMeasurementAt(): ?string{
		if(!$this->newMeasurements){
			return null;
		}
		$newMeasurementItems = $this->getCombinedNewQMMeasurements();
		$minAt = null;
		foreach($newMeasurementItems as $i){
			$currentAt = $i->getStartAt();
			if(!$minAt || $currentAt < $minAt){
				$minAt = $currentAt;
			}
		}
		return $minAt;
	}
	/**
	 * @return QMUser
	 */
	public function getQMUserFromMemory(): ?QMUser{
		$user = QMUser::findInMemory($this->userId);
		return $user;
	}
	/**
	 * @param string $reason
	 * @param array $updateArray
	 * @return int
	 */
	public function scheduleReCorrelationDynamic(string $reason, array $updateArray = []){
		return $this->setStatusInDatabase(UserVariableStatusProperty::STATUS_WAITING, $updateArray, $reason, true);
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @param string|null $reason
	 */
	public static function logSetWaitingStatusToGoogleAnalytics(int $userId, int $variableId, string $reason = null){
		QMLog::info("Set user variable status to WAITING for user $userId and variable id $variableId because $reason");
		GoogleAnalyticsEvent::logEventToGoogleAnalytics(self::TABLE, 'setStatusWaiting', 1, $userId, null,
			"VariableId: " . $variableId);
	}
	public function setChartLinks(){
		$urlParams = '?variableName=' . rawurlencode($this->name) . '&userId=' . $this->userId . "&pngUrl=" .
			rawurlencode($this->getPngUrl());
		$staticUrl = QMRequest::host() . '/api/v2/charts' . $urlParams;
		$this->chartsLinkStatic = $staticUrl;
		$this->chartsLinkDynamic = IonicHelper::getChartsUrl([
			'variableName' => $this->name,
			'userId' => $this->userId,
			'pngUrl' => $this->getPngUrl(),
		]);
		$subject = "Check out my $this->name data!";
		$this->chartsLinkFacebook = FacebookSharingButton::getFacebookShareLink($this->chartsLinkStatic);
		$this->chartsLinkTwitter = TwitterSharingButton::getTwitterShareLink($this->chartsLinkStatic, $subject);
		$body = "See my $this->name history at ";
		$this->chartsLinkEmail = EmailSharingButton::getEmailShareLink($this->chartsLinkStatic, $subject, $body);
	}
	/**
	 * @param float $valueInUserUnit
	 * @return float
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function toCommonUnit(float $valueInUserUnit): float{
		$userUnit = $this->getUserUnit();
		$commonUnit = $this->getCommonUnit();
		$converted = QMUnit::convertValue($valueInUserUnit, $userUnit, $commonUnit, $this);
		$commonUnit->validateValue($converted, $this);
		return $converted;
	}
	/**
	 * @param float|null $inCommonUnit
	 * @return float
	 */
	public function toUserUnit(?float $inCommonUnit): ?float{
		if($inCommonUnit === null){
			return null;
		}
		$this->unsetUserUnitIfItNotCompatibleWithCommonUnit();
		if(!$this->userUnitId){
			return $inCommonUnit;
		}
		if($this->userUnitId === $this->commonUnitId){
			return $inCommonUnit;
		}
		$common = $this->getCommonUnit();
		$user = $this->getUserUnit();
		try {
			return QMUnit::convertValue($inCommonUnit, $common, $user, $this);
		} catch (IncompatibleUnitException $e) {
			$this->addException($e);
			le($e);
		} catch (InvalidVariableValueException $e) {
			$this->addException($e);
			return null;
		}
	}
	/**
	 * @return string[]
	 */
	public function calculateNonUniqueDataSourceNames(): array{
		$measurements = $this->getMeasurementsWithTags();
		$names = Arr::pluck($measurements, 'sourceName');
		$names = array_values(array_filter($names)); // Remove null values
		foreach($names as $key => $name){
			if(BaseClientIdProperty::isQuantiModoAlias($name)){
				$names[$key] = 'QuantiModo';
			}
		}
		return $names;
	}
	/**
	 * @return bool
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function updateAndValidateMean(): bool{
		$this->forceAnalyze(__FUNCTION__);
		if(!$this->validateMean()){
			QMLog::error("Mean $this->meanInCommonUnit " . $this->getCommonUnit()->name .
				" still not valid for $this->name after update! User: " . $this->getQMUser()->displayName);
		}
		$this->scheduleReCorrelationDynamic("Mean not valid", []);
		return $this->validateMean();
	}
	/**
	 * @return float
	 */
	public function getLastValueInCommonUnit(): ?float{
		if($this->lastValue !== null && $this->lastValueInCommonUnit === null){
			$this->setLastValue($this->lastValue);
		}
		if(is_array($this->lastValuesInCommonUnit)){
			$this->lastValuesInCommonUnit = array_values($this->lastValuesInCommonUnit);
		}
		if(isset($this->lastValuesInCommonUnit[0])){
			$this->setLastValue($this->lastValuesInCommonUnit[0]);
		}
		if($this->lastValueInCommonUnit === null){
			/** @var UserVariable $l */
			if($l = $this->laravelModel){
				$val = $l->last_value;
				if($val !== null){
					$this->setLastValue($val);
				}
			}
		}
		return $this->lastValueInCommonUnit;
	}
	/**
	 * @return mixed
	 */
	public function getSecondToLastValueInCommonUnit(): ?float{
		if($this->secondToLastValue !== null && $this->secondToLastValueInCommonUnit === null){
			$this->setSecondToLastValue($this->secondToLastValue);
		}
		$arr = $this->lastValuesInCommonUnit;
		if(is_array($arr)){
			$this->lastValuesInCommonUnit = $arr = array_values($arr);
		}
		if(isset($arr[1])){
			$this->setSecondToLastValue($arr[1]);
		} elseif($this->secondToLastValueInCommonUnit === $this->lastValueInCommonUnit){
			$this->setSecondToLastValue(null);
		}
		return $this->secondToLastValueInCommonUnit;
	}
	/**
	 * @return mixed
	 */
	public function getThirdToLastValueInCommonUnit(): ?float{
		return $this->thirdToLastValueInCommonUnit;
	}
	/**
	 * @param string $fieldName
	 * @return mixed
	 */
	public function getProtectedFieldValue(string $fieldName){
		return $this->$fieldName;
	}
	public function convertValuesToUserUnit(){
		$this->unsetUserUnitIfItNotCompatibleWithCommonUnit();
		foreach($this as $key => $value){
			if($value === null || is_array($value)){
				continue;
			}
			if(strpos($key, 'InCommonUnit') !== false){
				$this->setAttributeInUserUnit($key);
			}
		}
	}
	/**
	 * @return float
	 */
	public function getLastProcessedDailyValueInCommonUnit(): ?float{
		if($this->dailyMeasurementsWithTags){
			return UserVariableLastProcessedDailyValueProperty::calculate($this);
		}
		return $this->lastProcessedDailyValueInCommonUnit;
	}
	protected function setAttributeInUserUnit(string $commonUnitAttr): ?float{
		$unspecifiedUnitAttr = str_replace('InCommonUnit', '', $commonUnitAttr);
		$userUnitAttr = QMStr::camelize($unspecifiedUnitAttr) . "InUserUnit";
		$inCommon = $this->getAttribute($commonUnitAttr);
		$inUser = $this->toUserUnit($inCommon);
		return $this->$unspecifiedUnitAttr = $this->$userUnitAttr = $inUser;
	}
	/**
	 * @return int
	 */
	private function calculateNumberOfProcessedDailyMeasurementsWithTagsJoinsChildren(): int{
		return BaseNumberOfProcessedDailyMeasurementsProperty::calculate($this);
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfProcessedDailyMeasurementsWithTagsJoinsChildren(): int{
		return $this->numberOfProcessedDailyMeasurements ?: $this->calculateNumberOfProcessedDailyMeasurementsWithTagsJoinsChildren();
	}
	/**
	 * @return bool
	 */
	public function checkTooSmallMeasurements(): bool{
		$qb = $this->getBaseMeasurementTableQuery()->whereRaw("value < " . $this->getCommonUnit()->minimumValue);
		$tooSmallMeasurements = $qb->getArray();
		if(!$tooSmallMeasurements){
			$this->saveAnalysisStatus("Too small values but no small measurements in DB");
			return false;
		}
		if(count($tooSmallMeasurements) < 2){
			$reason = "too small for variable and only 1 measurement";
			QMLog::error("Deleting " . count($tooSmallMeasurements) . " $this->name measurements because $reason",
				['variable' => $this]);
			$qb->update([
				Measurement::FIELD_DELETED_AT => date('Y-m-d H:i:s'),
				Measurement::FIELD_ERROR => $reason,
			]);
			return false;
		}
		$mostRecent = TimeHelper::timeSinceHumanString($tooSmallMeasurements[0]->start_time);
		$deletionUrl = $this->getMeasurementDeletionUrl('value=(lt)' . $this->getCommonUnit()->minimumValue);
		QMLog::errorOrInfoIfTesting(count($tooSmallMeasurements) . "
         $this->name measurements are too small for unit " . $this->getCommonUnit()->name .
			" ! Most recent was $mostRecent", [
			'delete_url' => $deletionUrl,
			'unit' => $this->getCommonUnit(),
			'measurements' => $tooSmallMeasurements,
		], "Delete them at $deletionUrl");
		return true;
	}

	/**
	 * @param QMButton[] $buttons
	 * @return array
	 */
	public function setActionButtons(array $buttons): array{
		IndividualPushNotificationData::exceptionIfDuplicateButton($buttons);
		return $this->actionArray = $buttons;
	}
	/**
	 * @param int $seconds
	 * @return float[]
	 */
	public function getAggregatedValues(int $seconds): array{
		if($values = $this->aggregatedValues[$seconds] ?? null){
			if(count($values) >= count($this->measurements)){
				return $values;
			}
		}
		$measurements = $this->getQMMeasurements();
		$groups = [];
		foreach($measurements as $m){
			$time = $m->roundStartTime($seconds);
			$at = db_date($time);
			$groups[$at][] = $m->value;
		}
		$aggregated = [];
		foreach($groups as $at => $values){
			$aggregated[$at] = $this->aggregateValues($values);
		}
		return $this->aggregatedValues[$seconds] = $aggregated;
	}
	/**
	 * @param $timeAt
	 * @return QMMeasurement|null
	 */
	public function alreadyHaveData($timeAt): ?QMMeasurement{
		$secs = $this->getMinimumAllowedSecondsBetweenMeasurements();
		$roundedUnixtime = TimeHelper::roundToNearestXSeconds($timeAt, $secs);
		$at = db_date($roundedUnixtime);
		$m = $this->getMeasurementByStartAt($at);
		if(!$this->measurementsAreSet()){
			le('!$this->measurementsAreSet()');
		}
		return $m;
	}
	public function aggregateValues(array $values): float{
		if($this->isSum()){
			return Stats::sum($values);
		}
		return Stats::average($values);
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getInvalidMeasurements(): array{
		$this->getValidMeasurements();
		return $this->invalidMeasurements;
	}

	/**
	 * @return Measurement[]
	 */
	public function getMeasurements(): array{
		$arr = Measurement::fromDBModels($this->getQMMeasurements());
		$arr = QMMeasurement::indexMeasurementsByStartAt($arr);
		return $arr;
	}
	/**
	 * @param Measurement|QMMeasurement $m
	 */
	public function addMeasurement($m): void{
		$m = $m->getDBModel();
		$start = $m->getStartAt();
		$this->measurements[$start] = $m;
		$this->dailyMeasurements = null;
		$this->setDailyMeasurementsWithTags(null);
		$this->setValidDailyMeasurementsWithTagsAndFilling(null);
		$this->dailyValues = null;
		$this->dailyValuesWithTagsAndFilling = null;
		$this->measurementsInUserUnit = null;
		$this->measurementsWithTags = null;
		$this->values = null;
		ksort($this->measurements);
	}
	/**
	 * @param QMMeasurement|Measurement $m
	 */
	public function addSavedMeasurement($m): void{
		$m = $m->getDBModel();
		$this->savedMeasurements[$m->getStartAt()] = $m;
		Memory::addNewMeasurements([$m]);
		if($this->measurementsAreSet()){
			$this->addMeasurement($m);
		}
	}
	/**
	 * @param Measurement|QMMeasurement $m
	 */
	public function addNewMeasurement($m): void{
		$m = $m->getDBModel();
		$this->newMeasurements[$m->getStartAt()] = $m;
	}
	/**
	 * @param $params
	 * @return string
	 */
	private function getMeasurementDeletionUrl($params): string{
		return UrlHelper::getApiV3UrlForPath('measurements/delete?userId=' . $this->userId . '&variableId=' .
			$this->variableId . '&' . $params);
	}
	/**
	 * @param int $userId
	 * @param string|int $variableIdOrName
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function findInDatabaseByNameOrVariableId(int $userId, $variableIdOrName, array $params = [],
		array $newVariableData = []): ?QMUserVariable{
		if(!$variableIdOrName){$variableIdOrName = VariableIdProperty::nameOrIdFromRequest();}
		if(!$variableIdOrName){le("No variable name or id provided to getByNameOrIdIncludingGlobals");}
		if(is_int($variableIdOrName)){
			$l = UserVariable::findByNameOrId($userId, $variableIdOrName);
			if(!$l){return null;}
			$userVariable = $l->getDBModel();
			$userVariableWithUnitInNameIfDifferent = $userVariable->updatePropertiesIfNecessary($newVariableData);
			if($userVariableWithUnitInNameIfDifferent && isset($params['includeTags'])){
				$userVariableWithUnitInNameIfDifferent->getAllCommonAndUserTagVariableTypes();
			}
			return $userVariableWithUnitInNameIfDifferent;
		}
		return self::findByName($userId, $variableIdOrName, $newVariableData);
	}
	/**
	 * @param int $userId
	 * @param string|int|null $variableIdOrName
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function findUserVariableByNameIdOrSynonym(int $userId, $variableIdOrName, array $params = [],
		array $newVariableData = []): ?QMUserVariable{
		if(!$variableIdOrName){
			le("No variable name or id provided to getByNameOrIdIncludingGlobals");
		}
		if(is_numeric($variableIdOrName)){
			$variableIdOrName = (int)$variableIdOrName;
		}
		$uv = self::findByNameVariableIdOrSynonymInMemory($userId, $variableIdOrName);
		if(!$uv){
			$uv = self::findInDatabaseByNameOrVariableId($userId, $variableIdOrName, $params, $newVariableData);
		}
		if(!$uv && !is_int($variableIdOrName)){
			$uv = self::findBySynonym($userId, $variableIdOrName);
		}
		if($uv && isset($params['includeTags'])){
			$uv->getAllCommonAndUserTagVariableTypes();
		}
		return $uv;
	}
	/**
	 * @param int $userId
	 * @param string|int $idOrName
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function findByNameIdSynonymOrSpending(int $userId, $idOrName, array $params = [],
		array $newVariableData = []): ?QMUserVariable{
		$variable = self::findUserVariableByNameIdOrSynonym($userId, $idOrName, $params, $newVariableData);
		if(!$variable && !is_int($idOrName) && !VariableNameProperty::isSpending($idOrName)){
			$spendingName = VariableNameProperty::toSpending($idOrName);
			$variable = self::findUserVariableByNameIdOrSynonym($userId, $spendingName, $params, $newVariableData);
		}
		return $variable;
	}
	/**
	 * @param int $userId
	 * @param string|int $idOrName
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function findWithCharts(int $userId, $idOrName, array $params = [],
		array $newVariableData = []): ?QMUserVariable{
		$params[QMRequest::PARAM_INCLUDE_CHARTS] = true;
		return self::findByNameIdSynonymOrSpending($userId, $idOrName, $params, $newVariableData);
	}
	/**
	 * @param int $userId
	 * @param string|int $variableIdOrName
	 * @param array $params
	 * @param array $newVariableParams
	 * @return QMUserVariable
	 * @throws UserVariableNotFoundException
	 */
	public static function getByNameOrId(int   $userId, $variableIdOrName = null, array $params = [],
                                         array $newVariableParams = []): QMUserVariable{
		if(!$variableIdOrName){$variableIdOrName = VariableIdProperty::nameOrIdFromRequest();}
		if(!$variableIdOrName){le("No variable name or id provided to getByNameOrIdIncludingGlobals");}
		$uv = self::findByNameOrIdInMemory($userId, $variableIdOrName, $newVariableParams);
		if(!$uv){
			$uv = self::findInDatabaseByNameOrVariableId($userId, $variableIdOrName, $params, $newVariableParams);
			if(!$uv){throw new UserVariableNotFoundException($variableIdOrName, $userId);}
			if($userId !== $uv->userId){
				$uv = self::findInDatabaseByNameOrVariableId($userId, $variableIdOrName, $params, $newVariableParams);
				le("wrong user id");
			}
			$uv = self::findByNameOrIdInMemory($userId, $uv->variableId);
			if(!$uv){le("no variable from findByNameOrIdInMemory");}
		}
		if(isset($params['includeTags'])){
			$uv->validateUnit();
			$uv->getAllUserTagVariableTypes();
			$uv->getAllCommonTagVariableTypes();
			$uv->validateUnit();
		}
		$uv->validateUnit();
		return $uv;
	}
	/**
	 * @return bool
	 */
	public function inGlobalsWithDifferentMeasurements(): bool{
		$fromMemory = $this->getMeFromMemory();
		if($fromMemory && $fromMemory->measurements !== $this->measurements){
			$this->logError("Duplicate user variable in globals with different measurements: ".
			                $this->getTitleAttribute());
			return true;
		}
		return false;
	}
	/**
	 * Returns variable information specific for a user, not including joined variables.
	 * Use $variable->hasMeasurements() to see if a user has measurements for this variable.
	 * @param int $userId
	 * @param string|int $nameOrVariableId
	 * @param array $params
	 * @param array $newVariableParams
	 * @return QMUserVariable
	 */
	public static function findOrCreateByNameOrId(int $userId, $nameOrVariableId, array $params = [],
		array $newVariableParams = []): QMUserVariable{
		$newVariableParams['userId'] = $userId;
		if(is_string($nameOrVariableId)){
			$nameOrVariableId = rawurldecode($nameOrVariableId);
		}
		if(empty($nameOrVariableId)){
			le("No variableNameOrId provided");
		}
		try {
			$uv = self::getByNameOrId($userId, $nameOrVariableId, $params, $newVariableParams);
		} catch (UserVariableNotFoundException $e) {
			//$v = self::getByNameOrId($userId, $nameOrVariableId, $params, $newVariableParams);
			$uv = self::createByNameOrId($userId, $nameOrVariableId, $params, $newVariableParams);
		}
		if(!$uv->variableName){
			le('!$uv->variableName');
		}
		return $uv;
	}
	public function getUserUnitId(): int{
		return $this->userUnitId ?? $this->unitId;
	}
	/**
	 * @param int $userId
	 * @param string|int $variableNameOrId
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function findOrCreateByNameOrIdOrSynonym(int $userId, $variableNameOrId, array $params = [],
		array $newVariableData = []): QMUserVariable{
		$userVariable = self::findOrCreateByNameOrId($userId, $variableNameOrId, $params, $newVariableData);
		if(!$userVariable){
			$commonVariable = QMCommonVariable::findByNameIdOrSynonym($variableNameOrId);
			if($commonVariable){
				$userVariable = $commonVariable->findQMUserVariable($userId);
			}
		}
		return $userVariable;
	}
	/**
	 * @param int $userId
	 * @param string|int $variableNameOrId
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	private static function findByNameOrIdInMemory(int $userId, $variableNameOrId,
		array $newVariableData = []): ?QMUserVariable{
		if(is_string($variableNameOrId)){
			$fromMemory = self::findInMemoryByName($userId, $variableNameOrId);
		} else{
			$fromMemory = self::findInMemoryByVariableId($userId, $variableNameOrId);
		}
		// return $fromMemory; TODO: Uncomment me
		if(isset($fromMemory)){
			$fromMemory->validateUnit();
			$userVariableWithUnitInNameIfDifferent = $fromMemory->updatePropertiesIfNecessary($newVariableData);
			$userVariableWithUnitInNameIfDifferent->validateUnit();
			return $userVariableWithUnitInNameIfDifferent;
		}
		return null;
	}
	/**
	 * @param int $userId
	 * @param string|int $variableNameOrId
	 * @return QMUserVariable
	 */
	private static function findByNameVariableIdOrSynonymInMemory(int $userId, $variableNameOrId): ?QMuserVariable{
		$newVariableData = [];
		$fromMemory = self::findByNameOrIdInMemory($userId, $variableNameOrId);
		if(!$fromMemory && is_string($variableNameOrId)){
			$fromMemory = self::findByNameOrSynonymInMemory($userId, $variableNameOrId, $newVariableData);
		}
		return $fromMemory;
	}
	/**
	 * @param array $requestBody
	 */
	public function updateOutcomeIfNecessary(array $requestBody = []){
		if(!is_array($requestBody)){
			$requestBody = json_decode(json_encode($requestBody), true);
		}
		if(isset($requestBody[Variable::FIELD_OUTCOME]) &&
			$this->outcome !== $requestBody[Variable::FIELD_OUTCOME]){
			$this->updateDbRow([Variable::FIELD_OUTCOME => $requestBody[Variable::FIELD_OUTCOME]]);
			$this->setOutcome($requestBody[Variable::FIELD_OUTCOME]);
			$this->logError("Setting outcome to $this->outcome");
			$this->getCommonVariable()->updateDbRow([Variable::FIELD_OUTCOME => $this->getAndCheckOutcome()]);
		}
	}
	/**
	 * @param array|int $requestBody
	 */
	public function updateVariableCategoryIdIfNecessary($requestBody = []){
		if(!QMAuth::getQMUserIfSet()){
			return;
		} // Let's not allow connectors to update this
		if(is_int($requestBody)){
			$id = $requestBody;
		} else{
			$requestBody = APIHelper::replaceNamesWithIdsInArray($requestBody);
			if(!isset($requestBody['variableCategoryId'])){
				return;
			}
			$id = (int)$requestBody['variableCategoryId'];
		}
		$this->changeVariableCategory($id);
	}
	/**
	 * @param int $newVariableCategoryId
	 * @param string|null $reason
	 */
	public function changeVariableCategory(int $newVariableCategoryId, string $reason = null){
		if(!$newVariableCategoryId){return;}
		$originalId = $this->getUserVariable()->variable_category_id;
		if($originalId === $newVariableCategoryId){return;}
		$v = $this->getVariable();
		$common = $v->variable_category_id;
		if($newVariableCategoryId === $common){return;}
		$original = QMVariableCategory::findInMemoryOrDB($originalId);
		$new = QMVariableCategory::find($newVariableCategoryId);
		$this->logError("User changed category from $original to $new");
		$stupid = $original->isStupidCategory() && !$new->isStupidCategory();
		$admin = QMAuth::isAdmin();
		if($stupid || $admin){
			$this->getCommonVariable()->changeVariableCategory($new->getId());
		} else{
			$this->updateDbRow([self::FIELD_VARIABLE_CATEGORY_ID => $new->getId()], $reason);
			$this->getMeasurementsQb()->where(Measurement::FIELD_VARIABLE_CATEGORY_ID, $original->getId())
				->update([Measurement::FIELD_VARIABLE_CATEGORY_ID => $new->getId()]);
		}
		$this->userVariableVariableCategoryId = $new->getId();
		QMVariableCategory::addVariableCategoryNamesToObject($this);
	}
	/**
	 * @return QMQB
	 */
	public function getMeasurementsQb(): QMQB{
		return QMMeasurement::writable()->where(Measurement::FIELD_VARIABLE_ID, $this->variableId)
			->where(Measurement::FIELD_USER_ID, $this->userId);
	}
	/**
	 * Returns variable information specific for a user, not including joined variables.
	 * Use $variable->hasMeasurements() to see if a user has measurements for this variable.
	 * @param int $userId
	 * @param int|Variable $variableId
	 * @param array $params
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function getOrCreateById(int $userId, $variableId, array $params = [],
		array $newVariableData = []): QMUserVariable{
		if($variableId instanceof Variable){
			$variableId = $variableId->id;
		}
		$uv = self::findInDatabaseByNameOrVariableId($userId, $variableId, $params);
		if(!$uv){
			$uv = self::createOrUnDeleteById($userId, $variableId, $newVariableData);
		}
		if(isset($params['includeTags']) && $params['includeTags']){
			$uv->getAllCommonAndUserTagVariableTypes();
		}       
		return $uv;
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @param array|null $newVariableData
	 * @return mixed|QMUserVariable
	 */
	public static function getCreateOrUpdateUserVariableByVariableId(int $userId, int $variableId,
		array $newVariableData = null): QMUserVariable{
		$userVariable = self::findOrCreateByNameOrId($userId, $variableId, [], $newVariableData);
		$userVariableWithUnitInNameIfDifferent = $userVariable->updatePropertiesIfNecessary($newVariableData);
		return $userVariableWithUnitInNameIfDifferent;
	}
	/**
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public function updatePropertiesIfNecessary(array $newVariableData = []): QMUserVariable{
		$withUnitIfDifferent = $this;
		if($newVariableData){
			$withUnitIfDifferent = $this->generateNewVariableOrUpdateIfDifferentUnit($newVariableData);
			$withUnitIfDifferent->updateVariableCategoryIdIfNecessary($newVariableData);
			$withUnitIfDifferent->updateUpcIfNecessary($newVariableData);
			$withUnitIfDifferent->updateOutcomeIfNecessary($newVariableData);
			$withUnitIfDifferent->updateImageUrlIfNecessary($newVariableData);
			$withUnitIfDifferent->updateProductUrlIfNecessary($newVariableData);
			$withUnitIfDifferent->validateUnit();
		}
		return $withUnitIfDifferent;
	}
	/**
	 * Returns variable information specific for a user, not including joined variables.
	 * Use $variable->hasMeasurements() to see if a user has measurements for this variable.
	 * @param int $userId
	 * @param string $name
	 * @param array $newVariableData
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByName(int $userId, string $name, array $newVariableData = []){
		$uv = UserVariable::findByName($name, $userId);
		if(!$uv){
			return null;
		}
		$variableId = $uv->variable_id;
		$dbm = $uv->getDBModel();
		if($dbm->variableId !== $variableId){
			le('$dbm->variableId !== $variableId');
		}
		$unitInNameIfDifferent = $dbm->updatePropertiesIfNecessary($newVariableData);
		return $unitInNameIfDifferent;
	}
	/**
	 * @param int $userId
	 * @param string $synonym
	 * @return QMUserVariable
	 */
	private static function findBySynonym(int $userId, string $synonym): ?QMUserVariable{
		$v = Variable::findBySynonym($synonym);
		if(!$v){return null;}
		$uv = $v->findUserVariable($userId);
		if(!$uv){return null;}
		return $uv->getQMUserVariable();
	}
	/**
	 * @return int
	 */
	public function getMostCommonConnectorId(): ?int{
		if($id = $this->mostCommonConnectorId){
			return $id;
		}
		$id = $this->userVariableMostCommonConnectorId ?: $this->commonMostCommonConnectorId;
		if($id){
			$this->setMostCommonConnectorId($id);
		}
		return parent::getMostCommonConnectorId();
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getJoinedUserTagVariables(): array{
		if($this->joinedUserTagVariables === null){
			$this->setJoinedUserTagVariables();
		}
		return self::instantiateNonDBRows($this->joinedUserTagVariables);
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function setJoinedUserTagVariables(): array{
		$this->joinedUserTagVariables = [];
		if(!empty($this->userTagVariables) && !empty($this->userTaggedVariables)){
			foreach($this->userTagVariables as $tag){
				foreach($this->userTaggedVariables as $tagged){
					/** @noinspection TypeUnsafeComparisonInspection */
					if($tag->tagConversionFactor == 1 && $tagged->tagConversionFactor == 1 &&
						$tagged->variableId === $tag->variableId){
						$this->joinedUserTagVariables[] = $tag;
						$this->joinedUserTagVariableIds[] = $tag->variableId;
					}
				}
			}
		}
		return $this->joinedUserTagVariables;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getParentUserTagVariables(): array{
		if($this->parentUserTagVariables === null){
			$this->setIngredientAndParentUserTagVariables();
		}
		return self::instantiateNonDBRows($this->parentUserTagVariables);
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getChildUserTagVariables(): array{
		if($this->childUserTagVariables === null){
			$this->setIngredientOfAndChildUserTagVariables();
		}
		return self::instantiateNonDBRows($this->childUserTagVariables);
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getIngredientOfUserTagVariables(): array{
		if($this->ingredientOfUserTagVariables === null){
			$this->setIngredientOfAndChildUserTagVariables();
		}
		return self::instantiateNonDBRows($this->ingredientOfUserTagVariables);
	}
	private function setIngredientOfAndChildUserTagVariables(): void{
		$this->ingredientOfUserTagVariables = $this->childUserTagVariables = [];
		$ids = $this->setJoinedUserTagVariableIds();
		/** @var QMVariable $taggedVariable */
		foreach($this->userTaggedVariables as $taggedVariable){
			if(in_array($taggedVariable->getVariableIdAttribute(), $ids, true)){
				continue;
			}
			if($taggedVariable->tagConversionFactor == 1){
				$this->childUserTagVariables[] = $taggedVariable;
			} else{
				$this->ingredientOfUserTagVariables[] = $taggedVariable;
			}
		}
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getIngredientUserTagVariables(): array{
		if($this->ingredientUserTagVariables === null){
			$this->setIngredientAndParentUserTagVariables();
		}
		return self::instantiateNonDBRows($this->ingredientUserTagVariables);
	}
	private function setIngredientAndParentUserTagVariables(): void{
		$this->ingredientUserTagVariables = $this->parentUserTagVariables = [];
		$ids = $this->setJoinedUserTagVariableIds();
		$userTagVariables = $this->getUserTagVariables();
		foreach($userTagVariables as $tagVariable){
			if(in_array($tagVariable->getVariableIdAttribute(), $ids, true)){
				continue;
			}
			if((int)$tagVariable->tagConversionFactor === 1){
				$this->parentUserTagVariables[] = $tagVariable;
			} else{
				$this->ingredientUserTagVariables[] = $tagVariable;
			}
		}
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getAllUserTagVariableTypes(): array{
		$variables = [];
		$variables = array_merge($variables, $this->getUserTagVariables());
		$variables = array_merge($variables, $this->getUserTaggedVariables());
		$variables = array_merge($variables, $this->getJoinedUserTagVariables());
		$variables = array_merge($variables, $this->getIngredientOfUserTagVariables());
		$variables = array_merge($variables, $this->getIngredientUserTagVariables());
		$variables = array_merge($variables, $this->getParentUserTagVariables());
		$variables = array_merge($variables, $this->getChildUserTagVariables());
		return $variables;
	}
	public function setAllUserTagVariableTypes(): void{
		$this->setUserTagVariables();
		$this->setUserTaggedVariables();
		$this->setJoinedUserTagVariables();
		$this->setIngredientOfAndChildUserTagVariables();
		$this->setIngredientAndParentUserTagVariables();
	}
	/**
	 * Retrieve the oldest waiting user variable
	 * @return QMUserVariable
	 */
	public static function getWaitingUserVariableToUpdate(): ?QMUserVariable{
		$req = self::getWaitingUserVariableBaseRequest();
		$req->setConnectorNameIsNotIn([
			RescueTimeConnector::NAME,
			GoogleCalendarConnector::NAME,
		]);
		$variables = $req->setUserVariables();
		if(!isset($variables[0])){
			$req = self::getWaitingUserVariableBaseRequest();
			$variables = $req->setUserVariables();
		}
		return $variables[0] ?? null;
	}
	/**
	 * @return GetUserVariableRequest
	 */
	private static function getWaitingUserVariableBaseRequest(): GetUserVariableRequest{
		$req = new GetUserVariableRequest([], self::ALL_USERS);
		$req->setLimit(1);
		$req->setSort('updatedAt');
		$req->setDeletedAt(null);
		$req->setStatus(UserVariableStatusProperty::STATUS_WAITING);
		return $req;
	}
	/**
	 * @param int $userId
	 * @param $variableNameOrId
	 * @return QMUserVariable
	 * @throws TooSlowToAnalyzeException
	 */
	public static function analyzeByNameOrId(int $userId, $variableNameOrId): QMUserVariable{
		if(!is_int($variableNameOrId)){
			$variableId = VariableIdProperty::getIdByNameOrSynonym($variableNameOrId);
		} else{
			$variableId = $variableNameOrId;
		}
		if(!$variableId){
			le("Variable $variableNameOrId not found!");
		}
		return self::getOrCreateAndAnalyze($userId, $variableId);
	}
	/**
	 * @param int $userId
	 * @param int $variableId
	 * @param bool $force
	 * @return QMUserVariable
	 * @throws TooSlowToAnalyzeException
	 */
	public static function getOrCreateAndAnalyze(int $userId, int $variableId, bool $force = false): QMUserVariable{
		$params['clientId'] = __METHOD__;
		$v = self::getOrCreateById($userId, $variableId, $params);
		try {
			if($force){
				$v->setAlreadyAnalyzed(false);
			}
			$v->analyzeFully(__FUNCTION__, true);
		} catch (AlreadyAnalyzingException $e) {
			$v->logError(__METHOD__.": ".$e->getMessage());
		} catch (AlreadyAnalyzedException $e) {
			$v->logInfo(__METHOD__.": ".$e->getMessage());
		}
		return $v;
	}
	/**
	 * @param bool $alreadyAnalyzed
	 */
	public function setAlreadyAnalyzed(bool $alreadyAnalyzed): void{
		if($alreadyAnalyzed && $this->validDailyMeasurementsWithTagsAndFilling && $this->mean === null){
			le("mean should not be null! " . $this->getAnalyzeUrl());
		}
		$this->alreadyAnalyzed = $alreadyAnalyzed;
	}
	/**
	 * @param int $userId
	 * @param int $commonVariableId
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public static function createOrUnDeleteById(int $userId, int $commonVariableId,
		array $newVariableData = []): QMUserVariable{
		$uv = self::unDeleteIfNecessary($userId, $commonVariableId);
		if($uv){
			return $uv;
		}
		$newUserVariableDbInsertionArray = [
			'alias' => null,
			'cause_only' => null,
			'created_at' => date('Y-m-d H:i:s'),
			'duration_of_action' => null,
			'earliest_filling_time' => null,
			'filling_type' => null,
			'filling_value' => -1,
			'join_with' => null,
			'last_unit_id' => null,
			'last_value' => null,
			'latest_filling_time' => null,
			'maximum_allowed_value' => null,
			self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS => 0,
			self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 0,
			'minimum_allowed_value' => null,
			'most_common_value' => null,
			'number_of_correlations' => 0,
			'number_of_measurements' => null,
			'onset_delay' => null,
			'parent_id' => null,
			UserVariable::FIELD_IS_PUBLIC => null,
			'second_to_last_value' => null,
			'status' => UserVariableStatusProperty::STATUS_UPDATED,
			'third_to_last_value' => null,
			'user_id' => $userId,
			'variable_category_id' => null,
			'variable_id' => $commonVariableId,
			self::FIELD_DEFAULT_UNIT_ID => null,
			self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 0,
		];
		if(AppMode::isApiRequest()){
			$newVariableData[UserVariable::FIELD_CLIENT_ID] = BaseClientIdProperty::fromRequest(false);
		}
		foreach($newUserVariableDbInsertionArray as $key => $value){
			if(array_key_exists($key, $newVariableData)){
				if(is_object($newVariableData[$key]) || is_array($newVariableData[$key])){
					$newVariableData[$key] = json_encode($newVariableData[$key]);
				}
				$newUserVariableDbInsertionArray[$key] = $newVariableData[$key];
			}
		}
		$cv = Variable::findInMemoryOrDB($commonVariableId);
		if(!$cv){
			le("Could not find variable with id $commonVariableId!");
		}
		$cv->logInfo("Creating user variable for $cv->name");
        $uv = UserVariable::insert($newUserVariableDbInsertionArray);
		$uv = self::findInDatabaseByNameOrVariableId($userId, $commonVariableId);
		if(!isset($uv)){
			le("Could not get user variable we just created!");
		}
		$uv->createTrackingReminderForNewVariableIfNecessary($newVariableData);
		return $uv;
	}
	public function getOnsetDelay(): int{
		$delay = $this->onsetDelay;
		if($delay === null){
			$delay = $this->getAttributeFromVariableOrCategory(self::FIELD_ONSET_DELAY);
		}
		return $this->setOnsetDelay($delay);
	}
	public function getDurationOfAction(): int{
		$val = $this->durationOfAction;
		if($val === null){
			$val = $this->getAttributeFromVariableOrCategory(self::FIELD_DURATION_OF_ACTION);
		}
		return $this->setDurationOfAction($val);
	}
	/**
	 * @param array $newVariableData
	 * @return void
	 */
	private function createTrackingReminderForNewVariableIfNecessary(array $newVariableData): void{
		if(!$this->weShouldCreateReminderForNewVariables($newVariableData)){
			return;
		}
		$this->createTrackingReminder();
	}
	/**
	 * @param array $newVariableData
	 * @return bool
	 */
	public function weShouldCreateReminderForNewVariables(array $newVariableData): bool{
		if(!isset($newVariableData[self::CREATE_TRACKING_REMINDER_IF_NEW])){
			return false;
		}
		if(!$this->getManualTracking()){
			return false;
		}
		if(!($this->isTreatment() || $this->isEmotion() || $this->isSymptom() || $this->isFood())){
			return false;
		}
		$currentConnector = QMConnector::getCurrentlyImportingConnector();
		if($currentConnector && !$currentConnector->getCreateRemindersForNewVariables()){
			return false;
		}
		if($this->getOrCalculateNumberOfTrackingReminders()){
			return false;
		}
		if(stripos($this->getVariableName(), 'cardio') !== false){
			$this->logError("Why are we creating a cardio reminder?");
			return false;
		}
		return true;
	}
	/**
	 * @return TrackingReminder
	 */
	public function createTrackingReminder(): TrackingReminder{
		//QMAuthenticator::setUser($this->getUser());  // Why is this here?  It can have unintended consequences
		$obj = json_decode(json_encode($this), false);
		foreach($this as $key => $value){
			if(is_object($value)){
				$obj->$key = $value;
			}
		}
		//$obj->variableId = $obj->id;
		$obj->userVariableId = $obj->id;
		unset($obj->id);
		// What is the point of this? It overwrites real clients sometimes and adds no useful information
		// => if(!isset($obj->clientId)){$obj->clientId = BaseClientIdProperty::CLIENT_ID_QUANTIMODO;}
		$obj->userId = $this->getQMUser()->getId();
		$reminder = TrackingReminder::fromData($obj);
		$this->trackingReminders[] = $reminder->getDBModel();
		$this->numberOfTrackingReminders++;
		return $this->trackingReminder = $reminder;
	}
	/**
	 * @return UserVariableChartGroup
	 */
	public function setCharts(): ChartGroup{
		$charts = new UserVariableChartGroup($this);
		return $this->charts = $charts;
	}
	/**
	 * @return UserVariableChartGroup
	 * @throws NotEnoughMeasurementsException
	 */
	public function getOrSetHighchartConfigs(): ChartGroup{
		/** @var UserVariableChartGroup $charts */
		$charts = $this->getChartGroup();
		if($charts->highchartsPopulated()){
			return $charts;
		}
		$raw = $this->getMeasurementsWithTags();
		//$processed = $this->getAllProcessedDailyMeasurementsWithTagsJoinsChildrenInCommonUnit();
		$processed = $this->getProcessedMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit();
		if(!$raw && !$processed){
			throw new NotEnoughMeasurementsException($this,
				"There are no raw or generated $this->name measurements to create charts.");
		}
		$charts->getOrSetHighchartConfigs();
		return $this->charts = $charts;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	/**
	 * @param array $params
	 * @return GetMeasurementRequest
	 */
	public function getMeasurementRequest(array $params = []){
		$measurementRequest = $this->measurementRequest;
		if(!$measurementRequest){
			$measurementRequest = $this->setMeasurementRequest($params);
		}
		$measurementRequest->setQmUserVariable($this);
		return $this->measurementRequest = $measurementRequest;
	}
	/**
	 * @param array|GetMeasurementRequest $req
	 * @return GetMeasurementRequest
	 */
	public function setMeasurementRequest($req = []){
		if(is_array($req)){
			$req = new GetMeasurementRequest($req);
		}
		$req->setQmUserVariable($this);
		return $this->measurementRequest = $req;
	}
	/**
	 * @return string
	 */
	public function setValence(): ?string{
		if($this->userVariableValence){
			$this->valence = $this->userVariableValence;
		} elseif($this->commonVariableValence){
			$this->valence = $this->commonVariableValence;
		} else{
			parent::setValence();
		}
		return $this->valence;
	}
	/**
	 * Returns an array of all tag_variable variables of the given variable
	 * @return QMUserVariable[]
	 */
	private function setUserTagVariables(): array{
		$variables = [];
		$params['clientId'] = __METHOD__;
		$u = $this->getQMUser();
		$rows = $u->getUserTagRowsForTaggedVariableId($this->getVariableIdAttribute());
		foreach($rows as $tagRow){
			$userTagVariable = self::findOrCreateByNameOrId($this->userId, $tagRow->tag_variable_id, $params);
			if($userTagVariable){
				$userTagVariable->tagConversionFactor = $tagRow->conversion_factor;
				$this->setTagDisplayTextOnTagVariable($userTagVariable);
				$variables[] = clone $userTagVariable; // Avoid recursion on json_encode
			} else{
				QMLog::error('Could not find tagged variable by id', [
					'userTaggedVariableId' => $tagRow->tag_variable_id,
					'tag' => $tagRow,
				]);
			}
		}
		return $this->userTagVariables = $variables;
	}
	/**    Returns an array of all tag_variable variables of the given variable
	 * @return QMUserVariable[]
	 */
	public function getUserTagVariables(): array{
		$variables = $this->userTagVariables;
		if($variables === null){
			$variables = $this->setUserTagVariables();
		}
		foreach($variables as $key => $uv){
			$mem = static::findInMemory($uv->id);
			if($mem && $mem->measurements 
			   //&& count($mem->measurements) > count($uv->measurements)
			){
				$variables[$key] = $mem;
			}
		}
		$variables = self::instantiateNonDBRows($variables);
		return $this->userTagVariables = $variables;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getCommonAndUserTaggedVariables(): array{
		$unique = [];
		$commonTagVariables = $this->getCommonTaggedVariables();
		foreach($commonTagVariables as $v){
			if($v->tagConversionFactor === null){
				le("tagConversionFactor not set on $v");
			}
			$v->inGlobalsWithDifferentMeasurements();
			$unique[$v->variableId] = $v;
		}
		$userTagVariables = $this->getUserTaggedVariables(); // Overwrites common
		foreach($userTagVariables as $v){
			if($v->tagConversionFactor === null){
				le("tagConversionFactor not set on $v");
			}
			$v->inGlobalsWithDifferentMeasurements();
			$unique[$v->variableId] = $v;
		}
		return $unique;
	}
	/**
	 * @return QMUserVariable[]
	 */
	public function getUserTaggedVariables(): array{
		$tagged = $this->userTaggedVariables;
		if($tagged === false){
			return [];
		}
		if($tagged !== null){
			return self::instantiateNonDBRows($tagged);
		}
		return $this->setUserTaggedVariables();
	}
	/**
	 * @return array
	 */
	public function getUserTaggedVariableIds(): array{
		$tagged = $this->getUserTaggedVariables();
		$userTaggedVariableIds = [];
		foreach($tagged as $userTaggedVariable){
			$userTaggedVariableIds[] = $userTaggedVariable->getVariableIdAttribute();
		}
		$userTaggedVariableIds[] = $this->getVariableIdAttribute();
		return $userTaggedVariableIds;
	}
	/**
	 * @return QMUserVariable[]
	 */
	protected function setUserTaggedVariables(): array{
		$variableId = $this->getVariableIdAttribute();
		$userId = $this->getUserId();
		$qb = static::qb()
		            ->join(UserTag::TABLE, self::TABLE.'.'.self::FIELD_VARIABLE_ID, '=',
			UserTag::TABLE . '.' . UserTag::FIELD_TAGGED_VARIABLE_ID)
		            ->whereRaw(UserTag::TABLE . '.' .UserTag::FIELD_TAGGED_VARIABLE_ID . ' <> '.$variableId)
		            ->where(UserTag::TABLE.'.'.UserTag::FIELD_TAG_VARIABLE_ID, $variableId)
		            ->where(UserTag::TABLE.'.'.UserTag::FIELD_USER_ID, $userId)
		            ->whereNull(UserTag::TABLE . '.' .UserTag::FIELD_DELETED_AT)
		            ->where(self::TABLE . '.' . self::FIELD_USER_ID, $userId);
		QMUserTag::addColumns($qb);
		$rows = $qb->get()->all();
		return $this->userTaggedVariables = $rows;
	}
	/**
	 * @return array
	 */
	public function getDailyValuesWithTagsAndFilling(): array{
		$values = $this->dailyValuesWithTagsAndFilling;
		$measurements = $this->getValidDailyMeasurementsWithTagsAndFilling();
		if($values !== null){
			$num = count($values);
			if($num === count($measurements)){
				return $values;
			}
		}
		$values = [];
		foreach($measurements as $m){
			$values[$m->startAt] = $m->value;
		}
		$measurements = $this->measurements;
		if($measurements && !$values){
			$invalidMeasurements = $this->invalidMeasurements;
			if(count($invalidMeasurements) < count($measurements)){
				$measurements = $this->getValidDailyMeasurementsWithTagsAndFilling();
				le("no ".__FUNCTION__." but we have ".count($measurements)." measurements", $this);
			}
		}
		return $this->dailyValuesWithTagsAndFilling = $values;
	}
	/**
	 * @return array
	 */
	private function setJoinedUserTagVariableIds(): array{
		$this->joinedUserTagVariableIds = [];
		foreach($this->getJoinedUserTagVariables() as $variable){
			$this->joinedUserTagVariableIds[] = $variable->getVariableIdAttribute();
		}
		return $this->joinedUserTagVariableIds;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getProcessedMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit(): array{
		$measurements = $this->processedMeasurementsInUserUnit;
		if(!$measurements){
			$measurements = $this->getProcessedMeasurementsInUserUnit();
		}
		$req = $this->getMeasurementRequest();
		$unitName = $req->getUnitAbbreviatedName();
		if($unitName){
			try {
				return QMMeasurement::convertToProvidedUnit($measurements, $unitName);
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				/** @var RuntimeException $e */
				throw $e;
			}
		}
		return $this->processedMeasurementsInUserUnit = $measurements;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getProcessedMeasurementsInUserUnit(): array{
		$inCommonUnit = $this->getProcessedMeasurements();
		$InUserUnit = $this->convertMeasurementsToUserUnit($inCommonUnit);
		return $this->processedMeasurementsInUserUnit = $InUserUnit;
	}
	/**
	 * @param array|GetMeasurementRequest $req
	 * @return QMMeasurement[]
	 */
	public function getMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit($req): array{
		if($req){
			$this->measurementRequest = $req;
		}
		return $this->getProcessedMeasurementsWithTagsJoinsChildrenInUserOrProvidedUnit();
	}
	/**
	 * @return QMUnit
	 */
	public function getUserUnit(): ?QMUnit{
		if($id = $this->getUnitIdAttribute()){
			return QMUnit::getUnitById($id);
		}
		return null;
	}
	/**
	 * @param int $number
	 * @return int
	 */
	public function setNumberOfMeasurementsWithTagsAtLastAnalysis(int $number): int{
		return $this->numberOfRawMeasurementsWithTagsJoinsChildrenAtLastAnalysis = $number;
	}
	/**
	 * @return QMUserVariable[]
	 */
	private function getUserAndCommonTaggedVariables(): array{
		$user = $this->getUserTaggedVariables();
		$common = $this->getCommonTaggedVariables();
		$both = [];
		foreach($common as $v){
			$both[$v->variableId] = $v;
		}
		foreach($user as $v){
			$both[$v->variableId] = $v;
		}
		return $both;
	}
	/**
	 * @param QMMeasurement[] $measurements
	 * @return void
	 */
	public function setMeasurements(array $measurements): void{
		if(isset($measurements[0])){
			$measurements = QMMeasurement::indexMeasurementsByStartAt($measurements);
		}
		$this->measurements = $measurements;
	}
	/**
	 * @param AnonymousMeasurement[] $measurements
	 */
	public function updateFromMeasurements(array $measurements){
		$l = $this->l();
		$measurements = Measurement::fromDBModels($measurements);
		$l->updateFromMeasurements($measurements);
		$this->populateByLaravelModel($l);
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getValidMeasurements(): array{
		$measurements = $this->getQMMeasurements();
		$valid = [];
		$min = $this->getMinimumAllowedValueAttribute();
		$max = $this->getMaximumAllowedValueAttribute();
		foreach($measurements as $m){
			if($min !== null && $m->value < $min){
				$this->addInvalidMeasurement($m, "Value $m->value is below minimum allowed value $min");
				continue;
			}
			if($max !== null && $m->value > $max){
				$this->addInvalidMeasurement($m, "Value $m->value is above maximum allowed value $max");
				continue;
			}
			$valid[$m->startAt] = $m;
		}
		return $validMeasurements = $valid;
	}
	/**
	 * @return QMMeasurement[]
	 * @throws InsufficientMemoryException
	 */
	public function getQMMeasurements(): array{
		$m = $this->measurements;
		if($m !== null){
			return $m;
		}
		$req = $this->measurementRequest;
		$gettingAll = false;
		if(!$req){
			$req = $this->getAllMeasurementsRequest();
			$gettingAll = true;
		}
		$req->setUserId($this->getUserId());
		$req->setQmUserVariable($this);
		if($req->excludeExtendedProperties === null){
			$req->setExcludeExtendedProperties(true); // We should set this later if we need them because it uses too much memory
		}
		$number = $this->getNumberOfMeasurements();
		if($number > BaseNumberOfMeasurementsProperty::MAXIMUM_IN_API_REQUEST){
			if(AppMode::isApiRequest()){
				throw new InsufficientMemoryException("Not enough memory in web server to get $number $this->name " .
					"measurements. Maximum is " . BaseNumberOfMeasurementsProperty::MAXIMUM_IN_API_REQUEST);
			}
		}
		if($number > BaseNumberOfMeasurementsProperty::MAXIMUM_IN_JOB){
			throw new InsufficientMemoryException("Not enough memory in web server to get $number " .
				"measurements. Maximum is " . BaseNumberOfMeasurementsProperty::MAXIMUM_IN_JOB);
		}
		$measurements = $req->getMeasurementsInCommonUnit();
		$this->addVariableToMeasurements($measurements);
		$this->setMeasurements($measurements);
		if($gettingAll){
			try {
				$this->analyzeIfWrongNumberOfMeasurements($measurements);
			} catch (AlreadyAnalyzedException | TooSlowToAnalyzeException $e) {
				le($e);
			}
		}
		return $measurements;
	}
	/**
	 * @return bool
	 */
	public function measurementsAreSet(): bool{
		return $this->measurements !== null;
	}
	/**
	 * @return bool
	 */
	public function measurementsWithTagsAreSet(): bool{
		return $this->measurementsWithTags !== null;
	}
	/**
	 * @param float|null $secondToLastValueInCommonUnit
	 * @return float
	 */
	public function setSecondToLastValue(?float $secondToLastValueInCommonUnit): ?float{
		$this->secondToLastValue = $this->secondToLastValueInCommonUnit = $secondToLastValueInCommonUnit;
		$this->secondToLastValueInUserUnit = $this->toUserUnit($secondToLastValueInCommonUnit);
		return $this->secondToLastValueInCommonUnit;
	}
	/**
	 * @param float|null $thirdToLastValueInCommonUnit
	 * @return float
	 */
	public function setThirdToLastValue(?float $thirdToLastValueInCommonUnit): ?float{
		$this->thirdToLastValue = $this->thirdToLastValueInCommonUnit = $thirdToLastValueInCommonUnit;
		$this->thirdToLastValueInUserUnit = $this->toUserUnit($thirdToLastValueInCommonUnit);
		return $this->thirdToLastValueInCommonUnit;
	}
	/**
	 * @param float|null $lastValueInCommonUnit
	 * @return float
	 */
	public function setLastValue(?float $lastValueInCommonUnit): ?float{
		$this->lastValue = $this->lastValueInCommonUnit = $lastValueInCommonUnit;
		$this->lastValueInUserUnit = $this->toUserUnit($lastValueInCommonUnit);
		return $this->lastValueInCommonUnit;
	}
	/**
	 * Filter out not allowed measurements based on minimum amd maximum allowed values, return valid measurements
	 * @return RawQMMeasurement[]
	 */
	public function getFilteredMeasurements(): array{
		$filtered = $this->filteredMeasurementsWithTags;
		if($filtered){
			return $filtered;
		}
		$all = $this->getMeasurementsWithTags();
		$earliest = $this->getEarliestFillingAt();
		$latest = $this->getLatestFillingAt();
		$filtered = QMMeasurement::filter($all, $earliest, $latest);
		return $this->filteredMeasurementsWithTags = $filtered;
	}
	/**
	 * @return DailyMeasurement[]
	 */
	public function getValidDailyMeasurementsWithTagsAndFilling(): array{
		$withFilling = $this->validDailyMeasurementsWithTagsAndFilling;
		if($withFilling !== null){
			return $withFilling;
		}
		$daily = $this->getValidDailyMeasurementsWithTags();
		if(!$this->hasFillingValue()){
			$this->setValidDailyMeasurementsWithTagsAndFilling($daily);
			return $daily;
		}
		$startAt = $this->getEarliestFillingAt();
		if($startAt === null){
			return []; // No Measurements
		}
		$endAt = $this->getLatestFillingAt();
		$startTime = strtotime($startAt);
		$endTime = strtotime($endAt);
		$currentTime = $startTime;
		$unit = $this->getCommonUnit();
		while($currentTime < $endTime){
			$currentDate = TimeHelper::YYYYmmddd($currentTime);
			if(!isset($daily[$currentDate])){
				try {
					$this->fillerMeasurements[$currentDate] =
					$daily[$currentDate] = new FillerMeasurement($currentTime, $this, $unit);
				} catch (\Throwable $e) {
					$this->hasFillingValue();
					QMLog::info(__METHOD__.": ".$e->getMessage());
					$daily[$currentDate] = new FillerMeasurement($currentTime, $this, $unit);
				}
			}
			$currentTime += 86400;
		}
		ksort($daily);
		$this->setValidDailyMeasurementsWithTagsAndFilling($daily);
		return $daily;
	}
	/**
	 * Process and aggregate measurements
	 * Processing includes:
	 * - filtering out measurements outside the specified temporal range
	 * - filtering out measurements outside the specified value range
	 * - grouping (summing or averaging) measurements over the specified grouping width
	 * - replacing null values with the specified filling value, if specified
	 * - grouping repeated measurements to perform basic compression
	 * Note: processMeasurements method can work with empty list of measurements. In this case it will try
	 * to populate results with filling value and might return group measurements with filling values.
	 * @return ProcessedQMMeasurement[]|DailyMeasurement[]|QMMeasurement[]|RawQMMeasurement[]
	 * @deprecated
	 */
	public function getProcessedMeasurements(): array{
		$ungrouped = $this->getFilteredMeasurements();
		if(!$ungrouped){
			return [];
		}
		$groupingWidth = (int)QMRequest::getParam('groupingWidth');
		if(!$groupingWidth){
			$groupingWidth = $this->getMeasurementRequest()->groupingWidth;
		}
		if(!$groupingWidth){
			return $ungrouped;
		}
		$groupingIndex = 0;
		$earliestAt = $this->getEarliestFillingAt();
		$earliest = ($earliestAt) ? strtotime($earliestAt) : null;
		if($earliest){
			while($ungrouped[$groupingIndex]->startTime < $earliest){
				$groupingIndex++;
				if(!isset($ungrouped[$groupingIndex])){
					break;
				}
			}
		}
		if(!isset($ungrouped[$groupingIndex])){
			return [];
		}
		$allGrouped = [];
		$numberUngrouped = count($ungrouped);
		$latest = $this->getLatestFillingAt();
		$latestTime = strtotime($latest);
		for($groupStartTime = $earliest; $groupStartTime <= $latestTime; $groupStartTime += $groupingWidth){
			$group = [];
			$groupEndTime = $groupStartTime + $groupingWidth;
			for($i = $groupingIndex; $i < $numberUngrouped; $i++){
				$current = $ungrouped[$i];
				$startTime = $current->getOrSetStartTime();
				if($startTime < $groupStartTime){
					continue;
				}
				$duration = $current->getDuration();
				$measurementEnd = $startTime;
				if($duration){
					$measurementEnd = $startTime + $duration - 1;
				} // Need to add 1 or measurements with 86400 duration are filtered out
				if($measurementEnd >= $groupEndTime){
					//$measurementEndString = TimeHelper::getIso8601UtcDateTimeString($measurementEnd);
					//$current->logInfo("Excluding because measurement end time is less than group end time");
					break;
				}
				$group[] = $current;
			}
			$groupingIndex = $i;
			if($grouped = $this->getGroupedOrFillerMeasurement($groupStartTime, $group)){
				$allGrouped[] = $grouped;
			}
		}
		return $this->processedMeasurements = $allGrouped;
	}
	/**
	 * Calculate aggregated value for measurements
	 * @param QMMeasurement[] $group
	 * @param string $combination
	 * @return float
	 * @throw RuntimeException When an invalid combinationOperation is given.
	 */
	public static function aggregateMeasurements(array $group, string $combination){
		if(empty($group)){
			le("No measurements provided to aggregateMeasurements");
		}
		$sum = 0;
		foreach($group as $m){
			$sum += $m->value;
		}
		$combination = strtoupper($combination);
		if($combination == BaseCombinationOperationProperty::COMBINATION_SUM){
			return $sum;
		}
		if($combination == BaseCombinationOperationProperty::COMBINATION_MEAN){
			return $sum / count($group);
		}
		throw new RuntimeException(sprintf(QMMeasurement::ERROR_INVALID_COMBINATION_OPERATION, $combination));
	}
	/**
	 * @param AnonymousMeasurement $current
	 * @param AnonymousMeasurement|null $previous
	 */
	private static function measurementsHaveSameVariableNameAndStartTime(AnonymousMeasurement $current,
		?AnonymousMeasurement $previous){
		if(!$previous){
			return;
		}
		if($current->startTime === $previous->startTime && $previous->getTagVariableNameOrVariableName() ===
			$current->getTagVariableNameOrVariableName()){ // Not tag measurements
			le("2 $previous->variableName measurements with same start time!");
		}
	}
	/**
	 * @param int $groupStartTime
	 * @return QMMeasurement|null
	 */
	private function getFillerMeasurementIfNecessary(int $groupStartTime): ?QMMeasurement{
		if(!$this->hasFillingValue()){
			return null;
		}
		$fillingValue = $this->getFillingValueAttribute();
		$min = $this->userMinimumAllowedValueInCommonUnit;
		if($min && $min > $fillingValue){
			return null;
		}
		$raw = $this->getFilteredMeasurements();
		$filler = new ProcessedQMMeasurement($raw[0], $groupStartTime);
		$filler->value = $fillingValue;
		$filler->originalValue = null;
		$filler->originalUnitId = null;
		$filler->note = "Auto-generated filler measurement";
		return $filler;
	}
	/**
	 * @param int $groupStartTime
	 * @param ProcessedQMMeasurement[] $group
	 * @return QMMeasurement|null
	 */
	private function getGroupedOrFillerMeasurement(int $groupStartTime, array $group): ?QMMeasurement{
		if(empty($group)){
			return $this->getFillerMeasurementIfNecessary($groupStartTime);
		} elseif(count($group) === 1){
			return $group[0];
		} else{
			$raw = $this->getFilteredMeasurements();
			$grouped = new ProcessedQMMeasurement($raw[0], $groupStartTime);
			$req = $this->getMeasurementRequest();
			$grouped->value = self::aggregateMeasurements($group, $this->getOrSetCombinationOperation());
			if(!$req->excludeExtendedProperties){
				$grouped->addGroupedMeasurements($group);
			}
			return $grouped;
		}
	}
	/**
	 * @param int|null $groupingWidth
	 * @return ProcessedQMMeasurement[]
	 */
	public function getAllMeasurementsWithTagsJoinsChildrenGroupedByGroupingWidth(int $groupingWidth = null): array{
		if(!$groupingWidth){
			$groupingWidth = $this->getDurationOfAction();
		}
		$grouped = [];
		$combOp = $this->getOrSetCombinationOperation();
		if($groupingWidth > 86400){
			// TODO: Should we do this? $combOp = BaseCombinationOperationProperty::COMBINATION_MEAN;
			$ungrouped =
				$this->getValidDailyMeasurementsWithTagsAndFilling(); // Use daily so we filter out invalid measurements
			// Use filling so we have 0's when averaging $ungrouped = $this->getDailyMeasurementsWithTags();
			$ungrouped = array_values($ungrouped);
		} else{
			$ungrouped = $this->getMeasurementsWithTags();
		}
		if(!$ungrouped){
			if($this->measurements){
				le('$this->measurements');
			}
			return [];
		}
		if(!$ungrouped[0]){
			$ungrouped = $this->getMeasurementsWithTags();
			le("!$ungrouped[0]");
		}
		$currentMeasurementIndex = 0;
		$latestFillingAt = $this->getLatestFillingAt();
		$latestFilling = strtotime($latestFillingAt);
		$earliestFillingAt = $this->getEarliestFillingAt();
		$earliestFilling = strtotime($earliestFillingAt);
		for($groupStart = $earliestFilling; $groupStart <= $latestFilling; $groupStart += $groupingWidth){
			$measurementsInGroup = [];
			$groupEndTime = $groupStart + $groupingWidth;
			// Loop over all measurements that haven't been added to a group yet.
			for($i = $currentMeasurementIndex, $iMax = count($ungrouped); $i < $iMax; $i++){
				$m = $ungrouped[$i];
				$t = $m->startTime;
				if($t < $groupStart){
					//$groupStartAt = db_date($groupStart);
					continue;
				}
				// Don't use >= $groupEndTime or duration 86400 will get never return any measurements. Use > $groupEndTime
				if($t + $ungrouped[$i]->getDuration() > $groupEndTime){
					break;
				}
				$measurementsInGroup[] = $ungrouped[$i];
				$currentMeasurementIndex = $i;
			}
			$processedMeasurement = new ProcessedQMMeasurement($ungrouped[0], $groupStart);
			if(empty($measurementsInGroup) && $this->hasFillingValue()){
				$processedMeasurement->value = $this->getFillingValueAttribute();
				$grouped[] = $processedMeasurement;
			} elseif(empty($measurementsInGroup)){
				continue;
			} else{
				$processedMeasurement->value = self::aggregateMeasurements($measurementsInGroup, $combOp);
				$grouped[] = $processedMeasurement;
			}
		}
		if(!$grouped){
			if($ungrouped){
				$byDate = [];
				foreach($ungrouped as $m){
					$byDate[$m->getDate()] = $m;
				}
				$earlyFillingDate = TimeHelper::YYYYmmddd($earliestFilling);
				$latestFillingDate = TimeHelper::YYYYmmddd($latestFilling);
				$this->logError("No grouped measurements for grouping width $groupingWidth seconds between " .
					$earlyFillingDate . " and " . $latestFillingDate . "even though we have " . count($ungrouped) .
					" raw measurements!", $byDate);
			}
			return [];
		}
		return $grouped;
	}
	/**
	 * @param bool $instantiate
	 * @return QMQB
	 */
	public static function qb(bool $instantiate = true): QMQB{
		$qb = GetUserVariableRequest::qb();
		if($instantiate){
			$qb->class = self::class;
		}
		return $qb;
	}
	/**
	 * @return int[]
	 */
	public function getUserVariableIdsToCorrelateWith(): array{
		$ids = $this->userVariableIdsToCorrelateWith;
		if($ids !== null){
			return $ids;
		}
		UserVariableLatestTaggedMeasurementStartAtProperty::fixInvalidRecords();
        $userVariable = $this->getUserVariable();
        $qb = $userVariable->userVariableIdsToCorrelateWithQB();
        $ids = $qb->pluck(static::TABLE . '.id')->all();
        foreach ($ids as $i => $id) {
            $ids[$i] = (int)$id;
        }
        return $this->userVariableIdsToCorrelateWith = $ids;
	}
	/**
	 * @param $userVariableRelationships
	 */
	public function afterCorrelation($userVariableRelationships = null){
		$number = $this->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
		$this->updateDbRow([
			self::FIELD_STATUS => UserVariableStatusProperty::STATUS_UPDATED,
			self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN => $number,
			self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => $number,
			self::FIELD_UPDATED_AT => now_at(),
			self::FIELD_LAST_CORRELATED_AT => now_at(),
		]);
		if($userVariableRelationships){
			GoogleAnalyticsEvent::logEventToGoogleAnalytics("CalculatedUserVariableRelationshipsForVariable",
				$this->getVariableName(), count($userVariableRelationships), $this->getUserId(), $this->getClientId());
		}
	}
	/**
	 * Set status for given variable and user
	 * @param string $status
	 * @param array $values
	 * @param string|null $reason
	 * @param bool $reCorrelate
	 * @return bool
	 * @internal param $ [] $updateInfo
	 * @internal param int $variableId
	 * @internal param int $userId
	 */
	public function setStatusInDatabase(string $status, array $values = [], string $reason = null,
		bool $reCorrelate = false){
		$values[self::FIELD_INTERNAL_ERROR_MESSAGE] = $reason;
		if($reCorrelate){
			$values[self::FIELD_NUMBER_OF_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION] = 0;
			$this->setNumberOfMeasurementsWithTagsAtLastAnalysis(
				$values[self::FIELD_MEASUREMENTS_AT_LAST_ANALYSIS] = 0);
		}
		if(isset($this->status) && $status === $this->status){
			$this->logInfo("already set to $this->status ");
			return false;
		}
		$this->setStatus($values[self::FIELD_STATUS] = $status);
		if($status === UserVariableStatusProperty::STATUS_WAITING){
			self::logSetWaitingStatusToGoogleAnalytics($this->userId, $this->variableId, $reason);
		}
		if($status === UserVariableStatusProperty::STATUS_CORRELATE){
			$this->logErrorOrDebugIfTesting("Set status to CORRELATE");
		}
		$values[self::FIELD_UPDATED_AT] = date('Y-m-d H:i:s');
		$res = $this->updateDbRow($values);
		if($this->status !== $status){
			le("Status should be $status but is $this->status");
		}
		return $res;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		//$string = $this->name." ($this->getVariableId()) ";
		$string = $this->name ?? $this->variableName ?? "variable id: " . $this->variableId;
		if($this->userId){
			$u = $this->getQMUserFromMemory();
			if($u){
				//$string .= "for ".$this->getUser()->getLoginNameAndIdString();
				$string .= " for " . $this->getQMUser()->loginName;
			}
		}
		return $string;
	}
	/**
	 * @return bool
	 */
	public function hasEnoughMeasurementsToCorrelate(): bool{
		if($this->numberOfProcessedDailyMeasurements < 10){
			if(XDebug::active()){
				$meas = $this->calculateNumberOfProcessedDailyMeasurementsWithTagsJoinsChildren();
				if($meas !== $this->numberOfProcessedDailyMeasurements){
					le("What");
				}
			}
			$this->logErrorOrDebugIfTesting('There are not enough ProcessedDailyMeasurements to correlate! numberOfProcessedDailyMeasurements: ' .
				$this->numberOfProcessedDailyMeasurements);
			return false;
		}
		if($this->numberOfChanges < 4){
			$this->logErrorOrDebugIfTesting('There are not enough changes to correlate! numberOfChanges: ' .
				$this->numberOfChanges);
			return false;
		}
		return true;
	}
	/**
	 * @return QMQB
	 */
	public static function readonlyWithCommonVariableJoin(): QMQB{
		return QMCommonVariable::addJoinOnId(static::readonly(), static::TABLE, static::FIELD_VARIABLE_ID);
	}
	public function setMeasurementsSubtitle(){

	}
	public function setRemindersSubtitle(){

	}
	/**
	 * @return bool
	 */
	public function validateMean(): bool{
		try {
			$this->validateValueForCommonVariableAndUnit($this->meanInCommonUnit, 'MeanInCommonUnit');
			return true;
		} catch (InvalidVariableValueException $e) {
			$this->logError("Mean " . $this->meanInCommonUnit . " not valid");
			return false;
		}
	}
	/**
	 * @return string
	 */
	public function timeSinceLatestTaggedMeasurementHumanString(): string{
		return TimeHelper::timeSinceHumanString($this->getLatestTaggedMeasurementAt());
	}
	/**
	 * @return bool
	 */
	public function latestTaggedMeasurementMoreThanAMonthAgo(): bool{
		$latest = $this->calculateLatestTaggedMeasurementAt();
		return strtotime($latest) < time() - 30 * 86400;
	}

	/**
	 * @param QMQB $qb
	 */
	protected function addUniqueWhereClauses($qb){
		$qb->where(self::FIELD_USER_ID, $this->userId)->where(self::FIELD_VARIABLE_ID, $this->getVariableIdAttribute());
	}
	/**
	 * @return array
	 */
	public function getLastValuesInUserUnit(): array{
		if($this->isYesNo()){
			return $this->lastValuesInUserUnit = [(float)1, (float)0];
		}
		$inUserUnit = [];
		$last = $this->getLastValueInUserUnit();
		if($last !== null){
			$inUserUnit[] = $last;
		}
		$second = $this->getSecondToLastValueInUserUnit();
		if($second !== null){
			$inUserUnit[] = $second;
		}
		$third = $this->getThirdToLastValueInUserUnit();
		if($third !== null){
			$inUserUnit[] = $third;
		}
		$unique = $this->getNumberOfUniqueValues();
		if($unique < 10){
			if(isset($this->mostCommonValueInCommonUnit)){
				$val = $this->mostCommonValueInCommonUnit;
				$inUserUnit[] = $this->toUserUnit($val);
			}
			if(isset($this->secondMostCommonValueInCommonUnit)){
				$inUserUnit[] = $this->toUserUnit($this->secondMostCommonValueInCommonUnit);
			}
			if(isset($this->thirdMostCommonValueInCommonUnit)){
				$inUserUnit[] = $this->toUserUnit($this->thirdMostCommonValueInCommonUnit);
			}
		}
		if($this->isOneToFiveRating()){
			$inUserUnit[] = 1;
			$inUserUnit[] = 3;
			$inUserUnit[] = 5;
		}
		$inUserUnit = QMArr::uniqueFloats($inUserUnit);
		$u = $this->getUserUnit();
		if(count($inUserUnit) < 2 && $u->isCountCategory()){
			$inUserUnit[] = 1;
		}
		$zeroAllowed = !$this->valueInvalidForCommonVariableOrUnit(0, 'lastActionValue');
		if(!$this->isRating() && $zeroAllowed){ // No 0's for Ratings
			array_unshift($inUserUnit, 0);
		} elseif($zeroAllowed && $u->isCountCategory()){
			array_unshift($inUserUnit, 0);
		}
        $valid = $inUserUnit = QMArr::uniqueFloats($inUserUnit);
		//$valid = $this->validateActionValues($inUserUnit);
		return $this->lastValuesInUserUnit = $valid;
	}

	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setOneToFiveActionArray(): array{
		$previous = $actionArray = [];
		$values = $this->getLastValuesInUserUnit();
		$ratingValues = array_merge($values, QMVariable::ONE_TO_FIVE_RATING_VALUES);
		foreach($ratingValues as $value){
			if(!$value){
				$commonUnit = $this->getCommonUnit();
				$userUnit = $this->getUserUnit();
				$uvUrl = $this->getUrl();
				$vUrl = $this->getVariable()->getUrl();
				QMLog::exceptionIfNotProduction("$value not valid $userUnit. Common unit is $commonUnit.\n\t$uvUrl\n\t$vUrl");
				continue;
			}
			$rounded =
				round($value); // Use rounded so we don't return 3.5/5 rating for instance because there's no button for it
			if(in_array($rounded, $previous)){
				continue;
			}
			$previous[] = $rounded;
			$actionArray[] = new RatingNotificationButton($rounded, $this);
		}
		return $this->setActionButtons($actionArray);
	}
	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setOneToTenActionArray(): array{
		$actionArray = [];
		$ratingValues = array_unique(array_merge($this->getLastValuesInUserUnit(), self::ONE_TO_TEN_RATING_VALUES));
		foreach($ratingValues as $value){
			try {
				$this->getUserUnit()->validateValue($value);
				$actionArray[] = new NotificationButton(QMUnit::getUnitByAbbreviatedName("/10"), $value . '/10', $value,
					QMTrackingReminderNotification::TRACK, $this);
			} catch (InvalidVariableValueException $e) {
			}
		}
		return $this->setActionButtons($actionArray);
	}
	/**
	 * @return UserVariableRelationship
	 */
	public function getBestUserVariableRelationship(): ?UserVariableRelationship{
		$c = $this->bestUserVariableRelationship;
		if($c){
			return $c;
		}
		if($c === false){
			return null;
		}
		$c = $this->setBestUserVariableRelationship();
		if(!$c){
			$this->bestUserVariableRelationship = false;
			return null;
		}
		return $this->bestUserVariableRelationship = $c;
	}
	/**
	 * @return UserVariableRelationship
	 */
	public function setBestUserVariableRelationship(): ?UserVariableRelationship{
		if($id = $this->bestUserVariableRelationshipId){
			return $this->bestUserVariableRelationship = UserVariableRelationship::findInMemoryOrDB($id);
		}
		$c = false;
		if($this->isOutcome()){
			if($c = $this->getBestCorrelationAsEffect()){
				$this->setUserBestCauseVariableId($c->getCauseVariableId());
			}
		} else{
			if($primaryOutcome = $this->getUser()->getPrimaryOutcomeQMUserVariable()){
				$c = UserVariableRelationship::whereUserId($this->getUserId())
					->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->getVariableIdAttribute())
					->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $primaryOutcome->getVariableIdAttribute())->first();
			}
			if(!$c){
				$c = $this->getBestCorrelationAsCause();
			}
			if($c){
				$this->setUserBestEffectVariableId($c->getEffectVariableId());
			}
		}
		if(!$c){
			$this->bestUserVariableRelationship = false;
			return null;
		}
		return $this->bestUserVariableRelationship = $c;
	}
	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setLastValuesActionArray(): array{
		$buttons = [];
		$values = $this->getLastValuesInUserUnit();
		$u = $this->getUserUnit();
		if(array_key_exists(0, $values)){
			$buttons[] = new NotificationButton($u, NotificationButton::CALLBACK_trackLastValueAction, $values[0],
				QMTrackingReminderNotification::TRACK, $this);
		}
		if(array_key_exists(1, $values)){
			$buttons[] =
				new NotificationButton($u, NotificationButton::CALLBACK_trackSecondToLastValueAction, $values[1],
					QMTrackingReminderNotification::TRACK, $this);
		}
		if(array_key_exists(2, $values)){
			$buttons[] =
				new NotificationButton($u, NotificationButton::CALLBACK_trackThirdToLastValueAction, $values[2],
					QMTrackingReminderNotification::TRACK, $this);
		}
		if(count($buttons) === 0){
			$buttons[] = new MeasurementAddVariableStateButton($this);
		} elseif(count($buttons) === 1){
			$buttons[] = new MeasurementAddVariableStateButton($this, [], "Record Another Value or Note");
		}
		$buttons[] = new SnoozeNotificationButton($this);
		if(!$u->isRating()){
			$buttons[] = new SkipNotificationButton($this);
		}
		$buttons = QMArr::getUniqueByProperty($buttons, 'title');
		return $this->setActionButtons($buttons);
	}
	/**
	 * @return NotificationButton[] $actionArray
	 */
	public function setYesNoActionArray(): array{
		$actionArray = parent::setYesNoActionArray();
		$actionArray[] = new SnoozeNotificationButton($this);
		return $this->setActionButtons($actionArray);
	}
	/**
	 * @return NotificationButton[]
	 */
	public function getNotificationActionButtons(): array{
		if($buttons = $this->actionArray){
			return $buttons; // This is pretty slow so avoid duplication
		}
		if($this->isOneToFiveRating()){
			$buttons = $this->setOneToFiveActionArray();
		} elseif($this->isOneToTenRating()){
			$buttons = $this->setOneToTenActionArray();
		} elseif($this->isYesNoOrCountWithOnlyOnesAndZeros()){
			$buttons = $this->setYesNoActionArray();
		} elseif($this->isVitalSign()){
			$buttons = $this->setVitalSignsActionArray();
		} else{
			$buttons = $this->setLastValuesActionArray();
		}
		return $this->setActionButtons($buttons);
	}
	public function isVitalSign(): bool{
		return $this->getVariableCategoryId() === VitalSignsVariableCategory::ID;
	}
	public function setVitalSignsActionArray(): array{
		return $this->setActionButtons([
			new MeasurementAddVariableStateButton($this),
			new SnoozeNotificationButton($this),
			new SkipNotificationButton($this),
		]);
	}
	public function validateButtons(){
		if($this->unitId === ServingUnit::ID){
			$buttons = $this->getNotificationActionButtons();
			$values = collect($buttons)->map(function($b){
				/** @var NotificationButton $b */
				return $b->parameters['value'];
			})->all();
			if($this->unitId === ServingUnit::ID && !in_array(0, $values)){
				$this->exceptionIfNotProductionAPI("Serving unit should have a 0 option!");
			}
		}
	}
	/**
	 * @return NotificationButton[]
	 */
	public function getAboveBelowAverageButtons(): ?array{
		$above = $this->getMostCommonAboveAverageValue();
		if($above === null){
			return null;
		}
		$below = $this->getMostCommonBelowAverageValue();
		if($below === null){
			return null;
		}
		$buttons[] =
			new NotificationButton($this->getUserUnit(), NotificationButton::CALLBACK_trackLastValueAction, $below,
				QMTrackingReminderNotification::TRACK, $this);
		$buttons[] =
			new NotificationButton($this->getUserUnit(), NotificationButton::CALLBACK_trackSecondToLastValueAction,
				$above, QMTrackingReminderNotification::TRACK, $this);
		return $buttons;
	}
	/**
	 * @return QMUnit
	 */
	protected function getUserUnitOrFallbackToCommon(): QMUnit{
		$unitId = $this->getUserUnitIdOrFallbackToCommon();
		$unit = QMUnit::getByNameOrId($unitId);
		if(!$unit){
			$this->logError("Could not get unit for unit id: $unitId");
		}
		return $unit;
	}
	/**
	 * @return int
	 */
	private function getUserUnitIdOrFallbackToCommon(): int{
		$this->userUnitId ?: $this->setFallbackUserUnitId();
		if(!$this->userUnitId){
			le("No userUnitId!");
		}
		return $this->userUnitId;
	}
	/**
	 * @return int
	 */
	public function setFallbackUserUnitId(): int{
		$this->userUnitId = $this->getCommonUnitId();
		if(!$this->userUnitId){
			le("No common unit id!");
		}
		return $this->userUnitId;
	}
	/**
	 * @return QMQB
	 */
	public function commonTaggedQb(): QMQB{
		return QMCommonTag::readonly()->whereRaw(QMCommonTag::FIELD_TAGGED_VARIABLE_ID . ' <> ' .
			$this->getVariableIdAttribute())->where(QMCommonTag::FIELD_TAG_VARIABLE_ID, $this->getVariableIdAttribute())
			->where(self::TABLE . '.' . self::FIELD_USER_ID, $this->getUserId())->where(self::TABLE . '.' .
				self::FIELD_NUMBER_OF_MEASUREMENTS, ">", 0)->whereNull(QMCommonTag::TABLE . '.' .
				QMCommonTag::FIELD_DELETED_AT)->join(self::TABLE, self::TABLE . '.' . self::FIELD_VARIABLE_ID, '=',
				QMCommonTag::TABLE . '.' . QMCommonTag::FIELD_TAGGED_VARIABLE_ID);
	}
	/**
	 * @return array
	 * We can only get common tags that a user has matching user variables for or we'll try to get 1000 common
	 *     variables for Folic Acid every time we try to create a study with Folic Acid
	 */
	public function setCommonTaggedRows(): array{
		$qb = $this->commonTaggedQb();
		$rows = $qb->getArray();
		if(!$rows){
			$rows = [];
		}
		return $this->commonTaggedRows = $rows;
	}
	/**    Returns an array of all tag_variable variables of the given variable
	 * @return QMUserVariable[]
	 */
	public function setCommonTagVariables(): array{
		$this->verifyJsonEncodableAndNonRecursive();
		$tagVars = [];
		$rows = $this->setCommonTagRows();
		$this->verifyJsonEncodableAndNonRecursive();
		foreach($rows as $tagRow){
			try {
				// WE HAVE TO CREATE USER VARIABLES in UserVariable->setCommonTagVariables EVEN THOUGH IT'S SLOW SO THAT user variables will be created FOR INGREDIENTS ETC.
				$tagVariable = self::findOrCreateByNameOrId($this->userId, $tagRow->tag_variable_id);
			} catch (Exception $e) {
				$this->logError("Could create user variable for COMMON tag variable id " . $tagRow->tag_variable_id .
					" because " . $e->getMessage());
			}
			if(isset($tagVariable)){
				$tagVariable->verifyJsonEncodableAndNonRecursive();
				$tagVariable->tagConversionFactor = $tagRow->conversion_factor;
				$this->setTagDisplayTextOnTagVariable($tagVariable);
				if($fixRecursionInController = true){
					$tagVars[] = $tagVariable;
				} else{
					$tagVars[] = $tagVariable->cloneAndRemoveRecursion();
				}
			} else{
				$this->logError('Could not find tagged variable by id! tag row: ' . 
				                QMLog::var_export($tagRow, true));
			}
		}
		$this->verifyJsonEncodableAndNonRecursive();
		return $this->commonTagVariables = $tagVars;
	}
	/**
	 * Get correlation coefficients for all variables in database
	 * @return QMUserVariableRelationship[]
	 * @throws TooSlowToAnalyzeException
	 */
	public function calculateCorrelationsIfNecessary(): ?array{
		if($this->onsetDelay === null){
			le('$this->onsetDelay === null');
		}
		$this->logInfo("Last calculated all user_variable_relationships " . TimeHelper::timeSinceHumanString($this->lastCorrelatedAt));
		$this->getPHPUnitTestUrlForCorrelateAll();
		$this->analyzeFullyIfNecessary("we're seeing if we need to calculate all user_variable_relationships with this variable");
		$shouldCalculate = $this->weShouldCalculateCorrelations();
		if(!$shouldCalculate){
			$this->setStatusInDatabase(UserVariableStatusProperty::STATUS_UPDATED, []);
			return null;
		}
		return $this->correlate();
	}
	/**
	 * @return RootCauseAnalysisEmail
	 * @throws \App\Mail\TooManyEmailsException
	 * @throws \SendGrid\Mail\TypeException
	 */
	public function sendRootCauseAnalysis(): RootCauseAnalysisEmail{
		$analysis = new RootCauseAnalysis($this->getVariableIdAttribute(), $this->getUserId());
		$email = new RootCauseAnalysisEmail($analysis);
		$email->send();
		return $email;
	}
	/**
	 * @param float $targetValue
	 * @param float|null $minimum
	 * @param float|null $maximum
	 * @param int $groupingWidth
	 * @return float
	 * @throws NotEnoughMeasurementsException
	 */
	public function getNearestGroupedValue(float $targetValue, float $minimum = null, float $maximum = null,
		int $groupingWidth = 86400): ?float{
		$minDiff = 9999999999999999999999999999999;
		$groupedValues = $this->groupedValues[$groupingWidth] ?? null;
		if($groupedValues === null){
			$measurements = $this->getAllMeasurementsWithTagsJoinsChildrenGroupedByGroupingWidth($groupingWidth);
			if(!$measurements){
				throw new NotEnoughMeasurementsException($this, "No measurements to getNearestGroupedValue for $this");
			}
			$this->groupedValues[$groupingWidth] = $groupedValues = Arr::pluck($measurements, 'value');
		}
		$nearest = null;
		if($minimum !== null){
			$nearest = max($groupedValues);
		}
		if($maximum !== null){
			$nearest = min($groupedValues);
		}
		foreach($groupedValues as $groupedValue){
			if($minimum !== null && $groupedValue < $minimum){
				continue;
			}
			if($maximum !== null && $groupedValue > $maximum){
				continue;
			}
			$absDiff = abs($targetValue - $groupedValue);
			if($absDiff < $minDiff){
				$minDiff = $absDiff;
				$nearest = $groupedValue;
			}
		}
		return $nearest;
	}
	/**
	 * @return null|string
	 */
	private function getNewMeasurementsSourceName(): ?string{
		$first = $this->getFirstNewMeasurement();
		if(!$first){
			return null;
		}
		return $first->getOrSetSourceName();
	}
	private function getFirstNewMeasurement(): ?QMMeasurement{
		$new = $this->newMeasurements;
		if(!$new){
			return null;
		}
		/** @var QMMeasurement $item */
		$first = QMArr::first($new);
		return $first;
	}
	/**
	 * @return null|string
	 */
	private function getNewMeasurementsClientId(): ?string{
		$first = $this->getFirstNewMeasurement();
		if(!$first){
			return null;
		}
		return $first->getClientId();
	}
	/**
	 * @return array
	 */
	public function getDataSourcesCount(): array{
		$dsc = parent::getDataSourcesCount();
		$sourceName = $this->getNewMeasurementsSourceName();
		if($sourceName){
			$dsc[$sourceName] = isset($dsc[$sourceName]) ? $dsc[$sourceName]++ : 1;
		}
		$sourceName = $this->getNewMeasurementsClientId();
		if($sourceName){
			$dsc[$sourceName] = isset($dsc[$sourceName]) ? $dsc[$sourceName]++ : 1;
		}
		if(empty($dsc) && !empty($this->dataSources)){
			foreach($this->dataSources as $ds){
				$dsc[$ds->displayName] = 1;
			}
		}
		return $this->dataSourcesCount = $dsc;
	}
	/**
	 * @param int|null $connectorId
	 * @return array
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 * @throws \App\Exceptions\ModelValidationException
	 * @throws \App\Exceptions\NoChangesException
	 * @deprecated Use Measurement::upsertArray($combined);
	 */
	public function saveMeasurements(int $connectorId = null): array {
		$combined = $this->getCombinedNewQMMeasurements();
		if(!$combined || !count($combined)){
			$this->logInfo("No measurements to save!");
			return [];
		}
		$count = count($combined);
		$this->logInfo("Saving $count measurements for $this");
		foreach($combined as $i){
			$i->setUserVariable($this);
			if($connectorId){
				$i->setConnectorIdAndSourceName($connectorId);
			}
			if(!$i->clientId){
				try {
					$i->clientId =
						BaseClientIdProperty::fromRequest(false) ?? BaseClientIdProperty::CLIENT_ID_QUANTIMODO;
				} catch (UnauthorizedException $e) {
					le($e);
				}
			}
		}
		Measurement::upsert($combined);
		if(!$this->getDataSourcesCount()){
			le("!\$this->getDataSourcesCount()");
		}
		return $combined;
	}
	/**
	 * @return UserVariable
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function firstOrNewLaravelModel(){
		$l = parent::firstOrNewLaravelModel();
		//$l->variable_id = $this->getVariableId(); // Slow
		//if($userId = $this->userId){$l->user_id = $userId;}  // Slow
		return $l;
	}
	/**
	 * @param QMMeasurement $m
	 * @return bool
	 * @throws InvalidVariableValueAttributeException
	 */
	public function addToMeasurementQueueIfNoneExist(QMMeasurement $m): bool{
		try {
			$this->alreadyHaveMeasurementInThisTimeRange($m->getStartAt());
			$this->addToMeasurementQueue($m);
			return true;
		} catch (DuplicateDataException $e) {
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			return false;
		}
	}
	/**
	 * @param QMMeasurement $m
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 * @throws \App\Exceptions\NoChangesException
	 */
	public function saveMeasurementIfNoneExist(QMMeasurement $m): void{
		$new = $this->addToMeasurementQueueIfNoneExist($m);
		if($new){
			$this->saveMeasurements();
		}
	}
	/**
	 * @param bool $throwException
	 * @return bool
	 */
	public function userUnitIsCompatibleWithCommonUnit(bool $throwException = true): bool{
		if(AppMode::isProduction() && AppMode::isApiRequest()){
			$throwException = false;
		}
		$commonUnit = $this->getCommonUnit();
		if(!$commonUnit){
			$commonUnit = $this->getCommonUnit();
		}
		$userUnitId = $this->getUnitIdAttribute();
		if(!$userUnitId){
			return true;
		}
		$result = $commonUnit->isCompatibleWith($userUnitId);
		if(!$result){
			$userUnit = QMUnit::find($userUnitId);
			$result = $commonUnit->isCompatibleWith($userUnitId);
			$message = "User unit id " . $userUnit->abbreviatedName . " is not compatible with common unit " .
				$commonUnit->abbreviatedName;
			if(!$throwException){
				$this->logError($message);
			} else{
				le($message . " for " . $this->getVariableName());
			}
		}
		return $result;
	}
	public function unsetUserUnitIfItNotCompatibleWithCommonUnit(){
		$compatible = $this->userUnitIsCompatibleWithCommonUnit(false);
		if(!$compatible){
			$userUnit = $this->getUserUnit();
			$commonUnit = $this->getCommonUnit();
			$message =
				"user unit $userUnit->abbreviatedName is not compatible with common unit $commonUnit->abbreviatedName";
			try {
				$this->updateUserVariableAttribute(UserVariable::FIELD_DEFAULT_UNIT_ID, null);
			} catch (ModelValidationException $e) {
				le($e);
			}
			$this->logError($message);
			$this->setUserUnit($commonUnit->id);
		}
	}
	/**
	 * @param int $unitId
	 * @param string $reason
	 * @return void
	 */
	private function updateDefaultUnitId(int $unitId, string $reason): void{
		$this->logError("Changing user default unit because $reason");
		if($this->getCommonUnitId() === $unitId){
			$this->setUserUnit($unitId);
			$unitId = null;
		}
		$this->updateDbRow([self::FIELD_DEFAULT_UNIT_ID => $unitId], $reason);
	}
	/**
	 * @param array $body
	 * @return QMUserVariable
	 */
	public function generateNewVariableOrUpdateIfDifferentUnit(array $body): QMUserVariable{
		$unitFromRequest = BaseUnitIdProperty::pluckParentDBModel($body);
		if($unitFromRequest){
			if($this->unitIsIncompatible($unitFromRequest->id)){
				return $this->createNewCommonAndUserVariableWithUnitInName($body);
			}
			if(QMAuth::getQMUserIfSet() && $this->getUnitIdAttribute() !== $unitFromRequest->id){
				// Let's not allow connectors to change default unit
				if(AppMode::isApiRequest()){
					$this->updateDefaultUnitId($unitFromRequest->getId(),
						"Different unit was submitted in API request");
				}
				$updatedVariable = self::getOrCreateById(QMAuth::id(), $this->getVariableIdAttribute(), []);
				if(!$updatedVariable){
					le("No user variable returned from getOrCreateById");
				}
				return $updatedVariable;
			}
		}
		$this->validateUnit();
		return $this;
	}
	/**
	 * @return string
	 */
	public function setQuestion(): string{
		parent::setQuestion();
		if($this->isYesNoOrCountWithOnlyOnesAndZeros()){
			$when = $this->getWhen();
			return $this->question = $this->getOrSetVariableDisplayName() . "$when?";
		}
		return $this->question;
	}
	/**
	 * @param Surface|null $surface
	 * @return string
	 */
	public function getLongQuestion(Surface $surface = null): string{
		$question =
			$this->isYesNoOrCountWithOnlyOnesAndZeros() ? $this->getYesNoLongQuestion() : parent::getLongQuestion($surface);
		return $this->longQuestion = $question;
	}
	/**
	 * @return QMUserVariable
	 */
	public function getNonPaymentVariable(): QMUserVariable{
		$commonNonPayment = $this->getCommonVariable()->getOrCreateNonPaymentVariable();
		return $commonNonPayment->findQMUserVariable($this->userId);
	}
	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @return array
	 */
	private function getParamsForVariablesToCorrelateWIth(): array{
		$latestAt = $this->getLatestFillingAt();
		$latest = strtotime($latestAt);
		$earliestAt = $this->getEarliestFillingAt();
		$earliest = strtotime($earliestAt);
		$variableParameters = ['limit' => 0];
		$variableParameters['numberOfRawMeasurements'] = '(gt)' .
			(CorrelationCauseNumberOfRawMeasurementsProperty::MINIMUM_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN - 1);
		$variableParameters['numberOfProcessedDailyMeasurements'] = '(gt)' .
			(CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN -
				1);
		$variableParameters['numberOfChanges'] = '(gt)' . (CorrelationCauseChangesProperty::MINIMUM_CHANGES - 1);
		$variableParameters['numberOfUniqueDailyValues'] = '(gt)1';
		$variableParameters['earliestTaggedMeasurementAt'] =
			'(lt)' . db_date($latest - BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON / 2 * 86400);
		$minLatest = ($earliest + BaseNumberOfDaysProperty::MINIMUM_NUMBER_OF_DAYS_IN_COMMON / 2 * 86400);
		$variableParameters['latestTaggedMeasurementStartAt'] = '(gt)' . db_date($minLatest);
		if(!$this->isSymptom() &&
			!\App\Utils\Env::get('CALCULATE_WITH_VARIABLES_IN_SAME_CATEGORY')){ // i.e. Don't correlate emotions with emotions
			$variableParameters['variableCategoryId'] = '(ne)' . $this->variableCategoryId;
		}
		$variableParameters[Variable::FIELD_OUTCOME] = !$this->isOutcome();
		return $variableParameters;  // Don't need to correlate non-outcomes like medications with other non-outcomes like medications, for instance
	}
	/**
	 * @return TrackingReminder
	 */
	public function createReminderAndSetTrackingInstructions(): ?TrackingReminder{
		$this->setTrackingInstructionsHtml();
		$this->getTrackingInstructionCard();
		if(!$this->getManualTracking()){
			return null;
		}
		return $this->getOrCreateTrackingReminder();
	}
	public function getOrCreateTrackingReminder(): TrackingReminder {
		$l = $this->l();
		return $l->firstOrCreateTrackingReminder();
	}
	/**
	 * @param array $params
	 * @return self[]
	 */
	public static function get(array $params = []): array{
		if(!isset($params['userId'])){
			if(isset($params['id'])){
				$fromMemory = static::findInMemory($params['id']);
				if($fromMemory){
					return [$fromMemory];
				}
			}
			$qb = static::qb(true);
			QMDB::addWhereClauses($qb, $params, static::TABLE);
			/** @var QMUserVariable[] $variables */
			$variables = $qb->getArray();
			$variables = static::instantiateNonDBRows($variables);
			return $variables;
		}
		return self::getUserVariables($params['userId'], $params);
	}
	/**
	 * @return string
	 */
	public function getDataQuantityOrTrackingInstructionsHTML(): string{
		if($this->numberOfRawMeasurementsWithTagsJoinsChildren){
			return $this->getDataQuantityHTML();
		}
		return $this->getTrackingInstructionsHtml();
	}
	/**
	 * @return QMButton[]
	 */
	public function setDefaultButtons(): array{
		$buttons = $this->buttons ?: [];
		if(!is_array($buttons)){
			$buttons = [];
		}  // Probably Mongo cached format
		$buttons = array_merge($buttons, $this->getUserVariableButtons(true));
		$buttons = array_merge($buttons, $this->getCommonVariableButtons(false));
		$this->buttons = $buttons;
		return parent::setDefaultButtons();
	}
	/**
	 * @param bool $includeMeasurementButton
	 * @return QMButton[]
	 */
	public function getUserVariableButtons(bool $includeMeasurementButton): array{
		if(isset(self::$userVariableButtons[$this->getVariableIdAttribute()][$includeMeasurementButton])){
			return self::$userVariableButtons[$this->getVariableIdAttribute()][$includeMeasurementButton];
		}
		$buttons = [];
		if($this->getNumberOfMeasurements()){
			$buttons[] = new ChartsStateButton($this);
			$buttons[] = new HistoryAllVariableStateButton($this);
			$buttons[] = new VariableSettingsVariableNameStateButton($this);
		}
		if($includeMeasurementButton && $this->actionArray){
			$notificationButtons = $this->getNotificationActionButtons();
			if(!is_array($notificationButtons)){
				le("notificationButtons not an array!");
			}
			$buttons = array_merge($buttons, $notificationButtons);
		}
		return self::$userVariableButtons[$this->getVariableIdAttribute()][$includeMeasurementButton] = $buttons;
	}
	/**
	 * @return int
	 */
	public function getUserBestCauseVariableId(): ?int{
		return $this->userBestCauseVariableId;
	}
	/**
	 * @return mixed
	 */
	public function getUserBestEffectVariableId(): ?int{
		return $this->userBestEffectVariableId;
	}
	/**
	 * @return string
	 */
	public function setBestUserStudyLink(){
		$params = $this->getUserBestStudyParams();
		if(!$params){
			return $this->bestUserStudyLink = false;
		}
		return $this->bestUserStudyLink = StudyStateButton::getStudyUrl($params);
	}
	/**
	 * @return StudyCard
	 */
	public function getBestUserStudyCard(): StudyCard{
		if($this->bestUserStudyCard === null){
			$this->setBestUserStudyCard();
		}
		return $this->bestUserStudyCard;
	}
	/**
	 * @return QMUserStudy
	 */
	public function getBestUserStudy(): QMUserStudy{
		return $this->getBestUserVariableRelationship()->findInMemoryOrNewQMStudy();
	}
	/**
	 * @return false|StudyCard
	 */
	public function setBestUserStudyCard(){
		$correlation = $this->getBestUserVariableRelationship();
		if(!$correlation){
			return $this->bestUserStudyCard = false;
		}
		return $this->bestUserStudyCard = $correlation->getOptionsListCard();
	}
	/**
	 * @return string
	 */
	public function setBestStudyLink(){
		$bestPopulationStudyLink = $this->setBestPopulationStudyLink();
		$bestUserStudyLink = $this->setBestUserStudyLink();
		return $this->bestStudyLink = $bestUserStudyLink ?: $bestPopulationStudyLink;
	}
	/**
	 * @return StudyCard
	 */
	public function setBestStudyCard(): ?StudyCard{
		$card = $this->getBestUserStudyCard();
		if($card){
			return $this->bestStudyCard = $card;
		}
		$this->getBestUserStudyCard();
		return parent::setBestStudyCard();
	}
	/**
	 * @return array
	 */
	protected function getUserBestStudyParams(){
		$params['userId'] = $this->getUserId();
		if($this->isOutcome() && $this->getUserBestCauseVariableId()){
			$params = [
				'causeVariableId' => $this->getUserBestCauseVariableId(),
				'effectVariableId' => $this->getVariableIdAttribute(),
			];
		} elseif(!$this->isOutcome() && $this->getUserBestEffectVariableId()){
			$params = [
				'causeVariableId' => $this->getVariableIdAttribute(),
				'effectVariableId' => $this->getUserBestEffectVariableId(),
			];
		} elseif($this->getUserBestCauseVariableId()){
			$params = [
				'causeVariableId' => $this->getUserBestCauseVariableId(),
				'effectVariableId' => $this->getVariableIdAttribute(),
			];
		} elseif($this->getUserBestEffectVariableId()){
			$params = [
				'causeVariableId' => $this->getVariableIdAttribute(),
				'effectVariableId' => $this->getUserBestEffectVariableId(),
			];
		} else{
			$params = false;
		}
		return $params;
	}
	/**
	 * @param int $userBestCauseVariableId
	 */
	public function setUserBestCauseVariableId(int $userBestCauseVariableId){
		if($userBestCauseVariableId === $this->getVariableIdAttribute()){
			le("userBestCauseVariableId cannot equal variableId");
		}
		$this->userBestCauseVariableId = $userBestCauseVariableId;
	}
	/**
	 * @param int $userBestEffectVariableId
	 */
	public function setUserBestEffectVariableId(int $userBestEffectVariableId){
		$this->userBestEffectVariableId = $userBestEffectVariableId;
	}
	/**
	 * @param bool $ucFirst
	 * @return string
	 */
	public function getLatestTaggedMeasurementAtSentence(bool $ucFirst = true): string{
		$sentence =
			"the latest " . $this->name . " tagged measurement was " . $this->getLatestTaggedMeasurementAt() . ". ";
		if($ucFirst){
			$sentence = ucfirst($sentence);
		}
		return $sentence;
	}
	/**
	 * @return string
	 */
	public function getTaggedMeasurementRangeSentence(): string{
		$earliest = TimeHelper::YYYYmmddd($this->getEarliestTaggedMeasurementAt());
		$latest = TimeHelper::YYYYmmddd($this->getEarliestTaggedMeasurementAt());
		$number = $this->getNumberOfRawMeasurementsWithTagsJoinsChildren();
		return "There are $number $this->name measurements (including tagged) span between $earliest and $latest. ";
	}
	/**
	 * @param bool $ucFirst
	 * @return string
	 */
	public function getEarliestTaggedMeasurementAtSentence(bool $ucFirst = true): string{
		$sentence =
			"the earliest " . $this->name . " tagged measurement was " . $this->getEarliestTaggedMeasurementAt() . ". ";
		if($ucFirst){
			$sentence = ucfirst($sentence);
		}
		return $sentence;
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfUserVariableRelationshipsAsCause(): ?int{
		$number = $this->numberOfUserVariableRelationshipsAsCause;
		if($number === null){
			return $this->calculateNumberOfUserVariableRelationshipsAsCause();
		}
		return $this->numberOfUserVariableRelationshipsAsCause = $number;
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfUserVariableRelationshipsAsCause(): ?int{
		return UserVariableNumberOfUserVariableRelationshipsAsCauseProperty::calculate($this);
	}
	/**
	 * @return int
	 */
	public function getOrCalculateNumberOfUserVariableRelationshipsAsEffect(): ?int{
		$number = $this->numberOfUserVariableRelationshipsAsEffect;
		if($number === null){
			$number = $this->calculateNumberOfUserVariableRelationshipsAsEffect();
		}
		return $number;
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfUserVariableRelationshipsAsEffect(): int{
		return UserVariableNumberOfUserVariableRelationshipsAsEffectProperty::calculate($this);
	}

	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return Collection|UserVariableRelationship[]
	 */
	public function setUserVariableRelationshipsAsEffect(int $limit = null, string $variableCategoryName = null): ?Collection{
		$qb = $this->getUserVariable()->best_correlations_where_effect_user_variable()->with([
			'cause_variable',
			'cause_user_variable',
		])
			//->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, "<>", $this->getVariableId())
			->limit($limit);
		UserVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		foreach($correlations as $c){
			$c->setRelationAndAddToMemory('effect_variable', $this->getVariable());
			$c->setRelationAndAddToMemory('effect_user_variable', $this->getUserVariable());
		}
		return $this->userVariableRelationshipsAsEffect = $correlations;
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return UserVariableRelationship[]|Collection
	 */
	public function setUserVariableRelationshipsAsCause(int $limit = null, string $variableCategoryName = null): ?Collection{
		$qb = $this->getUserVariable()->best_correlations_where_cause_user_variable()->with([
			'effect_variable',
			'effect_user_variable',
		])->limit($limit);
		UserVariableRelationship::applyDefaultOrderings($qb);
		$correlations = $qb->get();
		foreach($correlations as $c){
			$c->setRelationAndAddToMemory('cause_variable', $this->getVariable());
			$c->setRelationAndAddToMemory('cause_user_variable', $this->getUserVariable());
		}
		if(!$correlations->count()){
			$numberOfMeasurements = $this->getNumberOfRawMeasurementsWithTagsJoinsChildren();
			if($numberOfMeasurements >
				CorrelationCauseNumberOfProcessedDailyMeasurementsProperty::MINIMUM_PROCESSED_DAILY_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN &&
				$this->isCause()){
				$this->logInfo("No User Variable Relationships As Cause even though we have $numberOfMeasurements measurements!");
			}
		}
		return $this->userVariableRelationshipsAsCause = $correlations;
	}
	/**
	 * @return QMMeasurement[]
	 */
	public function getMeasurementsIndexedByRoundedStartAt(): array{
		$byRounded = $this->measurementsIndexedByStartTimeRoundedToMinimumSecondsBetween;
		if($byRounded === null){
			$byRounded = [];
			$old = $this->getQMMeasurements();
			foreach($old as $m){
				$byRounded[$m->getRoundedStartAt()] = $m;
			}
		}
		$new = $this->combinedNewMeasurementItems ?: []; // Too slow to regenerate all the time
		foreach($new as $m){
			$byRounded[$m->getRoundedStartAt()] = $m;
		}
		return $this->measurementsIndexedByStartTimeRoundedToMinimumSecondsBetween = $byRounded;
	}
	/**
	 * @param int|string $timeAt
	 * @return void
	 * @throws DuplicateDataException
	 */
	public function alreadyHaveMeasurementInThisTimeRange($timeAt): void{
		$min = $this->getMinimumAllowedSecondsBetweenMeasurements();
		if($min){
			$rounded = Stats::roundToNearestMultipleOf(time_or_exception($timeAt), $min);
			$rounded = db_date($rounded);
		} else{
			$rounded = db_date($timeAt);
		}
		$existing = $this->getMeasurementsIndexedByRoundedStartAt();
		if(isset($existing[$rounded])){
			throw new DuplicateDataException("Already have $this measurement for rounded time " . $rounded .
				". Original time was $timeAt and min secs between is $min");
		}
	}
	/**
	 * @param QMVariable $commonTagVariable \
	 */
	private function throwExceptionAndDeleteTagVariableIfCategoryNotValid(QMVariable $commonTagVariable){
		try {
			QMCommonTag::validateTagCategories($this->getVariableIdAttribute(), $commonTagVariable->getVariableIdAttribute());
			return;
		} catch (InvalidTagCategoriesException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			$category = $this->getQMVariableCategory();
			$tagCategory = $commonTagVariable->getQMVariableCategory();
			if($commonTagVariable->getQMVariableCategory()->isStupidCategory()){
				$commonTagVariable->getCommonVariable()->changeVariableCategory($this->getQMVariableCategory()
					->getId());
				return;
			}
			$tagRow = QMCommonTag::getTagRow($commonTagVariable->getVariableIdAttribute(), $this->getVariableIdAttribute());
			$settings = $commonTagVariable->getVariableSettingsUrl();
			$commonVariable = $commonTagVariable->getCommonVariable();
			$deletionUrl = $commonVariable->getHardDeletionUrl("stupid tags");
			$taggedSettings = $this->getVariableSettingsUrl();
			$message = "$this->name
                    category $category
                    does not match
                    tag category $tagCategory
                    for $commonTagVariable->name
                    (tag created " . $tagRow->getTimeSinceCreatedAt() . ")
                $this->name Settings:
                    $taggedSettings
                You can delete this tag at $settings
                You can delete $commonVariable at $deletionUrl
                ";
			QMLog::error($message);
			$this->logVariableSettingsLink();
			$commonTagVariable->logVariableSettingsLink();
			if(QMCommonTag::stupidCategoryPairs($tagCategory->getNameAttribute(), $category->getNameAttribute())){
				//$commonTagVariable->deleteCommonVariableAndAllAssociatedRecords($message, true);
				QMCommonTag::delete($commonTagVariable->getVariableIdAttribute(), $this->getVariableIdAttribute(), $message);
			} else{
				le($message);
			}
		}
	}
	/**
	 * @param string $commonTagVariableName
	 * @param array $newVariableData
	 * @param float $conversionFactor
	 * @return void
	 */
	private function createCommonTagAndUserVariable(string $commonTagVariableName, array $newVariableData,
		float $conversionFactor): void{
		$tagCommonVariable = QMCommonVariable::findOrCreateByName($commonTagVariableName, $newVariableData);
		try {
			QMCommonTag::validateTagCategories($this->getVariableIdAttribute(), $tagCommonVariable->getVariableIdAttribute());
		} catch (InvalidTagCategoriesException $e) {
			le($e);
		}
		self::createOrUnDeleteById($this->userId, $tagCommonVariable->getVariableIdAttribute(), $newVariableData);
		try {
			QMCommonTag::updateOrInsert($this->getVariableIdAttribute(), $tagCommonVariable->getVariableIdAttribute(), $conversionFactor);
		} catch (InvalidTagCategoriesException $e) {
			le($e);
		}
		self::createOrUnDeleteById($this->userId, $tagCommonVariable->getVariableIdAttribute());
	}
	/**
	 * @param array $measurements
	 * @throws AlreadyAnalyzedException
	 * @throws TooSlowToAnalyzeException
	 */
	private function analyzeIfWrongNumberOfMeasurements(array $measurements): void{
		$actual = count($measurements);
		$stored = $this->getNumberOfMeasurements();
		$message = "$this: $actual actual measurements but stored NumberOfRawMeasurements is $stored!  " .
			"Why didnt we update number of measurements in post measurement save incremental user variable update?";
		if(($stored && !$actual) || ($stored && $actual !==
				$stored)){ // Can't use $actual !== $stored because we're sometimes getting filtered measurements here
			if(!$this->getQMUser()->isTestUser()){ // This is expected if we're deleting test measurements
				//$this->logAllMeasurementsIfDevelopment();
				$this->logError($message);
			}
			if($this->getStatus() !== UserVariableStatusProperty::STATUS_ANALYZING){
				try {
					$this->alreadyAnalyzed = false; // This is set true at beginning or correlation
					// But we don't want to throw AlreadyAnalyzedException if we deleted measurements
					$this->analyzeFully($message, true);
				} catch (AlreadyAnalyzingException $e) {
					$this->logInfo($e->getMessage() . " $message");
				}
				$newStored = $this->getNumberOfMeasurements();
				if(($newStored && !$actual) || ($newStored && $actual !== $newStored)){
					$this->logError("getNumberOfRawMeasurements wrong AFTER UPDATE!");
				}
			}
		}
	}
	/**
	 * @param int $userId
	 * @return QMUserVariable[]
	 */
	public static function fromMemoryWhereUserId(int $userId): array{
		$all = static::getAllFromMemoryIndexedById();
		return QMArr::whereUserId($userId, $all);
	}
	/**
	 * @param string $nameOrSynonym
	 * @return QMUserVariable
	 */
	public static function findInMemoryForAnyUserWhereNameOrSynonym(string $nameOrSynonym): ?QMUserVariable{
		$all = static::getAllFromMemoryIndexedById();
		foreach($all as $variable){
			if($variable->name === $nameOrSynonym){
				return $variable;
			}
			if($variable->inSynonyms($nameOrSynonym)){
				return $variable;
			}
		}
		return null;
	}
	/**
	 * @param $val
	 * @return bool
	 */
	public function inSynonyms($val): bool{
		return $this->getVariable()->inSynonyms($val);
	}
	/**
	 * @param int $id
	 * @return QMUserVariable
	 */
	public static function findInMemoryForAnyUser(int $id): ?QMUserVariable{
		$all = static::getAllFromMemoryIndexedByUuidAndId();
		foreach($all as $slug => $variable){
			if($variable->getVariableIdAttribute() === $id){
				return $variable;
			}
		}
		return null;
	}
	/**
	 * @return int
	 */
	protected function setSortingScore(){
		$score = parent::setSortingScore();
		if($score !== null){
			return $score;
		}
		$latest = $this->getLatestTaggedMeasurementAt();
		if($latest){
			if($reminders = $this->getNumberOfTrackingReminders()){
				$score = strtotime($latest) * $reminders;
			} else{
				$score = strtotime($latest);
			}
		}
		if(!$score){
			$score = $this->getNumberOfUserVariables();
		}
		return $this->sortingScore = $score;
	}
	/**
	 * @return UserVariableChartGroup
	 */
	public function getChartGroup(): ChartGroup{
		$charts = $this->charts;
		if(!$charts){
			$charts = $this->setCharts();
		}
		$charts = UserVariableChartGroup::instantiateIfNecessary($charts);
		$charts->setSourceObject($this);
		if($this->measurementsAreSet()){
			$charts->getOrSetHighchartConfigs();
		}
		return $this->charts = $charts;
	}
	/**
	 * @param int $id
	 * @return int
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function setId($id){
		return $this->id = $this->userVariableId = $id;
	}
	/**
	 * @return string
	 */
	public function getPHPUnitJobTest(): ?string{
		if(AppMode::isAnyKindOfUnitTest()){
			return null;
		} // Pointless
		return $this->getPHPUnitTestUrl();
	}
	/**
	 * @param string|array $fieldName
	 * @param mixed $value
	 * @param int|null $limit
	 * @param int|null $offset
	 * @param QMQB|null $qb
	 * @return static[]
	 */
	public static function qmWhere($fieldName, $value = null, int $limit = null, int $offset = null,
		QMQB $qb = null): array{
		if(!$qb){
			$qb = GetUserVariableRequest::qb();
		}
		return parent::qmWhere($fieldName, $value, $limit, $offset, $qb);
	}
	/**
	 * @param array $rows
	 * @param bool $setDbRow
	 * @return static[]|bool
	 */
	protected static function convertRowsToModels(array $rows, bool $setDbRow): array{
		$models = [];
		foreach($rows as $row){
			if($global = self::findInMemoryByVariableId($row->userId, $row->variableId)){
				$models[] = $global;
			} else{
				$model = static::convertRowToModel($row, $setDbRow);
				$models[] = $model;
			}
		}
		return $models;
	}
	/**
	 * @return stdClass
	 */
	public function getCommonAdditionalMetaData(): stdClass{
		return $this->commonAdditionalMetaData = ObjectHelper::toStdClassIfNecessary($this->commonAdditionalMetaData);
	}
	/**
	 * @return stdClass
	 */
	public function getUserAdditionalMetaData(): stdClass{
		return $this->userAdditionalMetaData = ObjectHelper::toStdClassIfNecessary($this->userAdditionalMetaData);
	}
	/**
	 * @return float
	 */
	public function getMostCommonAboveAverageValue(): ?float{
		$values = $this->mostCommonValueInUserUnit;
		$avg = $this->meanInUserUnit;
		foreach($values as $value){
			if($value > $avg){
				return $value;
			}
		}
		return null;
	}
	/**
	 * @return float
	 */
	public function getMostCommonBelowAverageValue(): ?float{
		$values = $this->meanInCommonUnit;
		$avg = $this->meanInUserUnit;
		foreach($values as $value){
			if($value < $avg){
				return $value;
			}
		}
		return null;
	}
	/**
	 * @param QMUserVariable $cause
	 * @param QMUserVariable $effect
	 * @return QMUserVariableRelationship
	 */
	private function tryToCalculateForPair(QMUserVariable $cause, QMUserVariable $effect): ?QMUserVariableRelationship{
		$c = new QMUserVariableRelationship(null, $cause, $effect);
		try {
			$c->analyzeFullyAndSave(__FUNCTION__);
			return $c;
		} catch (ModelValidationException | TooManyMeasurementsException $e) {
			$this->errorOrLogicExceptionIfTesting($e->getMessage() . "\n" . $c->getUrl());
		} catch (NotEnoughDataException $e) {
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			return null;
		} catch (TooSlowToAnalyzeException | DuplicateFailedAnalysisException | StupidVariableNameException $e) {
			le($e);
		} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
			$this->infoOrLogicExceptionIfTesting(__METHOD__.": ".$e->getMessage());
		}
		le($e);
		/** @var LogicException $e */
		throw $e;
	}
	/**
	 * @param array $valuesInUserUnit
	 * @return array
	 */
	protected function validateActionValues(array $valuesInUserUnit): array{
		$valid = [];
		foreach($valuesInUserUnit as $value){
			try {
				$inCommonUnit = $this->toCommonUnit($value);
			} catch (IncompatibleUnitException | InvalidVariableValueException $e) {
				$this->logErrorOrInfoIfTesting($e->getProblemAndSolutionString());
				continue;
			}
			if($message = $this->valueInvalidForCommonVariableOrUnit($inCommonUnit, 'actionValue')){
				$charts = $this->getIonicChartsUrl([], true);
				$astral = $this->getDataLabShowUrl();
				$this->logError("INVALID LAST ACTION VALUE: " . $message . "
                    $charts
                    $astral
                ");
			} else{
				$valid[] = $value;
			}
		}
		return $valid;
	}
	public function getIonicChartsUrl(array $params = [], bool $forAdmin = false):string{
		return $this->getUserVariable()->getIonicChartsUrl($params, $forAdmin);
	}
	/**
	 * @param float $inUserUnit
	 * @return float|int|null
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 */
	public function convertToCommonUnit(float $inUserUnit): float{
		return QMUnit::convertValueByUnitIds($inUserUnit, $this->getUserUnitId(), $this->getCommonUnitId(), $this, null);
	}
	/**
	 * @param float $inCommonUnit
	 * @return float|int|null
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function convertToUserUnit(float $inCommonUnit): float{
		return QMUnit::convertValueByUnitIds($inCommonUnit, $this->getCommonUnitId(), $this->getUserUnitId(), $this, null);
	}
	/**
	 * @param $row
	 */
	protected static function setNumberOrUniqueValuesOnRow($row){
		$commonUnique = $row->commonNumberOfUniqueValues ?? null;
		$userUnique = $row->userNumberOfUniqueValues ?? null;
		$number = ($commonUnique > $userUnique) ? $commonUnique : $userUnique;
		if($number){
			$row->numberOfUniqueValues = $number;
		}
	}
	/**
	 * @param string $newValence
	 * @throws InvalidAttributeException
	 */
	public function updateValenceIfNecessary(string $newValence){
		if(!$this->isRating()){
			try {
				BaseValenceProperty::validateByValue($newValence, $this->l());
			} catch (InvalidAttributeException $e) {
				if(AppMode::isApiRequest()){ // TODO: Stop returning everything when posting tracking reminders
					$this->logDebug(__METHOD__.": ".$e->getMessage());
				} else{
					throw $e;
				}
			}
			return;
		}
		$arr = ['valence' => $newValence];
		$u = $this->getQMUser();
		if($u->isAdmin()){
			$previousCommonValence = $this->commonVariableValence;
			if(!$previousCommonValence){
				$cv = $this->getCommonVariable();
				$cv->updateValence($newValence, "Admin set valence when we did not have one previously");
			} elseif($previousCommonValence !== $newValence){
				$cv = $this->getCommonVariable();
				$cv->updateValence($newValence, "Admin changed valence to $newValence");
			}
		}
		$previousUserValence = $this->getValence();
		if(!$previousUserValence){
			$this->updateDbRow($arr, "User set valence to $newValence because we did not have a valence previously");
		} elseif($previousUserValence !== $newValence){
			$this->updateDbRow($arr, "User changed user valence to $newValence from $previousUserValence");
		}
	}
	/**
	 * @param string $reason
	 * @param bool $countFirst
	 * @return int|void
	 */
	public function hardDeleteWithRelations(string $reason, bool $countFirst = false): int{
		$l = $this->l();
		$l->user_variable_clients()->forceDelete();
		$tables = BaseModel::getAllTablesWithColumn([self::FIELD_VARIABLE_ID, self::FIELD_USER_ID]);
		foreach($tables as $table){
			if($table === self::TABLE){
				continue;
			}
			$result = Writable::getBuilderByTable($table)->where(self::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
			$this->logInfo("Deleted $result $table records...");
		}
		$causeEffectTables = [UserVariableRelationship::TABLE, Vote::TABLE];
		foreach($causeEffectTables as $table){
			$result = Writable::getBuilderByTable($table)
				->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
			$this->logInfo("Deleted $result $table records...");
			$result = Writable::getBuilderByTable($table)
				->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
			$this->logInfo("Deleted $result $table records...");
		}
		$result = QMStudy::writable()->where(Study::FIELD_CAUSE_VARIABLE_ID, $this->getVariableIdAttribute())
			->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
		$this->logInfo("Deleted $result study records...");
		$result = QMStudy::writable()->where(Study::FIELD_EFFECT_VARIABLE_ID, $this->getVariableIdAttribute())
			->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
		$this->logInfo("Deleted $result study records...");
		$l->user_tags_where_tag_user_variable()->forceDelete();
		$l->user_tags_where_tagged_user_variable()->forceDelete();
		$result = self::writable()->where(self::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
			->where(self::FIELD_USER_ID, $this->getUserId())->hardDelete($reason, $countFirst);
		$this->logInfo("Deleted $result user variable records...");
		return $result;
	}
	public function getSourceDataUrl(): string{
		return Measurement::generateDataLabIndexUrl([
			Measurement::FIELD_USER_VARIABLE_ID => $this->getUserVariableId(),
		]);
	}
	/**
	 * @return int
	 */
	public function calculateNumberOfRawMeasurementsWithTagsFromNumberOfMeasurementsPropertyOnTaggedVariables(): int{
		$tags = $this->getUserAndCommonTaggedVariables();
		$calculatedWithTags = $this->getNumberOfMeasurements() ?: 0;
		foreach($tags as $tag){
			$calculatedWithTags += $tag->getNumberOfMeasurements();
		}
		return $calculatedWithTags;
	}
	/**
	 * @param QMMeasurement $m
	 * @throws InvalidVariableValueAttributeException
	 */
	public function addToMeasurementQueue(QMMeasurement $m): void{
		$m->updatedAt = now_at();
		$m->setUserVariable($this);
		$this->logInfo("Adding $m from " . TimeHelper::timeSinceHumanString($m->getOrSetStartTime()));
		try {
			$m->validateValue();
		} catch (InvalidVariableValueAttributeException $e) {
			if(!$this->skipInvalidZeroValueMeasurements($m->value ?? $m->originalValue)){
				throw $e;
			}
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			return;
		} catch (InvalidAttributeException $e) {
			le($e);
		}
		$this->combinedNewMeasurementItems = null; // Avoid staleness (we cache combined measurements for performance)
		$this->addNewMeasurement($m);
	}
	public function analyzeFullyAndSave(string $reason): void{
		$this->analyzeFully($reason);
	}
	/**
	 * @param array $arr
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 */
	public function addMultipleMeasurementsToQueue(array $arr): void{
		foreach($arr as $m){
			if(!$m instanceof QMMeasurement){
				$l = $this->newMeasurement($m);
				$m = $l->getDBModel();
			}
			$this->addToMeasurementQueue($m);
		}
	}
	/**
	 * @param array $arr
	 * @return int
	 * @throws \App\Exceptions\IncompatibleUnitException
	 * @throws \App\Exceptions\InvalidVariableValueException
	 * @throws \App\Exceptions\NoChangesException
	 */
	public function saveMultipleMeasurements(array $arr): array {
		$this->addMultipleMeasurementsToQueue($arr);
		return $this->saveMeasurements();
	}
	/**
	 * @return array
	 */
	public static function getAnalysisSettingsFields(): array{
		$fields = parent::getAnalysisSettingsFields();
		$fields[] = self::FIELD_EXPERIMENT_END_TIME;
		$fields[] = self::FIELD_EXPERIMENT_START_TIME;
		return $fields;
	}
	/**
	 * @return bool
	 */
	public function needToAnalyze(): bool{
		return parent::needToAnalyze();
	}
	/**
	 * @return string|null
	 */
	public function getAnalysisSettingsModifiedAt(): ?string{
		return $this->analysisSettingsModifiedAt ?? null; // Overriding parent QMAnalyzable method because it's too
		// slow to get sourceObjects
	}
	/**
	 * @return GetMeasurementRequest
	 */
	private function getAllMeasurementsRequest(): GetMeasurementRequest{
		$req = new GetMeasurementRequest();
		$req->setDoNotConvert(true);
		$req->setDoNotProcess(true);
		$req->setExcludeExtendedProperties(true);
		$req->setLimit(0);
		$req->setSort('startTime');
		return $req;
	}
	/**
	 * @param QMMeasurement[] $measurements
	 */
	private function addVariableToMeasurements(array $measurements): void{
		$lastMeasurement = null;
		foreach($measurements as $m){
			$m->setUserVariable($this);
			QMUserVariable::measurementsHaveSameVariableNameAndStartTime($m, $lastMeasurement);
			$lastMeasurement = $m;
			$m->setUserId($this->getUserId());
			//$measurement->unsetNullProperties();  Why is this necessary?  it Breaks stuff. If you need it, you should have a simpler parent model
		}
	}
	/**
	 * @param string $message
	 */
	public function slack(string $message){
		UserVariableAnalysisJob::slack($this . ": $message");
	}
	/**
	 * @param string $predictorVariableCategoryName
	 * @param int $limit
	 * @return Collection|UserVariableRelationship[]
	 */
	protected function setCorrelationsForPredictorCategory(string $predictorVariableCategoryName,
		int $limit = 0): Collection{
		$byLimit = $this->correlationsForPredictorCategory[$predictorVariableCategoryName] ?? [];
		foreach($byLimit as $existingLimit => $correlations){
			if($existingLimit >= $limit){
				return $correlations->take($limit);
			}
		}
		$l = $this->l();
		$qb = $l->correlations_where_effect_user_variable();
		$qb->with([
			'cause_variable',
			'cause_user_variable',
		]);
		$cat = QMVariableCategory::find($predictorVariableCategoryName);
		$qb->where(UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_CAUSE_VARIABLE_CATEGORY_ID, $cat->id);
		if($limit){
			$qb->limit($limit);
		}
		$qb->whereNotNull(UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ANALYSIS_ENDED_AT);
		$correlations = $qb->get();
		return $this->correlationsForPredictorCategory[$predictorVariableCategoryName][$limit] = $correlations;
	}
	public function logTimes(){
		$ats = [];
		foreach($this as $key => $value){
			if(stripos($key, 'time') !== false){
				if(is_int($value)){
					$ats[$key] = db_date($value);
				} else{
					$ats[$key] = $value;
				}
			}
		}
		QMLog::logKeyValueArray($ats, "Times for $this");
	}
	/**
	 * @param bool $isPredictor
	 * @param string $userDisplayName
	 * @param string $displayName
	 * @return string
	 */
	public static function generateVariableDescription(bool $isPredictor, string $userDisplayName,
		string $displayName): string{
		if($isPredictor){
			$pOrO = "outcomes";
		} else{
			$pOrO = "predictors";
		}
		return "$displayName Overview with Data Visualizations and Likely $pOrO for $userDisplayName";
	}
	/**
	 * @return string
	 */
	public function getReportTitleAttribute(): string{
		return $this->getOrSetVariableDisplayName() . " Overview";
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return UserVariable::CLASS_DESCRIPTION;
	}
	/**
	 * @return array
	 * We can only get common tags that a user has matching user variables for or we'll try to get 1000 common
	 *     variables for Folic Acid every time we try to create a study with Folic Acid
	 */
	public function setCommonTaggedVariables(): array{
		$this->verifyJsonEncodableAndNonRecursive();
		$t = CommonTag::TABLE;
		$qb = static::qb()
			->join($t, self::TABLE . '.' . self::FIELD_VARIABLE_ID, '=', $t . '.' . CommonTag::FIELD_TAGGED_VARIABLE_ID)
			->whereRaw($t . '.' . CommonTag::FIELD_TAGGED_VARIABLE_ID . ' <> ' . $this->getVariableIdAttribute())->where($t .
				'.' . CommonTag::FIELD_TAG_VARIABLE_ID, $this->getVariableIdAttribute())->whereNull($t . '.' .
				CommonTag::FIELD_DELETED_AT)->where(self::TABLE . '.' . self::FIELD_USER_ID, $this->getUserId());
		QMCommonTag::addColumns($qb);
		$c = $qb->get();
		$a = $c->all();
		foreach($a as $v){
			if($v->tagConversionFactor === null){
				$c = $qb->get();
				le("tagConversionFactor is null on $v");
			}
		}
		$this->verifyJsonEncodableAndNonRecursive();
		return $this->commonTaggedVariables = $a;
	}
	public function shrink(){
		parent::shrink();
		$this->bestUserVariableRelationship = null;
	}
	public function getCategoryName(): string{
		return WpPost::CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS;
	}
	/**
	 * @return int
	 */
	public function getNumberOfVariablesToCorrelateWith(): int{
		$count = $this->userVariableIdsToCorrelateWithQB()->count();
		return $count;
	}
	/**
	 * @return string
	 */
	public function getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml(): string{
		$html = parent::getNoCorrelationsDataRequirementAndCurrentDataQuantityHtml();
		$count = $this->getNumberOfVariablesToCorrelateWith();
		$name = $this->getOrSetVariableDisplayName();
		$html .= "
            <p>
                You currently have $count variables with enough overlapping data for analysis with $name.
            </p>
        ";
		$u = $this->getQMUser();
		$html .= "
            <h4 class=\"text-2xl font-semibold\">
                Here's an overview of all your data:
            </h4>
        ";
		$html .= $u->getDataQuantityListRoundedButtonsHTML();
		$html .= HtmlHelper::getHelpButton();
		return $html;
	}
	/**
	 * @param QMUserVariableRelationship $c
	 */
	public function updateBestCorrelationAsCause(QMUserVariableRelationship $c): void{
		$l = $this->l();
		$l->optimal_value_message = $c->getHigherPredictsAndOptimalValueSentenceWithDurationOfAction();
		$this->bestUserVariableRelationship = $this->bestCorrelationWhereCause = $c->l();
		$l->best_user_variable_relationship_id = $c->getId();
		$l->best_effect_variable_id = $c->getEffectVariableId();
		try {
			$l->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		$this->setBestStudyLink();
	}
	/**
	 * @param QMUserVariableRelationship $c
	 */
	public function updateBestCorrelationAsEffect(QMUserVariableRelationship $c): void{
		$this->bestUserVariableRelationship = $this->bestCorrelationWhereEffect = $c->l();
		$l = $this->l();
		$l->optimal_value_message = $c->getHigherPredictsAndOptimalValueSentenceWithDurationOfAction();
		$this->bestUserVariableRelationship = $this->bestCorrelationWhereCause = $c->l();
		$l->best_user_variable_relationship_id = $c->getId();
		$l->best_cause_variable_id = $c->getCauseVariableId();
		try {
			$l->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		$this->setBestStudyLink();
	}
	/**
	 * @param string $predictorCategoryName
	 * @return string
	 */
	public function getDemoUserDisclaimerNotEnoughDataStartTrackingHtml(string $predictorCategoryName = "factors"): string{
		$name = $this->getOrSetVariableDisplayName();
		$width = CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH;
		$personalizedButton = StartTrackingQMCard::getWantPersonalizedResultsHtml();
		$html = "
            <div style='text-align: center; max-width: $width;'>
                <p>You don't have enough data to determine which $predictorCategoryName are most significant for YOUR $name personally.</p>
                <p>Below are the factors most significant for other users.</p>
                $personalizedButton
            </div>
        ";
		return $html;
	}
	public function getFileName(string $extension = null): string{
		$name = QMStr::slugify($this->getOrSetVariableDisplayName());
		if($extension){
			return $name . '.' . $extension;
		}
		return $name;
	}

	/**
	 * @return QMTrackingReminderNotification[]
	 */
	public function getNotifications(): array{
		if($this->trackingReminderNotifications !== null){
			return $this->trackingReminderNotifications;
		}
		$arr = QMTrackingReminderNotification::getTrackingReminderNotifications($this->getQMUser(),
			[QMTrackingReminderNotification::FIELD_USER_VARIABLE_ID => $this->getUserVariableId()]);
		return $this->trackingReminderNotifications = $arr;
	}
	public function getNumberOfUserVariableRelationships(): int{
		return $this->getOrCalculateNumberOfUserVariableRelationshipsAsCause() +
			$this->getOrCalculateNumberOfUserVariableRelationshipsAsEffect();
	}
	public function getParentAnalyzables(): array{
		return [$this->getCommonVariable()];
	}
	public function getCleanupUrl(): string{
		$commonUnit = $this->getCommonUnit();
		$units = UrlHelper::getCleanupSelectUrl("select * from measurements",
			"update measurements set unit_id = $commonUnit->id",
			"where user_variable_id = " . $this->getUserVariableId() . " and unit_id <> $commonUnit->id",
			"Change $this->name measurements unit to $commonUnit->name");
		$str = "
        $units
        User Variable Cleanup: " . UrlHelper::generateApiUrl('/cleanup', $this->getIdParams()) . "
        Common Variable Cleanup " .
			UrlHelper::generateApiUrl('/cleanup', [self::FIELD_VARIABLE_ID => $this->getVariableIdAttribute()]);
		if($max = $this->maximumAllowedValue){
			$str .= $this->getDeleteLargeMeasurementsUrl();
		}
		if($this->minimumAllowedValue !== null){
			$str .= $this->getDeleteSmallMeasurementsUrl();
		}
		return $str;
	}
	public function getDeleteSmallMeasurementsUrl(): string{
		$commonUnit = $this->getCommonUnit();
		$min = $this->minimumAllowedValueInCommonUnit;
		if($min !== null){
			return UrlHelper::getCleanupSelectUrl("select * from measurements", "delete measurements from measurements",
				"where user_variable_id = " . $this->getUserVariableId() .
				" and value < $min and unit = $commonUnit->id", "Delete Measurements Less Than $min $commonUnit->name");
		}
		return "No Minimum value";
	}
	public function getDeleteLargeMeasurementsUrl(): string{
		$commonUnit = $this->getCommonUnit();
		if($max = $this->maximumAllowedValue){
			return UrlHelper::getCleanupSelectUrl("select * from measurements", "delete measurements from measurements",
				"where user_variable_id = " . $this->getUserVariableId() .
				" and value > $max and unit = $commonUnit->id",
				"Delete Measurements Bigger Than $max $commonUnit->name");
		}
		return "No Maximum value";
	}
	public function getMeasurementsUrl(): string{
		return Measurement::generateDataLabUrl(null,
			[Measurement::FIELD_USER_VARIABLE_ID => $this->getUserVariableId()]);
	}
	public function weShouldPost(): bool{
		return $this->getNumberOfCorrelations() > 0 && $this->getIsPublic();
	}
	public function getQMUserVariable(): QMUserVariable{
		return $this;
	}
	/**
	 * @param int|null $userId
	 */
	public function populateFromBaseModel(int $userId = null){
		/** @var UserVariable $l */
		$l = $this->laravelModel;
		$variable = $l->getVariable();
		$this->setVariableName($variable->name);
		$map = GetUserVariableRequest::getUserVariableFieldMap();
		foreach($map as $field => $property){
			if(!$property){
				$property = QMStr::camelize($field);
			}
			if(property_exists($this, $property) && !isset($this->$property)){
				try {
					$this->$property = $l->getAttribute($field);
				} catch (Throwable $e) {
					$this->$property = $l->getAttribute($field);
				}
			}
		}
		foreach(GetUserVariableRequest::getCommonVariableFieldMap() as $field => $property){
			if(!$property){
				$property = QMStr::camelize($field);
			}
			if(property_exists($this, $property) && !isset($this->$property)){
				$this->$property = $variable->getAttribute($field);
			}
		}
		$this->getOrSetCauseOnly();
		$this->getOnsetDelay();
		$this->getDurationOfAction();
		$this->getOrSetCombinationOperation();
		$this->convertValuesToUserUnit();
		$this->getUnitIdAttribute();
		$this->getVariableName();
		$this->getFillingValueAttribute();
		$this->validateUnit();
	}
	/**
	 * @return bool
	 */
	public function getIsPublic(): ?bool{
		return $this->isPublic;
	}
	public function setIsPublic(?bool $isPublic): void{
		$this->setAttribute(self::FIELD_IS_PUBLIC, $isPublic);
		$this->shareUserMeasurements = $isPublic;
	}
	/**
	 * @return bool
	 */
	public function getOutcome(): bool{
		if($this->outcome === null){
			$this->setOutcome($this->getAttributeFromVariableOrCategory(self::FIELD_OUTCOME));
		}
		return $this->outcome;
	}
	/**
	 * @return string
	 */
	public function getOrSetCombinationOperation(): string{
		$op = $this->combinationOperation;
		if(!$op){
			/** @var UserVariable $l */
			if($l = $this->laravelModel){
				$op = $l->getVariable()->getAttribute(self::FIELD_COMBINATION_OPERATION);
			}
		}
		if(!$op){
			$op = parent::getOrSetCombinationOperation();
		}
		return $this->combinationOperation = $op;
	}
	public function validateId(){
		if(!$this->id){
			le('!$this->id');
		}
		if(!$this->userVariableId){
			le('!$this->userVariableId');
		}
		if($this->userVariableId !== $this->id){
			le('$this->userVariableId !== $this->id');
		}
		if($this->laravelModel){
			if($this->id !== $this->laravelModel->id){
				le('$this->id !== $this->laravelModel->id');
			}
		}
	}
	/**
	 * Get a specified relationship.
	 * @param string $relation
	 * @return mixed
	 */
	public function getRelation(string $relation){
		return $this->l()->getRelation($relation);
	}
	public static function getRequiredPropertyNames(): array{
		$arr = parent::getRequiredPropertyNames();
		$arr[] = 'variableCategoryName';
		$arr[] = 'variableId';
		return $arr;
	}
	public function validateRequiredProperties(){
		$unit = $this->getCommonUnit();
		if($max = $unit->getMaximumAggregatedValue()){
			if(!$this->maximumAllowedValue){
				le("MaximumAggregatedValue for $unit is $max but maximumAllowedValue on $this is not set!");
			}
		}
		if(!is_string($this->unitAbbreviatedName)){
			le("unitAbbreviatedName");
		}
		parent::validateRequiredProperties();
		$this->validateUnit();
	}
	/**
	 * @return UserVariable
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function l(){
		$l = parent::l();
//		if(empty($l->getAttributes())){
//			le('empty($l->getAttributes())');
//		}
		return $l;
	}
	/**
	 * @inheritDoc
	 */
	public function getMostCommonValue(): float{
		return $this->mostCommonValueInUserUnit;
	}
	public function outputConstructor(){
		$arr = [];
		foreach($this as $key => $value){
			$arr[$key] = $value;
		}
		ksort($arr);
		$uv = $this->l();
		$v = Variable::find($this->variableId);
		$c = $this->getQMVariableCategory();
		$unit = $this->getCommonUnit();
		$fromUnit = $fromCat = $fromVariable = $fromUserVariable = $nulls = [];
		foreach($arr as $key => $value){
			$userReplaced = QMStr::snakize(str_replace('user', "", $key));
			$userVariableReplaced = QMStr::snakize(str_replace('userVariable', "", $key));
			$inCommonUnitReplaced = QMStr::snakize(str_replace('InCommonUnit', "", $key));
			$commonReplaced = QMStr::snakize(str_replace('common', "", $key));
			$unitReplaced = QMStr::snakize(str_replace('unit', "", $key));
			$variableCategoryReplaced = QMStr::snakize(str_replace('variableCategory', "", $key));
			$snake = QMStr::snakize($key);
			if($uv->hasColumn($snake)){
				$fromUserVariable[$key] = "\$uv->$snake";
			} elseif($uv->hasColumn($userReplaced)){
				$fromUserVariable[$key] = "\$uv->$userReplaced";
			} elseif($uv->hasColumn($inCommonUnitReplaced)){
				$fromUserVariable[$key] = "\$uv->$inCommonUnitReplaced";
			} elseif($uv->hasColumn($userVariableReplaced)){
				$fromUserVariable[$key] = "\$uv->$userVariableReplaced";
			} elseif($v->hasColumn($snake)){
				$fromVariable[$key] = "\$v->$snake";
			} elseif($v->hasColumn($commonReplaced)){
				$fromVariable[$key] = "\$v->$commonReplaced";
			} elseif($c->hasAttribute($key)){
				$fromCat[$key] = "\$category->$key";
			} elseif($variableCategoryReplaced && $c->hasAttribute($variableCategoryReplaced)){
				$fromCat[$key] = "\$category->$variableCategoryReplaced";
			} elseif($unit->hasAttribute($key)){
				$fromUnit[$key] = "\$unit->$key";
			} elseif($unitReplaced && $unit->hasAttribute($unitReplaced)){
				$fromUnit[$key] = "\$unit->$unitReplaced";
			} elseif($value !== null){
				$nulls[$key] = null;
			}
		}
		\App\Logging\ConsoleLog::info("// From UserVariable");
		foreach($fromUserVariable as $key => $str){
			\App\Logging\ConsoleLog::info("\t\$this->$key = $str;");
		}
		\App\Logging\ConsoleLog::info("// From Variable");
		foreach($fromVariable as $key => $str){
			\App\Logging\ConsoleLog::info("\t\$this->$key = $str;");
		}
		\App\Logging\ConsoleLog::info("// From Category");
		foreach($fromCat as $key => $str){
			\App\Logging\ConsoleLog::info("\t\$this->$key = $str;");
		}
		\App\Logging\ConsoleLog::info("// From Unit");
		foreach($fromUnit as $key => $str){
			\App\Logging\ConsoleLog::info("\t\$this->$key = $str;");
		}
		\App\Logging\ConsoleLog::info("// nulls");
		foreach($nulls as $key => $value){
			\App\Logging\ConsoleLog::info("\t\$this->$key = null;");
		}
	}
	public function populateByLaravelVariable(Variable $v): void{
		parent::populateByLaravelVariable($v);
		$this->commonNumberOfUniqueValues = $v->number_of_unique_values;
	}
	/**
	 * @param UserVariable $uv
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function populateByLaravelModel(BaseModel $uv){
		$this->setLaravelModel($uv);
		$this->variableId = $variableId = $uv->variable_id;
		$this->userId = $uv->user_id;
		$v = $uv->getVariable();
		if($id = $uv->id){
			$this->setId($id);
		}
		if($this->variableId !== $variableId){
			le("");
		}
		if($this->variableId !== $variableId){
			le("");
		}
		$this->populateByLaravelUserVariable($uv);
		$this->populateByLaravelVariable($v);
		// From Category
		$this->setVariableCategory($uv->getVariableCategoryIdAttribute()); // Need to overwrite from variable
		// From Unit
		$unit = $this->getUserUnit();
		$this->setUserUnit($unit);
		$this->validateUnit();
		QMUnit::setInputType($this);
		$this->convertValuesToUserUnit();
		$this->validateUnit();
		if($this->variableId !== $variableId){
			le('$this->variableId !== $variableId');
		}
		foreach(self::DB_FIELD_NAME_TO_PROPERTY_NAME_MAP as $attr => $prop){
			if(isset($this->$prop)){
				continue;
			}
			$val = $uv->getAttribute($attr);
			if($val !== null){
				$this->$prop = $val;
			}
		}
		foreach($this as $key => $value){
			if(TimeHelper::isCarbon($value)){
				/** @var CarbonInterface $value */
				$this->$key = $value->toDateTimeString();
			}
		}
		$this->getTitleAttribute();
		$this->validateUnit();
		$this->setNumberOfMeasurements($uv->getNumberOfMeasurements());
	}
	/**
	 * @param string $key
	 * @param $value
	 * @throws ModelValidationException
	 */
	public function updateUserVariableAttribute(string $key, $value){
		$uv = $this->getUserVariable();
		$uv->setAttribute($key, $value);
		$uv->save();
	}
	private function updateVariable(): void{
		$l = $this->l();
		$v = $this->getVariable();
		$v->updateFromUserVariable($l);
	}
	/**
	 * @param string $reason
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
	public function analyzePartially(string $reason){
		$this->analyzeFully($reason);
	}
	public function calculateAttributes(): array{
		$res = parent::calculateAttributes();
		// Not sure why this is necessary, but it keeps being wrong
		UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty::calculate($this);
		UserVariableNumberOfMeasurementsProperty::calculate($this);
		$url = $this->getAnalyzeUrl();
		if($this->getNumberOfMeasurements()){
			if(!$this->latestFillingTime){
				le('!$this->latestFillingTime, $url');
			}
		}
		$withTags = $this->getMeasurementsWithTags();
		if($this->numberOfRawMeasurementsWithTagsJoinsChildren !== count($withTags)){
			le('$this->numberOfRawMeasurementsWithTagsJoinsChildren !== count($withTags), $url');
		}
		$l = $this->l();
		$number_of_raw_measurements_with_tags_joins_children = $l->number_of_raw_measurements_with_tags_joins_children;
		$countWithTags = count($withTags);
		if($number_of_raw_measurements_with_tags_joins_children !== $countWithTags){
			UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty::calculate($this);
			le("l->number_of_raw_measurements_with_tags_joins_children $number_of_raw_measurements_with_tags_joins_children !== count(withTags) $countWithTags for $this. \n\t Debug at $url");
		}
		$measurements = $this->getQMMeasurements();
		if($this->getNumberOfMeasurements() !== count($measurements)){
			le($url);
		}
		if($l->getNumberOfMeasurements() !== count($measurements)){
			le($url);
		}
		return $res;
	}
	/**
	 * Provided source name, client id, connector name, or QMDataSource name should be in measurements.source_name
	 * column
	 * @return array
	 */
	public function calculateDataSourcesCount(): array{
		return UserVariableDataSourcesCountProperty::calculate($this->l());
	}
	public function getUserVariableId(): int{
		if(!$this->userVariableId){
			le("no userVariableId");
		}
		return $this->userVariableId;
	}
	private function debugReasonForExistence(): void{
		$l = $this->l();
		$l->number_of_tracking_reminders = $l->tracking_reminders()->count();
		$l->number_of_soft_deleted_measurements =
			Measurement::withTrashed()->where(Measurement::FIELD_VARIABLE_ID, $this->getVariableIdAttribute())
				->where(Measurement::FIELD_USER_ID, $this->getUserId())->count();
		if($l->number_of_soft_deleted_measurements){
			$l->setInternalErrorMessageAttribute("This user variable only exists because it has " .
				$l->number_of_soft_deleted_measurements . " deleted measurements. ");
			return;
		}
		if($l->number_of_tracking_reminders){
			$l->setInternalErrorMessageAttribute("This user variable only exists because it has $l->number_of_tracking_reminders tracking reminders. ");
			return;
		}
		$tagged = $this->getCommonAndUserTaggedVariables();
		$measurements = $reminders = [];
		foreach($tagged as $v){
			if($forVar = $v->getQMMeasurements()){
				$measurements[$v->name] = $forVar;
			}
			if($forVar = $v->getQMTrackingReminders()){
				$reminders[$v->name] = $forVar;
			}
		}
		if($measurements){
			$l->setInternalErrorMessageAttribute("This user variable only exists because its tagged variables have measurements. ");
			return;
		}
		if($reminders){
			$l->setInternalErrorMessageAttribute("This user variable only exists because its tagged variables have reminders. ");
			return;
		}
		$l->setInternalErrorMessageAttribute($l->user_error_message =
			"No measurements (including tags), deleted measurements or reminders for analysis. Not sure why it exists? ");
		QMLog::printNonNullNumbersAndStrings($l->attributesToArray(), $l->getTitleAttribute());
		$l->status = UserVariableStatusProperty::STATUS_ERROR;
	}
	public function getMaximumDailyValue(): float{
		$values = $this->getDailyValuesWithTagsAndFilling();
		return max($values);
	}
	public function getMinimumDailyValue(): float{
		$values = $this->getDailyValuesWithTagsAndFilling();
		return min($values);
	}
	public function unsetOutcomes(){
		$this->userVariableRelationshipsAsCause = null;
		parent::unsetOutcomes();
	}
	public function unsetPredictors(){
		$this->userVariableRelationshipsAsEffect = null;
		parent::unsetPredictors();
	}
	public function getUrlParams(): array{
		return [
			'user_variable_id' => $this->getUserVariableId(),
			UserVariable::FIELD_VARIABLE_ID => $this->getVariableIdAttribute(),
			Variable::FIELD_VARIABLE_CATEGORY_ID => $this->getVariableCategoryId(),
			Variable::FIELD_DEFAULT_UNIT_ID => $this->getUnitIdAttribute(),
		];
	}
	public function getVariableCategoryId(): int{
		if($this->userVariableVariableCategoryId){
			$this->variableCategoryId = $this->userVariableVariableCategoryId;
		}
		if(!$this->variableCategoryId){
			$this->variableCategoryId = $this->commonVariableCategoryId;
		}
		if(!$this->variableCategoryId){
			$this->variableCategoryId = $this->getVariable()->getVariableCategoryId();
		}
		if(!$this->variableCategoryId){
			le("no cat id", $this);
		}
		return $this->variableCategoryId;
	}
	/**
	 * @param int|null $number
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public function setNumberOfMeasurements(?int $number): void{
		parent::setNumberOfMeasurements($number);
	}
	public function getChartsButtonHtml(): string{
		return $this->l()->getChartsButtonHtml();
	}
	public function getSettingsButtonHtml(string $fromUrl = null): string{
		return $this->l()->getSettingsButtonHtml();
	}
	public function getEarliestTaggedMeasurementDate(): ?string{
		return $this->l()->getEarliestTaggedMeasurementDate();
	}
	public function getLatestTaggedMeasurementDate(): ?string{
		return $this->l()->getLatestTaggedMeasurementDate();
	}
	/**
	 * @param bool $includeDeleted
	 * @return int
	 */
	public function calculateNumberOfMeasurements(bool $includeDeleted = false): int{
		return $this->l()->calculateNumberOfMeasurements();
	}
	/**
	 * @param int|null $limit
	 * @param string|null $variableCategoryName
	 * @return GlobalVariableRelationship[]|UserVariableRelationship[]|Collection
	 */
	public function getOutcomesOrPredictors(int $limit = null, string $variableCategoryName = null): ?Collection{
		return $this->l()->getOutcomesOrPredictors($limit, $variableCategoryName);
	}
	public function getCorrelationDataRequirementAndCurrentDataQuantityString(): string{
		return $this->l()->getCorrelationDataRequirementAndCurrentDataQuantityString();
	}
	/**
	 * @return string
	 */
	public function getDataQuantityHTML(): string{
		return $this->l()->getDataQuantityHTML();
	}
	public function getUrl(array $params = []): string{
		return $this->getShowUrl($params);
	}
	/**
	 * @return RootCauseAnalysis
	 */
	public function getRootCauseAnalysis(): RootCauseAnalysis{
		if($r = $this->rootCauseAnalysis){return $r;}
		return $this->rootCauseAnalysis = new RootCauseAnalysis($this->getVariableIdAttribute(),
			$this->getUserId());
	}
	/**
	 * @return string
	 */
	public function getEmailBody(): string{
		return $this->getRootCauseEmailBody();
	}
	public function hasFillingValue(): bool{
		$userVariable = $this->getUserVariable();
		return $userVariable->hasFillingValue();
	}
	public function getFillingValueAttribute(): ?float{
		if($this->fillingType === BaseFillingTypeProperty::FILLING_TYPE_NONE){
			return $this->fillingValue = null;
		}
		if($this->fillingType === BaseFillingTypeProperty::FILLING_TYPE_ZERO){
			return $this->fillingValue = 0;
		}
		if(isset($this->fillingValue)){
			return $this->fillingValue;
		}
		return $this->fillingValue = $this->getUserVariable()->getFillingValueAttribute();
	}
	public function getUserMaximumAllowedDailyValueAttribute(): ?float{
		return $this->userMaximumAllowedDailyValue = $this->getUserVariable()
			->getUserMaximumAllowedDailyValueAttribute();
	}
	public function getUserMinimumAllowedDailyValueAttribute(): ?float{
		return $this->userMinimumAllowedDailyValue =  $this->getUserVariable()
			->getUserMinimumAllowedDailyValueAttribute();
	}
	public function getShowContentView(array $params = []): View{
		return $this->l()->getShowContentView($params);
	}
	public function getShowPageView(array $params = []): View{
		return $this->l()->getShowPageView($params);
	}
	public function getIcon(): string{
		return $this->getVariable()->getIcon();
	}
	public function getWebhookUrl(): string{
		return MeasurementAPIController::getUrl($this->getUrlParams());
	}
	/**
	 * @param int $userId
	 * @param null $variableIdOrName
	 * @return \App\Models\UserVariable
	 */
	public static function findByNameOrId(int $userId, $variableIdOrName = null): ?self{
		$variableId = VariableIdProperty::pluckOrDefault($variableIdOrName);
		$uv = UserVariable::findInMemoryOrDBWhere([
			self::FIELD_USER_ID => $userId,
			self::FIELD_VARIABLE_ID => $variableId,
		]);
		if($uv){return $uv->getDBModel();}
		return null;
	}
	public function getHtmlPage(bool $inlineJs = false): string{
		$userVariable = $this->l();
		return $userVariable->getHtmlPage($inlineJs);
	}
}
