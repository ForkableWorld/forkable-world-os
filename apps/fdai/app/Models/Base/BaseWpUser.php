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
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\ChildParent;
use App\Models\Collaborator;
use App\Models\Connection;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationCausalityVote;
use App\Models\CorrelationUsefulnessVote;
use App\Models\DeviceToken;
use App\Models\Measurement;
use App\Models\MeasurementExport;
use App\Models\MeasurementImport;
use App\Models\OAAccessToken;
use App\Models\OAAuthorizationCode;
use App\Models\OAClient;
use App\Models\OARefreshToken;
use App\Models\PatientPhysician;
use App\Models\Phrase;
use App\Models\Purchase;
use App\Models\SentEmail;
use App\Models\SharerTrustee;
use App\Models\Study;
use App\Models\TrackerLog;
use App\Models\TrackerSession;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\UserClient;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\Vote;
use App\Models\WpLink;
use App\Models\WpPost;
use App\Models\WpUsermetum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpUser
 * @property int $ID
 * @property string $client_id
 * @property string $user_login
 * @property string $user_email
 * @property string $email
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_url
 * @property Carbon $user_registered
 * @property string $user_activation_key
 * @property int $user_status
 * @property string $display_name
 * @property string $avatar_image
 * @property string $reg_provider
 * @property string $provider_id
 * @property string $provider_token
 * @property string $remember_token
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property string $refresh_token
 * @property bool $unsubscribed
 * @property bool $old_user
 * @property bool $stripe_active
 * @property string $stripe_id
 * @property string $stripe_subscription
 * @property string $stripe_plan
 * @property string $last_four
 * @property Carbon $trial_ends_at
 * @property Carbon $subscription_ends_at
 * @property string $roles
 * @property int $time_zone_offset
 * @property Carbon $deleted_at
 * @property string $earliest_reminder_time
 * @property string $latest_reminder_time
 * @property bool $push_notifications_enabled
 * @property bool $track_location
 * @property bool $combine_notifications
 * @property bool $send_reminder_notification_emails
 * @property bool $send_predictor_emails
 * @property bool $get_preview_builds
 * @property string $subscription_provider
 * @property int $last_sms_tracking_reminder_notification_id
 * @property bool $sms_notifications_enabled
 * @property string $phone_verification_code
 * @property string $phone_number
 * @property bool $has_android_app
 * @property bool $has_ios_app
 * @property bool $has_chrome_extension
 * @property int $referrer_user_id
 * @property string $address
 * @property string $birthday
 * @property string $country
 * @property string $cover_photo
 * @property string $currency
 * @property string $first_name
 * @property string $gender
 * @property string $language
 * @property string $last_name
 * @property string $state
 * @property string $tag_line
 * @property string $verified
 * @property string $zip_code
 * @property int $spam
 * @property int $deleted
 * @property string $card_brand
 * @property string $card_last_four
 * @property Carbon $last_login_at
 * @property string $timezone
 * @property int $number_of_correlations
 * @property int $number_of_connections
 * @property int $number_of_tracking_reminders
 * @property int $number_of_user_variables
 * @property int $number_of_raw_measurements_with_tags
 * @property int $number_of_raw_measurements_with_tags_at_last_correlation
 * @property int $number_of_votes
 * @property int $number_of_studies
 * @property Carbon $last_correlation_at
 * @property Carbon $last_email_at
 * @property Carbon $last_push_at
 * @property int $primary_outcome_variable_id
 * @property int $wp_post_id
 * @property Carbon $analysis_ended_at
 * @property Carbon $analysis_requested_at
 * @property Carbon $analysis_started_at
 * @property string $internal_error_message
 * @property Carbon $newest_data_at
 * @property string $reason_for_analysis
 * @property string $user_error_message
 * @property string $status
 * @property Carbon $analysis_settings_modified_at
 * @property int $number_of_applications
 * @property int $number_of_oauth_access_tokens
 * @property int $number_of_oauth_authorization_codes
 * @property int $number_of_oauth_clients
 * @property int $number_of_oauth_refresh_tokens
 * @property int $number_of_button_clicks
 * @property int $number_of_collaborators
 * @property int $number_of_connector_imports
 * @property int $number_of_connector_requests
 * @property int $number_of_measurement_exports
 * @property int $number_of_measurement_imports
 * @property int $number_of_measurements
 * @property int $number_of_sent_emails
 * @property int $number_of_subscriptions
 * @property int $number_of_tracking_reminder_notifications
 * @property int $number_of_user_tags
 * @property int $number_of_users_where_referrer_user
 * @property bool $share_all_data
 * @property string $deletion_reason
 * @property string $password
 * @property int $number_of_patients
 * @property bool $is_public
 * @property int $sort_order
 * @property int $number_of_sharers
 * @property int $number_of_trustees
 * @property Variable $primary_outcome_variable
 * @property WpPost $wp_post
 * @property \App\Models\User $referrer_user
 * @property Collection|Application[] $applications
 * @property Collection|ButtonClick[] $button_clicks
 * @property Collection|Button[] $buttons
 * @property Collection|ChildParent[] $child_parents
 * @property Collection|ChildParent[] $child_parents_where_parent_user
 * @property Collection|Collaborator[] $collaborators
 * @property Collection|Connection[] $connections
 * @property Collection|ConnectorImport[] $connector_imports
 * @property Collection|ConnectorRequest[] $connector_requests
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property Collection|UserVariableRelationship[] $user_variable_relationships
 * @property Collection|DeviceToken[] $device_tokens
 * @property Collection|MeasurementExport[] $measurement_exports
 * @property Collection|MeasurementImport[] $measurement_imports
 * @property Collection|Measurement[] $measurements
 * @property Collection|OAAccessToken[] $oa_access_tokens
 * @property Collection|OAAuthorizationCode[] $oa_authorization_codes
 * @property Collection|OAClient[] $oa_clients
 * @property Collection|OARefreshToken[] $oa_refresh_tokens
 * @property Collection|PatientPhysician[] $patient_physicians_where_patient_user
 * @property Collection|PatientPhysician[] $patient_physicians_where_physician_user
 * @property Collection|Phrase[] $phrases
 * @property Collection|Purchase[] $purchases_where_subscriber_user
 * @property Collection|SentEmail[] $sent_emails
 * @property Collection|SharerTrustee[] $sharer_trustees_where_sharer_user
 * @property Collection|SharerTrustee[] $sharer_trustees_where_trustee_user
 * @property Collection|Study[] $studies
 * @property Collection|TrackerLog[] $tracker_logs
 * @property Collection|TrackerSession[] $tracker_sessions
 * @property Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property Collection|TrackingReminder[] $tracking_reminders
 * @property Collection|UserClient[] $user_clients
 * @property Collection|UserTag[] $user_tags
 * @property Collection|UserVariableClient[] $user_variable_clients
 * @property Collection|UserVariable[] $user_variables
 * @property Collection|Vote[] $votes
 * @property Collection|WpLink[] $wp_links
 * @property Collection|WpPost[] $wp_posts
 * @property Collection|WpUsermetum[] $usermeta
 * @property Collection|\App\Models\User[] $users_where_referrer_user
 * @package App\Models\Base
 * @property-read int|null $applications_count
 * @property-read int|null $oa_access_tokens_count
 * @property-read int|null $oa_authorization_codes_count
 * @property-read int|null $oa_clients_count
 * @property-read int|null $oa_refresh_tokens_count
 * @property-read int|null $button_clicks_count
 * @property-read int|null $buttons_count
 * @property-read int|null $collaborators_count
 * @property-read int|null $connections_count
 * @property-read int|null $connector_imports_count
 * @property-read int|null $connector_requests_count
 * @property-read int|null $correlation_causality_votes_count
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read int|null $correlations_count
 * @property-read int|null $device_tokens_count
 * @property mixed $raw
 * @property-read int|null $measurement_exports_count
 * @property-read int|null $measurement_imports_count
 * @property-read int|null $measurements_count
 * @property-read int|null $phrases_count
 * @property-read int|null $purchases_where_subscriber_user_count
 * @property-read int|null $sent_emails_count
 * @property-read int|null $studies_count
 * @property-read int|null $tracker_logs_count
 * @property-read int|null $tracker_sessions_count
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read int|null $tracking_reminders_count
 * @property-read int|null $user_clients_count
 * @property-read int|null $user_tags_count
 * @property-read int|null $user_variable_clients_count
 * @property-read int|null $user_variables_count
 * @property-read int|null $usermeta_count
 * @property-read int|null $users_where_referrer_user_count
 * @property-read int|null $votes_count
 * @property-read int|null $wp_comments_count
 * @property-read int|null $wp_links_count
 * @property-read int|null $wp_posts_count
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser newQuery()
 * @method static \Illuminate\Database\Query\Builder|BaseWpUser onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAnalysisEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAnalysisRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAnalysisSettingsModifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAnalysisStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereAvatarImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereBirthday($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCardLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCombineNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCoverPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereEarliestReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereGetPreviewBuilds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereHasAndroidApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereHasChromeExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereHasIosApp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastCorrelationAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastEmailAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastPushAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLastSmsTrackingReminderNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereLatestReminderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNewestDataAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfButtonClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfCollaborators($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfConnections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfConnectorRequests($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfMeasurementExports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfMeasurementImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfOauthAccessTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfOauthAuthorizationCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfOauthClients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfOauthRefreshTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfPatients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfRawMeasurementsWithTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser
 *     whereNumberOfRawMeasurementsWithTagsAtLastCorrelation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfSentEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfSubscriptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfUserTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfUsersWhereReferrerUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereNumberOfVotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereOldUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser wherePhoneVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser wherePrimaryOutcomeVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereProviderToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser wherePushNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereReasonForAnalysis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereReferrerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereRegProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSendPredictorEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSendReminderNotificationEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereShareAllData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSmsNotificationsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSpam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereStripeActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereStripePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereStripeSubscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSubscriptionEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereSubscriptionProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereTagLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereTimeZoneOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereTrackLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUnsubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserActivationKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserNicename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereUserUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereWpPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseWpUser whereZipCode($value)
 * @method static \Illuminate\Database\Query\Builder|BaseWpUser withTrashed()
 * @method static \Illuminate\Database\Query\Builder|BaseWpUser withoutTrashed()
 * @mixin \Eloquent
 */
abstract class BaseWpUser extends BaseModel {
	use SoftDeletes;
	public const FIELD_ID = 'ID';
	public const FIELD_ADDRESS = 'address';
	public const FIELD_ETH_ADDRESS = 'eth_address';
	public const FIELD_ANALYSIS_ENDED_AT = 'analysis_ended_at';
	public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
	public const FIELD_ANALYSIS_SETTINGS_MODIFIED_AT = 'analysis_settings_modified_at';
	public const FIELD_ANALYSIS_STARTED_AT = 'analysis_started_at';
	public const FIELD_AVATAR_IMAGE = 'avatar_image';
	public const FIELD_BIRTHDAY = 'birthday';
	public const FIELD_CARD_BRAND = 'card_brand';
	public const FIELD_CARD_LAST_FOUR = 'card_last_four';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMBINE_NOTIFICATIONS = 'combine_notifications';
	public const FIELD_COUNTRY = 'country';
	public const FIELD_COVER_PHOTO = 'cover_photo';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CURRENCY = 'currency';
	public const FIELD_DELETED = 'deleted';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DELETION_REASON = 'deletion_reason';
	public const FIELD_DISPLAY_NAME = 'display_name';
	public const FIELD_EARLIEST_REMINDER_TIME = 'earliest_reminder_time';
	public const FIELD_EMAIL = 'email';
	public const FIELD_FIRST_NAME = 'first_name';
	public const FIELD_GENDER = 'gender';
	public const FIELD_GET_PREVIEW_BUILDS = 'get_preview_builds';
	public const FIELD_HAS_ANDROID_APP = 'has_android_app';
	public const FIELD_HAS_CHROME_EXTENSION = 'has_chrome_extension';
	public const FIELD_HAS_IOS_APP = 'has_ios_app';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_LANGUAGE = 'language';
	public const FIELD_LAST_CORRELATION_AT = 'last_correlation_at';
	public const FIELD_LAST_EMAIL_AT = 'last_email_at';
	public const FIELD_LAST_FOUR = 'last_four';
	public const FIELD_LAST_LOGIN_AT = 'last_login_at';
	public const FIELD_LAST_NAME = 'last_name';
	public const FIELD_LAST_PUSH_AT = 'last_push_at';
	public const FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID = 'last_sms_tracking_reminder_notification_id';
	public const FIELD_LATEST_REMINDER_TIME = 'latest_reminder_time';
	public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
	public const FIELD_NUMBER_OF_APPLICATIONS = 'number_of_applications';
	public const FIELD_NUMBER_OF_BUTTON_CLICKS = 'number_of_button_clicks';
	public const FIELD_NUMBER_OF_COLLABORATORS = 'number_of_collaborators';
	public const FIELD_NUMBER_OF_CONNECTIONS = 'number_of_connections';
	public const FIELD_NUMBER_OF_CONNECTOR_IMPORTS = 'number_of_connector_imports';
	public const FIELD_NUMBER_OF_CONNECTOR_REQUESTS = 'number_of_connector_requests';
	public const FIELD_NUMBER_OF_CORRELATIONS = 'number_of_correlations';
	public const FIELD_NUMBER_OF_MEASUREMENT_EXPORTS = 'number_of_measurement_exports';
	public const FIELD_NUMBER_OF_MEASUREMENT_IMPORTS = 'number_of_measurement_imports';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS = 'number_of_oauth_access_tokens';
	public const FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES = 'number_of_oauth_authorization_codes';
	public const FIELD_NUMBER_OF_OAUTH_CLIENTS = 'number_of_oauth_clients';
	public const FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS = 'number_of_oauth_refresh_tokens';
	public const FIELD_NUMBER_OF_PATIENTS = 'number_of_patients';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS = 'number_of_raw_measurements_with_tags';
	public const FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION = 'number_of_raw_measurements_with_tags_at_last_correlation';
	public const FIELD_NUMBER_OF_SENT_EMAILS = 'number_of_sent_emails';
	public const FIELD_NUMBER_OF_SHARERS = 'number_of_sharers';
	public const FIELD_NUMBER_OF_STUDIES = 'number_of_studies';
	public const FIELD_NUMBER_OF_SUBSCRIPTIONS = 'number_of_subscriptions';
	public const FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_TRUSTEES = 'number_of_trustees';
	public const FIELD_NUMBER_OF_USER_TAGS = 'number_of_user_tags';
	public const FIELD_NUMBER_OF_USER_VARIABLES = 'number_of_user_variables';
	public const FIELD_NUMBER_OF_USERS_WHERE_REFERRER_USER = 'number_of_users_where_referrer_user';
	public const FIELD_NUMBER_OF_VOTES = 'number_of_votes';
	public const FIELD_OLD_USER = 'old_user';
	public const FIELD_PASSWORD = 'password';
	public const FIELD_PHONE_NUMBER = 'phone_number';
	public const FIELD_PHONE_VERIFICATION_CODE = 'phone_verification_code';
	public const FIELD_PRIMARY_OUTCOME_VARIABLE_ID = 'primary_outcome_variable_id';
	public const FIELD_PROVIDER_ID = 'provider_id';
	public const FIELD_PROVIDER_TOKEN = 'provider_token';
	public const FIELD_PUSH_NOTIFICATIONS_ENABLED = 'push_notifications_enabled';
	public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
	public const FIELD_REFERRER_USER_ID = 'referrer_user_id';
	public const FIELD_REFRESH_TOKEN = 'refresh_token';
	public const FIELD_REG_PROVIDER = 'reg_provider';
	public const FIELD_REMEMBER_TOKEN = 'remember_token';
	public const FIELD_ROLES = 'roles';
	public const FIELD_SEND_PREDICTOR_EMAILS = 'send_predictor_emails';
	public const FIELD_SEND_REMINDER_NOTIFICATION_EMAILS = 'send_reminder_notification_emails';
	public const FIELD_SHARE_ALL_DATA = 'share_all_data';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SMS_NOTIFICATIONS_ENABLED = 'sms_notifications_enabled';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_SPAM = 'spam';
	public const FIELD_STATE = 'state';
	public const FIELD_STATUS = 'status';
	public const FIELD_STRIPE_ACTIVE = 'stripe_active';
	public const FIELD_STRIPE_ID = 'stripe_id';
	public const FIELD_STRIPE_PLAN = 'stripe_plan';
	public const FIELD_STRIPE_SUBSCRIPTION = 'stripe_subscription';
	public const FIELD_SUBSCRIPTION_ENDS_AT = 'subscription_ends_at';
	public const FIELD_SUBSCRIPTION_PROVIDER = 'subscription_provider';
	public const FIELD_TAG_LINE = 'tag_line';
	public const FIELD_TIME_ZONE_OFFSET = 'time_zone_offset';
	public const FIELD_TIMEZONE = 'timezone';
	public const FIELD_TRACK_LOCATION = 'track_location';
	public const FIELD_TRIAL_ENDS_AT = 'trial_ends_at';
	public const FIELD_UNSUBSCRIBED = 'unsubscribed';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ACTIVATION_KEY = 'user_activation_key';
	public const FIELD_USER_EMAIL = 'user_email';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_LOGIN = 'user_login';
	public const FIELD_USER_NICENAME = 'user_nicename';
	public const FIELD_USER_PASS = 'user_pass';
	public const FIELD_USER_REGISTERED = 'user_registered';
	public const FIELD_USER_STATUS = 'user_status';
	public const FIELD_USER_URL = 'user_url';
	public const FIELD_VERIFIED = 'verified';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const FIELD_ZIP_CODE = 'zip_code';
	public const TABLE = 'wp_users';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'WordPress’ user management is one of its strongest features and one that makes it great as an application framework. This table is the driving force behind it.';
	protected $primaryKey = self::FIELD_ID;
	protected $casts = [
        self::FIELD_USER_REGISTERED => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_TRIAL_ENDS_AT => 'datetime',
        self::FIELD_SUBSCRIPTION_ENDS_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_LAST_LOGIN_AT => 'datetime',
        self::FIELD_LAST_CORRELATION_AT => 'datetime',
        self::FIELD_LAST_EMAIL_AT => 'datetime',
        self::FIELD_LAST_PUSH_AT => 'datetime',
        self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
        self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
        self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
        self::FIELD_NEWEST_DATA_AT => 'datetime',
        self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
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
		self::FIELD_DELETION_REASON => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_EARLIEST_REMINDER_TIME => 'string',
		self::FIELD_EMAIL => 'string',
		self::FIELD_FIRST_NAME => 'string',
		self::FIELD_GENDER => 'string',
		self::FIELD_GET_PREVIEW_BUILDS => 'bool',
		self::FIELD_HAS_ANDROID_APP => 'bool',
		self::FIELD_HAS_CHROME_EXTENSION => 'bool',
		self::FIELD_HAS_IOS_APP => 'bool',
		self::FIELD_ID => 'int',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_IS_PUBLIC => 'bool',
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
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'int',
		self::FIELD_NUMBER_OF_OAUTH_CLIENTS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'int',
		self::FIELD_NUMBER_OF_PATIENTS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => 'int',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'int',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'int',
		self::FIELD_NUMBER_OF_SHARERS => 'int',
		self::FIELD_NUMBER_OF_STUDIES => 'int',
		self::FIELD_NUMBER_OF_SUBSCRIPTIONS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_NUMBER_OF_TRUSTEES => 'int',
		self::FIELD_NUMBER_OF_USERS_WHERE_REFERRER_USER => 'int',
		self::FIELD_NUMBER_OF_USER_TAGS => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VOTES => 'int',
		self::FIELD_OLD_USER => 'bool',
		self::FIELD_PASSWORD => 'string',
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
		self::FIELD_ROLES => 'string',
		self::FIELD_SEND_PREDICTOR_EMAILS => 'bool',
		self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 'bool',
		self::FIELD_SHARE_ALL_DATA => 'bool',
		self::FIELD_SMS_NOTIFICATIONS_ENABLED => 'bool',
		self::FIELD_SORT_ORDER => 'int',
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
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_LOGIN => 'string',
		self::FIELD_USER_NICENAME => 'string',
		self::FIELD_USER_PASS => 'string',
		self::FIELD_USER_STATUS => 'int',
		self::FIELD_USER_URL => 'string',
		self::FIELD_VERIFIED => 'string',
		self::FIELD_WP_POST_ID => 'int',
		self::FIELD_ZIP_CODE => 'string',	];
	protected array $rules = [
		self::FIELD_ADDRESS => 'nullable|max:255',
		self::FIELD_ANALYSIS_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_REQUESTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ANALYSIS_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_AVATAR_IMAGE => 'nullable|max:2083',
		self::FIELD_BIRTHDAY => 'nullable|max:255',
		self::FIELD_CARD_BRAND => 'nullable|max:255',
		self::FIELD_CARD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_CLIENT_ID => 'required|max:255',
		self::FIELD_COMBINE_NOTIFICATIONS => 'nullable|boolean',
		self::FIELD_COUNTRY => 'nullable|max:255',
		self::FIELD_COVER_PHOTO => 'nullable|max:2083',
		self::FIELD_CURRENCY => 'nullable|max:255',
		self::FIELD_DELETED => 'required|boolean',
		self::FIELD_DELETION_REASON => 'nullable|max:280',
		self::FIELD_DISPLAY_NAME => 'nullable|max:250',
		self::FIELD_EARLIEST_REMINDER_TIME => 'required|date',
		self::FIELD_EMAIL => 'nullable|max:320',
		self::FIELD_FIRST_NAME => 'nullable|max:255',
		self::FIELD_GENDER => 'nullable|max:255',
		self::FIELD_GET_PREVIEW_BUILDS => 'nullable|boolean',
		self::FIELD_HAS_ANDROID_APP => 'nullable|boolean',
		self::FIELD_HAS_CHROME_EXTENSION => 'nullable|boolean',
		self::FIELD_HAS_IOS_APP => 'nullable|boolean',
		self::FIELD_ID => 'required|numeric|min:0|unique:users,ID',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_LANGUAGE => 'nullable|max:255',
		self::FIELD_LAST_CORRELATION_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_EMAIL_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_LAST_LOGIN_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_NAME => 'nullable|max:255',
		self::FIELD_LAST_PUSH_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID => 'nullable|numeric|min:0',
		self::FIELD_LATEST_REMINDER_TIME => 'required|date',
		self::FIELD_NEWEST_DATA_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_APPLICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_CLIENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_PATIENTS => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SHARERS => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_STUDIES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_SUBSCRIPTIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRUSTEES => 'required|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USERS_WHERE_REFERRER_USER => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_TAGS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_NUMBER_OF_VOTES => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_OLD_USER => 'nullable|boolean',
		self::FIELD_PASSWORD => 'nullable|max:255',
		self::FIELD_PHONE_NUMBER => 'nullable|max:25',
		self::FIELD_PHONE_VERIFICATION_CODE => 'nullable|max:25',
		self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_PROVIDER_ID => 'nullable|max:255',
		self::FIELD_PROVIDER_TOKEN => 'nullable|max:255',
		self::FIELD_PUSH_NOTIFICATIONS_ENABLED => 'nullable|boolean',
		self::FIELD_REASON_FOR_ANALYSIS => 'nullable|max:255',
		self::FIELD_REFERRER_USER_ID => 'nullable|numeric|min:0',
		self::FIELD_REFRESH_TOKEN => 'nullable|max:255',
		self::FIELD_REG_PROVIDER => 'nullable|max:25',
		self::FIELD_REMEMBER_TOKEN => 'nullable|max:100',
		self::FIELD_ROLES => 'nullable|max:255',
		self::FIELD_SEND_PREDICTOR_EMAILS => 'nullable|boolean',
		self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 'nullable|boolean',
		self::FIELD_SHARE_ALL_DATA => 'required|boolean',
		self::FIELD_SMS_NOTIFICATIONS_ENABLED => 'nullable|boolean',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_SPAM => 'required|boolean',
		self::FIELD_STATE => 'nullable|max:255',
		self::FIELD_STATUS => 'nullable|max:25',
		self::FIELD_STRIPE_ACTIVE => 'nullable|boolean',
		self::FIELD_STRIPE_ID => 'nullable|max:255',
		self::FIELD_STRIPE_PLAN => 'nullable|max:100',
		self::FIELD_STRIPE_SUBSCRIPTION => 'nullable|max:255',
		self::FIELD_SUBSCRIPTION_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_SUBSCRIPTION_PROVIDER => 'nullable',
		self::FIELD_TAG_LINE => 'nullable|max:255',
		self::FIELD_TIMEZONE => 'nullable|max:255',
		self::FIELD_TIME_ZONE_OFFSET => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_TRACK_LOCATION => 'nullable|boolean',
		self::FIELD_TRIAL_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_UNSUBSCRIBED => 'nullable|boolean',
		self::FIELD_USER_ACTIVATION_KEY => 'nullable|max:255',
		self::FIELD_USER_EMAIL => 'nullable|max:100|unique:users,user_email',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_USER_LOGIN => 'nullable|max:60|unique:users,user_login',
		self::FIELD_USER_NICENAME => 'nullable|max:50',
		self::FIELD_USER_PASS => 'nullable|max:255',
		self::FIELD_USER_REGISTERED => 'nullable|date',
		self::FIELD_USER_STATUS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_USER_URL => 'nullable|max:2083',
		self::FIELD_VERIFIED => 'nullable|max:255',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
		self::FIELD_ZIP_CODE => 'nullable|max:255',
	];
	protected $hints = [
		self::FIELD_ID => 'Unique number assigned to each user.',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_USER_LOGIN => 'Unique username for the user.',
		self::FIELD_USER_EMAIL => 'Email address of the user.',
		self::FIELD_EMAIL => 'Needed for laravel password resets because WP user_email field will not work',
		self::FIELD_USER_PASS => 'Hash of the user’s password.',
		self::FIELD_USER_NICENAME => 'Display name for the user.',
		self::FIELD_USER_URL => 'URL of the user, e.g. website address.',
		self::FIELD_USER_REGISTERED => 'Time and date the user registered.',
		self::FIELD_USER_ACTIVATION_KEY => 'Used for resetting passwords.',
		self::FIELD_USER_STATUS => 'Was used in Multisite pre WordPress 3.0 to indicate a spam user.',
		self::FIELD_DISPLAY_NAME => 'Desired name to be used publicly in the site, can be user_login, user_nicename, first name or last name defined in usermeta.',
		self::FIELD_AVATAR_IMAGE => '',
		self::FIELD_REG_PROVIDER => 'Registered via',
		self::FIELD_PROVIDER_ID => 'Unique id from provider',
		self::FIELD_PROVIDER_TOKEN => 'Access token from provider',
		self::FIELD_REMEMBER_TOKEN => 'Remember me token',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_REFRESH_TOKEN => 'Refresh token from provider',
		self::FIELD_UNSUBSCRIBED => 'Indicates whether the use has specified that they want no emails or any form of communication. ',
		self::FIELD_OLD_USER => '',
		self::FIELD_STRIPE_ACTIVE => '',
		self::FIELD_STRIPE_ID => '',
		self::FIELD_STRIPE_SUBSCRIPTION => '',
		self::FIELD_STRIPE_PLAN => '',
		self::FIELD_LAST_FOUR => '',
		self::FIELD_TRIAL_ENDS_AT => 'datetime',
		self::FIELD_SUBSCRIPTION_ENDS_AT => 'datetime',
		self::FIELD_ROLES => 'An array containing all roles possessed by the user.  This indicates whether the use has roles such as administrator, developer, patient, student, researcher or physician. ',
		self::FIELD_TIME_ZONE_OFFSET => 'The time-zone offset is the difference, in minutes, between UTC and local time. Note that this means that the offset is positive if the local timezone is behind UTC (i.e. UTC−06:00 Central) and negative if it is ahead.',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_EARLIEST_REMINDER_TIME => 'Earliest time of day at which reminders should appear in HH:MM:SS format in user timezone',
		self::FIELD_LATEST_REMINDER_TIME => 'Latest time of day at which reminders should appear in HH:MM:SS format in user timezone',
		self::FIELD_PUSH_NOTIFICATIONS_ENABLED => 'Should we send the user push notifications?',
		self::FIELD_TRACK_LOCATION => 'Set to true if the user wants to track their location',
		self::FIELD_COMBINE_NOTIFICATIONS => 'Should we combine push notifications or send one for each tracking reminder notification?',
		self::FIELD_SEND_REMINDER_NOTIFICATION_EMAILS => 'Should we send reminder notification emails?',
		self::FIELD_SEND_PREDICTOR_EMAILS => 'Should we send predictor emails?',
		self::FIELD_GET_PREVIEW_BUILDS => 'Should we send preview builds of the mobile application?',
		self::FIELD_SUBSCRIPTION_PROVIDER => '',
		self::FIELD_LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID => '',
		self::FIELD_SMS_NOTIFICATIONS_ENABLED => 'Should we send tracking reminder notifications via tex messages?',
		self::FIELD_PHONE_VERIFICATION_CODE => '',
		self::FIELD_PHONE_NUMBER => '',
		self::FIELD_HAS_ANDROID_APP => '',
		self::FIELD_HAS_IOS_APP => '',
		self::FIELD_HAS_CHROME_EXTENSION => '',
		self::FIELD_REFERRER_USER_ID => '',
		self::FIELD_ADDRESS => '',
		self::FIELD_BIRTHDAY => '',
		self::FIELD_COUNTRY => '',
		self::FIELD_COVER_PHOTO => '',
		self::FIELD_CURRENCY => '',
		self::FIELD_FIRST_NAME => '',
		self::FIELD_GENDER => '',
		self::FIELD_LANGUAGE => '',
		self::FIELD_LAST_NAME => '',
		self::FIELD_STATE => '',
		self::FIELD_TAG_LINE => '',
		self::FIELD_VERIFIED => '',
		self::FIELD_ZIP_CODE => '',
		self::FIELD_SPAM => '',
		self::FIELD_DELETED => '',
		self::FIELD_CARD_BRAND => '',
		self::FIELD_CARD_LAST_FOUR => '',
		self::FIELD_LAST_LOGIN_AT => 'datetime',
		self::FIELD_TIMEZONE => '',
		self::FIELD_NUMBER_OF_CORRELATIONS => '',
		self::FIELD_NUMBER_OF_CONNECTIONS => '',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => '',
		self::FIELD_NUMBER_OF_USER_VARIABLES => '',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS => '',
		self::FIELD_NUMBER_OF_RAW_MEASUREMENTS_WITH_TAGS_AT_LAST_CORRELATION => '',
		self::FIELD_NUMBER_OF_VOTES => '',
		self::FIELD_NUMBER_OF_STUDIES => '',
		self::FIELD_LAST_CORRELATION_AT => 'datetime',
		self::FIELD_LAST_EMAIL_AT => 'datetime',
		self::FIELD_LAST_PUSH_AT => 'datetime',
		self::FIELD_PRIMARY_OUTCOME_VARIABLE_ID => '',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_ANALYSIS_ENDED_AT => 'datetime',
		self::FIELD_ANALYSIS_REQUESTED_AT => 'datetime',
		self::FIELD_ANALYSIS_STARTED_AT => 'datetime',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
		self::FIELD_NEWEST_DATA_AT => 'datetime',
		self::FIELD_REASON_FOR_ANALYSIS => '',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_STATUS => '',
		self::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => 'datetime',
		self::FIELD_NUMBER_OF_APPLICATIONS => 'Number of Applications for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(id) as total, user_id
                            from applications
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_applications = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'Number of OAuth Access Tokens for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(access_token) as total, user_id
                            from oa_access_tokens
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_oauth_access_tokens = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'Number of OAuth Authorization Codes for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(authorization_code) as total, user_id
                            from oa_authorization_codes
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_oauth_authorization_codes = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_CLIENTS => 'Number of OAuth Clients for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(client_id) as total, user_id
                            from oa_clients
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_oauth_clients = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'Number of OAuth Refresh Tokens for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(refresh_token) as total, user_id
                            from oa_refresh_tokens
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_oauth_refresh_tokens = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'Number of Button Clicks for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(id) as total, user_id
                            from button_clicks
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_button_clicks = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'Number of Collaborators for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(id) as total, user_id
                            from collaborators
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_collaborators = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'Number of Connector Imports for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(id) as total, user_id
                            from connector_imports
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_connector_imports = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_REQUESTS => 'Number of Connector Requests for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(id) as total, user_id
                            from connector_requests
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_connector_requests = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'Number of Measurement Exports for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from measurement_exports
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_measurement_exports = count(grouped.total)]',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'Number of Measurement Imports for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from measurement_imports
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_measurement_imports = count(grouped.total)]',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from measurements
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'Number of Sent Emails for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from sent_emails
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_sent_emails = count(grouped.total)]',
		self::FIELD_NUMBER_OF_SUBSCRIPTIONS => 'Number of Subscriptions for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from subscriptions
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_subscriptions = count(grouped.total)]',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'Number of Tracking Reminder Notifications for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from tracking_reminder_notifications
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_tracking_reminder_notifications = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_TAGS => 'Number of User Tags for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from user_tags
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_user_tags = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USERS_WHERE_REFERRER_USER => 'Number of Users for this Referrer User.
                    [Formula: update wp_users
                        left join (
                            select count(ID) as total, referrer_user_id
                            from wp_users
                            group by referrer_user_id
                        )
                        as grouped on wp_users.ID = grouped.referrer_user_id
                    set wp_users.number_of_users_where_referrer_user = count(grouped.total)]',
		self::FIELD_SHARE_ALL_DATA => '',
		self::FIELD_DELETION_REASON => 'The reason the user deleted their account.',
		self::FIELD_PASSWORD => '',
		self::FIELD_NUMBER_OF_PATIENTS => '',
		self::FIELD_IS_PUBLIC => '',
		self::FIELD_SORT_ORDER => '',
		self::FIELD_NUMBER_OF_SHARERS => 'Number of people sharing their data with you.',
		self::FIELD_NUMBER_OF_TRUSTEES => 'Number of people that you are sharing your data with.',
	];
	protected array $relationshipInfo = [
		'primary_outcome_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'primary_outcome_variable_id',
			'foreignKey' => \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'primary_outcome_variable_id',
			'ownerKey' => \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID,
			'methodName' => 'primary_outcome_variable',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => \App\Models\User::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => \App\Models\User::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
		'referrer_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'referrer_user_id',
			'foreignKey' => \App\Models\User::FIELD_REFERRER_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'referrer_user_id',
			'ownerKey' => \App\Models\User::FIELD_REFERRER_USER_ID,
			'methodName' => 'referrer_user',
		],
		'applications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Application::class,
			'foreignKey' => Application::FIELD_USER_ID,
			'localKey' => Application::FIELD_ID,
			'methodName' => 'applications',
		],
		'button_clicks' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ButtonClick::class,
			'foreignKey' => ButtonClick::FIELD_USER_ID,
			'localKey' => ButtonClick::FIELD_ID,
			'methodName' => 'button_clicks',
		],
		'buttons' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Button::class,
			'foreignKey' => Button::FIELD_USER_ID,
			'localKey' => Button::FIELD_ID,
			'methodName' => 'buttons',
		],
		'child_parents' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ChildParent::class,
			'foreignKey' => ChildParent::FIELD_CHILD_USER_ID,
			'localKey' => ChildParent::FIELD_ID,
			'methodName' => 'child_parents',
		],
		'child_parents_where_parent_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ChildParent::class,
			'foreignKey' => ChildParent::FIELD_PARENT_USER_ID,
			'localKey' => ChildParent::FIELD_ID,
			'methodName' => 'child_parents_where_parent_user',
		],
		'collaborators' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Collaborator::class,
			'foreignKey' => Collaborator::FIELD_USER_ID,
			'localKey' => Collaborator::FIELD_ID,
			'methodName' => 'collaborators',
		],
		'connections' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connection::class,
			'foreignKey' => Connection::FIELD_USER_ID,
			'localKey' => Connection::FIELD_ID,
			'methodName' => 'connections',
		],
		'connector_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKey' => ConnectorImport::FIELD_USER_ID,
			'localKey' => ConnectorImport::FIELD_ID,
			'methodName' => 'connector_imports',
		],
		'connector_requests' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorRequest::class,
			'foreignKey' => ConnectorRequest::FIELD_USER_ID,
			'localKey' => ConnectorRequest::FIELD_ID,
			'methodName' => 'connector_requests',
		],
		'correlation_causality_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_USER_ID,
			'localKey' => CorrelationCausalityVote::FIELD_ID,
			'methodName' => 'correlation_causality_votes',
		],
		'correlation_usefulness_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_USER_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_ID,
			'methodName' => 'correlation_usefulness_votes',
		],
		'user_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_USER_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'user_variable_relationships',
		],
		'device_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => DeviceToken::class,
			'foreignKey' => DeviceToken::FIELD_USER_ID,
			'localKey' => DeviceToken::FIELD_ID,
			'methodName' => 'device_tokens',
		],
		'measurement_exports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => MeasurementExport::class,
			'foreignKey' => MeasurementExport::FIELD_USER_ID,
			'localKey' => MeasurementExport::FIELD_ID,
			'methodName' => 'measurement_exports',
		],
		'measurement_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => MeasurementImport::class,
			'foreignKey' => MeasurementImport::FIELD_USER_ID,
			'localKey' => MeasurementImport::FIELD_ID,
			'methodName' => 'measurement_imports',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_USER_ID,
			'localKey' => Measurement::FIELD_ID,
			'methodName' => 'measurements',
		],
		'oa_access_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OAAccessToken::class,
			'foreignKey' => OAAccessToken::FIELD_USER_ID,
			'localKey' => OAAccessToken::FIELD_ID,
			'methodName' => 'oa_access_tokens',
		],
		'oa_authorization_codes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OAAuthorizationCode::class,
			'foreignKey' => OAAuthorizationCode::FIELD_USER_ID,
			'localKey' => OAAuthorizationCode::FIELD_ID,
			'methodName' => 'oa_authorization_codes',
		],
		'oa_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKey' => OAClient::FIELD_USER_ID,
			'localKey' => OAClient::FIELD_ID,
			'methodName' => 'oa_clients',
		],
		'oa_refresh_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OARefreshToken::class,
			'foreignKey' => OARefreshToken::FIELD_USER_ID,
			'localKey' => OARefreshToken::FIELD_ID,
			'methodName' => 'oa_refresh_tokens',
		],
		'patient_physicians_where_patient_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => PatientPhysician::class,
			'foreignKey' => PatientPhysician::FIELD_PATIENT_USER_ID,
			'localKey' => PatientPhysician::FIELD_ID,
			'methodName' => 'patient_physicians_where_patient_user',
		],
		'patient_physicians_where_physician_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => PatientPhysician::class,
			'foreignKey' => PatientPhysician::FIELD_PHYSICIAN_USER_ID,
			'localKey' => PatientPhysician::FIELD_ID,
			'methodName' => 'patient_physicians_where_physician_user',
		],
		'phrases' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Phrase::class,
			'foreignKey' => Phrase::FIELD_USER_ID,
			'localKey' => Phrase::FIELD_ID,
			'methodName' => 'phrases',
		],
		'purchases_where_subscriber_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Purchase::class,
			'foreignKey' => Purchase::FIELD_SUBSCRIBER_USER_ID,
			'localKey' => Purchase::FIELD_ID,
			'methodName' => 'purchases_where_subscriber_user',
		],
		'sent_emails' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SentEmail::class,
			'foreignKey' => SentEmail::FIELD_USER_ID,
			'localKey' => SentEmail::FIELD_ID,
			'methodName' => 'sent_emails',
		],
		'sharer_trustees_where_sharer_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SharerTrustee::class,
			'foreignKey' => SharerTrustee::FIELD_SHARER_USER_ID,
			'localKey' => SharerTrustee::FIELD_ID,
			'methodName' => 'sharer_trustees_where_sharer_user',
		],
		'sharer_trustees_where_trustee_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SharerTrustee::class,
			'foreignKey' => SharerTrustee::FIELD_TRUSTEE_USER_ID,
			'localKey' => SharerTrustee::FIELD_ID,
			'methodName' => 'sharer_trustees_where_trustee_user',
		],
		'studies' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Study::class,
			'foreignKey' => Study::FIELD_USER_ID,
			'localKey' => Study::FIELD_ID,
			'methodName' => 'studies',
		],
		'tracker_logs' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackerLog::class,
			'foreignKey' => TrackerLog::FIELD_USER_ID,
			'localKey' => TrackerLog::FIELD_ID,
			'methodName' => 'tracker_logs',
		],
		'tracker_sessions' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackerSession::class,
			'foreignKey' => TrackerSession::FIELD_USER_ID,
			'localKey' => TrackerSession::FIELD_ID,
			'methodName' => 'tracker_sessions',
		],
		'tracking_reminder_notifications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminderNotification::class,
			'foreignKey' => TrackingReminderNotification::FIELD_USER_ID,
			'localKey' => TrackingReminderNotification::FIELD_ID,
			'methodName' => 'tracking_reminder_notifications',
		],
		'tracking_reminders' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminder::class,
			'foreignKey' => TrackingReminder::FIELD_USER_ID,
			'localKey' => TrackingReminder::FIELD_ID,
			'methodName' => 'tracking_reminders',
		],
		'user_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserClient::class,
			'foreignKey' => UserClient::FIELD_USER_ID,
			'localKey' => UserClient::FIELD_ID,
			'methodName' => 'user_clients',
		],
		'user_tags' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_USER_ID,
			'localKey' => UserTag::FIELD_ID,
			'methodName' => 'user_tags',
		],
		'user_variable_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableClient::class,
			'foreignKey' => UserVariableClient::FIELD_USER_ID,
			'localKey' => UserVariableClient::FIELD_ID,
			'methodName' => 'user_variable_clients',
		],
		'user_variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_USER_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables',
		],
		'votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_USER_ID,
			'localKey' => Vote::FIELD_ID,
			'methodName' => 'votes',
		],
		'wp_links' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpLink::class,
			'foreignKey' => WpLink::FIELD_LINK_OWNER,
			'localKey' => WpLink::FIELD_ID,
			'methodName' => 'wp_links',
		],
		'wp_posts' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKey' => WpPost::FIELD_POST_AUTHOR,
			'localKey' => WpPost::FIELD_ID,
			'methodName' => 'wp_posts',
		],
		'wp_usermeta' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpUsermetum::class,
			'foreignKey' => WpUsermetum::FIELD_USER_ID,
			'localKey' => WpUsermetum::FIELD_ID,
			'methodName' => 'wp_usermeta',
		],
		'users_where_referrer_user' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKey' => \App\Models\User::FIELD_REFERRER_USER_ID,
			'localKey' => \App\Models\User::FIELD_ID,
			'methodName' => 'users_where_referrer_user',
		],
	];
	public function primary_outcome_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID,
			Variable::FIELD_ID, \App\Models\User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, \App\Models\User::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			\App\Models\User::FIELD_WP_POST_ID);
	}
	public function referrer_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, \App\Models\User::FIELD_REFERRER_USER_ID,
			\App\Models\User::FIELD_ID, \App\Models\User::FIELD_REFERRER_USER_ID);
	}
	public function applications(): HasMany{
		return $this->hasMany(Application::class, Application::FIELD_USER_ID, static::FIELD_ID);
	}
	public function button_clicks(): HasMany{
		return $this->hasMany(ButtonClick::class, ButtonClick::FIELD_USER_ID, static::FIELD_ID);
	}
	public function buttons(): HasMany{
		return $this->hasMany(Button::class, Button::FIELD_USER_ID, static::FIELD_ID);
	}
	public function child_parents(): HasMany{
		return $this->hasMany(ChildParent::class, ChildParent::FIELD_CHILD_USER_ID, static::FIELD_ID);
	}
	public function child_parents_where_parent_user(): HasMany{
		return $this->hasMany(ChildParent::class, ChildParent::FIELD_PARENT_USER_ID, static::FIELD_ID);
	}
	public function collaborators(): HasMany{
		return $this->hasMany(Collaborator::class, Collaborator::FIELD_USER_ID, static::FIELD_ID);
	}
	public function connections(): HasMany{
		return $this->hasMany(Connection::class, Connection::FIELD_USER_ID, static::FIELD_ID);
	}
	public function connector_imports(): HasMany{
		return $this->hasMany(ConnectorImport::class, ConnectorImport::FIELD_USER_ID, static::FIELD_ID);
	}
	public function connector_requests(): HasMany{
		return $this->hasMany(ConnectorRequest::class, ConnectorRequest::FIELD_USER_ID, static::FIELD_ID);
	}
	public function correlation_causality_votes(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_USER_ID,
			static::FIELD_ID);
	}
	public function correlation_usefulness_votes(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class, CorrelationUsefulnessVote::FIELD_USER_ID,
			static::FIELD_ID);
	}
	public function correlations(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_USER_ID, static::FIELD_ID);
	}
	public function device_tokens(): HasMany{
		return $this->hasMany(DeviceToken::class, DeviceToken::FIELD_USER_ID, static::FIELD_ID);
	}
	public function measurement_exports(): HasMany{
		return $this->hasMany(MeasurementExport::class, MeasurementExport::FIELD_USER_ID, static::FIELD_ID);
	}
	public function measurement_imports(): HasMany{
		return $this->hasMany(MeasurementImport::class, MeasurementImport::FIELD_USER_ID, static::FIELD_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_USER_ID, static::FIELD_ID);
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany|OAAccessToken
	 */
	public function oa_access_tokens(): HasMany{
		return $this->hasMany(OAAccessToken::class, OAAccessToken::FIELD_USER_ID, static::FIELD_ID);
	}
	public function oa_authorization_codes(): HasMany{
		return $this->hasMany(OAAuthorizationCode::class, OAAuthorizationCode::FIELD_USER_ID, static::FIELD_ID);
	}
	public function oa_clients(): HasMany{
		return $this->hasMany(OAClient::class, OAClient::FIELD_USER_ID, static::FIELD_ID);
	}
	public function oa_refresh_tokens(): HasMany{
		return $this->hasMany(OARefreshToken::class, OARefreshToken::FIELD_USER_ID, static::FIELD_ID);
	}
	public function patient_physicians_where_patient_user(): HasMany{
		return $this->hasMany(PatientPhysician::class, PatientPhysician::FIELD_PATIENT_USER_ID, static::FIELD_ID);
	}
	public function patient_physicians_where_physician_user(): HasMany{
		return $this->hasMany(PatientPhysician::class, PatientPhysician::FIELD_PHYSICIAN_USER_ID, static::FIELD_ID);
	}
	public function phrases(): HasMany{
		return $this->hasMany(Phrase::class, Phrase::FIELD_USER_ID, static::FIELD_ID);
	}
	public function purchases_where_subscriber_user(): HasMany{
		return $this->hasMany(Purchase::class, Purchase::FIELD_SUBSCRIBER_USER_ID, static::FIELD_ID);
	}
	public function sent_emails(): HasMany{
		return $this->hasMany(SentEmail::class, SentEmail::FIELD_USER_ID, static::FIELD_ID);
	}
	public function sharer_trustees_where_sharer_user(): HasMany{
		return $this->hasMany(SharerTrustee::class, SharerTrustee::FIELD_SHARER_USER_ID, static::FIELD_ID);
	}
	public function sharer_trustees_where_trustee_user(): HasMany{
		return $this->hasMany(SharerTrustee::class, SharerTrustee::FIELD_TRUSTEE_USER_ID, static::FIELD_ID);
	}
	public function studies(): HasMany{
		return $this->hasMany(Study::class, Study::FIELD_USER_ID, static::FIELD_ID);
	}
	public function tracker_logs(): HasMany{
		return $this->hasMany(TrackerLog::class, TrackerLog::FIELD_USER_ID, static::FIELD_ID);
	}
	public function tracker_sessions(): HasMany{
		return $this->hasMany(TrackerSession::class, TrackerSession::FIELD_USER_ID, static::FIELD_ID);
	}
	public function tracking_reminder_notifications(): HasMany{
		return $this->hasMany(TrackingReminderNotification::class, TrackingReminderNotification::FIELD_USER_ID,
			static::FIELD_ID);
	}
	public function tracking_reminders(): HasMany{
		return $this->hasMany(TrackingReminder::class, TrackingReminder::FIELD_USER_ID, static::FIELD_ID);
	}
	public function user_clients(): HasMany{
		return $this->hasMany(UserClient::class, UserClient::FIELD_USER_ID, static::FIELD_ID);
	}
	public function user_tags(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_USER_ID, static::FIELD_ID);
	}
	public function user_variable_clients(): HasMany{
		return $this->hasMany(UserVariableClient::class, UserVariableClient::FIELD_USER_ID, static::FIELD_ID);
	}
	public function user_variables(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_USER_ID, static::FIELD_ID);
	}
	public function votes(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_USER_ID, static::FIELD_ID);
	}
	public function wp_links(): HasMany{
		return $this->hasMany(WpLink::class, WpLink::FIELD_LINK_OWNER, WpLink::FIELD_ID);
	}
	public function wp_posts(): HasMany{
		return $this->hasMany(WpPost::class, WpPost::FIELD_POST_AUTHOR, WpPost::FIELD_ID);
	}
	public function wp_usermeta(): HasMany{
		return $this->hasMany(WpUsermetum::class, WpUsermetum::FIELD_USER_ID, static::FIELD_ID);
	}
	public function users_where_referrer_user(): HasMany{
		return $this->hasMany(\App\Models\User::class, \App\Models\User::FIELD_REFERRER_USER_ID, static::FIELD_ID);
	}
}
