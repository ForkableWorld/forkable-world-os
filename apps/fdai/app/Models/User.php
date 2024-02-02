<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
namespace App\Models;
use Analytics;
use App\Actions\ActionEvent;
use App\AppSettings\AppSettings;
use App\Astral\Actions\FollowAction;
use App\Astral\Actions\UnFollowAction;
use App\Astral\Filters\UserType;
use App\Astral\Lenses\FollowerLens;
use App\Astral\Lenses\FollowingLens;
use App\Astral\UserVariableBaseAstralResource;
use App\Buttons\QMButton;
use App\Buttons\RelationshipButtons\User\UserConnectionsButton;
use App\Buttons\RelationshipButtons\User\UserVariableRelationshipsButton;
use App\Buttons\RelationshipButtons\User\UserMeasurementsButton;
use App\Buttons\RelationshipButtons\User\UserStudiesButton;
use App\Buttons\RelationshipButtons\User\UserTrackingRemindersButton;
use App\Buttons\RelationshipButtons\User\UserUserVariablesButton;
use App\Buttons\RelationshipButtons\User\UserVotesButton;
use App\Buttons\RelationshipButtons\UserVariable\UserVariableTrackingReminderNotificationsButton;
use App\Buttons\States\SettingsStateButton;
use App\Cards\TrackingReminderNotificationCard;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\DataSources\Connectors\FacebookConnector;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\Connectors\GithubConnector;
use App\DataSources\Connectors\LinkedInConnector;
use App\DataSources\Connectors\OuraConnector;
use App\DataSources\Connectors\TwitterConnector;
use App\DataSources\LocationBasedConnector;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\DeletedUserException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\DuplicateNotificationException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitBranchAlreadyExistsException;
use App\Exceptions\GitBranchNotFoundException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\GitRepoAlreadyExistsException;
use App\Exceptions\InvalidDeviceTokenException;
use App\Exceptions\InvalidEmailException;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\InvalidUsernameException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoDeviceTokensException;
use App\Exceptions\NoEmailAddressException;
use App\Exceptions\NoGeoDataException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NoTimeZoneException;
use App\Exceptions\SecretException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooManyMeasurementsException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Fields\Password;
use App\Fields\PasswordConfirmation;
use App\Fields\Timezone;
use App\Files\FileHelper;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Mail\MaterialPostsEmail;
use App\Mail\PhysicianInvitationEmail;
use App\Mail\TooManyEmailsException;
use App\Menus\JournalMenu;
use App\Menus\QMMenu;
use App\Models\Base\BaseWpUser;
use App\Models\Cards\PreviewCard;
use App\Models\WpPosts\PatientOverviewWpPost;
use App\Nfts\Traits\Tokenizer;
use App\Notifications\MiroNotification;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\Base\BaseCountryProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\Base\BasePostTypeProperty;
use App\Properties\Base\BaseRolesProperty;
use App\Properties\Base\BaseUserErrorMessageProperty;
use App\Properties\Base\BaseUserLoginProperty;
use App\Properties\Measurement\MeasurementOriginalStartAtProperty;
use App\Properties\Measurement\MeasurementOriginalUnitIdProperty;
use App\Properties\Measurement\MeasurementOriginalValueProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserAvatarImageProperty;
use App\Properties\User\UserClientIdProperty;
use App\Properties\User\UserDisplayNameProperty;
use App\Properties\User\UserEmailProperty;
use App\Properties\User\UserFirstNameProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserLastNameProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Properties\User\UserPasswordProperty;
use App\Properties\User\UserProviderIdProperty;
use App\Properties\User\UserRegProviderProperty;
use App\Properties\User\UserRolesProperty;
use App\Properties\User\UserStatusProperty;
use App\Properties\User\UserUserEmailProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Properties\User\UserUserNicenameProperty;
use App\Properties\User\UserUserPassProperty;
use App\Properties\User\UserUserUrlProperty;
use App\Properties\Variable\VariableIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Reports\RootCauseAnalysis;
use App\Repos\IndividualCaseStudiesRepo;
use App\Repos\StudiesRepo;
use App\Services\StripeService;
use App\Slim\Configuration\RouteConfiguration;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Notifications\MissingApplePushCertException;
use App\Slim\Model\Notifications\PushNotificationData;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\PhysicianUser;
use App\Slim\Model\User\PublicUser;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\User\UserMeta;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use App\Storage\QueryBuilderHelper;
use App\Storage\S3\S3Private;
use App\Storage\S3\S3Public;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
use App\Studies\QMUserStudy;
use App\Traits\AnalyzableTrait;
use App\Traits\HasButton;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasFiles;
use App\Traits\HasLocalDates;
use App\Traits\HasModel\HasUser;
use App\Traits\HasName;
use App\Traits\HasPatients;
use App\Traits\IsEditable;
use App\Traits\PostableTrait;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\CssHelper;
use App\UI\FontAwesome;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\GeoLocation;
use App\Utils\IPHelper;
use App\Utils\QMTimeZone;
use App\Utils\UrlHelper;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use App\Variables\CommonVariables\SleepCommonVariables\SleepDurationCommonVariable;
use App\Variables\QMUserVariable;
use Corcel\Concerns\Aliases;
use Eloquent;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Subscription;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use LogicException;
use Overtrue\LaravelFavorite\Traits\Favoriter;
use Overtrue\LaravelFollow\Followable;
use Overtrue\LaravelLike\Like;
use Overtrue\LaravelLike\Traits\Liker;
use App\Nfts\Traits\Tokenizable;
use ReflectionException;
use SendGrid\Mail\TypeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Spatie\Permission\Traits\HasRoles;
use Stripe\Customer;
use Throwable;
/**
 * App\Models\User
 * @property integer $ID
 * @property integer $id Alias for ID
 * @property string $client_id registered through client's application
 * @property string $user_login
 * @property string $user_pass
 * @property string $password
 * @property string $user_nicename
 * @property string $user_email
 * @property string $email  // Need email field for Laravel password reset
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property integer $user_status
 * @property string $display_name
 * @property string $name Alias for display_name
 * @property string $url_profile_url
 * @property string $reg_provider Registered via
 * @property string $provider_id Unique id from provider
 * @property string $provider_token Access token from provider
 * @property string $remember_token Remember me token
 * @property boolean $unsubscribed email subscription
 * @property boolean $old_user
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $refresh_token Refresh token from provider
 * @property-read Collection|Measurement[] $measurements
 * @method static Builder|User whereUserLogin($value)
 * @method static Builder|User whereUserPass($value)
 * @method static Builder|User whereUserNicename($value)
 * @method static Builder|User whereUserEmail($value)
 * @method static Builder|User whereUserUrl($value)
 * @method static Builder|User whereUserRegistered($value)
 * @method static Builder|User whereUserActivationKey($value)
 * @method static Builder|User whereUserStatus($value)
 * @method static Builder|User whereDisplayName($value)
 * @method static Builder|User whereUserProfileUrl($value)
 * @method static Builder|User whereRegProvider($value)
 * @method static Builder|User whereProviderId($value)
 * @method static Builder|User whereProviderToken($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereRefreshToken($value)
 * @property string $avatar_image
 * @property int|null $stripe_active
 * @property string|null $stripe_id
 * @property string|null $stripe_subscription
 * @property string|null $stripe_plan
 * @property string|null $last_four
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $subscription_ends_at
 * @property array|null $roles
 * @property int|null $time_zone_offset User timezone offset in seconds
 * @property string|null $deleted_at
 * @property string $earliest_reminder_time Earliest time of day at which reminders should appear in HH:MM:SS format in
 *     user timezone
 * @property string $latest_reminder_time Latest time of day at which reminders should appear in HH:MM:SS format in
 *     user timezone
 * @property int|null $push_notifications_enabled Should we send the user push notifications?
 * @property int|null $track_location Set to true if the user wants to track their location
 * @property int $combine_notifications Should we combine push notifications or send one for each tracking reminder
 *     notification?
 * @property int $send_reminder_notification_emails Should we send reminder notification emails?
 * @property int $send_predictor_emails Should we send predictor emails?
 * @property int $get_preview_builds Should we send preview builds?
 * @property string|null $subscription_provider
 * @property int|null $last_sms_tracking_reminder_notification_id
 * @property int $sms_notifications_enabled
 * @property string|null $phone_verification_code
 * @property string|null $phone_number
 * @property int|null $has_android_app
 * @property int|null $has_ios_app
 * @property int|null $has_chrome_extension
 * @property int|null $referrer_user_id
 * @property string|null $address
 * @property string|null $birthday
 * @property string|null $country
 * @property string|null $cover_photo
 * @property string|null $currency
 * @property string|null $first_name
 * @property string|null $gender
 * @property string|null $language
 * @property string|null $last_name
 * @property string|null $state
 * @property string|null $tag_line
 * @property string|null $verified
 * @property string|null $zip_code
 * @property int $spam
 * @property int $deleted
 * @property-read Collection|\App\Models\Application[] $apps
 * @property-read mixed $avatar
 * @property-read mixed $gravatar
 * @property-read Collection|\App\Models\Organization[] $organizations
 * @property-read Collection|Subscription[] $subscriptions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAvatarImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCombineNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCoverPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereEarliestReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereGetPreviewBuilds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHasAndroidApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHasChromeExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereHasIosApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User
 *     whereLastSmsTrackingReminderNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLatestReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereOldUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePhoneVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePushNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereReferrerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSendPredictorEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSendReminderNotificationEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSmsNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSpam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStripeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStripePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStripeSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSubscriptionEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereSubscriptionProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereTagLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereTimeZoneOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereTrackLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUnsubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereZipCode($value)
 * @mixin Eloquent
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property string|null $last_login_at
 * @property string|null $timezone
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereTimezone($value)
 * @property int|null $number_of_correlations
 * @property int|null $number_of_connections
 * @property int|null $number_of_tracking_reminders
 * @property int|null $number_of_user_variables
 * @property int|null $number_of_raw_measurements_with_tags
 * @property int|null $number_of_raw_measurements_with_tags_at_last_correlation
 * @property int|null $number_of_votes
 * @property int|null $number_of_studies
 * @property string|null $last_correlation_at
 * @property string|null $last_email_at
 * @property string|null $last_push_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastCorrelationAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastEmailAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereLastPushAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfConnections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfRawMeasurementsWithTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User
 *     whereNumberOfRawMeasurementsWithTagsAtLastCorrelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfVotes($value)
 * @property int|null $primary_outcome_variable_id
 * @property-read int|null $apps_count
 * @property-read int|null $measurements_count
 * @property-read int|null $notifications_count
 * @property-read int|null $organizations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Phrase[] $phrases
 * @property-read int|null $phrases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Purchase[] $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|SentEmail[] $sent_emails
 * @property-read int|null $sent_emails_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Study[] $studies
 * @property-read int|null $studies_count
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TrackerLog[] $tracker_logs
 * @property-read int|null $tracker_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TrackerSession[] $tracker_sessions
 * @property-read int|null $tracker_sessions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TrackingReminderNotification[]
 *     $tracking_reminder_notifications
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|TrackingReminder[] $tracking_reminders
 * @property-read int|null $tracking_reminders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|ConnectorImport[] $updates
 * @property-read int|null $connector_imports_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserTag[] $user_tags
 * @property-read int|null $user_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read \Illuminate\Database\Eloquent\Collection|WpUsermetum[] $wp_usermeta
 * @property-read int|null $wp_usermeta_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @property-read int|null $variable_user_sources_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Vote[] $votes
 * @property-read int|null $votes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|WpPost[] $wp_posts
 * @property-read int|null $wp_posts_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User wherePrimaryOutcomeVariableId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Application[] $applications
 * @property-read int|null $applications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|OAAccessToken[] $oa_access_tokens
 * @property-read int|null $oa_access_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|OAAuthorizationCode[] $oa_authorization_codes
 * @property-read int|null $oa_authorization_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|OAClient[] $oa_clients
 * @property-read int|null $oa_clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|OARefreshToken[] $oa_refresh_tokens
 * @property-read int|null $oa_refresh_tokens_count
 * @property-read Button $button
 * @property-read \Illuminate\Database\Eloquent\Collection|ButtonClick[] $button_clicks
 * @property-read int|null $button_clicks_count
 * @property-read Card $card
 * @property-read \Illuminate\Database\Eloquent\Collection|Collaborator[] $collaborators
 * @property-read int|null $collaborators_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Connection[] $connections
 * @property-read int|null $connections_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Credential[] $credentials
 * @property-read int|null $credentials_count
 * @property-read \Illuminate\Database\Eloquent\Collection|DeviceToken[] $device_tokens
 * @property-read int|null $device_tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|MeasurementExport[] $measurement_exports
 * @property-read int|null $measurement_exports_count
 * @property-read \Illuminate\Database\Eloquent\Collection|MeasurementImport[] $measurement_imports
 * @property-read int|null $measurement_imports_count
 * @property-read \App\Models\User|null $user
 * @property-read Variable|null $variable
 * @property-read \Illuminate\Database\Eloquent\Collection|Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ConnectorImport[] $connector_imports
 * @property-read \Illuminate\Database\Eloquent\Collection|UserClient[] $user_clients
 * @property-read int|null $user_clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|UserVariableClient[] $user_variable_clients
 * @property-read int|null $user_variable_clients_count
 * @property int|null $wp_post_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereWpPostId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|WpLink[] $wp_links
 * @property-read int|null $wp_links_count
 * @property-read \App\Models\WpPost $wp_post
 * @property string|null $analysis_ended_at
 * @property string|null $analysis_requested_at
 * @property string|null $analysis_started_at
 * @property string|null $internal_error_message
 * @property string|null $newest_data_at
 * @property string|null $reason_for_analysis
 * @property string|null $user_error_message
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @property string|null $analysis_settings_modified_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $followedUsers
 * @property-read int|null $followed_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $followers
 * @property-read int|null $followers_count
 * @property int|null $number_of_applications Number of Applications for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from applications
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_applications = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_access_tokens Number of OAuth Access Tokens for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(access_token) as total, user_id
 *                             from oa_access_tokens
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_oauth_access_tokens = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_authorization_codes Number of OAuth Authorization Codes for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(authorization_code) as total, user_id
 *                             from oa_authorization_codes
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_oauth_authorization_codes = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_clients Number of OAuth Clients for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(client_id) as total, user_id
 *                             from oa_clients
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_oauth_clients = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_refresh_tokens Number of OAuth Refresh Tokens for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(refresh_token) as total, user_id
 *                             from oa_refresh_tokens
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_oauth_refresh_tokens = count(grouped.total)
 *                 ]
 * @property int|null $number_of_button_clicks Number of Button Clicks for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from button_clicks
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_button_clicks = count(grouped.total)
 *                 ]
 * @property int|null $number_of_collaborators Number of Collaborators for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from collaborators
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_collaborators = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_imports Number of Connector Imports for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from connector_imports
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_connector_imports = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_requests Number of Connector Requests for this User.
 *                 [Formula:
 *                     update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from connector_requests
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_connector_requests = count(grouped.total)
 *                 ]
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Button[] $buttons
 * @property-read int|null $buttons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ConnectorRequest[] $connector_requests
 * @property-read int|null $connector_requests_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CorrelationCausalityVote[]
 *     $correlation_causality_votes
 * @property-read int|null $correlation_causality_votes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CorrelationUsefulnessVote[]
 *     $correlation_usefulness_votes
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read \App\Models\Variable|null $primary_outcome_variable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Purchase[] $purchases_where_subscriber_user
 * @property-read int|null $purchases_where_subscriber_user_count
 * @property-read \App\Models\User|null $referrer_user
 * @property-read \Illuminate\Database\Eloquent\Collection|Token[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $wp_users_where_referrer_user
 * @property-read int|null $wp_users_where_referrer_user_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfButtonClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfCollaborators($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfOauthAccessTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfOauthAuthorizationCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfOauthClients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfOauthRefreshTokens($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users_where_referrer_user
 * @property-read int|null $users_where_referrer_user_count
 * @property int|null $number_of_measurement_exports Number of Measurement Exports for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from measurement_exports
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_measurement_exports = count(grouped.total)]
 * @property int|null $number_of_measurement_imports Number of Measurement Imports for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from measurement_imports
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_measurement_imports = count(grouped.total)]
 * @property int|null $number_of_measurements Number of Measurements for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from measurements
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_measurements = count(grouped.total)]
 * @property int|null $number_of_sent_emails Number of Sent Emails for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from sent_emails
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_sent_emails = count(grouped.total)]
 * @property int|null $number_of_subscriptions Number of Subscriptions for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from subscriptions
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_subscriptions = count(grouped.total)]
 * @property int|null $number_of_tracking_reminder_notifications Number of Tracking Reminder Notifications for this
 *     User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from tracking_reminder_notifications
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_tracking_reminder_notifications = count(grouped.total)]
 * @property int|null $number_of_user_tags Number of User Tags for this User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(id) as total, user_id
 *                             from user_tags
 *                             group by user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.user_id
 *                     set wp_users.number_of_user_tags = count(grouped.total)]
 * @property int|null $number_of_users_where_referrer_user Number of Users for this Referrer User.
 *                     [Formula: update wp_users
 *                         left join (
 *                             select count(ID) as total, referrer_user_id
 *                             from wp_users
 *                             group by referrer_user_id
 *                         )
 *                         as grouped on wp_users.ID = grouped.referrer_user_id
 *                     set wp_users.number_of_users_where_referrer_user = count(grouped.total)]
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfMeasurementExports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfMeasurementImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfSentEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfSubscriptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User
 *     whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfUserTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereNumberOfUsersWhereReferrerUser($value)
 * @property int $share_all_data
 * @property string|null $deletion_reason The reason the user deleted their account.
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $followings
 * @property-read int|null $followings_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User whereShareAllData($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property int $number_of_patients
 * @property-read Collection|\Overtrue\LaravelFavorite\Favorite[] $favorites
 * @property-read int|null $favorites_count
 * @property-read Collection|Like[] $likes
 * @property-read int|null $likes_count
 * @property-read Collection|\App\Models\TrackingReminderNotification[] $past_tracking_reminder_notifications
 * @property-read int|null $past_tracking_reminder_notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNumberOfPatients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @property bool|null $is_public
 * @property int $sort_order
 * @property int $number_of_sharers Number of people sharing their data with you.
 * @property int $number_of_trustees Number of people that you are sharing your data with.
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read Collection|\App\Models\ChildParent[] $child_parents
 * @property-read int|null $child_parents_count
 * @property-read Collection|\App\Models\ChildParent[] $child_parents_where_parent_user
 * @property-read int|null $child_parents_where_parent_user_count
 * @property-read \App\Models\OAClient $client
 * @property mixed|null $raw
 * @property-read \App\Models\OAClient $oa_client
 * @property-read Collection|\App\Models\PatientPhysician[] $patient_physicians_where_patient_user
 * @property-read int|null $patient_physicians_where_patient_user_count
 * @property-read Collection|\App\Models\PatientPhysician[] $patient_physicians_where_physician_user
 * @property-read int|null $patient_physicians_where_physician_user_count
 * @property-read Collection|\App\Models\SharerTrustee[] $sharer_trustees_where_sharer_user
 * @property-read int|null $sharer_trustees_where_sharer_user_count
 * @property-read Collection|\App\Models\SharerTrustee[] $sharer_trustees_where_trustee_user
 * @property-read int|null $sharer_trustees_where_trustee_user_count*
 * @property string $eth_address
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNumberOfSharers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNumberOfTrustees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSortOrder($value)
 */
class User extends BaseWpUser
	implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract,
	\Filament\Models\Contracts\FilamentUser,
	HasMedia {
    use HasFactory;
	use Tokenizer;
	use Tokenizable;
	use HasButton, HasName, IsEditable;
	use HasUser;
	use HasRoles;
	use HasFiles, PostableTrait;
	use HasPatients;
	use Aliases, AnalyzableTrait, Authenticatable, Authorizable, Billable, CanResetPassword, Favoriter, Liker, Followable, //CacheQueryBuilder,
		HasApiTokens, HasErrors, HasLocalDates, // HasRoleAndPermission breaks https://local.quantimo.do/datalab/users/230 profile without any error message,
		Notifiable, HasDBModel;
	public const ANALYZABLE           = true;
	public const CLASS_DESCRIPTION    = 'Scientists!';
	public const CLASS_CATEGORY       = "Users";
	public const COLOR                = QMColor::HEX_FUCHSIA;
	public const DEFAULT_IMAGE        = ImageUrls::FITNESS_MEASURING_TAPE;
	public const DEFAULT_LIMIT        = 20;
	public const DEFAULT_SEARCH_FIELD = self::FIELD_DISPLAY_NAME;
	public const DEFAULT_ORDERINGS    = [self::FIELD_USER_REGISTERED => self::ORDER_DIRECTION_DESC];
	public const FONT_AWESOME         = FontAwesome::USER_CIRCLE;
	/**
	 * @param array $body
	 * @return User
	 */
	public static function createUserFromClient(array $body): User{
		if(isset($body['email'])){$body[User::FIELD_USER_EMAIL] = $body['email'];}
		$clientId = QMArr::getValueForSnakeOrCamelCaseKey($body, QMUser::FIELD_CLIENT_ID);
        $providerId = UserProviderIdProperty::pluck($body);
        if(!$providerId){
            $providerId = UserUserLoginProperty::pluck($body);
        }
		$body[User::FIELD_USER_LOGIN] = QMStr::truncate($providerId . '-' .$clientId, 49,"");
		$body[User::FIELD_REG_PROVIDER] = $body[QMUser::FIELD_CLIENT_ID] = $clientId;
		$body[User::FIELD_PROVIDER_ID] = $providerId;
		$body[User::FIELD_UNSUBSCRIBED] = true; // Don't want to send other people's clients emails
		if(!isset($body[User::FIELD_USER_EMAIL])){
			$body[User::FIELD_USER_EMAIL] = $providerId."@".$clientId.".com";
		}
        // emails
        unset($body[OAClient::FIELD_CLIENT_SECRET]);
		try {
			return User::createNewUser($body);
		} catch (InvalidUsernameException $e) {
			le($e);
		}
	}
	/**
	 * @param array $requestBody
	 * @return \App\Models\User
	 */
	public static function createNewUserAndLogin(array $requestBody): User{
		$user = User::createNewUser($requestBody);
		$user->login();
		return $user;
	}
	/**
	 * @return User
	 */
	public static function demo(): self{
		return static::findInMemoryOrDB(UserIdProperty::USER_ID_DEMO);
	}
	public static function findByClientUserId(string $clientUserId, string $clientId): ?User{
		$user =
			User::query()->where(BaseWpUser::FIELD_PROVIDER_ID, $clientUserId)
                ->where(BaseWpUser::FIELD_CLIENT_ID, $clientId)
				->first();
		return $user;
	}
	public static function getSlimClass(): string{ return QMUser::class; }
	public $timestamps = true;
	protected $table = 'wp_users';
	// Hide the user_pass field
	protected $primaryKey = 'ID';
	protected $hidden = [
		self::FIELD_USER_PASS,
		self::FIELD_PASSWORD,
	];
	protected $appends = [
		'client_user_id'
	];
	protected $guarded = [
		self::FIELD_ROLES,
		self::FIELD_USER_PASS,
		self::FIELD_REMEMBER_TOKEN,
		self::FIELD_PROVIDER_TOKEN,
		self::FIELD_SPAM,
		self::FIELD_USER_LOGIN,
		self::FIELD_CLIENT_ID,
		self::FIELD_DELETED_AT,
		self::FIELD_USER_REGISTERED,
		self::FIELD_CARD_BRAND,
		self::FIELD_CARD_LAST_FOUR,
		self::FIELD_STRIPE_SUBSCRIPTION,
		self::FIELD_SUBSCRIPTION_ENDS_AT,
		self::FIELD_SUBSCRIPTION_PROVIDER,
	];
	protected array $rules = [
		self::FIELD_ADDRESS => 'nullable|max:255',
		self::FIELD_AVATAR_IMAGE => 'nullable|max:2083',
		self::FIELD_BIRTHDAY => 'nullable|max:255',
		self::FIELD_CARD_BRAND => 'nullable|max:255',
		self::FIELD_CARD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_CLIENT_ID => 'required|min:2|max:255',
		// We have a 2 letter client
		self::FIELD_COMBINE_NOTIFICATIONS => 'nullable|boolean',
		self::FIELD_COUNTRY => 'nullable|max:255',
		self::FIELD_COVER_PHOTO => 'nullable|max:2083',
		self::FIELD_CURRENCY => 'nullable|max:255',
		self::FIELD_DELETED => 'nullable|boolean',
		self::FIELD_DISPLAY_NAME => 'nullable|max:250',
		self::FIELD_EARLIEST_REMINDER_TIME => 'nullable|string',
		self::FIELD_FIRST_NAME => 'nullable|max:255',
		self::FIELD_GENDER => 'nullable|max:255',
		self::FIELD_GET_PREVIEW_BUILDS => 'nullable|boolean',
		self::FIELD_HAS_ANDROID_APP => 'nullable|boolean',
		self::FIELD_HAS_CHROME_EXTENSION => 'nullable|boolean',
		self::FIELD_HAS_IOS_APP => 'nullable|boolean',
		//self::FIELD_ID => 'required|numeric|min:0',
		// Don't use unique:wp_users,ID because it constantly queries the database to check if the ID is unique
		self::FIELD_LANGUAGE => 'nullable|max:255',
		self::FIELD_LAST_CORRELATION_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_EMAIL_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_LAST_LOGIN_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_NAME => 'nullable|max:255',
		self::FIELD_LAST_PUSH_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID => 'nullable|numeric|min:0',
		self::FIELD_LATEST_REMINDER_TIME => 'nullable|string',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_STUDIES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_VOTES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OLD_USER => 'nullable|boolean',
		self::FIELD_PHONE_NUMBER => 'nullable|max:25',
		self::FIELD_PHONE_VERIFICATION_CODE => 'nullable|max:25',
		self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PROVIDER_ID => 'nullable|max:255',
		self::FIELD_PROVIDER_TOKEN => 'nullable|max:255',
		self::FIELD_PUSH_NOTIFICATIONS_ENABLED => 'nullable|boolean',
		self::FIELD_REFERRER_USER_ID => 'nullable|numeric|min:0',
		self::FIELD_REFRESH_TOKEN => 'nullable|max:255',
		self::FIELD_REG_PROVIDER => 'nullable|max:25',
		self::FIELD_REMEMBER_TOKEN => 'nullable|max:100',
		self::FIELD_ROLES => 'nullable|max:255',
		self::FIELD_SEND_PREDICTOR_EMAILS => 'nullable|boolean',
		self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 'nullable|boolean',
		self::FIELD_SMS_NOTIFICATIONS_ENABLED => 'nullable|boolean',
		self::FIELD_SPAM => 'nullable|boolean',
		self::FIELD_STATE => 'nullable|max:255',
		self::FIELD_STRIPE_ACTIVE => 'nullable|boolean',
		self::FIELD_STRIPE_ID => 'nullable|max:255',
		self::FIELD_STRIPE_PLAN => 'nullable|max:100',
		self::FIELD_STRIPE_SUBSCRIPTION => 'nullable|max:255',
		self::FIELD_SUBSCRIPTION_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_SUBSCRIPTION_PROVIDER => 'nullable',
		self::FIELD_TAG_LINE => 'nullable|max:255',
		self::FIELD_TIME_ZONE_OFFSET => 'nullable|integer|min:-1440|max:1440',
		self::FIELD_TIMEZONE => 'nullable|max:255',
		self::FIELD_TRACK_LOCATION => 'nullable|boolean',
		self::FIELD_TRIAL_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_UNSUBSCRIBED => 'nullable|boolean',
		self::FIELD_USER_ACTIVATION_KEY => 'nullable|max:255',
		self::FIELD_USER_EMAIL => 'nullable|min:3|max:100',
		// Don't use unique:wp_users,user_email because it constantly queries the database to check if the ID is unique
		self::FIELD_EMAIL => 'nullable|min:3|max:100',
		// Don't use unique:wp_users,user_email because it constantly queries the database to check if the ID is unique
		self::FIELD_USER_LOGIN => 'required|min:2|max:60',
		// Don't use unique:wp_users,user_login because it constantly queries the database to check if the ID is unique
		self::FIELD_USER_NICENAME => 'nullable|max:50',
		self::FIELD_USER_PASS => 'nullable|min:6|max:255',
		self::FIELD_USER_REGISTERED => 'nullable|date',
		self::FIELD_USER_STATUS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_USER_URL => 'nullable|max:2083',
		self::FIELD_VERIFIED => 'nullable|max:255',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
		self::FIELD_ZIP_CODE => 'nullable|max:255',
	];
	/**
	 * @var array
	 */
	protected static array $aliases = [
		'name' => self::FIELD_DISPLAY_NAME,
		'username' => self::FIELD_USER_LOGIN,
		// Need actual email field for password resets 'email' => self::FIELD_USER_EMAIL,
		'url' => self::FIELD_USER_URL,
		'image' => self::FIELD_AVATAR_IMAGE,
		'avatar' => self::FIELD_AVATAR_IMAGE,
		'password' => self::FIELD_USER_PASS,
		'id' => self::FIELD_ID,
	];
	protected array $openApiSchema = [
		self::FIELD_ROLES => ['type' => 'array', 'items' => ['type' => 'string']],
	];
	protected $casts = [ // Need to a separate one from BaseWpUser because roles has to be cast to array
		self::FIELD_ADDRESS => 'string',
		self::FIELD_AVATAR_IMAGE => 'string',
		self::FIELD_BIRTHDAY => 'string',
		self::FIELD_CARD_BRAND => 'string',
		self::FIELD_CARD_LAST_FOUR => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMBINE_NOTIFICATIONS => 'bool',
		self::FIELD_COUNTRY => 'string',
		self::FIELD_COVER_PHOTO => 'string',
		self::FIELD_CURRENCY => 'string',
		self::FIELD_DELETED => 'int',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_EARLIEST_REMINDER_TIME => 'string',
		self::FIELD_FIRST_NAME => 'string',
		self::FIELD_GENDER => 'string',
		self::FIELD_GET_PREVIEW_BUILDS => 'bool',
		self::FIELD_HAS_ANDROID_APP => 'bool',
		self::FIELD_HAS_CHROME_EXTENSION => 'bool',
		self::FIELD_HAS_IOS_APP => 'bool',
		self::FIELD_ID => 'int',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_LANGUAGE => 'string',
		self::FIELD_LAST_FOUR => 'string',
		self::FIELD_LAST_NAME => 'string',
		self::FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID => 'int',
		self::FIELD_LATEST_REMINDER_TIME => 'string',
		self::FIELD_NUMBER_OF_APPLICATIONS => 'int',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'int',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'int',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'int',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'int',
		self::FIELD_NUMBER_OF_OAUTH_CLIENTS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'int',
		self::FIELD_NUMBER_OF_STUDIES => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VOTES => 'int',
		self::FIELD_OLD_USER => 'bool',
		self::FIELD_PHONE_NUMBER => 'string',
		self::FIELD_PHONE_VERIFICATION_CODE => 'string',
		self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => 'int',
		self::FIELD_PROVIDER_ID => 'string',
		self::FIELD_PROVIDER_TOKEN => 'string',
		self::FIELD_PUSH_NOTIFICATIONS_ENABLED => 'bool',
		self::FIELD_REASON_FOR_ANALYSIS => 'string',
		self::FIELD_REFERRER_USER_ID => 'int',
		self::FIELD_REFRESH_TOKEN => 'string',
		self::FIELD_REG_PROVIDER => 'string',
		self::FIELD_REMEMBER_TOKEN => 'string',
		self::FIELD_ROLES => 'array',
		self::FIELD_SEND_PREDICTOR_EMAILS => 'bool',
		self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 'bool',
		self::FIELD_SHARE_ALL_DATA => 'bool',
		self::FIELD_SMS_NOTIFICATIONS_ENABLED => 'bool',
		self::FIELD_SPAM => 'int',
		self::FIELD_STATE => 'string',
		self::FIELD_STATUS => 'string',
		self::FIELD_STRIPE_ACTIVE => 'bool',
		self::FIELD_STRIPE_ID => 'string',
		self::FIELD_STRIPE_PLAN => 'string',
		self::FIELD_STRIPE_SUBSCRIPTION => 'string',
		self::FIELD_SUBSCRIPTION_PROVIDER => 'string',
		self::FIELD_TAG_LINE => 'string',
		self::FIELD_TIMEZONE => 'string',
		self::FIELD_TIME_ZONE_OFFSET => 'int',
		self::FIELD_TRACK_LOCATION => 'bool',
		self::FIELD_UNSUBSCRIBED => 'bool',
		self::FIELD_USER_ACTIVATION_KEY => 'string',
		self::FIELD_USER_EMAIL => 'string',
		self::FIELD_EMAIL => 'string', // Need email field for Laravel password reset
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_LOGIN => 'string',
		self::FIELD_USER_NICENAME => 'string',
		self::FIELD_USER_PASS => 'string',
		self::FIELD_USER_STATUS => 'int',
		self::FIELD_USER_URL => 'string',
		self::FIELD_VERIFIED => 'string',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_ZIP_CODE => 'string',
	];
	/**
	 * @param int $id
	 * @return User|null
	 */
	public static function findDeleted(int $id): ?User{
		return User::whereID($id)->withTrashed()->first();
	}
	/**
	 * @param array $arr
	 */
	public static function setUserPlatform(array $arr): void{
		$u = QMAuth::getQMUserIfSet();
		if(!$arr || !$u){
			return;
		}
		$l = $u->l();
		$platform = QMArr::getValueForSnakeOrCamelCaseKey($arr, 'platformType');
		if($platform === 'ios' && !$l->has_ios_app){
			$l->has_ios_app = true;
			try {
				$l->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
			if(!$u->hasIosApp){
				$users = Memory::getUsers();
				le("Not updating DBModel on save!");
			}
		}
		if($platform === 'android' && !$l->has_android_app){
			$l->has_android_app = true;
			try {
				$l->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
		}
		if($platform === 'chrome' && !$l->has_chrome_extension){
			$l->has_chrome_extension = true;
			try {
				$l->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @return User
	 */
	public static function testUser(): User{
		$u = static::findInMemoryOrDB(UserIdProperty::USER_ID_TEST_USER);
        if(!$u){
            $u = static::firstOrFakeSave();
        }
        return $u;
	}
	/**
	 * @param array $data
	 * @return void
	 */
	public static function validatePasswordCreateNewUserAndLogin(array $data): void{
		$plain = UserPasswordProperty::pluck($data);
		$data[User::FIELD_PASSWORD] = $plain;
		if($email = UserUserEmailProperty::pluckOrDefault($data)){
			if(QMUser::findByEmail(UserUserEmailProperty::pluckOrDefault($data))){
				throw new BadRequestException("User with email $email already exists!");
			}
		}
		if($login = UserUserLoginProperty::pluckOrDefault($data)){
			if(User::findByLoginName($login)){
				throw new BadRequestException("User $login already exists!");
			}
		}
		User::createNewUserAndLogin($data);
	}
	/**
	 * @return array
	 */
	public static function getAccessibleUsers(): array{
		$u = QMAuth::getUser();
		$ids[] = $u->getAccessibleUserIds();
		foreach($ids as $id){
			$users[] = self::findInMemoryOrDB($id);
		}
		return $users;
	}
	public static function findByEthAddress(string $ethAddress){
		$all = static::getAllFromMemoryIndexedById();
		foreach($all as $user){
			if($user->eth_address === $ethAddress){
				return $user;
			}
		}
		$first = User::whereEthAddress($ethAddress)->first();
		if($first){$first->addToMemory();}
		return $first;
	}
	public function calculateInterestingNumberOfRelationCounts(): array{
		return $this->getQMUser()->calculateInterestingNumberOfRelationCounts();
	}
	/**
	 * @param array $purchaseData
	 * @return mixed
	 */
	public function recordPurchase(array $purchaseData){
		$purchaseData['updated_at'] = date("c");
		$purchaseData['created_at'] = date("c");
		$purchaseData['client_id'] = StripeService::getClientId();
		if($this->last_four){
			$purchaseData['last_four'] = $this->last_four;
		}
		$purchase = Purchase::create($purchaseData);
		return $purchase->id;
	}
	public function fixIfDifferenceBetweenEarliestAndLatestTimesIsLessThanTwelveHours(){
		$this->getQMUser()->fixIfDifferenceBetweenEarliestAndLatestTimesIsLessThanTwelveHours();
	}
	public function getEmail(): string{
		return $this->getUserEmailAttribute();
	}
	public function getPrincipalInvestigatorProfileHtml(): string{
		return $this->getQMUser()->getPrincipalInvestigatorProfileHtml();
	}
	/**
	 * @return \App\Models\User
	 */
	public function unsetCustomProperties(): User{
		//update `wp_users` set `updated_at` = ?, `accessToken` = ?, `refreshToken` = ?, `accessTokenExpires` = ?, `accessTokenExpiresAtMilliseconds` = ?, `remember_token` = ? where `ID` = ?
		unset($this->accessAndRefreshToken);
		unset($this->accessToken);
		unset($this->refreshToken);
		unset($this->accessTokenExpires);
		unset($this->accessTokenExpiresAtMilliseconds);
		return $this;
	}
	/**
	 * @return HasMany|Application[]
	 */
	public function applications(): HasMany{
		return $this->hasMany(Application::class, 'user_id', 'id');
	}
	/**
	 * @return string
	 */
	public function getEmailForPasswordReset(): string{
		return $this->attributes[self::FIELD_USER_EMAIL];
	}
	/**
	 * @return HasMany
	 */
	public function organizations(): HasMany{
		return $this->hasMany(Organization::class);
	}
	/**
	 * @param string $role
	 * @return bool
	 */
	public function inRole(string $role): bool{
		$roles = $this->roles ?? [];
		return in_array($role, $roles);
	}
	/**
	 * @return QMUser
	 */
	public function getQMUser(): QMUser{
		$qmUser = QMUser::findInMemory($this->getId());
		if(!$qmUser){
			$qmUser = $this->instantiateQMUser();
		}
		if(empty($qmUser->loginName)){
			le('empty($qmUser->loginName)');
		}
		return $qmUser;
	}
	/**
	 * @param string|null $clientId
	 * @return string
	 */
	public function getOrCreateAccessTokenString(string $clientId = null): ?string{
		$u = $this->getQMUser();
		return $u->getOrSetAccessTokenString($clientId);
	}
    public function getOrCreateAccessToken(string $clientId):OAAccessToken{
        $u = OAAccessToken::findInMemoryDBOrCreate([
            'user_id' => $this->getId(),
            'client_id' => $clientId,
        ]);
        return $u;
    }
    public function getOrCreateRefreshToken(string $clientId):OARefreshToken{
        $u = OARefreshToken::findInMemoryDBOrCreate([
            'user_id' => $this->getId(),
            'client_id' => $clientId,
        ]);
        return $u;
    }
	/**
	 * @return string
	 * Returns encrypted password
	 */
	public function getHashedPassword(): string{
		return $this->user_pass;
	}
	/**
	 * @return string
	 * Returns encrypted password
	 */
	public function getEncryptedPassword(): string{
		return $this->getHashedPassword();
	}
	public function addAccessTokenIfNecessary(string $clientId = null): void{
		$qmUser = $this->getQMUser();
		if(!isset($qmUser->accessToken)){
			if($clientId = $clientId ?? BaseClientIdProperty::fromRequestDirectly(false)){
				$this->addAccessToken($clientId, $qmUser);
			} else{
				QMLog::errorWithoutObfuscation("No client id to create access token!");
			}
		}
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		$id = $this->getIDAttribute();
		if($id === null){
			le("ID should not be null for user " . $this->__toString(), $this);
		}
		return $id;
	}
	/**
	 * @param int $id
	 * @return User|null
	 */
	public static function getById(int $id): ?User{
		return User::findInMemoryOrDB($id);
	}
	public function getFields(): array{
		$fields = parent::getFields();
		$fields[] = Password::make('Password', User::FIELD_USER_PASS)->onlyOnForms()
			->creationRules('required', 'string', 'min:8')->updateRules('nullable', 'string', 'min:8');
		$fields[] = PasswordConfirmation::make('Password Confirmation');
		$fields[] = Timezone::make('Timezone')->onlyOnForms();
		$fields[] = UserVariableBaseAstralResource::hasMany();
		return $fields;
	}
	/**
	 * @param string|int $abbreviationOrOffsetInMinutes
	 */
	public function setTimeZone($abbreviationOrOffsetInMinutes){
		$this->getQMUser()->setTimeZone($abbreviationOrOffsetInMinutes);
	}
	/**
	 * https://laracasts.com/discuss/channels/laravel/email-column-name-different-for-password-reset
	 * @param $driver
	 * @param $notification
	 * @return MorphMany|mixed|string|null
	 */
	public function routeNotificationFor($driver, $notification){
		if(method_exists($this, $method = 'routeNotificationFor' . Str::studly($driver))){
			return $this->{$method}($notification);
		}
		switch($driver) {
			case 'database':
				return $this->notifications();
			case 'mail':
				return $this->getEmailForPasswordReset();
			case 'nexmo':
				return $this->phone_number;
		}
		le("$driver not found");
	}
	/**
	 * @return bool
	 */
	public function isTestUser(): bool{
		return $this->getQMUser()->isTestUser();
	}
	/**
	 * Get a subscription instance by name.
	 * @param string $subscription
	 * @return Subscription|null
	 */
	public function subscription($subscription = 'default'): ?Subscription{
		$instance = $this->subscriptions->sortByDesc(fn($value) => $value->created_at->getTimestamp())
			->first(fn($value) => $value->name === $subscription);
		if($instance){
			// Not sure why user attribute is never set on \Laravel\Cashier\Billable::subscription
			// So I pasted the function above and do it manually here
			$instance->setAttribute('user', $this);
		}
		return $instance;
	}
	/**
	 * @return HasMany|\App\Models\Subscription[]
	 */
	public function subscriptions(): HasMany|array{
		return $this->hasMany(Subscription::class, 'user_id', self::FIELD_ID);
	}
	public function updateLastEmailAt(){
		$this->last_email_at = now_at();
		try {
			$this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @return string|null
	 */
	public function getSubscriptionProvider(): ?string{
		return $this->getQMUser()->getSubscriptionProvider();
	}
	/**
	 * @return User
	 */
	public static function mike(): User{
        $user = self::findInMemoryOrDB(UserIdProperty::USER_ID_MIKE);
        return $user;
	}
	/**
	 * @return User
	 */
	public static function physician(): User{
		$user = self::findInMemoryOrDB(UserIdProperty::USER_ID_PHYSICIAN);
		if(!$user){
			self::createNewUser([
				'id' => UserIdProperty::USER_ID_PHYSICIAN,
				'name' => 'Physician',
				'email' => 'dr@gmail.com',
				'password' => 'testing123',
			]);
		}
		return $user;
	}
	/**
	 * @param string $reason
	 * @throws Exception
	 */
	public function softDeleteWithRelations(string $reason){
		$this->logInfo(__FUNCTION__ . " because $reason");
		$this->cancelDeveloperSubscriptions();
		// Tables that have user_id field
		$tables = [
			'oa_access_tokens',
			'oa_authorization_codes',
			'oa_clients',
			'oa_refresh_tokens',
			'collaborators',
			'connections',
			'credentials',
			'measurement_exports',
			'tracking_reminders',
			'variable_user_sources',
			'wp_usermeta',
		];
		$userId = $this->getId();
		$deletedRows = 0;
		foreach($tables as $table){
			$deletedRows += DB::table($table)->where('user_id', $userId)->delete();
		}
		$this->softDeleteRelations();
		$this->cancelUserSubscription();
		$this->delete();
	}
	/**
	 * @param bool $stripeActive
	 * @param string $subscriptionProvider
	 * @return bool
	 */
	public function setStripActive(bool $stripeActive, string $subscriptionProvider = 'stripe'): bool{
		$this->stripe_active = $stripeActive;
		$this->subscription_provider = $subscriptionProvider;
		try {
			return $this->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param string|int|null $effectNameOrId
	 * @return RootCauseAnalysis
	 */
	public function getRootCauseAnalysis($effectNameOrId = null): RootCauseAnalysis{
		return $this->getQMUser()->getRootCauseAnalysis($effectNameOrId);
	}
	public function getUserId(): ?int{
		$id = $this->attributes[self::FIELD_ID] ?? null;
		if($id === null){
			le("no id");
		}
		return $id;
	}
	/**
	 * @inheritDoc
	 */
	public function getSubtitleAttribute(): string{
		if(!$this->hasId()){
			return static::CLASS_DESCRIPTION;
		}
		return "Overview of discoveries, research from " . $this->display_name . "'s data";
		//return "Summary of ".$this->display_name."'s Data";
		//return $this->tag_line ?? $this->display_name;
	}
	public function getReportTitleAttribute(): string{
		if(!$this->hasId()){
			return "Scientist";
		}
		return $this->display_name . " Research and Data Overview";
	}
	/**
	 * Fallback to title case user_login if no display_name
	 * @return string
	 */
	public function getDisplayNameAttribute(): string{
		return UserDisplayNameProperty::pluckOrDefault($this->attributes);
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 * Don't delete this
	 */
	public function getAvatarImageAttribute(): string{
		$img = $this->attributes[self::FIELD_AVATAR_IMAGE] ?? null;
		if(empty($img)){
			$img = ImageHelper::getRobotPuzzledUrl();
		}
		return $img;
	}
	/**
	 * @return string
	 */
	public function getClientUserIdAttribute(): ?string{
		return $this->provider_id;
	}
	/**
	 * @return mixed
	 */
	public function downgrade(): array{
		if(empty($this->stripe_id)){
			$data['message'] =
				"User does not have stripe id.  Maybe they subscribed via Google or Apple.  Subscription provider is: " .
				$this->subscription_provider;
		} else{
			StripeService::setStripeKeyToTestingIfNecessary();
			$subscription = $this->subscription('main');
			if($subscription){
				$subscription->cancel();
				$this->recordPurchase([
					'product_id' => 'downgrade',
					'subscription_provider' => 'stripe',
					'subscriber_user_id' => $this->getId(),
				]);
				$data['stripeCustomer'] = Customer::retrieve($this->stripe_id);
			} else{
				/** @noinspection PhpUnusedLocalVariableInspection */
				$subscription = $this->subscription('main');
				QMLog::errorOrDebugIfTesting("No subscription to cancel even though user->stripe_id is $this->stripe_id");
			}
		}
		$this->setStripActive(false);
		$data['user'] = $this->getQMUserArray();
		Analytics::trackEvent('Purchase', 'Downgrade', $this->subscription_provider, 0);
		return $data;
	}
	public function getQMUserArray(): array{
		$arr = json_decode(json_encode($this->getQMUser()), true);
		return $arr;
	}
	/**
	 * @param string|null $productId
	 */
	public function swapPlanIfNecessary(string $productId): void{
		if($this->stripe_plan !== $productId && $this->stripe_plan && $this->asStripeCustomer()){
			try {
				$subscription = $this->subscription('main');
				if($subscription){
					$subscription = $subscription->skipTrial();
					/** @noinspection PhpUnusedLocalVariableInspection */
					$owner = $subscription->owner();
					$subscription->swap($productId);
				}
			} catch (Throwable $e) {
				if(AppMode::isTestingOrStaging()){
					/** @var LogicException $e */
					throw $e;
				}
				ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			}
		}
	}
	/**
	 * @return static
	 */
	public static function system(): self{
        $u = User::findInMemoryOrDB(UserIdProperty::USER_ID_SYSTEM);
        if(!$u){
            self::seed();
            $u = User::findInMemoryOrDB(UserIdProperty::USER_ID_SYSTEM);
            if(!$u){
                le("System user not found");
            }
        }
        return $u;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->user_login . " (" . $this->ID . ")";
	}
	/**
	 * @return string
	 */
	public function getDataQuantityListRoundedButtonsHTML(): string{
		return $this->getQMUser()->getDataQuantityListRoundedButtonsHTML();
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return "User data overview and links to analyses.";
	}
	/**
	 * @param int|null $limit
	 * @return User[]|\Illuminate\Support\Collection
	 */
	public static function withoutPosts(int $limit = null){
		$qb = self::withCount('wp_posts');
		$qb->orderBy('wp_posts_count', 'DESC');
		if($limit){
			$qb->take($limit);
		}
		$qb->whereNotIn("users.ID", UserIdProperty::getTestSystemAndDeletedUserIds());
		return $qb->get();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->getDataLabShowUrl($params);
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		return $this->avatar_image;
	}
	public function getNameAttribute(): string{
		return $this->getDisplayNameAttribute();
	}
	/**
	 * @param int|null $limit
	 * @return \Illuminate\Support\Collection|PreviewCard[]
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public function getWpPostPreviewCardsCollection(int $limit = 10){
		\App\Logging\ConsoleLog::info(__METHOD__);
		$posts = $this->latestPosts($limit)->get();
		$cards = [];
		/** @var WpPost $post */
		foreach($posts as $post){
			if(empty($post->post_title)){
				continue;
			}
			$cards[] = $post->getPreviewCard();
		}
		return collect($cards);
	}
	/**
	 * Get latest 5 comments from hasMany relation.
	 * @param int $limit
	 * @return WpPost|\Illuminate\Database\Eloquent\Builder
	 */
	public function latestPosts(int $limit = 5): \Illuminate\Database\Eloquent\Builder{
		return WpPost::wherePostAuthor($this->ID)->where(WpPost::FIELD_POST_TYPE, BasePostTypeProperty::TYPE_POST)
			->where(WpPost::FIELD_POST_NAME, "NOT LIKE", '%-revision-%')->orderBy(WpPost::FIELD_POST_MODIFIED, 'desc')
			->take($limit);
	}
	/**
	 * @param string $previewText
	 * @param int|null $limit
	 * @return MaterialPostsEmail
	 */
	public function getWpPostPreviewTableMail(string $previewText, int $limit = 10): MaterialPostsEmail{
		$posts = $this->getPosts($limit);
		return new MaterialPostsEmail($posts, $previewText);
	}
	/**
	 * @param string $previewText
	 * @param int|null $limit
	 * @return string
	 */
	public function getWpPostPreviewTableHtml(string $previewText, int $limit = 10): string{
		$this->logInfo(__METHOD__ . " getting posts...");
		$qb = WpPost::where(WpPost::FIELD_POST_AUTHOR, $this->ID);
		$qb->where(WpPost::FIELD_POST_TYPE, BasePostTypeProperty::TYPE_POST);
		if($limit){
			$qb->take($limit);
		}
		$posts =
			$qb->get([ // Don't get post_content because it can be huge if we're getting lots of posts and not needed for cards
				WpPost::FIELD_POST_TITLE,
				WpPost::FIELD_POST_AUTHOR,
				WpPost::FIELD_POST_EXCERPT,
				WpPost::FIELD_GUID,
			]);
		if(!$posts->count()){
			return "";
		}
		$mail = new MaterialPostsEmail($posts, $previewText);
		try {
			$html = $mail->render();
			if(is_string($html)){
				return $html;
			} else{
				return $html->render();
			}
		} catch (ReflectionException | Throwable $e) {
			le($e);
		}
	}
	/**
	 * @return string
	 * Really slow!  Use getWpPostPreviewTableHtml instead
	 */
	public function getWpPostPreviewCardsGridHtml(): string{
		$cardCollection = $this->getWpPostPreviewCardsCollection();
		if(!$cardCollection->count()){
			$this->logError("No WpPostPreviewCards!");
			return "";
		}
		try {
			$cardHtml = view('components.post-cards-grid', ['cardCollection' => $cardCollection])->render();
			return CssHelper::inlineCssFromPathsOrUrls([CssHelper::LARAVEL_MATERIAL_CSS], $cardHtml);
		} catch (Throwable $e) {
			le($e);
		}
	}
	/**
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function getWpPostPreviewCardListHtml(): string{
		$cards = $this->getWpPostPreviewCardsCollection();
		$html = '';
		foreach($cards as $card){
			try {
				$html .= $card->html;
			} catch (\Throwable $e) {
				throw new \LogicException(__METHOD__.": ".$e->getMessage());
			}
		}
		$html = CssHelper::addCssUrlsAsLinkTags($html, PreviewCard::CSS);
		return $html;
	}
	/**
	 * @param int $limit
	 * @return \Illuminate\Database\Eloquent\Builder[]|Collection
	 */
	public function getPosts(int $limit){
		$this->logInfo(__METHOD__ . " getting posts...");
		$qb = WpPost::where(WpPost::FIELD_POST_AUTHOR, $this->ID);
		$qb->where(WpPost::FIELD_POST_TYPE, BasePostTypeProperty::TYPE_POST);
		if($limit){
			$qb->take($limit);
		}
		$posts = $qb->get();
		return $posts;
	}
	public function isAdmin(): bool{
		return in_array(UserRolesProperty::ROLE_ADMINISTRATOR, $this->roles);
	}
	public function getRolesAttribute(): array{
		$prop = $this->getPropertyModel(self::FIELD_ROLES);
		return $prop->getAccessorValue();
	}
	/**
	 * @param $value
	 */
	public function setRolesAttribute($value): void{
		$prop = $this->getPropertyModel(self::FIELD_ROLES);
		$prop->processAndSetDBValue($value);
	}
	public function followers(): BelongsToMany{
		return $this->belongsToMany(self::class, 'followers', 'followed_user_id', 'user_id')->withTimestamps();
	}
	public function followedUsers(): BelongsToMany{
		return $this->belongsToMany(self::class, 'followers', 'user_id', 'followed_user_id')->withTimestamps();
	}
	/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
	/**
	 * @param $userId
	 * @return $this
	 */
	public function follow($userId): User{
		$this->followedUsers()->attach($userId);
		return $this;
	}
	/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
	/**
	 * @param $userId
	 * @return $this
	 */
	public function unfollow($userId): User{
		$this->followedUsers()->detach($userId);
		return $this;
	}
	/** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
	/**
	 * @param $userId
	 * @return bool
	 */
	public function isFollowing($userId): bool{
		return (boolean)$this->followedUsers()->where('followed_user_id', $userId)->first(['id']);
	}
	/** @noinspection PhpUnused */
	/**
	 * @return int|string
	 */
	public function receivesBroadcastNotificationsOn(){
		//return 'users.' . $this->id; // I think it needs to be like this for coreproc/astral-notification-feed
		return self::generateBroadcastChannelName($this->ID);
	}
	/**
	 * @param $userIdOrPlaceholder
	 * @return string|int
	 */
	public static function generateBroadcastChannelName($userIdOrPlaceholder): string{
		$name = 'App.User';
		if(!$userIdOrPlaceholder){
			return $name;
		}
		return $name . '.' . $userIdOrPlaceholder;
	}
	public function getNumberOfUserVariablesButton(): QMButton{
		return UserUserVariablesButton::instance($this);
	}
	public function getNumberOfCorrelationsButton(): QMButton{
		return UserVariableRelationshipsButton::instance($this);
	}
	public function getNumberOfConnectionsButton(): QMButton{
		return UserConnectionsButton::instance($this);
	}
	public function getNumberOfStudiesButton(): QMButton{
		return UserStudiesButton::instance($this);
	}
	public function getNumberOfVotesButton(): QMButton{
		return UserVotesButton::instance($this);
	}
	public function getNumberOfTrackingRemindersButton(): QMButton{
		$b = UserTrackingRemindersButton::instance($this);
		return $b;
	}
	public function getTrackingReminderNotificationsButton(): QMButton{
		return UserVariableTrackingReminderNotificationsButton::instance($this);
	}
	public function getMeasurementsButton(): QMButton{
		$b = new UserMeasurementsButton($this);
		return $b;
	}
	/**
	 * Route notifications for the FCM channel.
	 * @param \Illuminate\Notifications\Notification $notification
	 * @return array
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function routeNotificationForFcm(\Illuminate\Notifications\Notification $notification): array{
		$all = $this->device_tokens;
		if(!$all->count()){
			$this->logError("No device tokens for $this");
		}
		$tokens = $all->where(DeviceToken::FIELD_PLATFORM, "<>", BasePlatformProperty::PLATFORM_IOS)
			->pluck(DeviceToken::FIELD_DEVICE_TOKEN)->all();
		return $tokens;
	}

    /**
     * @return string
     * @throws NoGeoDataException
     */
    public function getCountryCode(): ?string{
        $code = $this->country;
        if($code && BaseCountryProperty::isCountryCode($code)){
            return $code;
        }
	    $tz = $this->timezone;
	    if($tz && str_starts_with($tz, "America")){
		    return BaseCountryProperty::COUNTRY_CODE_US;
	    }
        if($zip = $this->zip_code){
            $code = BaseCountryProperty::getCountryCodeFromZip($zip);
            if($code){
                return $code;
            }
        }
        if($geoIP = $this->getIpGeoLocation()){
            return $geoIP->country_code;
        }
		return null;
	}
	/**
	 * @param string $clientId
	 * @return string
	 */
	public function getClientUserId(string $clientId){
		return $this->getUserMetaValue($clientId . '_client_user_id');
	}
	/**
	 * @param string $clientId
	 * @param $id
	 * @return mixed
	 */
	public function setClientUserId(string $clientId, $id): bool{
		return $this->setUserMetaValue($clientId . '_client_user_id', $id);
	}
	public function getTruncatedNameLink(array $params = []): string{
		$url = $this->getUrl($params);
		$truncated = QMStr::truncate($this->user_login, 5, "");
		$class = QMStr::classToTitle((new \ReflectionClass(static::class))->getShortName());
		return "<a href=\"$url\" target='_self' title=\"See $truncated $class Details\">$truncated...</a>";
	}
	public static function attributeToTitle(string $attribute): string{
		$title = str_replace('user_', '', $attribute);
		$title = parent::attributeToTitle($title);
		return $title;
	}
	/**
	 * @param string $reason
	 * @return bool|null
	 */
	public function hardDeleteWithRelations(string $reason): ?bool{
		if(!$this->isTestUser()){
			$this->isTestUser();
			le("Why are we deleting $this");
		}
		$this->logError("Hard deleting because $reason");
		$id = $this->getId();
		\App\Models\Application::whereUserId($id)->forceDelete();
		OARefreshToken::whereUserId($id)->forceDelete();
		OAAccessToken::whereUserId($id)->forceDelete();
		OAAuthorizationCode::whereUserId($id)->forceDelete();
		Measurement::whereUserId($id)->forceDelete();
		TrackingReminderNotification::whereUserId($id)->forceDelete();
		TrackingReminder::whereUserId($id)->forceDelete();
		UserVariableRelationship::whereUserId($id)->forceDelete();
		UserVariableClient::whereUserId($id)->forceDelete();
		WpPost::wherePostAuthor($id)->forceDelete();
		UserVariable::whereUserId($id)->forceDelete();
		$qb = Variable::whereCreatorUserId($id);
		$clients = $qb->get();
		foreach($clients as $client){
			try {
				$client->forceDelete();
			} catch (QueryException $e) {
				if($client->isTestVariable()){
					$client->hardDeleteWithRelations("Is test variable");
				}
			}
		}
		WpUsermetum::whereUserId($id)->forceDelete();
		SentEmail::whereUserId($id)->forceDelete();
		Study::whereUserId($id)->forceDelete();
		Credential::whereUserId($id)->forceDelete();
		UserClient::whereUserId($id)->forceDelete();
		ConnectorRequest::whereUserId($id)->forceDelete();
		ConnectorImport::whereUserId($id)->forceDelete();
		Connection::whereUserId($id)->forceDelete();
		$qb = OAClient::whereUserId($id);
		$clients = $qb->get();
		foreach($clients as $client){
			try {
				$client->forceDelete();
			} catch (QueryException $e) {
				if($client->isTestClient()){
					$client->hardDeleteWithRelations("Is test client");
				} else{
					le("Cannot delete client $client->client_id because " . $e->getMessage());
				}
			}
		}
		try {
			return $this->forceDelete();
		} catch (\Throwable $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return $this->forceDelete();
		}
	}
	private function cancelDeveloperSubscriptions(): void{
		$apps = $this->applications();
		foreach($apps as $app){
			/** @var \App\Models\Application $app */
			if($app->subscribed()){
				$app->subscription()->cancel();
			}
			try {
				$app->delete();
			} catch (Exception $e) {
				/** @var LogicException $e */
				throw $e;
			}
		}
	}
	private function cancelUserSubscription(): void{
		if($this->subscribed()){
			$this->subscription()->cancel();
		}
	}
	private function softDeleteRelations(): void{
		$userId = $this->getId();
		$tablesForSoftDelete = [
			'oa_access_tokens',
			'oa_authorization_codes',
			'oa_clients',
			'oa_refresh_tokens',
			'collaborators',
			'connections',
			'user_variable_relationships',
			'credentials',
			'measurement_exports',
			'measurements',
			'tracking_reminders',
			'user_variables',
			'variable_user_sources',
			'wp_usermeta',
		];
		foreach($tablesForSoftDelete as $table){
			DB::table($table)->where('user_id', $userId)->update(['deleted_at' => date('Y-m-d H:i:s')]);
		}
	}
	public function getRolesString(): string{
		$roles = $this->roles;
		$admin = $this->isAdmin();
		if(!$roles){
			if($admin){
				QMLog::exceptionIfNotProduction("No roles even though an admin!");
			}
			return "No Roles Defined";
		}
		if(in_array(BaseRolesProperty::ROLE_ADMINISTRATOR, $roles) && !$admin){
			QMLog::exceptionIfNotProduction("isAdmin returns false but roles are " . \App\Logging\QMLog::print_r($roles, true) .
				"for $this");
		}
		return implode(', ', $roles);
	}
	/**
	 * @return QMUser
	 */
	public function instantiateQMUser(): QMUser{
		$this->makeVisible($this->hidden);
		$qmUser = new QMUser($this);
		$this->makeHidden($this->hidden);
		if(empty($qmUser->loginName)){le('empty($qmUser->loginName)');}
		if($this->getId() !== $qmUser->id){
			le($qmUser);
		}
		$qmUser->addToMemory();
		return $qmUser;
	}
	/**
	 * @param int $id
	 * @return BaseWpUser|\Illuminate\Database\Eloquent\Builder
	 * DON'T DELETE THIS OR YOU GET
	 * SQLSTATE[42S22]: Column not found: 1054 Unknown column 'i_d' in 'where clause' (SQL: select * from `wp_users`
	 *     where `i_d` = 1
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function whereID($id){
		return static::where(static::FIELD_ID, $id);
	}
	public function getUserIdLink(array $params = []): string{
		return $this->getDataLabIdLink($params);
	}
	public function updateInterestingRelationshipCountFields(): array{
		$this->number_of_raw_measurements_with_tags =
			$this->user_variables()->sum(\App\Models\UserVariable::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_JOINS_CHILDREN);
		$arr =
			parent::updateInterestingRelationshipCountFields(); // Must come after number_of_raw_measurements_with_tags so it's saved to DB
		$arr[self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS] = $this->number_of_raw_measurements_with_tags;
		$table = [];
		foreach($arr as $key => $val){
			$table[] = ['Relationship' => $key, 'Total' => $val];
		}
		QMClockwork::addUserTable("Relationship Counts", $table);
		return $arr;
	}
	public function needsToApproveFollowRequests(): bool{
		// Your custom logic here
		return (bool)$this->getIsPublic();
	}
	public function getTimeSinceRegistered(): string{
		return TimeHelper::timeSinceHumanString($this->user_registered);
	}
	/**
	 * Route notifications for the mail channel.
	 * @param \Illuminate\Notifications\Notification $notification
	 * @return string
	 * @noinspection PhpUnused
	 * @noinspection PhpUnusedParameterInspection
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function routeNotificationForMail($notification): string{
		return $this->user_email;
	}
	/**
	 * @return bool|null
	 */
	public function delete(): ?bool{
		$this->logError("Deleting $this");
		return parent::delete();
	}
	public function getAccessibleUserIds(): array{
		return $this->getQMUser()->getAccessibleUserIds();
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $qb
	 * @param User|QMUser $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		// Don't need to restrict by IS_PUBLIC $qb = parent::restrictQueryBasedOnPermissions($qb, $user);
		if($user->isAdmin()){
			return $qb;
		}
		$qb->whereIn(User::TABLE . '.' . User::FIELD_ID, $user->getAccessibleUserIds());
		return $qb;
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $value
	 */
	public function setAvatarImageAttribute($value){
		if($value && !Str::startsWith($value, 'http')){
			$value = \Storage::disk(S3Public::DISK_NAME)->url($value);
		}
		$this->attributes[self::FIELD_AVATAR_IMAGE] = $value;
	}
	public function getEditUrl(array $params = []): string{
		return SettingsStateButton::make()->getUrl($params);
	}
	public function getTimezoneIfSet(): ?string{
		if($this->timezone === false){
			return null;
		}
		if($this->timezone){
			return $this->timezone;
		}
		try {
			return $this->timezone = $this->getTimezone();
		} catch (NoTimeZoneException $e) {
			$this->logInfo("Assuming UTC timezone for $this because:\n" . $e->getProblemAndSolutionString());
			$this->timezone = false;
			return null;
		}
	}
	/**
	 * @return string
	 * @throws NoTimeZoneException
	 */
	public function getTimezone(): string{
		if($this->timezone){
			return $this->timezone;
		}
		if($this->time_zone_offset === null){
			throw new NoTimeZoneException($this);
		}
		$minutes = $this->minutesBeforeUTC();
		$abbreviation = QMTimeZone::convertTimeZoneOffsetToStringAbbreviation($minutes);
		if(empty($abbreviation)){
			throw new NoTimeZoneException($this, "Could not determine time zone for $minutes minutes offset!");
		}
		return $this->timezone = $abbreviation;
	}
	/**
	 * The time-zone offset is the difference, in minutes, between UTC and local time. Note that this means that
	 * the offset is positive if the local timezone is behind UTC (i.e. UTC−06:00 Central) and negative if it is ahead.
	 * To convert local to UTC, add this value
	 * To convert UTC to local, subtract this value
	 * @return int
	 */
	public function minutesBeforeUTC(): ?int{
		return $this->getQMUser()->minutesBeforeUTC();
	}
	/**
	 * @param int|string $utc
	 * @return string
	 */
	public function utcToLocalHis($utc): string{
		$c = $this->convertToLocalTimezone($utc);
		return $c->format('H:i:s');
	}
	/**
	 * @param string $localTimeString
	 * @return string
	 */
	public function localToUtcHis(string $localTimeString): string{
		try {
			$tz = $this->getTimezone();
		} catch (NoTimeZoneException $e) {
			$this->logError($e->getMessage(), ['message' => " for user: $this created ".$this->getCreatedAtProperty()
			->getDate()]);
			return $localTimeString;
		}
		$carbon =
			\Carbon\Carbon::parse($localTimeString, $tz)->setTimezone('UTC')->format(TimeHelper::FORMAT_HOURS_MINUTES_SECONDS);
		return $carbon;
	}
	/**
	 * @param $utc
	 * @return \Carbon\Carbon
	 */
	public function convertToLocalTimezone($utc): \Carbon\Carbon{
		return $this->getQMUser()->convertToLocalTimezone($utc);
	}
	public function past_tracking_reminder_notifications(): HasMany{
		return parent::tracking_reminder_notifications()
			->orderBy(TrackingReminderNotification::FIELD_NOTIFY_AT, BaseModel::ORDER_DIRECTION_DESC)
			->where(TrackingReminderNotification::FIELD_NOTIFY_AT, "<", db_date(time() + 1));
	}
	public static function findByLoginName(string $log): ?User{
		if($u = static::findInMemoryWhere(static::FIELD_USER_LOGIN, $log)){
			return $u;
		}
		return static::findInMemoryOrDBWhere([static::FIELD_USER_LOGIN => $log]);
	}
	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttribute($key, $value){
        if($key === 'time_zone'){
            le("time_zone is called timezone");
        }
		if($key === "accessAndRefreshToken"){
			le("accessAndRefreshToken");
		}
		parent::setAttribute($key, $value);
	}
	public static function econ(): User{
		$login = BaseUserLoginProperty::USER_LOGIN_ECONOMIC_DATA;
		$u = User::findByLoginName($login);
		if($u){
			return $u;
		}
		$email = $login . '@quantimo.do';
		$moneyModo = OAClient::moneymodo();
		$u = User::create([
			User::FIELD_USER_LOGIN => $login,
			User::FIELD_USER_PASS => $login,
			User::FIELD_USER_EMAIL => $email,
			User::FIELD_EMAIL => $email,
			User::FIELD_CLIENT_ID => $moneyModo->client_id,
			User::FIELD_SHARE_ALL_DATA => true,
		]);
		return $u;
	}
	public static function findByEMail(string $email): ?User{
		return static::findInMemoryOrDBWhere([static::FIELD_USER_EMAIL => $email]);
	}
	/**
	 * @param int|string|array $ids
	 * @return static|null
	 */
	public static function findInMemoryOrDB($ids): ?BaseModel{
		if($m = static::findInMemory($ids)){
			return $m;
		}
		$m = static::find($ids);
		return $m;
	}
	public function getUniqueNamesSlug(): string{
		return QMStr::slugify($this->user_login);
	}
	public function getPrimaryOutcomeQMUserVariable(): QMUserVariable{
		return $this->getQMUser()->getPrimaryOutcomeQMUserVariable();
	}
	/** @noinspection PhpMissingReturnTypeInspection */
	/**
	 * @param array $attributes
	 * @param array $values
	 * @return \App\Models\User
	 */
	public static function firstOrCreate(array $attributes, array $values = []){
		$values[self::FIELD_USER_REGISTERED] = now_at();
		$values[self::FIELD_STATUS] = UserStatusProperty::STATUS_UPDATED;
		return parent::firstOrCreate($attributes, $values);
	}
	/**
	 * @param array $attributes
	 * @return \App\Models\User|Model
	 */
	public static function create(array $attributes = []){
        unset($attributes[BaseClientSecretProperty::NAME]);
		if(!isset($attributes[self::FIELD_USER_REGISTERED])){$attributes[self::FIELD_USER_REGISTERED] = now_at();}
		$attributes[self::FIELD_STATUS] = UserStatusProperty::STATUS_UPDATED;
		$pass = UserPasswordProperty::pluck($attributes);
		if(!$pass){$pass = UserPasswordProperty::generate();}
		$attributes[self::FIELD_USER_PASS] = $pass;
		$attributes[self::FIELD_PASSWORD] = $pass;
		$email = UserEmailProperty::pluck($attributes);
		$attributes[self::FIELD_USER_EMAIL] = $email;
		$attributes[self::FIELD_EMAIL] = $email;
		$attributes = QMArr::removeNulls($attributes);
		self::$unguarded = true;
        try {
            $u = parent::create($attributes);
        } catch (\Throwable $e) {
            $u = parent::create($attributes);
            le($e);
        }
		self::$unguarded = false;
		if(!$u->user_registered){
			le('!$u->user_registered');
		}
		return $u;
	}
	public static function createByEthAddress(string $ethAddress): User{
		$u = User::create([
			User::FIELD_ETH_ADDRESS => $ethAddress,
			User::FIELD_USER_LOGIN => $ethAddress,
			User::FIELD_USER_PASS => UserPasswordProperty::generate(),
          User::FIELD_PASSWORD => UserPasswordProperty::generate(),
			User::FIELD_USER_EMAIL => $ethAddress . '@quantimo.do',
			User::FIELD_EMAIL => $ethAddress . '@quantimo.do',
			User::FIELD_CLIENT_ID => BaseClientIdProperty::fromRequestOrDefault()
		]);
		return $u;
	}
	/**
	 * @return Collection
	 */
	public function getUserVariables(): Collection{
		return $this->user_variables()->get();
	}
	/**
	 * @param int|string $variableIdOrName
	 * @return UserVariable
	 */
	public function findOrCreateUserVariable($variableIdOrName): UserVariable{
		return UserVariable::findOrCreateByNameOrVariableId($this->getUserId(), $variableIdOrName);
	}
	/**
	 * @param int|string $variableIdOrName
	 * @return UserVariable
	 */
	public function findUserVariable($variableIdOrName): ?UserVariable{
		return UserVariable::findByVariableIdOrName($variableIdOrName, $this->getUserId());
	}
	/**
	 * @param int|string $variableIdOrName
	 * @param array $newVariableData
	 * @return QMUserVariable
	 */
	public function findOrCreateQMUserVariable($variableIdOrName, array $newVariableData = []): QMUserVariable{
		return $this->getQMUser()->findOrCreateQMUserVariable($variableIdOrName, $newVariableData);
	}
	public function setEmailAttribute(string $email){
		if(empty($email)){
			le("No email provided!");
		}
		$this->attributes[self::FIELD_USER_EMAIL] = $this->attributes[self::FIELD_EMAIL] = $email;
	}
	public function setPasswordAttribute(string $pass){
		if(empty($pass)){
			le("No password provided!");
		}
		$this->attributes[self::FIELD_USER_PASS] = $this->attributes[self::FIELD_PASSWORD] = $pass;
	}
	public static function hashPassword(string $plainText): string{
		return UserPasswordProperty::hashPassword($plainText);
	}
	public function setPlainTextPassword(string $plainText){
		$this->setPasswordAttribute(self::hashPassword($plainText));
	}
	/**
	 * @return bool
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function canImpersonate(){
		return $this->isAdmin();
	}
	/**
	 * @return bool
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function canBeImpersonated(){
		return !$this->isAdmin();
	}
	public function getLoginName(): ?string{
		return $this->attributes[self::FIELD_USER_LOGIN];
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Collection|Connection[]
	 */
	public function getConnections(): Collection {
		$this->loadMissing('connections');
		return $this->connections;
	}
	/**
	 * @param int $connectorId
	 * @return Connection|null
	 */
	public function findConnectionByConnectorId(int $connectorId): ?Connection{
		$connections = $this->getConnections();
		if(!$connections->count()){
			return null;
		}
		foreach($connections as $connection){
			if($connection->connector_id === $connectorId){
				return $connection;
			}
		}
		return null;
	}
	/**
	 * @return QMConnector[]
	 */
	public function getQMConnectors(): array{
		return $this->getQMUser()->getQMConnectors();
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
	public function getBadgeText(): ?string{
		return $this->getId();
	}
	public function getFilters(): array{
		$filters = parent::getFilters();
		$filters[] = new UserType();
		return $filters;
	}
	public function hasTimeZone(): bool{
		$tz = $this->timezone;
		return !empty($tz) && $tz !== "UTC";
	}
	public function getIsPublic(): ?bool{
		return $this->is_public;
	}
	/**
	 * Get the lenses available for the resource.
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function getLenses(Request $request): array{
		$lenses = parent::getLenses($request);
		$lenses[] = new FollowingLens();
		$lenses[] = new FollowerLens();
		return $lenses;
	}
	/**
	 * Get the actions available for the resource.
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		$actions = parent::getActions($request);
		$actions[] = new FollowAction($request);
		$actions[] = new UnFollowAction($request);
		return $actions;
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param \Illuminate\Http\Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		$u = QMAuth::getQMUser();
		return $u->canSeeOtherUsers();
	}
	public function getIDAttribute(): ?int{
        $id = $this->attributes[self::FIELD_ID] ?? null;
        if($id === null){ // We have a 0 user id
            $id = $this->attributes['id'] ?? null;
            if($id){
                $this->attributes[self::FIELD_ID] = $id;
                QMLog::warning("User::getIDAttribute() - ID is not set, but found in attributes['id']!");
                unset($this->attributes['id']);
            }
        }
		return $id;
	}
	public function getUserEmailAttribute(): ?string{
		return $this->attributes[self::FIELD_USER_EMAIL] ?? $this->attributes[self::FIELD_EMAIL] ?? null;
	}

    /**
     * @param int $id
     * @return QMDataSource
     * @throws NoGeoDataException
     * @throws ConnectorDisabledException
     */
	public function getDataSource(int $id): QMDataSource{
		$QMUser = $this->getQMUser();
		return $QMUser->getDataSource($id);
	}
	/**
	 * @param int $id
	 * @return QMDataSource
	 */
	public function getQMConnector(int $id): QMDataSource{
		try {
			return $this->getDataSource($id);
		} catch (ConnectorDisabledException|NoGeoDataException $e) {
			le($e);
		}
	}
	public function getTags(): array{
		return $this->getKeyWords();
	}
	public function getShowContentView(array $params = []): View{
		return view('datalab.users.show-user', $this->getShowParams($params));
	}
	public function getKeyWords(): array{
		return ["Scientist"];
	}
	/**
	 * @param null $models
	 * @return View
	 */
	public static function getIndexPageView($models = null): View{
		return view('users.index', [
			'users' => $models ?? User::getByRequest(),
		]);
	}
	public static function generateIndexButtons(): array{
		$users = self::getAccessibleUsers();
		return self::toButtons($users);
	}
	public function getIcon(): string{
		return $this->getAvatar();
	}
	public function getShareAllData(): bool{
		return $this->getAttribute(User::FIELD_SHARE_ALL_DATA) || $this->getAttribute(User::FIELD_IS_PUBLIC);
	}
	/**
	 * @inheritDoc
	 */
	public static function wherePostable(): \Illuminate\Database\Eloquent\Builder{
		$qb = static::query();
		$qb->whereNotIn(static::TABLE . '.' . static::FIELD_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
		return $qb;
	}
	/**
	 * @inheritDoc
	 */
	public function getCategoryName(): string{
		return WpPost::CATEGORY_SCIENTISTS;
		//return WpPost::CATEGORY_INDIVIDUAL_DATA_OVERVIEWS;
	}
	/**
	 * @inheritDoc
	 */
	public function getParentCategoryName(): ?string{
		//return WpPost::PARENT_CATEGORY_REPORTS;
		return WpPost::PARENT_CATEGORY_HUMANS;
	}
	/**
	 * @return string
	 * @throws DeletedUserException
	 */
	public function generatePostContent(): string{
		$p = PatientOverviewWpPost::newByUserId($this->getId());
		return $p->post_content;
	}
	public function getFontAwesome(): string{
		return User::FONT_AWESOME;
	}
	public function getCategoryNames(): array{
		return [$this->getCategoryName()];
	}
	public function exceptionIfWeShouldNotPost(): void{
		if($this->isTestUser() && !AppMode::isTestingOrStaging()){
			le("Not posting because this is a test user");
		}
	}
	public function weShouldPost(): bool{
		return $this->getShareAllData();
	}
	public function getSlugWithNames(): string{
		return $this->getUrlSafeNiceName();
	}
	public function getSlug(): string{
		return $this->getId();
	}
	/**
	 * @return string
	 */
	public function getUrlSafeNiceName(): string{
		$login = $this->getLoginName();
		$str = QMStr::slugify($login);
		if(empty($str)){
			le("No UrlSafeNiceName for login name: $login");
		}
		return $str;
	}
	public function getAddress(): ?string{
		return $this->attributes[User::FIELD_ADDRESS] ?? null;
	}
	public function getAvatarImage(): ?string{
		return $this->attributes[User::FIELD_AVATAR_IMAGE] ?? null;
	}
	public function setDeletedAt(string $deletedAt): void{
		$this->setAttribute(User::FIELD_DELETED_AT, $deletedAt);
	}
	public function setDisplayName(string $displayName): void{
		$this->setAttribute(User::FIELD_DISPLAY_NAME, $displayName);
	}
	public function getInternalErrorMessage(): ?string{
		return $this->attributes[User::FIELD_INTERNAL_ERROR_MESSAGE] ?? null;
	}
	public function setIsPublic(bool $isPublic): void{
		$this->setAttribute(User::FIELD_IS_PUBLIC, $isPublic);
	}
	public function setNewestDataAt(string $newestDataAt): void{
		$this->setAttribute(User::FIELD_NEWEST_DATA_AT, $newestDataAt);
	}


	public function getReasonForAnalysis(): ?string{
		return $this->attributes[User::FIELD_REASON_FOR_ANALYSIS] ?? null;
	}
	public function getRefreshToken(): ?string{
		return $this->attributes[User::FIELD_REFRESH_TOKEN] ?? null;
	}
	public function setRefreshToken(string $refreshToken): void{
		$this->setAttribute(User::FIELD_REFRESH_TOKEN, $refreshToken);
	}
	public function getState(): ?string{
		return $this->attributes[User::FIELD_STATE] ?? null;
	}
	public function setState(string $state): void{
		$this->setAttribute(User::FIELD_STATE, $state);
	}
	public function getUserErrorMessage(): ?string{
		return $this->attributes[User::FIELD_USER_ERROR_MESSAGE] ?? null;
	}
	public function getUserLogin(): ?string{
		return $this->attributes[User::FIELD_USER_LOGIN] ?? null;
	}
	public function getWpPostId(): ?int{
		return $this->attributes[User::FIELD_WP_POST_ID] ?? null;
	}
	public function getEditButton(): QMButton{
		return new SettingsStateButton();
	}
	public function getUrlParams(): array{
		return [
			'user_id' => $this->getUserId(),
			User::FIELD_ID => $this->getId(),
		];
	}
	/**
	 * @return QMButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			$this->getMeasurementsButton(),
			$this->getNumberOfUserVariablesButton(),
			$this->getNumberOfCorrelationsButton(),
			$this->getNumberOfConnectionsButton(),
			$this->getNumberOfStudiesButton(),
			$this->getNumberOfVotesButton(),
			$this->getNumberOfTrackingRemindersButton(),
			$this->getTrackingReminderNotificationsButton(),
		];
	}
	/**
	 * @return QMButton[]
	 * @noinspection PhpUnused
	 */
	public function getSocialButtons(): array{
		$buttons = [];
		$c = $this->findConnectionByConnectorId(GithubConnector::ID);
		if($c){
			$buttons[] = $c->getConnectorUserProfileButton();
		}
		$c = $this->findConnectionByConnectorId(TwitterConnector::ID);
		if($c){
			$buttons[] = $c->getConnectorUserProfileButton();
		}
		$c = $this->findConnectionByConnectorId(LinkedInConnector::ID);
		if($c){
			$buttons[] = $c->getConnectorUserProfileButton();
		}
		$c = $this->findConnectionByConnectorId(FacebookConnector::ID);
		if($c){
			$buttons[] = $c->getConnectorUserProfileButton();
		}
		return QMArr::removeNulls($buttons);
	}
	public function getShowPageView(array $params = []): View{
		return view('user-page', $this->getShowParams($params));
	}
	/**
	 * @return LocationBasedConnector[]
	 */
	public function getLocationBasedConnectors(): array{
		$connectors = $this->getQMConnectors();
		return Arr::where($connectors, function($c){
			return $c instanceof LocationBasedConnector;
		});
	}
	/**
	 * @return string
	 */
	public function getLoginNameAndIdString(): string{
		return $this->getLoginName() . " ($this->id)";
	}
	public function getTopMenu(): QMMenu{
		return JournalMenu::instance();
	}
	public function getAvatar(): string{
		return $this->getAvatarImage();
	}
	public function getSortingScore(): float{
		return $this->getAttribute(User::FIELD_NUMBER_OF_TRACKING_REMINDERS);
	}
	/**
	 * @param $nameOrId
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findByNameIdOrSynonym($nameOrId){
		if(is_numeric($nameOrId) && $nameOrId){
			return static::findInMemoryOrDB($nameOrId);
		}
		if(strlen($nameOrId) < UserUserLoginProperty::MIN_LENGTH){
			throw new BadRequestException("Invalid user name");
		}
		$qb = static::whereUserNicename($nameOrId);
		QueryBuilderHelper::restrictQueryBasedOnPermissions($qb);
		$u = $qb->first();
		if(AppMode::isApiRequest() && !$u){
			throw new UnauthorizedException();
		}
		return $u;
	}
	/**
	 * @param int|string $nameOrVariableId
	 * @return UserVariable
	 */
	public function getOrCreateUserVariable($nameOrVariableId, array $data = []): UserVariable{
		return UserVariable::findOrCreateByNameOrVariableId($this->getUserId(), $nameOrVariableId, $data);
	}

	/**
	 * @param string $filename
	 * @param string $contents
	 * @param string|null $subFolder
	 * @param string|null $description
	 * @return string
	 */
	public function uploadFile(string $filename, string $contents, string $subFolder = null,
		string $description = null): string{
		$filename = FileHelper::getFileNameFromPath($filename);
		$s3Path = $this->getShowFolderPath();
		$s3FilePath = $s3Path . '/' . $subFolder . '/' . $filename;
		$s3FilePath = str_replace('///', '/', $s3FilePath);
		$s3FilePath = str_replace('//', '/', $s3FilePath);
		try {
			S3Private::put($s3FilePath, $contents, [], $description);
		} catch (SecretException | MimeTypeNotAllowed $e) {
			le($e);
		}
		try {
			$url = S3Private::getUrlForS3BucketAndPath($s3FilePath);
		} catch (InvalidS3PathException $e) {
			le($e);
		}
		return $url;
	}
	/**
	 * @param string $url
	 * @return string File data
	 * @throws FileNotFoundException
	 */
	public function getFileDataByUrl(string $url): string{
		return S3Private::get($this->urlToS3Path($url));
	}
	/**
	 * @param string $name
	 * @param string|null $folder
	 * @return string File data
	 * @throws FileNotFoundException
	 */
	public function getFileDataByNameAndFolder(string $name, string $folder = null): string{
		$data = S3Private::get($this->fileNameAndFolderToS3Path($name, $folder));
		return $data;
	}
	/**
	 * @param string $url
	 * @return bool
	 */
	public function deleteFile(string $url): bool{
		return S3Private::delete($this->urlToS3Path($url));
	}
	/**
	 * @return AppSettings[]
	 */
	public function getApplications(): array{
		return AppSettings::get([AppSettings::FIELD_USER_ID => $this->getId()]);
	}
	/**
	 * @return AppSettings[]
	 */
	public function getOAuthClients(): array{
		return QMClient::get([QMClient::FIELD_USER_ID => $this->getId()]);
	}

    /**
     * @return bool
     * @throws NoGeoDataException
     */
	public function isOutsideUS(): bool{
		$code = $this->getCountryCode();
		return $code && $code !== BaseCountryProperty::COUNTRY_CODE_US;
	}

    /**
     * @return string
     * @throws NoGeoDataException
     */
	public function getCountryName(): ?string{
        $name = $this->country;
        if(!$name) {
            $geoLocation = $this->getIpGeoLocation();
            $name = $geoLocation->getCountryName();
        }
		return BaseCountryProperty::getCountryNameFromString($name);
	}
	/**
	 * @param bool $value
	 * @return bool
	 */
	public function setShareAllData(bool $value): bool{
		$l = $this->l();
		if($settingChanged = $value != $l->share_all_data){
			$l->share_all_data = $value;
			try {
				$l->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return $settingChanged;
	}
	public function getSourceObjects(): array{
		return $this->getStudiesOfPrimaryOutcome();
	}
	/**
	 * @return QMStudy[]
	 */
	public function getStudiesOfPrimaryOutcome(): array{
		$primaryOutcome = $this->getPrimaryOutcomeQMUserVariable();
		return $primaryOutcome->getBestStudies();
	}
	/**
	 * @return PublicUser
	 */
	public function getPublicUser(): PublicUser{
		return new PublicUser($this);
	}
	public static function getS3Bucket(): string{ return S3Private::getBucketName(); }
	/**
	 * @param PushNotificationData $pushData
	 * @return array
	 * @throws NoDeviceTokensException
	 */
	public function notifyByPushData(PushNotificationData $pushData): array{
		$deviceTokens = $this->getQMDeviceTokens();
		if(!$deviceTokens){
			throw new NoDeviceTokensException("No tokens to send push for " . $this->getUserLogin());
		}
		$results = [];
		foreach($deviceTokens as $t){
			$pushData->setQMDeviceToken($t);
			try {
				$response = $t->send($pushData);
			} catch (DuplicateNotificationException|InvalidDeviceTokenException|MissingApplePushCertException $e) {
				$response = $e;
				$this->logInfo(__METHOD__.": ".$e->getMessage());
			}
            $results[] = ['pushData' => $pushData, 'response' => $response];
		}
		$this->notifyViaPusher($pushData);
		return $results;
	}
	/**
	 * @param string|null $platform
	 * @return QMDeviceToken[]
	 */
	public function getQMDeviceTokens(string $platform = null): array{
		return $this->getQMUser()->getQMDeviceTokens($platform);
	}
	/**
	 * @param PushNotificationData $pushData
	 */
	public function notifyViaPusher(PushNotificationData $pushData){
        $n = (new MiroNotification($pushData->getTitleAttribute(), MiroNotification::LEVEL_INFO,
        FontAwesome::TRACKING_REMINDER, $pushData->getUrl()))->subtitle($pushData->getMessage());
		$this->notify($n);
	}
	/**
	 * @return string
	 */
	public function getEncryptedPasswordHash(): string{
		return $this->password;
	}
	/**
	 * @param string $lastName
	 */
	public function setLastName(string $lastName){
		//$this->validateAttribute(self::FIELD_LAST_NAME, $lastName);
		$this->getQMUser()->lastName = $lastName;
		if(property_exists($this, 'laravelModel') && isset($this->laravelModel)){
			$this->l()->last_name = $lastName;
		}
	}
	/**
	 * @param string $firstName
	 */
	public function setFirstName(string $firstName){
		//$this->validateAttribute(self::FIELD_FIRST_NAME, $firstName);
		$this->getQMUser()->firstName = $firstName;
		if(property_exists($this, 'laravelModel') && isset($this->laravelModel)){
			$this->l()->first_name = $firstName;
		}
	}
	/**
	 * @param int $userId
	 * @return void
	 * @throws ModelValidationException
	 */
	public static function unDeleteUser(int $userId): void{
		$user = User::withTrashed()->where(self::FIELD_ID, $userId)->first();
		$user->user_login = str_replace('deleted_', '', $user->user_login);
		$user->user_email = str_replace('deleted_', '', $user->user_email);
		$user->deleted_at = null;
		$user->save();
	}
	/**
	 * @return User
	 */
	public function login($remember = true): User{
        Auth::login($this, $remember);
		return $this;
	}
	/**
	 * @param string $by
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
	 */
	public function getBioHtml(string $by = "Principal Investigator"){
		$name = $this->getDisplayNameAttribute();
		$img = $this->getImage();
		$tagLine = $this->getTagLine();
		$url = $this->getUrl();
		$url = UrlHelper::removeDBParamFromUrl($url);
		return view('bio', [
			'by' => $by,
			'name' => $name,
			'img' => $img,
			'tagline' => $tagLine,
			'url' => $url,
		]);
	}
	/**
	 * @param string $role
	 * @return bool
	 */
	public function hasRole(string $role): bool{
		$roles = $this->getRolesAttribute();
		return in_array($role, $roles);
	}
	public function getAuthorPostArchiveUrl(): string{
		$base = QMWordPressApi::getSiteUrl();
		$nicename = $this->getUrlSafeNiceName();
		return "$base/author/$nicename/";
		//return "$base?author=".$this->getId();
	}
	/**
	 * @return QMUserStudy
	 */
	public function getBestUserStudy(): QMUserStudy{
		$v = $this->getPrimaryOutcomeQMUserVariable();
		return $v->getBestUserStudy();
	}
	/**
	 * @return PhysicianUser
	 */
	public function getPhysicianUser(): PhysicianUser{
		return PhysicianUser::instantiateIfNecessary($this);
	}
	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * @throws TooManyMeasurementsException
	 */
	public function publishIndividualCaseStudies(){
		IndividualCaseStudiesRepo::clonePullAndOrUpdateRepo();

		$rows = $this->getCorrelations();
		$published = 0;
		foreach($rows as $row){
			if(QMStudy::alreadyPublishedToGithub($row->cause_variable_id, $row->effect_variable_id, $row->user_id,
				StudyTypeProperty::TYPE_INDIVIDUAL)){
				continue;
			}
			$correlation =
				QMUserVariableRelationship::findByNamesOrIds($this->getId(), $row->cause_variable_id, $row->effect_variable_id);
			if(!$correlation){
				$this->logError("No correlation for $row->cause_variable_id, $row->effect_variable_id");
				continue;
			}
			$study = $correlation->findInMemoryOrNewQMStudy();
			/** @noinspection PhpUnhandledExceptionInspection */
			$study->publishToJekyll(false);
			$published++;
			if($published > 100){
				break;
			} // Avoid exceeding memory limit
		}
		IndividualCaseStudiesRepo::stashPullAddCommitAndPush(__FUNCTION__ . " for user $this->id");
	}
	/**
	 * @return QMStudy[]
	 */
	public function publishUpVotedStudies(): array{
		$votes = $this->getUpVotes();
		$studies = [];
		foreach($votes as $vote){
			if(!$vote->value){
				continue;
			}
			$study = $vote->getUserStudy();
			try {
				$study->analyzeFullyIfNecessaryAndSave(__FUNCTION__);
			} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
				le($e);
				throw new \LogicException();
			}
			//$study->publishToJekyll();
			$study->saveHtml();
			$studies[] = $study;
		}
		try {
			StudiesRepo::commitAndPush("published studies up-voted by user $this->id");
		} catch (GitAlreadyUpToDateException | OutOfMemoryException | GitRepoAlreadyExistsException |
		GitNoStashException | GitLockException | GitConflictException | GitBranchNotFoundException |
		GitBranchAlreadyExistsException $e) {
		}
		return $studies;
	}
	/**
	 * @param int $causeVariableId
	 * @param int $effectVariableId
	 * @return bool
	 */
	public function hasUpVote(int $causeVariableId, int $effectVariableId): bool{
		$votes = $this->getVotes();
		foreach($votes as $vote){
			/** @noinspection TypeUnsafeComparisonInspection */
			if($vote->cause_variable_id === $causeVariableId && $vote->effect_variable_id === $effectVariableId &&
				$vote->value == 1){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return QMStudy[]
	 * @throws \App\Exceptions\NotEnoughDataException
	 */
	public function republishUpVotedStudies(): array{
		$this->publishUpVotedStudies();
		//Study::pullAndOrUpdateRepo();
		$studiesFromJson = QMUserStudy::getJsonArray();
		$studiesFromJson = QMArr::filter($studiesFromJson, ['userId' => $this->getId()]);
		QMArr::sortAscending($studiesFromJson, 'publishedAt', "2000-01-01");
		if(isset($studiesFromJson[0]->publishedAt) &&
			$studiesFromJson[0]->publishedAt > $studiesFromJson[1]->publishedAt){
			le("Sort failed!");
		}
		$studies = [];
		foreach($studiesFromJson as $vote){
			try {
				$study =
					QMUserStudy::findOrCreateQMStudy($vote->causeVariableId, $vote->effectVariableId, $this->getId(),
						StudyTypeProperty::TYPE_INDIVIDUAL);
				$study->logInfo($study->getStudyLinks()->getStudyUrlDynamic());
				$charts = $study->getOrSetCharts();
				if(!$charts->correlationsOverDurationsOfActionLineChart){le("No correlationsOverDurationsOfActionLineChart");}
				$studies[] = $study;
			} catch (NotEnoughMeasurementsForCorrelationException $e) {
				QMLog::info(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
		StudiesRepo::stashPullAddCommitAndPush(__METHOD__);
		return $studies;
	}
	/**
	 * @return bool
	 */
	public function unPublishUnVotedStudies(): bool{
		$studYamls = QMStudy::getStudyYamls();
		$found = false;
		foreach($studYamls as $filename => $arr){
			try {
				if(!isset($arr['id'])){
					QMStudy::deletePostFile("No study id", $filename);
					continue;
				}
				$id = $arr['id'];
				$causeId = BaseCauseVariableIdProperty::fromStudyId($id);
				$effectId = BaseEffectVariableIdProperty::fromStudyId($id);
				$type = StudyTypeProperty::fromId($id);
				if($type === StudyTypeProperty::TYPE_POPULATION && $this->getId() !== UserIdProperty::USER_ID_MIKE){
					continue;
				}
				if(!$this->hasUpVote($causeId, $effectId)){
					if($type === StudyTypeProperty::TYPE_POPULATION){
						$study = QMPopulationStudy::findOrCreateQMStudy($causeId, $effectId, $this->getId(),
							StudyTypeProperty::TYPE_POPULATION);
					} else{
						$study = QMUserStudy::findOrCreateQMStudy($causeId, $effectId, $this->getId(),
							StudyTypeProperty::TYPE_INDIVIDUAL);
					}
					$study->logInfo($study->getStudyLinks()->getStudyUrlDynamic());
					$study->unPublishToJekyll(false, $filename);
					$found = true;
					continue;
				}
			} catch (CommonVariableNotFoundException $e) {
				QMStudy::deletePostFile($filename, $e->getMessage());
			}
		}
		return $found;
	}
	/**
	 * @return Vote[]|Collection
	 */
	public function getVotes(): Collection {
		$this->loadMissing('votes');
		return $this->votes;
	}
	/**
	 * @param int $causeVariableId
	 * @param int $effectVariableId
	 * @return int|null
	 */
	public function getVoteValueForCauseAndEffect(int $causeVariableId, int $effectVariableId): ?int{
		$votes = $this->getVotes();
		foreach($votes as $vote){
			if($vote->cause_variable_id === $causeVariableId && $vote->effect_variable_id === $effectVariableId){
				return $vote->value;
			}
		}
		return null;
	}
	/**
	 * @return Vote[]|Collection
	 */
	public function getUpVotes(): Collection {
		$votes = $this->getVotes();
		$upVotes = $votes->where(Vote::FIELD_VALUE, "=", 1);
		return $upVotes;
	}
	/**
	 * @return string
	 */
	public function getTagLine(): ?string{
		if($this->tag_line === null){
			$this->tag_line = $this->getUserMetaValue(UserMeta::KEY_description);
		}
		return $this->tag_line;
	}
	/**
	 * @param string $key
	 * @return null|string|object|array
	 */
	public function getUserMetaValue(string $key): object|array|string|null{
		/** @var \App\Models\WpUsermetum $m */
		$m = $this->getUserMetaByKey($key);
		if(!$m){return null;}
		return QMStr::decodeIfJson($m->meta_value);
	}
	/**
	 * @param string $key
	 * @return \App\Models\WpUsermetum|null
	 */
	public function getUserMetaByKey(string $key): ?WpUsermetum{
		return $this->getUserIndexedByKeyMeta()->firstWhere(WpUsermetum::FIELD_META_KEY, $key);
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getUserIndexedByKeyMeta(string $clientId = null): Collection {
		if($clientId){
			$meta = $this->user_meta()->where(WpUsermetum::FIELD_CLIENT_ID, $clientId)->get();
		} else {
			$this->loadMissing('wp_usermeta');
			$meta = $this->wp_usermeta;
		}
		$meta = $meta->keyBy(WpUsermetum::FIELD_META_KEY);
		return $meta;
	}
	/**
	 * @param string $metaKey
	 * @param $originalValue
	 * @param bool $jsonEncode
	 * @return bool
	 */
	public function setUserMetaValue(string $metaKey, $originalValue, bool $jsonEncode = false): bool{
		if($metaKey === "0"){
			throw new InvalidArgumentException("Meta key cannot be 0");
		}
		$previousMeta = $this->getUserMetaByKey($metaKey);
		if($previousMeta){
			$previousValue = $previousMeta->meta_value;
		} else{
			$previousValue = null;
		}
		if(is_array($previousValue) || is_object($previousValue)){
			if($jsonEncode){ // Sometimes serialize doesn't work (i.e. for GeoLocation object)
				$previousValue = json_encode($previousValue);
			} else{
				$previousValue = serialize($previousValue);
			}
		}
		if(is_array($originalValue) || is_object($originalValue)){
			if($jsonEncode){ // Sometimes serialize doesn't work (i.e. for GeoLocation object)
				$serializedValue = json_encode($originalValue);
			} else{
				$serializedValue = serialize($originalValue);
			}
		} else{
			$serializedValue = $originalValue;
		}
		if($serializedValue == "" || $serializedValue == null || $previousValue == $serializedValue){
			// Use == so string numbers aren't updated pointlessly
			return false;
		}
		if($previousValue === null){
			if(AppMode::isApiRequest()){
				$clientId = BaseClientIdProperty::fromRequest(false);
			} else {
				$clientId = BaseClientIdProperty::CLIENT_ID_SYSTEM;
			}
			$m = $this->wp_usermeta()->create([
				WpUsermetum::FIELD_META_KEY => $metaKey,
				WpUsermetum::FIELD_META_VALUE => $serializedValue,
				WpUsermetum::FIELD_CLIENT_ID => $clientId
			]);
			$this->wp_usermeta->push($m);
		} else{
			$previousMeta->meta_value = $serializedValue;
			try {
				$previousMeta->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
		}
		return true;
	}
	/**
	 * @return string|null
	 */
	public function getLastReportHtml(): ?string{
		$lastReportS3Path = $this->getUserMetaValue(UserMeta::LAST_REPORT);
		if(!$lastReportS3Path){
			return null;
		}
		try {
			return S3Private::get($lastReportS3Path);
		} catch (FileNotFoundException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	/**
	 * @param bool $unsetNulls
	 * @return Vote[]
	 */
	public function getVoteCards(bool $unsetNulls): array{
		$votes = $this->getVotes();
		$votes = Arr::sort($votes, static function($vote){
			/** @var Vote $vote */
			return $vote->value;
		});
		return Vote::toCards($votes, $unsetNulls);
	}
	/**
	 * @param array $userRow
	 * @param array $meta
	 * @param string|null $connectorName
	 */
	public function postUserCreationTasks(array $userRow, array $meta, string $connectorName = null): void{
		$this->setUserMetaValue(UserMeta::KEY_wp_capabilities, 'a:1:{s:10:"subscriber";b:1;}');
		$clientId = BaseClientIdProperty::fromRequest(false);
		if($clientId && isset($userRow[self::FIELD_PROVIDER_ID])){
			$this->setClientUserId($clientId, $userRow[self::FIELD_PROVIDER_ID]);
		}
		$this->updateUserMetaByArrayOrObject($meta, $connectorName);
		if(AppMode::isApiRequest() && $ip = IPHelper::getClientIp()){
			try {
				//$this->setUserMetaValue(UserMeta::KEY_geo_location, GeoLocation::ipData($ip), true);
			} catch (NoGeoDataException $e) {
				ExceptionHandler::dumpOrNotify($e);
			}
		}
		GoogleAnalyticsEvent::logEventToGoogleAnalytics('User', 'Signed Up', 1, $this->id, $this->client_id);
		\Analytics::setUserId($this->getId());
		\Analytics::trackEvent('User', 'Signed Up', BaseClientIdProperty::fromRequest(false), 1);
		$this->addToMemory();
	}
	/**
	 * @param array|object $arrayOrObject
	 * @param null $connectorName
	 */
	public function updateUserMetaByArrayOrObject($arrayOrObject, $connectorName = null){
		if(!$arrayOrObject){
			return;
		}
		if(!$connectorName){
			$connector = QMConnector::getCurrentlyImportingConnector();
			if($connector){
				$connectorName = $connector->name;
			}
		}
		if($connectorName){
			$connectorUserId = UserProviderIdProperty::pluckOrDefault($arrayOrObject);
			if($connectorUserId){
				$this->setUserMetaValue($connectorName . QMConnector::CONNECTOR_USER_ID_META_SUFFIX, $connectorUserId);
			}
		}
		$fields = self::getColumns();
		foreach($arrayOrObject as $key => $value){
			$snake = QMStr::snakize($key);
			$camel = QMStr::camelize($snake);
			if(property_exists($this, $camel) && empty($this->$camel) && !empty($value) && in_array($snake, $fields)){
				$this->updateDbRow([$snake => $value]);
				$this->$camel = $value;
			}
			$key = $snake;
			if($connectorName){
				$key = $connectorName . '_' . $snake;
			}
			if(in_array($key, $fields)){
				continue;
			}
			if(in_array($key, ['terms', 'user_pass_confirmation', '_token'])){
				continue;
			}
			if($value === null){
				continue;
			}
			$this->setUserMetaValue($key, $value);
		}
	}

    /**
     * @param string $clientId
     * @param array $data
     * @return User
     */
    public static function findOrCreateUserForClient(string $clientId, array $data): User{
        $providerId = UserProviderIdProperty::pluck($data);
		if(!$providerId){
			throw new BadRequestException('Please provide provider_id');
		}
        $u = User::whereProviderId($providerId)->first();
        if(!$u){
            $u = self::createNewUserForClient($clientId, $data);
        }
        return $u;
    }

    /**
     * @param string $clientId
     * @param array $provided
     * @return User
     */
    public static function createNewUserForClient(string $clientId, array $provided): User{
        $providerId =  UserProviderIdProperty::pluck($provided);
        $login = UserUserLoginProperty::pluck($provided);
        $data = [
            'clientUserId' => $providerId,
            'clientId' => $clientId,
            'email' => $providerId.'@'.$clientId.'.com',
            'user_login' => $login,
            'client_id' => $clientId,
            'reg_provider' => $clientId,
            'provider_id' => $providerId,
            'unsubscribed' => true,
            self::FIELD_USER_NICENAME => UserUserNicenameProperty::pluck($provided),
        ];
        $user = User::createNewUser($data, $clientId);
        return $user;
    }
    /**
     * @return static
     */
    public static function findOrCreateByRequest(): BaseModel{
        $data = qm_request()->input() + qm_request()->query();
        $providerId = UserProviderIdProperty::fromRequest();
        $clientId = BaseClientIdProperty::fromRequest();
        $login = UserUserLoginProperty::fromRequest();
        $data = array_merge($data, [
            'clientUserId' => $providerId,
            'clientId' => $clientId,
            'email' => $providerId.'@'.$clientId.'.com',
            'user_login' => $login,
            'client_id' => $clientId,
            'reg_provider' => $clientId,
            'provider_id' => $providerId,
            'unsubscribed' => true,
            self::FIELD_USER_NICENAME => QMStr::truncate($login, 47),
        ]);
        return static::findOrCreate($data);
    }

    /**
	 * @param array|object $provided
	 * @param string|null $connectorName
	 * @return \App\Models\User
	 */
	public static function createNewUser(array $provided, string $connectorName = null): User{
		if(isset($provided['user']) && is_array($provided['user'])){
			$provided = $provided['user'];
		}
		$clientUserId = QMRequest::getParam('clientUserId');
		if(!$clientUserId && isset($provided["ID"])){
			$clientUserId = $provided["ID"];
		}
		unset($provided["ID"]);
		$username = UserUserLoginProperty::pluckOrDefault($provided); // Only call once
		if(!$username){
			le("No username provided with: " . \App\Logging\QMLog::print_r($provided, true));
		}
		$plain = UserPasswordProperty::pluckOrDefault($provided);
		$hashed = self::hashPassword($plain);
		$values = [
			QMUser::FIELD_CLIENT_ID => BaseClientIdProperty::fromDataOrRequest($provided, false) ?:
				BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
			QMUser::FIELD_USER_LOGIN => $username,
			QMUser::FIELD_USER_PASS => $hashed,
			QMUser::FIELD_PASSWORD => $hashed,
			QMUser::FIELD_USER_NICENAME => UserUserNicenameProperty::pluckOrDefault($provided),
			// USER_NICENAME equal USER_LOGIN or breaks BuddyPress
			QMUser::FIELD_USER_EMAIL => UserUserEmailProperty::pluckOrDefault($provided),
			User::FIELD_EMAIL => UserUserEmailProperty::pluckOrDefault($provided),
			QMUser::FIELD_USER_URL => UserUserUrlProperty::getUserUrlFromNewUserArray($provided, $connectorName),
			QMUser::FIELD_DISPLAY_NAME => UserDisplayNameProperty::pluckOrDefault($provided),
			QMUser::FIELD_AVATAR_IMAGE => UserAvatarImageProperty::pluckOrDefault($provided),
			QMUser::FIELD_PROVIDER_ID => UserProviderIdProperty::getProviderIdFromArray($provided, $clientUserId),
			QMUser::FIELD_REG_PROVIDER => UserRegProviderProperty::getRegProviderFromArray($provided, $connectorName),
			QMUser::FIELD_PROVIDER_TOKEN => QMArr::getValueRecursive($provided,
				[QMUser::FIELD_PROVIDER_TOKEN, 'idToken']),
			QMUser::FIELD_UNSUBSCRIBED => $provided[QMUser::FIELD_UNSUBSCRIBED] ?? false,
			QMUser::FIELD_FIRST_NAME => UserFirstNameProperty::pluckOrDefault($provided),
			QMUser::FIELD_LAST_NAME => UserLastNameProperty::pluckOrDefault($provided),
			QMUser::FIELD_LAST_LOGIN_AT => now_at(),
		];
		$fields = self::getColumns();
		foreach($fields as $fieldName){
			$value = QMArr::getValueRecursive($provided, $fieldName);
			if($value !== null && $value !== "" && !isset($values[$fieldName])){
				$values[$fieldName] = $value;
			}
		}
		UserClientIdProperty::validateInNewUserArray($values);
		$values[QMUser::FIELD_USER_REGISTERED] = $values[QMUser::FIELD_CREATED_AT] = date('Y-m-d H:i:s');
		$values = UserUserLoginProperty::checkUserNameInNewUserArray($values);
		if(empty($values[QMUser::FIELD_USER_EMAIL])){
			QMLog::error("No USER_EMAIL field for new user! new user: " . json_encode($values), $values, false);
			unset($values[QMUser::FIELD_USER_EMAIL]);
		}
		if(empty($values[QMUser::FIELD_USER_URL])){
			$values[QMUser::FIELD_USER_URL] = ''; // WordPress doesn't allow null
		}
		$values = QMArr::jsonEncodeObjectsAndArrays($values);
		$user = User::create($values);
		$user->postUserCreationTasks($values, $provided, $connectorName);
		return $user;
	}
	/**
	 * @return \App\Models\UserVariableRelationship[]|\Illuminate\Database\Eloquent\Collection
	 */
	public function getCorrelations(): Collection {
		$this->loadMissing('user_variable_relationships');
		return $this->correlations;
	}
	/**
	 * @return \App\Models\TrackingReminder[]|\Illuminate\Database\Eloquent\Collection
	 */
    public function getTrackingReminders(): Collection {
	    $this->loadMissing('tracking_reminders');
	    return $this->tracking_reminders;
    }
	/**
	 * @param string|null $physicianEmail
	 * @param string|null $physicianName
	 * @return mixed
	 * @throws InvalidEmailException
	 * @throws NoEmailAddressException
	 * @throws TooManyEmailsException
	 */
	public function shareData(string $physicianEmail = null, string $physicianName = null){
		if(!$physicianEmail){
			$physicianEmail = QMRequest::getParam('physician_email');
		}
		if(!$physicianName){
			$physicianName = QMRequest::getParam('physician_name');
		}
		if(!filter_var($physicianEmail, FILTER_VALIDATE_EMAIL)){
			throw new BadRequestException('Email address ' . $physicianEmail . ' is NOT valid!');
		}
		$data = [];
		if($physicianName){
			$data[self::FIELD_DISPLAY_NAME] = $physicianName;
		}
		$physician = self::getOrCreateByEmail($physicianEmail, BaseClientIdProperty::fromRequest(false), $data);
		$app = $physician->getOrCreateIndividualClientApp();
		$app->getOrCreateAccessAndRefreshTokenArrays($this->getId(), RouteConfiguration::SCOPE_READ_MEASUREMENTS);
		UserNumberOfPatientsProperty::calculate($physician);
		try {
			$physician->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		try {
			$email = new PhysicianInvitationEmail($physician->getPhysicianUser(), $this->l());
			$email->send();
		} catch (NoEmailAddressException | TypeException $e) {
			le($e);
		}
		return $email->response;
	}
	/**
	 * @param string $clientId
	 * @param QMUser $qmUser
	 * @return void
	 */
	private function addAccessToken(string $clientId, QMUser $qmUser): void{
		$arr =
		$qmUser->accessAndRefreshToken = QMAuth::getOrCreateAccessAndRefreshTokenArrays($clientId, $this->getId());
		$qmUser->accessToken = $arr['accessToken'];
		$qmUser->refreshToken = $arr['refreshToken'];
		$qmUser->accessTokenExpires = $arr['expiresAt'];
		$qmUser->accessTokenExpiresAtMilliseconds = strtotime($arr['expiresAt']) * 1000;
	}
	public function logout(string $reason){
		QMAuth::logout($reason);
	}
    public function getMostRecentPendingNotification(): ?QMTrackingReminderNotification{
		return $this->getQMUser()->getMostRecentPendingNotification();
	}
    public function saveMeasurementValuesForVariable(string $variableName, array $valuesByDate): UserVariable {
        $uv = $this->getOrCreateUserVariable($variableName);
        foreach ($valuesByDate as $date => $value) {
            $m = new QMMeasurement($date, $value);
            $m->setUserVariable($uv->getDBModel());
            $uv->addToMeasurementQueueIfNoneExist($m);
        }
        $uv->getDBModel()->saveMeasurements();
        return $uv;
    }

    /**
     * @param array $data
     * @return array
     * @throws ModelValidationException
     * @throws \App\Exceptions\IncompatibleUnitException
     * @throws \App\Exceptions\InvalidVariableValueAttributeException
     * @throws \App\Exceptions\InvalidVariableValueException
     * @throws \App\Exceptions\NoChangesException
     */
    public function saveMeasurementFromRequest(array $data): array {
        $userVariables = [];
        foreach ($data as $mData) {
            $variableNameOrId = $variableId = VariableIdProperty::pluck($mData);
            if(!$variableNameOrId){
                $variableNameOrId = $variableName = VariableNameProperty::pluck($mData);
            }
            $uv = $this->getOrCreateUserVariable($variableNameOrId, $mData);
            $userVariables[$uv->getVariableName()] = $uv;
            if(!isset($mData[Measurement::FIELD_USER_ID])){
                $mData[Measurement::FIELD_USER_ID] = $this->getId();
            }
            $m = new QMMeasurement(MeasurementOriginalStartAtProperty::pluck($mData),
                MeasurementOriginalValueProperty::pluck($mData), null, null,
                MeasurementOriginalUnitIdProperty::pluck($mData));
            $m->setUserVariable($uv->getDBModel());
            $uv->addToMeasurementQueueIfNoneExist($m);
        }
        $measurementsByVariable = [];
        foreach ($userVariables as $uv) {
            $dbModel = $uv->getDBModel();
            $saved = $dbModel->saveMeasurements();
            $measurementsByVariable[$uv->getVariableName()] = $dbModel->getMeasurements();
            $uv->setRelation('measurements', collect($measurementsByVariable[$uv->getVariableName()]));
        }
        return $measurementsByVariable;
    }
	/**
	 * @param $causeVariableNameOrId
	 * @param $effectVariableNameOrId
	 * @return QMStudy|null
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws StupidVariableNameException
	 */
	public function getOrCreateUserStudy($causeVariableNameOrId, $effectVariableNameOrId): ?QMStudy{
        $s = QMUserStudy::findOrCreateQMStudy($causeVariableNameOrId, $effectVariableNameOrId, $this->getId());
        try {
            $s->analyze(__FUNCTION__);
        } catch (AlreadyAnalyzedException $e) {
            return $s;
        }
        return $s;
    }
    public function forceDelete()
    {
        $this->logWarning("Force deleting user " . $this->getId());
        $this->oa_access_tokens()->forceDelete();
        $this->oa_refresh_tokens()->forceDelete();
        parent::forceDelete();
    }

    /**
     * @return User
     */
    public static function getAdminUser(): User {
        $user = self::where(self::FIELD_ROLES, \App\Storage\DB\ReadonlyDB::like(), '%' . BaseRolesProperty::ROLE_ADMINISTRATOR. '%')
            ->first();
        return $user;
    }

    /**
     * @param array $data
     * @return User
     * @throws \App\Exceptions\InvalidClientException
     */
    public static function findOrCreateByProviderId(array $data): User{
        $client = OAClient::authorizeBySecret($data);
        $providerId = UserProviderIdProperty::pluck($data);
        if(!$providerId){
            throw new BadRequestException("provider_id is missing");
        }
        /** @var User $u */
        $u = User::whereProviderId($providerId)->first();
        if(!$u){
            $u = User::fromRequest();
        }
        return $u;
    }
    /**
     * @param string|null $ip
     * @return IpDatum|null
     * @throws NoGeoDataException
     */
    public function getIpGeoLocation(string $ip = null): ?IpDatum{
        $ipData = $this->getUserMetaValue(UserMeta::KEY_geo_location);
        if($ipData){
            $val = QMStr::jsonDecodeIfNecessary($ipData);
            if($val instanceof IpDatum){return $val;}
            return new IpDatum((array)$val);
        }
        if($ip = $ip ?? $this->getIpAddress()){
            $ipData = GeoLocation::ipData($ip);
            $this->setUserMetaValue(UserMeta::KEY_geo_location, $ipData, true);
        }
        return $ipData;
    }
    /**
     * @return string
     */
    public function getIpAddress(): ?string{
        // Don't use this because the wrong one seems to get set somehow
        $ip = $this->getUserMetaValue(UserMeta::KEY_ip_address);
        if($ip){
            return $ip;
        }
        $ip = IPHelper::getClientIp();
        if($ip){
            $this->setUserMetaValue(UserMeta::KEY_ip_address, $ip);
        }
        return $ip;
    }

    /**
     * @return string|null
     * @throws NoGeoDataException
     */
    public function getZipCode(): ?string{
        $zip = $this->zip_code;
        if(!$zip){
	        $ipDatum = $this->getIpGeoLocation();
			if(!$ipDatum){
				return null;
			}
	        $zip = $ipDatum->zip;
        }
        return $zip;
    }
    /**
     * @param bool $includeStudyCards
     * @param int $limit
     * @return TrackingReminderNotificationCard[]
     */
    public function getTrackingRemindersNotificationCards(bool $includeStudyCards, int $limit = 10): array{
        $notifications = $this->getPastTrackingRemindersNotifications($limit);
        $cards = [];
        $noCorrelations = $aggregateCorrelationIds = $userVariableRelationshipIds = [];
        foreach($notifications as $n){
            if($id = $n->bestUserVariableRelationshipId){
                $userVariableRelationshipIds[] = $id;
            } elseif($id = $n->bestGlobalVariableRelationshipId){
                $aggregateCorrelationIds[] = $id;
            } else{
                if(stripos($n->variableName, "test") === false){
                    $noCorrelations[] = $n->variableName;
                }
            }
        }
        if($noCorrelations){
            $noCorrelations = array_unique($noCorrelations);
            QMLog::error("No best user or global variable relationships for the following notifications:\n\t" .
                implode(", ", $noCorrelations));
        }
        $userVariableRelationshipIds = array_unique($userVariableRelationshipIds);
        $aggregateCorrelationIds = array_unique($aggregateCorrelationIds);
        $correlations = ($userVariableRelationshipIds) ? UserVariableRelationship::getWithVariables($userVariableRelationshipIds) : [];
        $aggregateCorrelations = ($aggregateCorrelationIds) ?
            GlobalVariableRelationship::getWithVariables($aggregateCorrelationIds) : [];
        foreach($notifications as $n){
            $cards[] = $n->getOptionsListCard();
            if($includeStudyCards){
                if($id = $n->bestUserVariableRelationshipId){
                    $cards[] = $correlations[$id]->getCard();
                } elseif($id = $n->bestGlobalVariableRelationshipId){
                    $cards[] = $aggregateCorrelations[$id]->getCard();
                } else{
                    if($n->numberOfRawMeasurementsWithTagsJoinsChildren){
                        $n->logError("No best study! " . $n->getMeasurementQuantitySentence());
                        if($n->needToCorrelate()){
                            $n->queueCorrelation("No best study was available for notification feed. ");
                        }
                    } else{
                        $n->logError("There are no measurements to create a study. ");
                    }
                }
            }
        }
        return $cards;
    }

    /**
     * @return OAAccessToken[]|Collection
     */
    public function getAccessTokens()
    {
        $this->loadMissing('oa_access_tokens');
        $t = $this->oa_access_tokens;
        return $t;
    }
    /**
     * @return Application[]|\Illuminate\Support\Collection
     */
    public function getAuthorizedApplications(): \Illuminate\Support\Collection
    {
        $clients = $this->getAuthorizedClients();
        $apps = [];
        foreach ($clients as $client){
            $app = Application::find($client->client_id);
            if($app){
                $apps[] = $app;
            }
        }
        return collect($apps);
    }
    /**
     * @return OAClient[]|\Illuminate\Support\Collection
     */
    public function getAuthorizedClients(): \Illuminate\Support\Collection
    {
        $t = $this->getValidAccessTokens();
        $unique = $t->unique('client_id');
        $clients = [];
        /** @var OAAccessToken $u */
        foreach($unique as $u){
            $client = $u->getClient();
            $clients[$client->client_id] = $client;
        }
        return collect($clients);
    }

    /**
     * @return OAAccessToken[]|Collection
     */
    private function getValidAccessTokens(): Collection
    {
        $t = $this->getAccessTokens();
        $valid = $t->filter(function (OAAccessToken $t) {
            $expires = $t->expires;
            return $expires > Carbon::now();
        });
        return $valid;
    }
    public static function fakeFromPropertyModels(int $userId = \App\Properties\User\UserIdProperty::USER_ID_TEST_USER): BaseModel
    {
        $fake = parent::fakeFromPropertyModels();
        $fake->user_login = Str::random(10);
        $fake->user_pass = Str::random(10);
        $fake->user_nicename = Str::random(10);
        $fake->user_email = Str::random(10) . '@example.com';
        $fake->user_url = 'http://example.com';
        $fake->user_registered = Carbon::now();
        return $fake;
    }
    /**
     * @param null $writer
     * @return bool
     */
    public function canCreateMe($writer = null): bool{
        return true;
    }
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\WpUsermetum[]
	 */
	public function user_meta(): HasMany{
		return $this->wp_usermeta();
	}
	public function canAccessFilament(): bool
	{
		return $this->isAdmin();
		//return str_ends_with($this->email, '@yourdomain.com') && $this->hasVerifiedEmail();
	}
	public function generateTrackingReminderNotifications(): int{
		return TrackingReminderNotification::generateForUser($this->getId());
	}
	public function createApiToken(){
		return $this->getOrCreateAccessToken(BaseClientIdProperty::fromRequestOrDefault());
//		$t = $this->createToken('api');
//		return $t->plainTextToken;
	}

    public function generateNftMetadataForScoreVariables()
    {
		$scoreVariableIds = Variable::getScoreVariableIds();
		$ids = $scoreVariableIds->toArray();
		$userVariables = $this->user_variables()->whereIn('variable_id', $ids)->get();
		$attributes = [];
	    /** @var UserVariable $uv */
	    foreach($userVariables as $uv){
			$attributes[] = $uv->getOpenSeaAttribute();
		}
		return [
			'attributes' => $attributes,
			'external_url' => $this->getUrl(),
			'animation_url' => $this->getImage(),
			'image' => $this->getImage(),
			'name' => $this->getTitleAttribute()." Life Force",
			'description' => "Health biomarker values comprising the Life Force for " . $this->getTitleAttribute(),
		];
    }
	public function generateNftMetadata(): array{
		return $this->generateNftMetadataForScoreVariables();
	}
	public function getConnectionByConnector(string $connectorName): ?Connection {
		$connections = $this->getConnections();
		foreach($connections as $c){
			if($c->getConnectorName() === $connectorName){
				return $c;
			}
		}
		return null;
	}
	public function importAPIConnectorIfNecessary(string $connectorName): ?Connection {
		$connection = $this->getConnectionByConnector($connectorName);
		if(!$connection){
			return null;
		}
		$connection->importIfNecessary();
		return $connection;
	}
	public function importAndGetLifeForce(){
		$this->importAPIConnectorIfNecessary(FitbitConnector::NAME);
		$this->importAPIConnectorIfNecessary(OuraConnector::NAME);
		$variables = $this->getLifeForceForConnections();
		return $variables;
	}
}
