<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Models\Study;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseStudy
 * @property string $id
 * @property string $type
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $analysis_parameters
 * @property string $user_study_text
 * @property string $user_title
 * @property string $study_status
 * @property string $comment_status
 * @property string $study_password
 * @property string $study_images
 * @property Carbon $updated_at
 * @property string $client_id
 * @property Carbon $published_at
 * @property int $wp_post_id
 * @property Carbon $newest_data_at
 * @property Carbon $analysis_requested_at
 * @property string $reason_for_analysis
 * @property Carbon $analysis_ended_at
 * @property Carbon $analysis_started_at
 * @property string $internal_error_message
 * @property string $user_error_message
 * @property string $status
 * @property Carbon $analysis_settings_modified_at
 * @property bool $is_public
 * @property int $sort_order
 * @property Variable $cause_variable
 * @property OAClient $oa_client
 * @property Variable $effect_variable
 * @property \App\Models\User $user
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseStudy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereAnalysisParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereCauseVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereCommentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereEffectVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereStudyImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereStudyPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereStudyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereUserStudyText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereUserTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseStudy whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|BaseStudy withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseStudy withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseStudy extends BaseModel {
	use SoftDeletes;
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_PARAMETERS = 'analysis_parameters';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMMENT_STATUS = 'comment_status';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
	public const FIELD_ID = 'id';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_PUBLISHED_AT = 'published_at';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_STATUS = 'status';
	public const FIELD_STUDY_IMAGES = 'study_images';
	public const FIELD_STUDY_PASSWORD = 'study_password';
	public const FIELD_STUDY_STATUS = 'study_status';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USER_STUDY_TEXT = 'user_study_text';
	public const FIELD_USER_TITLE = 'user_title';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'studies';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Stores Study Settings';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_PUBLISHED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
		self::FIELD_ANALYSIS_PARAMETERS => 'string',
		self::FIELD_CAUSE_VARIABLE_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMMENT_STATUS => 'string',
		self::FIELD_EFFECT_VARIABLE_ID => 'int',
		self::FIELD_ID => 'string',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_STATUS => 'string',
		self::FIELD_STUDY_IMAGES => 'string',
		self::FIELD_STUDY_PASSWORD => 'string',
		self::FIELD_STUDY_STATUS => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_USER_STUDY_TEXT => 'string',
		self::FIELD_USER_TITLE => 'string',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_PARAMETERS => 'nullable|max:65535',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_CAUSE_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_COMMENT_STATUS => 'required|max:20',
		self::FIELD_EFFECT_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_PUBLISHED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_STUDY_IMAGES => 'nullable|max:65535',
		self::FIELD_STUDY_PASSWORD => 'nullable|max:20',
		self::FIELD_STUDY_STATUS => 'required|max:20',
		self::FIELD_TYPE => 'required|max:20',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_USER_STUDY_TEXT => 'nullable',
		self::FIELD_USER_TITLE => 'nullable|max:65535',
		self::FIELD_WP_POST_ID => 'nullable|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => 'Study id which should match OAuth client id',
		self::FIELD_TYPE => 'The type of study may be population, individual, or cohort study',
		self::FIELD_CAUSE_VARIABLE_ID => 'variable ID of the cause variable for which the user desires user_variable_relationships',
		self::FIELD_EFFECT_VARIABLE_ID => 'variable ID of the effect variable for which the user desires user_variable_relationships',
		self::FIELD_USER_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ANALYSIS_PARAMETERS => 'Additional parameters for the study such as experiment_end_time, experiment_start_time, cause_variable_filling_value, effect_variable_filling_value',
		self::FIELD_USER_STUDY_TEXT => 'Overrides auto-generated study text',
		self::FIELD_USER_TITLE => '',
		self::FIELD_STUDY_STATUS => '',
		self::FIELD_COMMENT_STATUS => '',
		self::FIELD_STUDY_PASSWORD => '',
		self::FIELD_STUDY_IMAGES => 'Provided images will override the auto-generated images',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_PUBLISHED_AT => 'datetime',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NEWEST_DATA_AT => 'datetime',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => '',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_STATUS => '',
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [
		'cause_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'cause_variable_id',
			'foreignKey' => Study::FIELD_CAUSE_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'cause_variable_id',
			'ownerKey' => Study::FIELD_CAUSE_VARIABLE_ID,
			'methodName' => 'cause_variable',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Study::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Study::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'effect_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'effect_variable_id',
			'foreignKey' => Study::FIELD_EFFECT_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'effect_variable_id',
			'ownerKey' => Study::FIELD_EFFECT_VARIABLE_ID,
			'methodName' => 'effect_variable',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Study::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Study::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function cause_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Study::FIELD_CAUSE_VARIABLE_ID, Variable::FIELD_ID,
			Study::FIELD_CAUSE_VARIABLE_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Study::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Study::FIELD_CLIENT_ID);
	}
	public function effect_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Study::FIELD_EFFECT_VARIABLE_ID, Variable::FIELD_ID,
			Study::FIELD_EFFECT_VARIABLE_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Study::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Study::FIELD_USER_ID);
	}
}
