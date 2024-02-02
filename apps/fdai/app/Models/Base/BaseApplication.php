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
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Collaborator;
use App\Models\OAClient;
use App\Models\Variable;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseApplication
 * @property int $id
 * @property int $organization_id
 * @property string $client_id
 * @property string $app_display_name
 * @property string $app_description
 * @property string $long_description
 * @property int $user_id
 * @property string $icon_url
 * @property string $text_logo
 * @property string $splash_screen
 * @property string $homepage_url
 * @property string $app_type
 * @property string $app_design
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $enabled
 * @property int $stripe_active
 * @property string $stripe_id
 * @property string $stripe_subscription
 * @property string $stripe_plan
 * @property string $last_four
 * @property Carbon $trial_ends_at
 * @property Carbon $subscription_ends_at
 * @property string $company_name
 * @property string $country
 * @property string $address
 * @property string $state
 * @property string $city
 * @property string $zip
 * @property int $plan_id
 * @property int $exceeding_call_count
 * @property float $exceeding_call_charge
 * @property int $study
 * @property int $billing_enabled
 * @property int $outcome_variable_id
 * @property int $predictor_variable_id
 * @property int $physician
 * @property string $additional_settings
 * @property string $app_status
 * @property bool $build_enabled
 * @property int $wp_post_id
 * @property int $number_of_collaborators_where_app
 * @property bool $is_public
 * @property int $sort_order
 * @property OAClient $oa_client
 * @property Variable $outcome_variable
 * @property Variable $predictor_variable
 * @property WpPost $wp_post
 * @property Collection|Collaborator[] $collaborators
 * @package App\Models\Base
 * @property-read int|null $collaborators_count
 * @property mixed $raw

 * @property-read \App\Models\User $user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseApplication onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAdditionalSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAppDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAppDesign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAppDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAppStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereAppType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereBillingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereBuildEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereExceedingCallCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereExceedingCallCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereHomepageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereLongDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereNumberOfCollaboratorsWhereApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereOutcomeVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication wherePhysician($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication wherePredictorVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereSplashScreen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereStripeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereStripePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereStripeSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereStudy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereSubscriptionEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereTextLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereWpPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseApplication whereZip($value)
 * @method static \Illuminate\Database\Query\Builder|BaseApplication withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseApplication withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseApplication extends BaseModel {
	use SoftDeletes;
	public const FIELD_ADDITIONAL_SETTINGS = 'additional_settings';
	public const FIELD_ADDRESS = 'address';
	public const FIELD_APP_DESCRIPTION = 'app_description';
	public const FIELD_APP_DESIGN = 'app_design';
	public const FIELD_APP_DISPLAY_NAME = 'app_display_name';
	public const FIELD_APP_STATUS = 'app_status';
	public const FIELD_APP_TYPE = 'app_type';
	public const FIELD_BILLING_ENABLED = 'billing_enabled';
	public const FIELD_BUILD_ENABLED = 'build_enabled';
	public const FIELD_CITY = 'city';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMPANY_NAME = 'company_name';
	public const FIELD_COUNTRY = 'country';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ENABLED = 'enabled';
	public const FIELD_EXCEEDING_CALL_CHARGE = 'exceeding_call_charge';
	public const FIELD_EXCEEDING_CALL_COUNT = 'exceeding_call_count';
	public const FIELD_HOMEPAGE_URL = 'homepage_url';
	public const FIELD_ICON_URL = 'icon_url';
	public const FIELD_ID = 'id';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_LAST_FOUR = 'last_four';
	public const FIELD_LONG_DESCRIPTION = 'long_description';
	public const FIELD_NUMBER_OF_COLLABORATORS_WHERE_APP = 'number_of_collaborators_where_app';
	public const FIELD_ORGANIZATION_ID = 'organization_id';
	public const FIELD_OUTCOME_VARIABLE_ID = 'outcome_variable_id';
	public const FIELD_PHYSICIAN = 'physician';
	public const FIELD_PLAN_ID = 'plan_id';
	public const FIELD_PREDICTOR_VARIABLE_ID = 'predictor_variable_id';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_SPLASH_SCREEN = 'splash_screen';
	public const FIELD_STATE = 'state';
	public const FIELD_STRIPE_ACTIVE = 'stripe_active';
	public const FIELD_STRIPE_ID = 'stripe_id';
	public const FIELD_STRIPE_PLAN = 'stripe_plan';
    public const FIELD_SLUG = 'slug';
	public const FIELD_STRIPE_SUBSCRIPTION = 'stripe_subscription';
	public const FIELD_STUDY = 'study';
	public const FIELD_SUBSCRIPTION_ENDS_AT = 'subscription_ends_at';
	public const FIELD_TEXT_LOGO = 'text_logo';
	public const FIELD_TRIAL_ENDS_AT = 'trial_ends_at';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const FIELD_ZIP = 'zip';
	public const TABLE = 'applications';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Settings for applications created by the no-code QuantiModo app builder at https://builder.quantimo.do.  ';
	protected $casts = [
        self::FIELD_TRIAL_ENDS_AT => 'datetime',
        self::FIELD_SUBSCRIPTION_ENDS_AT => 'datetime',
		self::FIELD_ADDITIONAL_SETTINGS => 'object',
		self::FIELD_ADDRESS => 'string',
		self::FIELD_APP_DESCRIPTION => 'string',
		self::FIELD_APP_DESIGN => 'object',
		self::FIELD_APP_DISPLAY_NAME => 'string',
		self::FIELD_APP_STATUS => 'object',
		self::FIELD_APP_TYPE => 'string',
		self::FIELD_BILLING_ENABLED => 'int',
		self::FIELD_BUILD_ENABLED => 'bool',
		self::FIELD_CITY => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMPANY_NAME => 'string',
		self::FIELD_COUNTRY => 'string',
		self::FIELD_ENABLED => 'int',
		self::FIELD_EXCEEDING_CALL_CHARGE => 'float',
		self::FIELD_EXCEEDING_CALL_COUNT => 'int',
		self::FIELD_HOMEPAGE_URL => 'string',
		self::FIELD_ICON_URL => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_LAST_FOUR => 'string',
		self::FIELD_LONG_DESCRIPTION => 'string',
		self::FIELD_NUMBER_OF_COLLABORATORS_WHERE_APP => 'int',
		self::FIELD_ORGANIZATION_ID => 'int',
		self::FIELD_OUTCOME_VARIABLE_ID => 'int',
		self::FIELD_PHYSICIAN => 'int',
		self::FIELD_PLAN_ID => 'int',
		self::FIELD_PREDICTOR_VARIABLE_ID => 'int',
		self::FIELD_SORT_ORDER => 'int',
		self::FIELD_SPLASH_SCREEN => 'string',
		self::FIELD_STATE => 'string',
		self::FIELD_STRIPE_ACTIVE => 'int',
		self::FIELD_STRIPE_ID => 'string',
		self::FIELD_STRIPE_PLAN => 'string',
		self::FIELD_STRIPE_SUBSCRIPTION => 'string',
		self::FIELD_STUDY => 'int',
		self::FIELD_TEXT_LOGO => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_ZIP => 'string',	];
	protected array $rules = [
		self::FIELD_ADDITIONAL_SETTINGS => 'nullable|max:65535',
		self::FIELD_ADDRESS => 'nullable|max:255',
		self::FIELD_APP_DESCRIPTION => 'nullable|max:255',
		self::FIELD_APP_DESIGN => 'nullable|max:65535',
		self::FIELD_APP_DISPLAY_NAME => 'required|max:255',
		self::FIELD_APP_STATUS => 'nullable|max:65535',
		self::FIELD_APP_TYPE => 'nullable|max:32',
		self::FIELD_BILLING_ENABLED => 'required|boolean',
		self::FIELD_BUILD_ENABLED => 'required|boolean',
		self::FIELD_CITY => 'nullable|max:100',
		self::FIELD_CLIENT_ID => 'required|max:80|unique:applications,client_id',
		self::FIELD_COMPANY_NAME => 'nullable|max:100',
		self::FIELD_COUNTRY => 'nullable|max:100',
		self::FIELD_ENABLED => 'required|boolean',
		self::FIELD_EXCEEDING_CALL_CHARGE => 'nullable|numeric|max:99999999999999.99',
		self::FIELD_EXCEEDING_CALL_COUNT => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_HOMEPAGE_URL => 'nullable|max:255',
		self::FIELD_ICON_URL => 'nullable|max:2083',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_LONG_DESCRIPTION => 'nullable|max:65535',
		self::FIELD_NUMBER_OF_COLLABORATORS_WHERE_APP => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_ORGANIZATION_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_OUTCOME_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PHYSICIAN => 'required|boolean',
		self::FIELD_PLAN_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PREDICTOR_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SPLASH_SCREEN => 'nullable|max:2083',
		self::FIELD_STATE => 'nullable|max:100',
		self::FIELD_STRIPE_ACTIVE => 'required|boolean',
		self::FIELD_STRIPE_ID => 'nullable|max:255',
		self::FIELD_STRIPE_PLAN => 'nullable|max:100',
		self::FIELD_STRIPE_SUBSCRIPTION => 'nullable|max:255',
		self::FIELD_STUDY => 'required|boolean',
		self::FIELD_SUBSCRIPTION_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_TEXT_LOGO => 'nullable|max:2083',
		self::FIELD_TRIAL_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
		self::FIELD_ZIP => 'nullable|max:10',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_ORGANIZATION_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_APP_DISPLAY_NAME => '',
		self::FIELD_APP_DESCRIPTION => '',
		self::FIELD_LONG_DESCRIPTION => '',
		self::FIELD_USER_ID => '',
		self::FIELD_ICON_URL => '',
		self::FIELD_TEXT_LOGO => '',
		self::FIELD_SPLASH_SCREEN => '',
		self::FIELD_HOMEPAGE_URL => '',
		self::FIELD_APP_TYPE => '',
		self::FIELD_APP_DESIGN => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ENABLED => '',
		self::FIELD_STRIPE_ACTIVE => '',
		self::FIELD_STRIPE_ID => '',
		self::FIELD_STRIPE_SUBSCRIPTION => '',
		self::FIELD_STRIPE_PLAN => '',
		self::FIELD_LAST_FOUR => '',
		self::FIELD_TRIAL_ENDS_AT => 'datetime',
		self::FIELD_SUBSCRIPTION_ENDS_AT => 'datetime',
		self::FIELD_COMPANY_NAME => '',
		self::FIELD_COUNTRY => '',
		self::FIELD_ADDRESS => '',
		self::FIELD_STATE => '',
		self::FIELD_CITY => '',
		self::FIELD_ZIP => '',
		self::FIELD_PLAN_ID => '',
		self::FIELD_EXCEEDING_CALL_COUNT => '',
		self::FIELD_EXCEEDING_CALL_CHARGE => '',
		self::FIELD_STUDY => '',
		self::FIELD_BILLING_ENABLED => '',
		self::FIELD_OUTCOME_VARIABLE_ID => '',
		self::FIELD_PREDICTOR_VARIABLE_ID => '',
		self::FIELD_PHYSICIAN => '',
		self::FIELD_ADDITIONAL_SETTINGS => 'Additional non-design settings for your application.',
		self::FIELD_APP_STATUS => '',
		self::FIELD_BUILD_ENABLED => '',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_NUMBER_OF_COLLABORATORS_WHERE_APP => 'Number of Collaborators for this App.
                [Formula:
                    update applications
                        left join (
                            select count(id) as total, app_id
                            from collaborators
                            group by app_id
                        )
                        as grouped on applications.id = grouped.app_id
                    set applications.number_of_collaborators_where_app = count(grouped.total)
                ]
                ',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Application::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Application::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'outcome_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'outcome_variable_id',
			'foreignKey' => Application::FIELD_OUTCOME_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'outcome_variable_id',
			'ownerKey' => Application::FIELD_OUTCOME_VARIABLE_ID,
			'methodName' => 'outcome_variable',
		],
		'predictor_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'predictor_variable_id',
			'foreignKey' => Application::FIELD_PREDICTOR_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'predictor_variable_id',
			'ownerKey' => Application::FIELD_PREDICTOR_VARIABLE_ID,
			'methodName' => 'predictor_variable',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Application::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Application::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => Application::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => Application::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'collaborators' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Collaborator::class,
			'foreignKey' => Collaborator::FIELD_APP_ID,
			'localKey' => Collaborator::FIELD_ID,
			'methodName' => 'collaborators',
		],
	];

    /**
     * @return BelongsTo|OAClient
     */
    public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Application::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Application::FIELD_CLIENT_ID);
	}
	public function outcome_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Application::FIELD_OUTCOME_VARIABLE_ID, Variable::FIELD_ID,
			Application::FIELD_OUTCOME_VARIABLE_ID);
	}
	public function predictor_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, Application::FIELD_PREDICTOR_VARIABLE_ID, Variable::FIELD_ID,
			Application::FIELD_PREDICTOR_VARIABLE_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Application::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Application::FIELD_USER_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, Application::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			Application::FIELD_WP_POST_ID);
	}
	public function collaborators(): HasMany{
		return $this->hasMany(Collaborator::class, Collaborator::FIELD_APP_ID, static::FIELD_ID);
	}
}
