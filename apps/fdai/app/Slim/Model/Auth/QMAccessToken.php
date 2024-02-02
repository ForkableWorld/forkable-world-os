<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection CryptographicallySecureRandomnessInspection */
/** @noinspection CryptographicallySecureRandomnessInspection */
namespace App\Slim\Model\Auth;
use App\AppSettings\AppSettings;
use App\DataSources\QMClient;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\InsufficientScopeException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserNumberOfPatientsProperty;
use App\Properties\User\UserProviderTokenProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use App\Slim\QMSlim;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use App\Traits\HasModel\HasUser;
use App\Types\QMStr;
use App\Utils\AppMode;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Query\Builder;
/**
 * @mixin OAAccessToken
 */
class QMAccessToken extends DBModel {
	use HasUser;
	public const ACCESS_TOKEN_LIFETIME_IN_SECONDS = 31536000;
	public const FIELD_ACCESS_TOKEN = 'access_token';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_EXPIRES = 'expires';
	public const FIELD_SCOPE = 'scope';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_ID = self::FIELD_ACCESS_TOKEN;
	public const SCOPE_READ_MEASUREMENTS = 'readmeasurements';
	public const SCOPE_WRITE_MEASUREMENTS = 'writemeasurements';
	public const MEMCACHE_EXPIRATION_IN_SECONDS = 3600;
	public const TABLE = 'oa_access_tokens';
	public const INVALID_TOKEN_EXCEPTION = "No access tokens in database match provided token string";
	public $accessToken;
	public $clientId;
	public $expires;
	public $scope;
	public $userId;
	public const LARAVEL_CLASS = OAAccessToken::class;
	/**
	 * @param object|array|string|null $rowOrString
	 */
	public function __construct(object|array|string $rowOrString = null){
		$t = $rowOrString;
		if(is_string($rowOrString)){
			$t = self::find($rowOrString);
		}
		if(!$t){
			return;
		}
		$this->populateFieldsByArrayOrObject($t);
		if(!is_string($this->clientId)){
			le("Client id is not a string: " . $this->clientId);
		}
	}
	/**
	 * @param string|null $required_scope
	 * @return QMAccessToken|null
	 * @throws AccessTokenExpiredException
	 * @throws AuthorizationException
	 */
	public static function fromRequest(string $required_scope = null): ?QMAccessToken{
		$str = BaseAccessTokenProperty::fromRequest();
		if(!$str){
			return null;
		}
		if($str === BaseAccessTokenProperty::ADMIN_TEST_TOKEN && AppMode::isUnitOrStagingUnitTest()){
			if(!Writable::isTestingOrStaging()){
				le("Admin test token can only be used on databases including 'testing' or 'staging'");
			}
			$t = OAAccessToken::firstOrCreate([OAAccessToken::FIELD_ACCESS_TOKEN => $str],
				[
					OAAccessToken::FIELD_ACCESS_TOKEN => $str,
					OAAccessToken::FIELD_CLIENT_ID => BaseClientIdProperty::CLIENT_ID_QUANTIMODO,
					OAAccessToken::FIELD_USER_ID => User::getAdminUser()->id,
					OAAccessToken::FIELD_SCOPE => self::SCOPE_WRITE_MEASUREMENTS." ".self::SCOPE_READ_MEASUREMENTS,
					OAAccessToken::FIELD_EXPIRES => Carbon::now()->addYear()->toDateTimeString(),
				]
			);
		} else {
			$t = QMAccessToken::find($str);
		}
		if(!$t){
			$db = Writable::db()->getConfig('database');
			$host = Writable::db()->getConfig('host');
			QMLog::error("No token in DB $db@$host matches one from request: " . $str, [
				"See Astral for Available Access Tokens" => OAAccessToken::getDataLabIndexUrl(),
			]);
			return null;
		}
		$t->validateScopeAndExpiration($required_scope);
		Memory::setAccessTokenIfNotSet($t);
		BaseClientIdProperty::setInMemory($t->clientId);
		return $t->getDBModel();
	}
	public function cacheByClientAndUser(): bool{
		Memory::setAccessTokenIfNotSet($this);  // Can't QMGlobals::setQmAccessToken because we overwrite patient's token with physician token
		$key = self::cacheKey($this->getClientId(), $this->getUserId());
		return QMFileCache::set($key, $this, now()->addSeconds(self::MEMCACHE_EXPIRATION_IN_SECONDS));
	}
	/**
	 * Generates an unique access token.
	 * Implementing classes may want to override this function to implement
	 * other access token generation schemes.
	 * @return bool|string An unique access token.
	 * An unique access token.
	 * @ingroup oauth2_section_4
	 */
	public static function generateRandomString(): string{
		if(function_exists('random_bytes')){
			try {
				$randomData = random_bytes(20);
			} catch (Exception $e) {
				le($e);
				throw new \LogicException();
			}
			if($randomData !== false && strlen($randomData) === 20){
				return bin2hex($randomData);
			}
		}
		if(function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM')){
			$randomData = mcrypt_create_iv(20, MCRYPT_DEV_URANDOM);
			if($randomData !== false && strlen($randomData) === 20){
				return bin2hex($randomData);
			}
		}
		if(function_exists('openssl_random_pseudo_bytes')){
			$randomData = openssl_random_pseudo_bytes(20);
			if($randomData !== false && strlen($randomData) === 20){
				return bin2hex($randomData);
			}
		}
		if(@file_exists('/dev/urandom')){ // Get 100 bytes of random data
			$randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
			if($randomData !== false && strlen($randomData) === 20){
				return bin2hex($randomData);
			}
		}
		// Last resort which you probably should just get rid of:
		$randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
		return substr(hash('sha512', $randomData), 0, 40);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @return mixed
	 */
	public static function findCachedByClientAndUser(string $clientId, int $userId): ?QMAccessToken{
		$row = QMFileCache::get(self::cacheKey($clientId, $userId));
		if(!$row){
			return null;
		}
		$row = json_decode(json_encode($row), true);
		$t = new QMAccessToken($row);
		Memory::setAccessTokenIfNotSet($t);
		return $t;
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @return string
	 */
	private static function cacheKey(string $clientId, int $userId): string{
		return self::TABLE . ':' . $clientId . ":" . $userId;
	}
	public static function getFromRequest(): ?self{
		$str = BaseAccessTokenProperty::fromRequest();
		if($str){
			return self::find($str);
		}
		return null;
	}
	public static function delete(string $clientId, int $userId): int{
		return self::expire($clientId, $userId);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @return int
	 */
	public static function expire(string $clientId, int $userId): int{
		$tokens = OAAccessToken::whereUserId($userId)->where(OAAccessToken::FIELD_CLIENT_ID, $clientId)->get();
		foreach($tokens as $t){
			$t->expire();
		}
		return $tokens->count();
	}
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|Builder|QMQB $qb
	 * @param string|null $expirationCutoff
	 */
	public static function addExpirationWhereClause(\Illuminate\Database\Eloquent\Builder|QMQB|Builder $qb,
		string $expirationCutoff = null){
		if(!$expirationCutoff){
			$expirationCutoff = date('Y-m-d H:i:s');
		}
        if($qb instanceof \Illuminate\Database\Eloquent\Builder){
            $qb = $qb->getQuery();
        }
		$qb->where(self::TABLE . "." . self::FIELD_EXPIRES, ">", $expirationCutoff);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @return QMAccessToken
	 */
	public static function getOrCreateToken(string $clientId, int $userId,
		string $scopes = 'readmeasurements writemeasurements', int $expiresInSeconds = null): QMAccessToken{
		if(QMClient::isLaravel($clientId)){le("We shouldn't be creating access token for laravel!");}
		if(!$clientId){
			$clientId = BaseClientIdProperty::fromMemory();
		}
		$clientId = BaseClientIdProperty::replaceWithQuantiModoIfAlias($clientId);
		BaseClientIdProperty::validateClientId($clientId);
		if($t = Memory::getQmAccessTokenObject($clientId)){
			return $t;
		}
		if($t = self::findCachedByClientAndUser($clientId, $userId)){
			return $t;
		}
		if($t = self::getFromDB($clientId, $userId, $scopes)){
			return $t;
		}
		return self::create($clientId, $userId, $scopes, $expiresInSeconds);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @param string $scopes
	 * @param int|null $expiresInSeconds
	 * @return QMAccessToken
	 * @throws ClientNotFoundException
	 */
	public static function create(string $clientId, int $userId, string $scopes = 'readmeasurements writemeasurements',
		int $expiresInSeconds = null): QMAccessToken{
		if(BaseClientIdProperty::isQuantiModoAlias($clientId) &&
			$clientId !== BaseClientIdProperty::CLIENT_ID_QUANTIMODO){
			QMLog::error("Why are we creating token for $clientId ?");
		}
		$expiresInSeconds = $expiresInSeconds ?: self::ACCESS_TOKEN_LIFETIME_IN_SECONDS;
		if($userId === UserIdProperty::USER_ID_MIKE && $expiresInSeconds > 2 * 86400){
			$expiresInSeconds = 2 * 86400;
		}
		OAAccessToken::unguard(true);
		$client = OAClient::findInMemoryOrDB($clientId);
		if(!$client){
			throw new ClientNotFoundException("Client not found: $clientId");
		}
		$l = $client->oa_access_tokens()->create([
			"access_token" => self::generateRandomString(),
			"client_id" => $clientId,
			"user_id" => $userId,
			"expires" => db_date(time() + $expiresInSeconds),
			"scope" => $scopes,
		]);
		OAAccessToken::unguard(false);
		$t = $l->getDBModel();
		if(BaseClientIdProperty::isPhysicianClientId($clientId)){
			$client = $t->getClient();
			UserNumberOfPatientsProperty::calculate($client->getQmUser());
		}
		//$t->cacheByClientAndUser();
		return $t;
	}
	/**
	 * @param string $tokenString
	 */
	public static function checkSessionToken(string $tokenString){
		//if (session_status() == PHP_SESSION_NONE) {  session_start(); }
		if(!self::getSessionToken()){
			QMLog::info("Could not get CSRF token from session", ["provided token" => $tokenString]);
			//Application::getInstance()->halt(400, 'Could not get CSRF token from session. Please alert help@curedao.org.');
		} elseif(self::getSessionToken() !== $tokenString){
			QMLog::errorOrInfoIfTesting("Invalid CSRF token", [
				"provided token" => $tokenString,
				"SessionToken" => self::getSessionToken(),
			]);
			QMSlim::getInstance()->halt(400, 'Invalid CSRF token. ' . QMStr::CONTACT_MIKE_FOR_HELP_STRING);
		}
	}
	/**
	 * @return string
	 */
	public static function createOrGetCsrfSessionToken(): string{
		// @todo don't use sessions to handle the CSRF token.
		//if (session_status() == PHP_SESSION_NONE) {  session_start(); }
		if(!isset($_SESSION['token'])){
			try {
				$_SESSION['token'] = sha1(serialize($_SERVER) . random_int(0, 0xffffffff));
			} catch (Exception $e) {
			}
		}
		return $_SESSION['token'];
	}
	/**
	 * @return string
	 */
	public static function getSessionToken(): ?string{
		if(isset($_SESSION['token'])){
			return $_SESSION['token'];
		}
		return null;
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @param string $scopes
	 * @return mixed|QMQB
	 */
	private static function getFromDB(string $clientId, int $userId, string $scopes): ?QMAccessToken{
		$qb = OAAccessToken::query()
			->where(self::FIELD_USER_ID, $userId)
			->where(self::FIELD_CLIENT_ID, $clientId)
			->where(self::FIELD_EXPIRES, ">", Carbon::now())
			->where(self::FIELD_SCOPE, $scopes);
		$row = $qb->first();
		if(!$row){
			return null;
		}
		$t = $row->getDBModel();
		$t->cacheByClientAndUser();
		return $t;
	}
	/**
	 * @param string $id
	 * @return QMAccessToken|null
	 */
	public static function find($id): ?self{
		$t = Memory::getQmAccessTokenObject();
		if($t && $t->accessToken === $id){
			return $t;
		}
		if(UserProviderTokenProperty::idTokenFromRequest() === $id){
			return null;
		}
		$l = OAAccessToken::findInMemoryOrDB($id);
		if(!$l){
			$message = self::INVALID_TOKEN_EXCEPTION;
			throw new UnauthorizedException($message, 401);
		}
		$t = $l->getDBModel();
		Memory::setAccessTokenIfNotSet($t);
		return $t;
	}
	public static function deleteCachedByClientAndUser(string $client_id, int $user_id){
		QMFileCache::delete(self::cacheKey($client_id, $user_id));
	}
	/**
	 * @return bool
	 */
	public function isExpired(): bool{
		return strtotime($this->expires) < time();
	}
	/**
	 * @return string
	 */
	public function getAccessTokenString(): string{
		return $this->accessToken;
	}
	/**
	 * @return bool
	 */
	public static function isDemo(): bool{
		return BaseAccessTokenProperty::fromRequest() === BaseAccessTokenProperty::DEMO_TOKEN;
	}
	/**
	 * @param QMUser $user
	 * @return array
	 */
	public static function getAllForUser(QMUser $user): array{
		$tokens = [];
		$qb = self::readonly()->where(self::FIELD_USER_ID, $user->getId());
		$fields = self::getColumns();
		$db = ReadonlyDB::db();
		foreach($fields as $field){
			$qb->columns[] = $db->raw('MAX(' . $field . ') as ' . $field);
		}
		self::addExpirationWhereClause($qb);
		$qb->groupBy([
			self::FIELD_CLIENT_ID,
			self::FIELD_SCOPE,
		]); // Don't get tons of duplicate clients
		$rows = $qb->getArray();
		if(count($rows) > 100){
			QMLog::error("$user has " . count($rows) . " valid access tokens!");
		}
		if(!$rows){
			return [];
		}
		foreach($rows as $row){
			$tokens[] = new QMAccessToken($row);
		}
		return $tokens;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	/**
	 * @param string|null $required_scope
	 * @throws AccessTokenExpiredException
	 * @throws InsufficientScopeException
	 */
	public function validate(string $required_scope = null): void {
		if($this->isExpired()){
			throw new AccessTokenExpiredException();
		}
		if($required_scope){
			OAuth2Server::checkScope($required_scope, $this->scope);
		}
	}
	/**
	 * @return int
	 */
	public function getExpiresInSeconds(): int{
		return $this->getExpirationTime() - time();
	}
	/**
	 * @return string
	 */
	public function getExpiresAt(): string{
		return $this->expires;
	}
	/**
	 * @return int
	 */
	public function getExpirationTime(): int{
		return strtotime($this->getExpiresAt());
	}
	/**
	 * @return AppSettings
	 */
	public function getAppSettings(): AppSettings{
		return $this->getClient()->getAppSettings();
	}
	/**
	 * @return QMSlim|BaseModel
	 */
	public function getApplication(): QMSlim{
		return $this->getAppSettings()->l();
	}
	public function getClient(): QMClient{
		$c = QMClient::find($this->clientId);
		if(!$c){
			throw new NotFoundException("Could not find client with id: $this->clientId");
		}
		$c->scope = $this->scope;
		return $c;
	}
	/**
	 * @return bool
	 */
	public function isStudy(): bool{
		return BaseClientIdProperty::isStudy($this->clientId);
	}
	/**
	 * @return bool
	 */
	public function isPhysician(): bool{
		return BaseClientIdProperty::isPhysicianClientId($this->clientId);
	}
	public function getTitleAttribute(): string{
		return OAAccessToken::getClassNameTitle();
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		return $this->accessToken;
	}
}
