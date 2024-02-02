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
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\Collaborator;
use App\Models\CommonTag;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
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
use App\Models\Phrase;
use App\Models\Purchase;
use App\Models\SentEmail;
use App\Models\SourcePlatform;
use App\Models\SpreadsheetImporter;
use App\Models\Study;
use App\Models\ThirdPartyCorrelation;
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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseOAClient
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 * @property string $grant_types
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $icon_url
 * @property string $app_identifier
 * @property Carbon $deleted_at
 * @property Carbon $earliest_measurement_start_at
 * @property Carbon $latest_measurement_start_at
 * @property int $number_of_global_variable_relationships
 * @property int $number_of_applications
 * @property int $number_of_oauth_access_tokens
 * @property int $number_of_oauth_authorization_codes
 * @property int $number_of_oauth_refresh_tokens
 * @property int $number_of_button_clicks
 * @property int $number_of_collaborators
 * @property int $number_of_common_tags
 * @property int $number_of_connections
 * @property int $number_of_connector_imports
 * @property int $number_of_connectors
 * @property int $number_of_correlations
 * @property int $number_of_measurement_exports
 * @property int $number_of_measurement_imports
 * @property int $number_of_measurements
 * @property int $number_of_sent_emails
 * @property int $number_of_studies
 * @property int $number_of_tracking_reminder_notifications
 * @property int $number_of_tracking_reminders
 * @property int $number_of_user_tags
 * @property int $number_of_user_variables
 * @property int $number_of_variables
 * @property int $number_of_votes
 * @property \App\Models\User $user
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property Application $application
 * @property Collection|ButtonClick[] $button_clicks
 * @property Collection|Button[] $buttons
 * @property Collection|Collaborator[] $collaborators
 * @property Collection|CommonTag[] $common_tags
 * @property Collection|Connection[] $connections
 * @property Collection|ConnectorImport[] $connector_imports
 * @property Collection|Connector[] $connectors
 * @property Collection|CorrelationCausalityVote[] $correlation_causality_votes
 * @property Collection|CorrelationUsefulnessVote[] $correlation_usefulness_votes
 * @property Collection|UserVariableRelationship[] $user_variable_relationships
 * @property Collection|DeviceToken[] $device_tokens
 * @property Collection|MeasurementExport[] $measurement_exports
 * @property Collection|MeasurementImport[] $measurement_imports
 * @property Collection|Measurement[] $measurements
 * @property Collection|OAAccessToken[] $oa_access_tokens
 * @property Collection|OAAuthorizationCode[] $oa_authorization_codes
 * @property Collection|OARefreshToken[] $oa_refresh_tokens
 * @property Collection|Phrase[] $phrases
 * @property Collection|Purchase[] $purchases
 * @property Collection|SentEmail[] $sent_emails
 * @property Collection|SourcePlatform[] $source_platforms
 * @property Collection|SpreadsheetImporter[] $spreadsheet_importers
 * @property Collection|Study[] $studies
 * @property Collection|ThirdPartyCorrelation[] $third_party_correlations
 * @property Collection|TrackerLog[] $tracker_logs
 * @property Collection|TrackerSession[] $tracker_sessions
 * @property Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property Collection|TrackingReminder[] $tracking_reminders
 * @property Collection|UserClient[] $user_clients
 * @property Collection|UserTag[] $user_tags
 * @property Collection|UserVariableClient[] $user_variable_clients
 * @property Collection|UserVariable[] $user_variables
 * @property Collection|Variable[] $variables
 * @property Collection|Vote[] $votes
 * @package App\Models\Base
 * @property-read int|null $global_variable_relationships_count
 * @property-read int|null $oa_access_tokens_count
 * @property-read int|null $oa_authorization_codes_count
 * @property-read int|null $oa_refresh_tokens_count
 * @property-read int|null $button_clicks_count
 * @property-read int|null $buttons_count
 * @property-read int|null $collaborators_count
 * @property-read int|null $common_tags_count
 * @property-read int|null $connections_count
 * @property-read int|null $connector_imports_count
 * @property-read int|null $connectors_count
 * @property-read int|null $correlation_causality_votes_count
 * @property-read int|null $correlation_usefulness_votes_count
 * @property-read int|null $correlations_count
 * @property-read int|null $device_tokens_count
 * @property-read int|null $measurement_exports_count
 * @property-read int|null $measurement_imports_count
 * @property-read int|null $measurements_count
 * @property-read int|null $phrases_count
 * @property-read int|null $purchases_count
 * @property-read int|null $sent_emails_count
 * @property-read int|null $source_platforms_count
 * @property-read int|null $spreadsheet_importers_count
 * @property-read int|null $studies_count
 * @property-read int|null $third_party_correlations_count
 * @property-read int|null $tracker_logs_count
 * @property-read int|null $tracker_sessions_count
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read int|null $tracking_reminders_count
 * @property-read int|null $user_clients_count
 * @property-read int|null $user_tags_count
 * @property-read int|null $user_variable_clients_count
 * @property-read int|null $user_variables_count
 * @property-read int|null $variable_user_sources_count
 * @property-read int|null $variables_count
 * @property-read int|null $votes_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAClient onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereAppIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereEarliestMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereGrantTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereLatestMeasurementStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfGlobalVariableRelationships($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfButtonClicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfCollaborators($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfCommonTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfConnections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfConnectorImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfConnectors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfCorrelations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfMeasurementExports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfMeasurementImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfMeasurements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfOauthAccessTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfOauthAuthorizationCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfOauthRefreshTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfSentEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfTrackingReminderNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfTrackingReminders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfUserTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient
 *     whereNumberOfUserVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereNumberOfVotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereRedirectUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseOAClient whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAClient withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseOAClient withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseOAClient extends BaseModel {
	use SoftDeletes;
	public const FIELD_APP_IDENTIFIER = 'app_identifier';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CLIENT_SECRET = 'client_secret';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EARLIEST_MEASUREMENT_START_AT = 'earliest_measurement_start_at';
	public const FIELD_GRANT_TYPES = 'grant_types';
	public const FIELD_ICON_URL = 'icon_url';
	public const FIELD_LATEST_MEASUREMENT_START_AT = 'latest_measurement_start_at';
	public const FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS = 'number_of_global_variable_relationships';
	public const FIELD_NUMBER_OF_APPLICATIONS = 'number_of_applications';
	public const FIELD_NUMBER_OF_BUTTON_CLICKS = 'number_of_button_clicks';
	public const FIELD_NUMBER_OF_COLLABORATORS = 'number_of_collaborators';
	public const FIELD_NUMBER_OF_COMMON_TAGS = 'number_of_common_tags';
	public const FIELD_NUMBER_OF_CONNECTIONS = 'number_of_connections';
	public const FIELD_NUMBER_OF_CONNECTOR_IMPORTS = 'number_of_connector_imports';
	public const FIELD_NUMBER_OF_CONNECTORS = 'number_of_connectors';
	public const FIELD_NUMBER_OF_CORRELATIONS = 'number_of_correlations';
	public const FIELD_NUMBER_OF_MEASUREMENT_EXPORTS = 'number_of_measurement_exports';
	public const FIELD_NUMBER_OF_MEASUREMENT_IMPORTS = 'number_of_measurement_imports';
	public const FIELD_NUMBER_OF_MEASUREMENTS = 'number_of_measurements';
	public const FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS = 'number_of_oauth_access_tokens';
	public const FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES = 'number_of_oauth_authorization_codes';
	public const FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS = 'number_of_oauth_refresh_tokens';
	public const FIELD_NUMBER_OF_SENT_EMAILS = 'number_of_sent_emails';
	public const FIELD_NUMBER_OF_STUDIES = 'number_of_studies';
	public const FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS = 'number_of_tracking_reminder_notifications';
	public const FIELD_NUMBER_OF_TRACKING_REMINDERS = 'number_of_tracking_reminders';
	public const FIELD_NUMBER_OF_USER_TAGS = 'number_of_user_tags';
	public const FIELD_NUMBER_OF_USER_VARIABLES = 'number_of_user_variables';
	public const FIELD_NUMBER_OF_VARIABLES = 'number_of_variables';
	public const FIELD_NUMBER_OF_VOTES = 'number_of_votes';
	public const FIELD_REDIRECT_URI = 'redirect_uri';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'oa_clients';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'client_id';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'datetime',
        self::FIELD_LATEST_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_APP_IDENTIFIER => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CLIENT_SECRET => 'string',
		self::FIELD_GRANT_TYPES => 'string',
		self::FIELD_ICON_URL => 'string',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_APPLICATIONS => 'int',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'int',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'int',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'int',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'int',
		self::FIELD_NUMBER_OF_CONNECTORS => 'int',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'int',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'int',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'int',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'int',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'int',
		self::FIELD_NUMBER_OF_STUDIES => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'int',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'int',
		self::FIELD_NUMBER_OF_USER_TAGS => 'int',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VARIABLES => 'int',
		self::FIELD_NUMBER_OF_VOTES => 'int',
		self::FIELD_REDIRECT_URI => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_APP_IDENTIFIER => 'nullable|max:255',
		self::FIELD_CLIENT_ID => 'required|max:80|unique:oa_clients,client_id',
		self::FIELD_CLIENT_SECRET => 'required|max:80',
		self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_GRANT_TYPES => 'nullable|max:80',
		self::FIELD_ICON_URL => 'nullable|max:2083',
		self::FIELD_LATEST_MEASUREMENT_START_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_APPLICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTORS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_STUDIES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_TAGS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VARIABLES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_NUMBER_OF_VOTES => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_REDIRECT_URI => 'nullable|max:2000',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CLIENT_SECRET => '',
		self::FIELD_REDIRECT_URI => '',
		self::FIELD_GRANT_TYPES => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ICON_URL => '',
		self::FIELD_APP_IDENTIFIER => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_EARLIEST_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_LATEST_MEASUREMENT_START_AT => 'datetime',
		self::FIELD_NUMBER_OF_AGGREGATE_CORRELATIONS => 'Number of Global Population Studies for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from global_variable_relationships
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_global_variable_relationships = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_APPLICATIONS => 'Number of Applications for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from applications
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_applications = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_ACCESS_TOKENS => 'Number of OAuth Access Tokens for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(access_token) as total, client_id
                            from oa_access_tokens
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_oauth_access_tokens = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_AUTHORIZATION_CODES => 'Number of OAuth Authorization Codes for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(authorization_code) as total, client_id
                            from oa_authorization_codes
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_oauth_authorization_codes = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_OAUTH_REFRESH_TOKENS => 'Number of OAuth Refresh Tokens for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(refresh_token) as total, client_id
                            from oa_refresh_tokens
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_oauth_refresh_tokens = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_BUTTON_CLICKS => 'Number of Button Clicks for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from button_clicks
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_button_clicks = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COLLABORATORS => 'Number of Collaborators for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from collaborators
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_collaborators = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_COMMON_TAGS => 'Number of Common Tags for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from common_tags
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_common_tags = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTIONS => 'Number of Connections for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from connections
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_connections = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTOR_IMPORTS => 'Number of Connector Imports for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from connector_imports
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_connector_imports = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CONNECTORS => 'Number of Connectors for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from connectors
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_connectors = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_CORRELATIONS => 'Number of Individual Case Studies for this Client.
                [Formula:
                    update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from user_variable_relationships
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_correlations = count(grouped.total)
                ]
                ',
		self::FIELD_NUMBER_OF_MEASUREMENT_EXPORTS => 'Number of Measurement Exports for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from measurement_exports
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_measurement_exports = count(grouped.total)]',
		self::FIELD_NUMBER_OF_MEASUREMENT_IMPORTS => 'Number of Measurement Imports for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from measurement_imports
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_measurement_imports = count(grouped.total)]',
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'Number of Measurements for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from measurements
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_measurements = count(grouped.total)]',
		self::FIELD_NUMBER_OF_SENT_EMAILS => 'Number of Sent Emails for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from sent_emails
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_sent_emails = count(grouped.total)]',
		self::FIELD_NUMBER_OF_STUDIES => 'Number of Studies for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from studies
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_studies = count(grouped.total)]',
		self::FIELD_NUMBER_OF_TRACKING_REMINDER_NOTIFICATIONS => 'Number of Tracking Reminder Notifications for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from tracking_reminder_notifications
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_tracking_reminder_notifications = count(grouped.total)]',
		self::FIELD_NUMBER_OF_TRACKING_REMINDERS => 'Number of Tracking Reminders for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from tracking_reminders
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_tracking_reminders = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_TAGS => 'Number of User Tags for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from user_tags
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_user_tags = count(grouped.total)]',
		self::FIELD_NUMBER_OF_USER_VARIABLES => 'Number of User Variables for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from user_variables
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_user_variables = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VARIABLES => 'Number of Variables for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from variables
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_variables = count(grouped.total)]',
		self::FIELD_NUMBER_OF_VOTES => 'Number of Votes for this Client.
                    [Formula: update oa_clients
                        left join (
                            select count(id) as total, client_id
                            from votes
                            group by client_id
                        )
                        as grouped on oa_clients.client_id = grouped.client_id
                    set oa_clients.number_of_votes = count(grouped.total)]',
	];
	protected array $relationshipInfo = [
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => OAClient::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => OAClient::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'global_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_CLIENT_ID,
			'localKey' => GlobalVariableRelationship::FIELD_CLIENT_ID,
			'methodName' => 'global_variable_relationships',
		],
		'application' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => Application::class,
			'foreignKey' => Application::FIELD_CLIENT_ID,
			'localKey' => Application::FIELD_CLIENT_ID,
			'methodName' => 'application',
		],
		'oa_access_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OAAccessToken::class,
			'foreignKey' => OAAccessToken::FIELD_CLIENT_ID,
			'localKey' => OAAccessToken::FIELD_CLIENT_ID,
			'methodName' => 'oa_access_tokens',
		],
		'oa_authorization_codes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OAAuthorizationCode::class,
			'foreignKey' => OAAuthorizationCode::FIELD_CLIENT_ID,
			'localKey' => OAAuthorizationCode::FIELD_CLIENT_ID,
			'methodName' => 'oa_authorization_codes',
		],
		'oa_refresh_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => OARefreshToken::class,
			'foreignKey' => OARefreshToken::FIELD_CLIENT_ID,
			'localKey' => OARefreshToken::FIELD_CLIENT_ID,
			'methodName' => 'oa_refresh_tokens',
		],
		'button_clicks' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ButtonClick::class,
			'foreignKey' => ButtonClick::FIELD_CLIENT_ID,
			'localKey' => ButtonClick::FIELD_CLIENT_ID,
			'methodName' => 'button_clicks',
		],
		'buttons' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Button::class,
			'foreignKey' => Button::FIELD_CLIENT_ID,
			'localKey' => Button::FIELD_CLIENT_ID,
			'methodName' => 'buttons',
		],
		'collaborators' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Collaborator::class,
			'foreignKey' => Collaborator::FIELD_CLIENT_ID,
			'localKey' => Collaborator::FIELD_CLIENT_ID,
			'methodName' => 'collaborators',
		],
		'common_tags' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CommonTag::class,
			'foreignKey' => CommonTag::FIELD_CLIENT_ID,
			'localKey' => CommonTag::FIELD_CLIENT_ID,
			'methodName' => 'common_tags',
		],
		'connections' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connection::class,
			'foreignKey' => Connection::FIELD_CLIENT_ID,
			'localKey' => Connection::FIELD_CLIENT_ID,
			'methodName' => 'connections',
		],
		'connector_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ConnectorImport::class,
			'foreignKey' => ConnectorImport::FIELD_CLIENT_ID,
			'localKey' => ConnectorImport::FIELD_CLIENT_ID,
			'methodName' => 'connector_imports',
		],
		'connectors' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connector::class,
			'foreignKey' => Connector::FIELD_CLIENT_ID,
			'localKey' => Connector::FIELD_CLIENT_ID,
			'methodName' => 'connectors',
		],
		'correlation_causality_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationCausalityVote::class,
			'foreignKey' => CorrelationCausalityVote::FIELD_CLIENT_ID,
			'localKey' => CorrelationCausalityVote::FIELD_CLIENT_ID,
			'methodName' => 'correlation_causality_votes',
		],
		'correlation_usefulness_votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => CorrelationUsefulnessVote::class,
			'foreignKey' => CorrelationUsefulnessVote::FIELD_CLIENT_ID,
			'localKey' => CorrelationUsefulnessVote::FIELD_CLIENT_ID,
			'methodName' => 'correlation_usefulness_votes',
		],
		'user_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_CLIENT_ID,
			'localKey' => UserVariableRelationship::FIELD_CLIENT_ID,
			'methodName' => 'user_variable_relationships',
		],
		'device_tokens' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => DeviceToken::class,
			'foreignKey' => DeviceToken::FIELD_CLIENT_ID,
			'localKey' => DeviceToken::FIELD_CLIENT_ID,
			'methodName' => 'device_tokens',
		],
		'measurement_exports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => MeasurementExport::class,
			'foreignKey' => MeasurementExport::FIELD_CLIENT_ID,
			'localKey' => MeasurementExport::FIELD_CLIENT_ID,
			'methodName' => 'measurement_exports',
		],
		'measurement_imports' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => MeasurementImport::class,
			'foreignKey' => MeasurementImport::FIELD_CLIENT_ID,
			'localKey' => MeasurementImport::FIELD_CLIENT_ID,
			'methodName' => 'measurement_imports',
		],
		'measurements' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Measurement::class,
			'foreignKey' => Measurement::FIELD_CLIENT_ID,
			'localKey' => Measurement::FIELD_CLIENT_ID,
			'methodName' => 'measurements',
		],
		'phrases' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Phrase::class,
			'foreignKey' => Phrase::FIELD_CLIENT_ID,
			'localKey' => Phrase::FIELD_CLIENT_ID,
			'methodName' => 'phrases',
		],
		'purchases' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Purchase::class,
			'foreignKey' => Purchase::FIELD_CLIENT_ID,
			'localKey' => Purchase::FIELD_CLIENT_ID,
			'methodName' => 'purchases',
		],
		'sent_emails' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SentEmail::class,
			'foreignKey' => SentEmail::FIELD_CLIENT_ID,
			'localKey' => SentEmail::FIELD_CLIENT_ID,
			'methodName' => 'sent_emails',
		],
		'source_platforms' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SourcePlatform::class,
			'foreignKey' => SourcePlatform::FIELD_CLIENT_ID,
			'localKey' => SourcePlatform::FIELD_CLIENT_ID,
			'methodName' => 'source_platforms',
		],
		'spreadsheet_importers' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SpreadsheetImporter::class,
			'foreignKey' => SpreadsheetImporter::FIELD_CLIENT_ID,
			'localKey' => SpreadsheetImporter::FIELD_CLIENT_ID,
			'methodName' => 'spreadsheet_importers',
		],
		'studies' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Study::class,
			'foreignKey' => Study::FIELD_CLIENT_ID,
			'localKey' => Study::FIELD_CLIENT_ID,
			'methodName' => 'studies',
		],
		'third_party_correlations' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => ThirdPartyCorrelation::class,
			'foreignKey' => ThirdPartyCorrelation::FIELD_CLIENT_ID,
			'localKey' => ThirdPartyCorrelation::FIELD_CLIENT_ID,
			'methodName' => 'third_party_correlations',
		],
		'tracker_logs' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackerLog::class,
			'foreignKey' => TrackerLog::FIELD_CLIENT_ID,
			'localKey' => TrackerLog::FIELD_CLIENT_ID,
			'methodName' => 'tracker_logs',
		],
		'tracker_sessions' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackerSession::class,
			'foreignKey' => TrackerSession::FIELD_CLIENT_ID,
			'localKey' => TrackerSession::FIELD_CLIENT_ID,
			'methodName' => 'tracker_sessions',
		],
		'tracking_reminder_notifications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminderNotification::class,
			'foreignKey' => TrackingReminderNotification::FIELD_CLIENT_ID,
			'localKey' => TrackingReminderNotification::FIELD_CLIENT_ID,
			'methodName' => 'tracking_reminder_notifications',
		],
		'tracking_reminders' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => TrackingReminder::class,
			'foreignKey' => TrackingReminder::FIELD_CLIENT_ID,
			'localKey' => TrackingReminder::FIELD_CLIENT_ID,
			'methodName' => 'tracking_reminders',
		],
		'user_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserClient::class,
			'foreignKey' => UserClient::FIELD_CLIENT_ID,
			'localKey' => UserClient::FIELD_CLIENT_ID,
			'methodName' => 'user_clients',
		],
		'user_tags' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserTag::class,
			'foreignKey' => UserTag::FIELD_CLIENT_ID,
			'localKey' => UserTag::FIELD_CLIENT_ID,
			'methodName' => 'user_tags',
		],
		'user_variable_clients' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableClient::class,
			'foreignKey' => UserVariableClient::FIELD_CLIENT_ID,
			'localKey' => UserVariableClient::FIELD_CLIENT_ID,
			'methodName' => 'user_variable_clients',
		],
		'user_variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_CLIENT_ID,
			'localKey' => UserVariable::FIELD_CLIENT_ID,
			'methodName' => 'user_variables',
		],
		'variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_CLIENT_ID,
			'localKey' => Variable::FIELD_CLIENT_ID,
			'methodName' => 'variables',
		],
		'votes' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Vote::class,
			'foreignKey' => Vote::FIELD_CLIENT_ID,
			'localKey' => Vote::FIELD_CLIENT_ID,
			'methodName' => 'votes',
		],
	];
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, OAClient::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			OAClient::FIELD_USER_ID);
	}
	public function global_variable_relationships(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_CLIENT_ID,
			GlobalVariableRelationship::FIELD_CLIENT_ID);
	}
	public function application(): HasOne{
		return $this->hasOne(Application::class, Application::FIELD_CLIENT_ID, Application::FIELD_CLIENT_ID);
	}
	public function button_clicks(): HasMany{
		return $this->hasMany(ButtonClick::class, ButtonClick::FIELD_CLIENT_ID, ButtonClick::FIELD_CLIENT_ID);
	}
	public function buttons(): HasMany{
		return $this->hasMany(Button::class, Button::FIELD_CLIENT_ID, Button::FIELD_CLIENT_ID);
	}
	public function collaborators(): HasMany{
		return $this->hasMany(Collaborator::class, Collaborator::FIELD_CLIENT_ID, Collaborator::FIELD_CLIENT_ID);
	}
	public function common_tags(): HasMany{
		return $this->hasMany(CommonTag::class, CommonTag::FIELD_CLIENT_ID, CommonTag::FIELD_CLIENT_ID);
	}
	public function connections(): HasMany{
		return $this->hasMany(Connection::class, Connection::FIELD_CLIENT_ID, Connection::FIELD_CLIENT_ID);
	}
	public function connector_imports(): HasMany{
		return $this->hasMany(ConnectorImport::class, ConnectorImport::FIELD_CLIENT_ID,
			ConnectorImport::FIELD_CLIENT_ID);
	}
	public function connectors(): HasMany{
		return $this->hasMany(Connector::class, Connector::FIELD_CLIENT_ID, Connector::FIELD_CLIENT_ID);
	}
	public function correlation_causality_votes(): HasMany{
		return $this->hasMany(CorrelationCausalityVote::class, CorrelationCausalityVote::FIELD_CLIENT_ID,
			CorrelationCausalityVote::FIELD_CLIENT_ID);
	}
	public function correlation_usefulness_votes(): HasMany{
		return $this->hasMany(CorrelationUsefulnessVote::class, CorrelationUsefulnessVote::FIELD_CLIENT_ID,
			CorrelationUsefulnessVote::FIELD_CLIENT_ID);
	}
	public function correlations(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_CLIENT_ID, UserVariableRelationship::FIELD_CLIENT_ID);
	}
	public function device_tokens(): HasMany{
		return $this->hasMany(DeviceToken::class, DeviceToken::FIELD_CLIENT_ID, DeviceToken::FIELD_CLIENT_ID);
	}
	public function measurement_exports(): HasMany{
		return $this->hasMany(MeasurementExport::class, MeasurementExport::FIELD_CLIENT_ID,
			MeasurementExport::FIELD_CLIENT_ID);
	}
	public function measurement_imports(): HasMany{
		return $this->hasMany(MeasurementImport::class, MeasurementImport::FIELD_CLIENT_ID,
			MeasurementImport::FIELD_CLIENT_ID);
	}
	public function measurements(): HasMany{
		return $this->hasMany(Measurement::class, Measurement::FIELD_CLIENT_ID, Measurement::FIELD_CLIENT_ID);
	}

    /**
     * @return HasMany|OAAccessToken[]
     */
    public function oa_access_tokens(): HasMany{
		return $this->hasMany(OAAccessToken::class, OAAccessToken::FIELD_CLIENT_ID, OAAccessToken::FIELD_CLIENT_ID);
	}
	public function oa_authorization_codes(): HasMany{
		return $this->hasMany(OAAuthorizationCode::class, OAAuthorizationCode::FIELD_CLIENT_ID,
			OAAuthorizationCode::FIELD_CLIENT_ID);
	}
	public function oa_refresh_tokens(): HasMany{
		return $this->hasMany(OARefreshToken::class, OARefreshToken::FIELD_CLIENT_ID, OARefreshToken::FIELD_CLIENT_ID);
	}
	public function phrases(): HasMany{
		return $this->hasMany(Phrase::class, Phrase::FIELD_CLIENT_ID, Phrase::FIELD_CLIENT_ID);
	}
	public function purchases(): HasMany{
		return $this->hasMany(Purchase::class, Purchase::FIELD_CLIENT_ID, Purchase::FIELD_CLIENT_ID);
	}
	public function sent_emails(): HasMany{
		return $this->hasMany(SentEmail::class, SentEmail::FIELD_CLIENT_ID, SentEmail::FIELD_CLIENT_ID);
	}
	public function source_platforms(): HasMany{
		return $this->hasMany(SourcePlatform::class, SourcePlatform::FIELD_CLIENT_ID, SourcePlatform::FIELD_CLIENT_ID);
	}
	public function spreadsheet_importers(): HasMany{
		return $this->hasMany(SpreadsheetImporter::class, SpreadsheetImporter::FIELD_CLIENT_ID,
			SpreadsheetImporter::FIELD_CLIENT_ID);
	}
	public function studies(): HasMany{
		return $this->hasMany(Study::class, Study::FIELD_CLIENT_ID, Study::FIELD_CLIENT_ID);
	}
	public function third_party_correlations(): HasMany{
		return $this->hasMany(ThirdPartyCorrelation::class, ThirdPartyCorrelation::FIELD_CLIENT_ID,
			ThirdPartyCorrelation::FIELD_CLIENT_ID);
	}
	public function tracker_logs(): HasMany{
		return $this->hasMany(TrackerLog::class, TrackerLog::FIELD_CLIENT_ID, TrackerLog::FIELD_CLIENT_ID);
	}
	public function tracker_sessions(): HasMany{
		return $this->hasMany(TrackerSession::class, TrackerSession::FIELD_CLIENT_ID, TrackerSession::FIELD_CLIENT_ID);
	}
	public function tracking_reminder_notifications(): HasMany{
		return $this->hasMany(TrackingReminderNotification::class, TrackingReminderNotification::FIELD_CLIENT_ID,
			TrackingReminderNotification::FIELD_CLIENT_ID);
	}
	public function tracking_reminders(): HasMany{
		return $this->hasMany(TrackingReminder::class, TrackingReminder::FIELD_CLIENT_ID,
			TrackingReminder::FIELD_CLIENT_ID);
	}
	public function user_clients(): HasMany{
		return $this->hasMany(UserClient::class, UserClient::FIELD_CLIENT_ID, UserClient::FIELD_CLIENT_ID);
	}
	public function user_tags(): HasMany{
		return $this->hasMany(UserTag::class, UserTag::FIELD_CLIENT_ID, UserTag::FIELD_CLIENT_ID);
	}
	public function user_variable_clients(): HasMany{
		return $this->hasMany(UserVariableClient::class, UserVariableClient::FIELD_CLIENT_ID,
			UserVariableClient::FIELD_CLIENT_ID);
	}
	public function user_variables(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_CLIENT_ID, UserVariable::FIELD_CLIENT_ID);
	}
	public function variables(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_CLIENT_ID, Variable::FIELD_CLIENT_ID);
	}
	public function votes(): HasMany{
		return $this->hasMany(Vote::class, Vote::FIELD_CLIENT_ID, Vote::FIELD_CLIENT_ID);
	}
}
