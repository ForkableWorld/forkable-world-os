<?php namespace App\Models;
use App\Properties\OAClient\OAClientClientSecretProperty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\OAClient\OAClientUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\States\ConfigurationStateButton;
use App\DataSources\QMClient;
use App\Exceptions\InvalidClientException;
use App\Models\Base\BaseOAClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\ReadonlyDB;
use App\Traits\HasDBModel;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\OAClientTrait;
use App\UI\FontAwesome;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\OAClient
 * @property string $client_id
 * @property string $client_secret
 * @property string|null $redirect_uri
 * @property string|null $grant_types
 * @property string|null $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $icon_url
 * @property string|null $app_identifier
 * @property string|null $deleted_at
 * @method static Builder|OAClient newModelQuery()
 * @method static Builder|OAClient newQuery()
 * @method static Builder|OAClient query()
 * @method static Builder|OAClient whereAppIdentifier($value)
 * @method static Builder|OAClient whereClientId($value)
 * @method static Builder|OAClient whereClientSecret($value)
 * @method static Builder|OAClient whereCreatedAt($value)
 * @method static Builder|OAClient whereDeletedAt($value)
 * @method static Builder|OAClient whereGrantTypes($value)
 * @method static Builder|OAClient whereIconUrl($value)
 * @method static Builder|OAClient whereRedirectUri($value)
 * @method static Builder|OAClient whereUpdatedAt($value)
 * @method static Builder|OAClient whereUserId($value)
 * @mixin Eloquent
 * @property Carbon|null $earliest_measurement_start_at
 * @property Carbon|null $latest_measurement_start_at
 * @method static Builder|OAClient whereEarliestMeasurementStartAt($value)
 * @method static Builder|OAClient whereLatestMeasurementStartAt($value)
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property-read int|null $global_variable_relationships_count
 * @property-read Application $application
 * @property-read Collection|OAAccessToken[] $oa_access_tokens
 * @property-read int|null $oa_access_tokens_count
 * @property-read Collection|OAAuthorizationCode[] $oa_authorization_codes
 * @property-read int|null $oa_authorization_codes_count
 * @property-read Collection|OARefreshToken[] $oa_refresh_tokens
 * @property-read int|null $oa_refresh_tokens_count
 * @property-read Button $button
 * @property-read Collection|ButtonClick[] $button_clicks
 * @property-read int|null $button_clicks_count
 * @property-read Card $card
 * @property-read Collection|Collaborator[] $collaborators
 * @property-read int|null $collaborators_count
 * @property-read Collection|CommonTag[] $common_tags
 * @property-read int|null $common_tags_count
 * @property-read Collection|Connection[] $connections
 * @property-read int|null $connections_count
 * @property-read Collection|Connector[] $connectors
 * @property-read int|null $connectors_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|Credential[] $credentials
 * @property-read int|null $credentials_count
 * @property-read Collection|DeviceToken[] $device_tokens
 * @property-read int|null $device_tokens_count
 * @property-read Collection|MeasurementExport[] $measurement_exports
 * @property-read int|null $measurement_exports_count
 * @property-read Collection|MeasurementImport[] $measurement_imports
 * @property-read int|null $measurement_imports_count
 * @property-read Collection|Measurement[] $measurements
 * @property-read int|null $measurements_count
 * @property-read Collection|Phrase[] $phrases
 * @property-read int|null $phrases_count
 * @property-read Collection|Purchase[] $purchases
 * @property-read int|null $purchases_count
 * @property-read Collection|SentEmail[] $sent_emails
 * @property-read int|null $sent_emails_count
 * @property-read Collection|SourcePlatform[] $source_platforms
 * @property-read int|null $source_platforms_count
 * @property-read Collection|Study[] $studies
 * @property-read int|null $studies_count
 * @property-read Collection|ThirdPartyCorrelation[] $third_party_correlations
 * @property-read int|null $third_party_correlations_count
 * @property-read Collection|TrackerLog[] $tracker_logs
 * @property-read int|null $tracker_logs_count
 * @property-read Collection|TrackerSession[] $tracker_sessions
 * @property-read int|null $tracker_sessions_count
 * @property-read Collection|TrackingReminderNotification[] $tracking_reminder_notifications
 * @property-read int|null $tracking_reminder_notifications_count
 * @property-read Collection|TrackingReminder[] $tracking_reminders
 * @property-read int|null $tracking_reminders_count
 * @property-read Collection|ConnectorImport[] $connector_imports
 * @property-read int|null $connector_imports_count
 * @property-read User $user
 * @property-read Collection|UserTag[] $user_tags
 * @property-read int|null $user_tags_count
 * @property-read Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read int|null $variable_user_sources_count
 * @property-read Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @property-read Collection|Vote[] $votes
 * @property-read int|null $votes_count
 * @property-read Collection|UserClient[] $user_clients
 * @property-read int|null $user_clients_count
 * @property-read Collection|UserVariableClient[] $user_variable_clients
 * @property-read int|null $user_variable_clients_count
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property int|null $number_of_global_variable_relationships Number of Global Population Studies for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from global_variable_relationships
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_global_variable_relationships = count(grouped.total)
 *                 ]
 * @property int|null $number_of_applications Number of Applications for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from applications
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_applications = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_access_tokens Number of OAuth Access Tokens for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(access_token) as total, client_id
 *                             from oa_access_tokens
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_oauth_access_tokens = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_authorization_codes Number of OAuth Authorization Codes for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(authorization_code) as total, client_id
 *                             from oa_authorization_codes
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_oauth_authorization_codes = count(grouped.total)
 *                 ]
 * @property int|null $number_of_oauth_refresh_tokens Number of OAuth Refresh Tokens for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(refresh_token) as total, client_id
 *                             from oa_refresh_tokens
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_oauth_refresh_tokens = count(grouped.total)
 *                 ]
 * @property int|null $number_of_button_clicks Number of Button Clicks for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from button_clicks
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_button_clicks = count(grouped.total)
 *                 ]
 * @property int|null $number_of_collaborators Number of Collaborators for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from collaborators
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_collaborators = count(grouped.total)
 *                 ]
 * @property int|null $number_of_common_tags Number of Common Tags for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from common_tags
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_common_tags = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connections Number of Connections for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from connections
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_connections = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connector_imports Number of Connector Imports for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from connector_imports
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_connector_imports = count(grouped.total)
 *                 ]
 * @property int|null $number_of_connectors Number of Connectors for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from connectors
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_connectors = count(grouped.total)
 *                 ]
 * @property int|null $number_of_correlations Number of Individual Case Studies for this Client.
 *                 [Formula:
 *                     update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from user_variable_relationships
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_correlations = count(grouped.total)
 *                 ]
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|OAClient whereNumberOfGlobalVariableRelationships($value)
 * @method static Builder|OAClient whereNumberOfApplications($value)
 * @method static Builder|OAClient whereNumberOfButtonClicks($value)
 * @method static Builder|OAClient whereNumberOfCollaborators($value)
 * @method static Builder|OAClient whereNumberOfCommonTags($value)
 * @method static Builder|OAClient whereNumberOfConnections($value)
 * @method static Builder|OAClient whereNumberOfConnectorImports($value)
 * @method static Builder|OAClient whereNumberOfConnectors($value)
 * @method static Builder|OAClient whereNumberOfCorrelations($value)
 * @method static Builder|OAClient whereNumberOfOauthAccessTokens($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OAClient
 *     whereNumberOfOauthAuthorizationCodes($value)
 * @method static Builder|OAClient whereNumberOfOauthRefreshTokens($value)
 * @property-read Collection|Button[] $buttons
 * @property-read int|null $buttons_count
 * @property-read Collection|CorrelationCausalityVote[]
 *     $correlation_causality_votes
 * @property-read int|null $correlation_causality_votes_count
 * @property-read Collection|CorrelationUsefulnessVote[]
 *     $correlation_usefulness_votes
 * @property-read int|null $correlation_usefulness_votes_count
 * @property int|null $number_of_measurement_exports Number of Measurement Exports for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from measurement_exports
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_measurement_exports = count(grouped.total)]
 * @property int|null $number_of_measurement_imports Number of Measurement Imports for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from measurement_imports
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_measurement_imports = count(grouped.total)]
 * @property int|null $number_of_measurements Number of Measurements for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from measurements
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_measurements = count(grouped.total)]
 * @property int|null $number_of_sent_emails Number of Sent Emails for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from sent_emails
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_sent_emails = count(grouped.total)]
 * @property int|null $number_of_studies Number of Studies for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from studies
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_studies = count(grouped.total)]
 * @property int|null $number_of_tracking_reminder_notifications Number of Tracking Reminder Notifications for this
 *     Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from tracking_reminder_notifications
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_tracking_reminder_notifications = count(grouped.total)]
 * @property int|null $number_of_tracking_reminders Number of Tracking Reminders for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from tracking_reminders
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_tracking_reminders = count(grouped.total)]
 * @property int|null $number_of_user_tags Number of User Tags for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from user_tags
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_user_tags = count(grouped.total)]
 * @property int|null $number_of_user_variables Number of User Variables for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from user_variables
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_user_variables = count(grouped.total)]
 * @property int|null $number_of_variables Number of Variables for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from variables
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_variables = count(grouped.total)]
 * @property int|null $number_of_votes Number of Votes for this Client.
 *                     [Formula: update oa_clients
 *                         left join (
 *                             select count(id) as total, client_id
 *                             from votes
 *                             group by client_id
 *                         )
 *                         as grouped on oa_clients.client_id = grouped.client_id
 *                     set oa_clients.number_of_votes = count(grouped.total)]
 * @method static Builder|OAClient whereNumberOfMeasurementExports($value)
 * @method static Builder|OAClient whereNumberOfMeasurementImports($value)
 * @method static Builder|OAClient whereNumberOfMeasurements($value)
 * @method static Builder|OAClient whereNumberOfSentEmails($value)
 * @method static Builder|OAClient whereNumberOfStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OAClient
 *     whereNumberOfTrackingReminderNotifications($value)
 * @method static Builder|OAClient whereNumberOfTrackingReminders($value)
 * @method static Builder|OAClient whereNumberOfUserTags($value)
 * @method static Builder|OAClient whereNumberOfUserVariables($value)
 * @method static Builder|OAClient whereNumberOfVariables($value)
 * @method static Builder|OAClient whereNumberOfVotes($value)

 * @property-read Collection|SpreadsheetImporter[] $spreadsheet_importers
 * @property-read int|null $spreadsheet_importers_count
 * @property mixed $raw
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class OAClient extends BaseOAClient {
    public const CLASS_DISPLAY_NAME = "OAuth Client";
    use HasFactory;
	use OAClientTrait;
	use HasUser, HasDBModel;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = OAClient::FIELD_ID;
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [
		OAClient::FIELD_CLIENT_ID,
	];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	//public static $with = ['user'];
	public static $group = OAClient::CLASS_CATEGORY;
	const CLASS_CATEGORY = OAAccessToken::CLASS_CATEGORY;
	public static function getSlimClass(): string{ return QMClient::class; }
	public const CLASS_DESCRIPTION = "OAuth clients allow users to permit access to their self-tracking data to the owner of the client. ";
	public const FIELD_ID = self::FIELD_CLIENT_ID;
	public const FONT_AWESOME = FontAwesome::ID_CARD;
	public static function getUniqueIndexColumns(): array{
		return [self::FIELD_CLIENT_ID];
	}
	public const DEFAULT_SEARCH_FIELD = self::FIELD_CLIENT_ID;
	public const DEFAULT_ORDER_DIRECTION = 'asc';
	public const DEFAULT_LIMIT = 20;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_KEY;
	protected $primaryKey = 'client_id';
    protected $keyType = 'string';
	/**
	 * @var bool Indicates if the IDs are auto-incrementing.
	 */
	public $incrementing = false;
	public $hidden = [
		self::FIELD_CLIENT_SECRET,
	];
	/**
	 * @param string $clientId
	 * @param array $values
	 * @return OAClient
	 */
	public static function getOrCreate(string $clientId, array $values = []): OAClient{
		if(!isset($values[OAClient::FIELD_CLIENT_SECRET])){
			$values[OAClient::FIELD_CLIENT_SECRET] = Str::random(32);
		}
		$values[OAClient::FIELD_CLIENT_ID] = $clientId;
		$c = self::findInMemoryOrDB($clientId);
		if($c){
			return $c;
		}
		BaseClientIdProperty::validateClientId($clientId);
		\App\Logging\ConsoleLog::info("Creating client " . $values[self::FIELD_CLIENT_ID] . "...");
		$c = self::create($values);
		/** @var self $c */
		return $c;
	}
	public function hardDeleteWithRelations(string $reason): void{
		$this->logError("Hard deleting $this");
		if(stripos($this->client_id, 'test') === false && !str_contains($reason, 'test')){
			le("Are you sure you want to hard delete $this?");
		}
		$this->collaborators()->forceDelete();
		$this->oa_access_tokens()->forceDelete();
		$this->oa_refresh_tokens()->forceDelete();
		$this->application()->forceDelete();
		$this->device_tokens()->forceDelete();
		$this->forceDelete();
	}
	public function getLogMetaDataString(): string{
		return $this->client_id;
	}
	public function isTestClient(): bool{
		return stripos($this->client_id, BaseClientIdProperty::TEST_CLIENT_ID_PREFIX) !== false ||
			stripos($this->client_id, 'test-user') !== false;
	}
	public function getEditUrl(array $params = []): string{
		$params[self::FIELD_CLIENT_ID] = $this->client_id;
		return ConfigurationStateButton::make()->getUrl($params);
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new OAClientUserButton($this),
		];
	}
	public function toNonNullArray(): array{
		$arr = parent::toNonNullArray();
		// client_secret is not included because it's hidden but we need it for DBModel
		$arr[self::FIELD_CLIENT_SECRET] = $this->client_secret;
		return $arr;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getNameAttribute(): string{
		return $this->getClientId();
	}
	/**
	 * Get the displayable label of the resource.
	 * @return string
	 */
	public static function label(): string{
		return "Clients";
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{
		return QMAuth::isAdmin();
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function getCollaborators(): Collection {
		$this->loadMissing('collaborators');
		return $this->collaborators;
	}
	/**
	 * @return string
	 */
	public function getRedirectUrisWithLineBreaks(): string {
		$uris = str_replace(' ', "\r\n", $this->redirect_uri);
		return $uris;
	}
	/**
	 * @return array
	 */
	public function getAbbreviatedUsers(): array {
		$users = QMUser::readonly()
			->join(OAAccessToken::TABLE, OAAccessToken::TABLE.'.user_id', '=', User::TABLE.'.ID')
			->orderBy(User::TABLE.'.user_login', 'asc')
			->groupBy([User::TABLE.'.user_login'])
			->where(OAAccessToken::TABLE.'.client_id', $this->client_id)
			->where(OAAccessToken::TABLE.'.expires',  '>',  Carbon::now());
		$db = ReadonlyDB::db();
		$users->columns[] = $db->raw('MAX(oa_access_tokens.access_token) as access_token');
		$users->columns[] = $db->raw('MAX('. User::TABLE.'.display_name) as display_name');
		$users->columns[] = $db->raw('MAX('. User::TABLE.'.user_login) as user_login');
		$users->columns[] = $db->raw('MAX('. User::TABLE.'.avatar_image) as avatar');
		$users->columns[] = $db->raw('MAX('. User::TABLE.'.user_email) as user_email');
		$users = $users->getArray();
		foreach($users as $applicationUser){
			if(!isset($applicationUser->avatar)){
				$applicationUser->avatar = ImageHelper::generateGravatarImageOrDefault($applicationUser->user_email);
			}
			if(!isset($applicationUser->display_name)){$applicationUser->display_name = $applicationUser->user_login;}
			$applicationUser->email = $applicationUser->user_email;
		}
		return $users;
	}

    /**
     * @throws InvalidClientException
     */
    public static function authorizeBySecret(array $data): OAClient {
        $client_id = $client_secret = null;
        if($data){
            $client_id = BaseClientIdProperty::pluck($data);
            $client_secret = BaseClientSecretProperty::pluck($data);
        }
        if(!$client_id){$client_id = BaseClientIdProperty::fromRequest(false);}
        if(!$client_secret){$client_secret = BaseClientSecretProperty::fromRequest(false);}
        if(!$client_id){throw new InvalidClientException("No client_id provided");}
        if(!$client_secret){throw new InvalidClientException("No client_secret provided");}
        $client = static::findInMemoryOrDB($client_id);
        if(!$client){throw new InvalidClientException();}
        if($client->client_secret !== $client_secret){throw new InvalidClientException();}
        return $client;
    }

    /**
     * @return User[]|Collection
     */
    public function getUsers()
    {
        $tokens = $this->getValidAccessTokens();
        $users = User::whereIn(User::FIELD_ID, $tokens->pluck(OAAccessToken::FIELD_USER_ID)->all())
            ->get();
        return $users;
    }

    /**
     * @return OAAccessToken[]|Collection
     */
    public function getValidAccessTokens()
    {
        $tokens = $this->oa_access_tokens()->where('expires', '>', Carbon::now())->get();
        return $tokens;
    }
    public static function getTestClient():self{
        return static::findInMemoryOrDB(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
    }
    public function getUsersCountAttribute():int{
        return $this->getUsers()->count();
    }
    public static function findOrCreate($data): BaseModel
    {
        if(!isset($data[self::FIELD_CLIENT_SECRET])){
            $data[self::FIELD_CLIENT_SECRET] = OAClientClientSecretProperty::generate();
        }
        return parent::findOrCreate($data); // TODO: Change the autogenerated stub
    }

    /**
     * @return static|null
     */
    public static function fromRequest(): ?self{
        $clientId = BaseClientIdProperty::fromRequest();
        if(!$clientId){return null;}
        return static::findInMemoryOrDB($clientId);
    }
    /**
     * @return string|null
     */
    public function getHumanizedIdentifierAttributeName(): ?string
    {
        return self::FIELD_CLIENT_ID;
    }
    public function getTitleAttribute(): string{
        $client_id = $this->client_id;
        if(!$client_id){
            return static::CLASS_DISPLAY_NAME;
        }
        return $client_id;
    }
    public static function seed(): void
    {
        parent::seed();
    }
    /**
     * @return mixed
     */
    public static function getSeedData(): array
    {
        $arr = parent::getSeedData();
        foreach ($arr as $item){
            if(!isset($item[self::FIELD_CLIENT_SECRET])){
                $item[self::FIELD_CLIENT_SECRET] = OAClientClientSecretProperty::TEST_CLIENT_SECRET;
            }
        }
        return $arr;
    }
}
