<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
namespace App\DataSources;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\ProtectedDatabaseException;
use App\Exceptions\TooBigForCacheException;
use App\Logging\QMLog;
use App\Models\Connection;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Traits\HasMemory;
use App\Traits\LoggerTrait;
use App\Types\QMArr;
use App\Utils\AppMode;
use App\Utils\Env;
use InvalidArgumentException;
use LogicException;
use OAuth\OAuth2\Token\StdOAuth2Token;
use StdClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;
/** Class Credential
 * @package App\Slim\Model
 */
class CredentialStorage {
    use LoggerTrait, HasMemory;
    public const TABLE = 'credentials';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_connector_id = 'connector_id';
    public const FIELD_attr_value = 'attr_value';
    public int $userId;
    public int $connectorId;
    private $credentials;
    private Connection $connection;
    /**
     * Credential constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection){
	    $this->userId = $connection->user_id;
	    $this->connectorId = $connection->connector_id;
	    $this->connection = $connection;
        $this->addToMemory();
    }

    public static function getEncryptionKey()
    {
		return Env::getRequired('APP_KEY');
    }

    public function getAttribute(string $key){
		return $this->$key ?? null;
	}
	public static function getUniqueIndexColumns(): array{return ['userId', 'connectorId'];}
    /**
     * @return stdClass[]
     */
    public static function getAll(): array {
        $db = self::readonly();
        $qb = $db->select(
            self::FIELD_connector_id.' as connectorId',
            self::FIELD_USER_ID.' as userId',
            'attr_key as key',
            'attr_value as encryptedValue',
            $db->raw("AES_DECRYPT(attr_value, '".self::getEncryptionKey()."') as value")
        );
        $qb->columns[] = 'created_at as createdAt';
        $qb->columns[] = 'updated_at as updatedAt';
        $qb->whereNull(CredentialStorage::FIELD_DELETED_AT);
        return $qb->getArray();
    }
    /**
     * Stores raw credentials
     * @param array $originalUnencryptedCredentialsResponse associative array containing credentials as key=>value
     * @param null $endOfLife
     * @param array|null $allowedFields
     */
    public function store(array $originalUnencryptedCredentialsResponse, $endOfLife = null,
                          array $allowedFields = null): void{
	    $connection = $this->getConnection();
	    if(!$this->userId){
            $connection->logInfo("Cannot store credentials because no user id");
            return;
        }
        $formattedCredentials = $this->formatCredentials($originalUnencryptedCredentialsResponse, $allowedFields);
		$connection->credentials = $formattedCredentials;
	    $connection->logInfo("Storing credentials for user ".$this->userId);
		$connection->save();
        $this->saveTestCredentials($originalUnencryptedCredentialsResponse);
        try {
            $this->insert($formattedCredentials, $endOfLife);
        } catch (\Throwable $e) {
            // Deprecated because we store credentials in the connection table
        }
    }
    /**
     * @param object|array $credentialsToSave
     * @param array|null $allowedFields
     * @return array
     */
    private function formatCredentials(object|array $credentialsToSave, array $allowedFields = null): array{
        if(is_object($credentialsToSave)){
            $class = get_class($credentialsToSave);
            if(stripos($class, 'token')){
                $credentialsToSave = ['token' => $credentialsToSave];
            }else{
                $credentialsToSave = json_decode(json_encode($credentialsToSave), true);
                if(empty($credentialsToSave)){   // Probably was an oauth token instead of POST body credentials array
                    throw new BadRequestHttpException("Please provide credentials as array.  OAuth tokens should be an array with a token key");
                }
            }
        }
        unset($credentialsToSave['variables']); // for tests
        foreach($credentialsToSave as $key => $value){
            if(is_array($value)){
                unset($credentialsToSave[$key]);
                $this->getConnection()->logError("$key is not a string!");
            }
            if($allowedFields && !in_array($key, $allowedFields, true)){
                unset($credentialsToSave[$key]);
            }
        }
        return $credentialsToSave;
    }
    /**
     * @param string $reason
     * @return int
     */
    public function hardDeleteCredentials(string $reason): int{
        $this->deleteFromMemory();
        return CredentialStorage::writable()
            ->where('user_id', $this->userId)
            ->where('connector_id', $this->connectorId)
            ->hardDelete($reason, true);
    }
    /**
     * @param string $reason
     */
    public function softDeleteCredentials(string $reason): void{
	    $connection = $this->getConnection();   
	    $connection->logDebug('Removing credentials, if any exist');
	    $connection->credentials = null;
        $this->deleteFromMemory();
        $values = ['message' => $reason];
        CredentialStorage::writable()
            ->where('user_id', $this->userId)
            ->where('connector_id', $this->connectorId)
            ->softDelete($values, $reason);
    }
    /**
     * @return array
     */
    public function get(): array {
        if(isset($this->credentials)){
            return $this->credentials;
        }
		$connection = $this->getConnection();
		$credentials = $connection->credentials;
		if($credentials){
			if(!is_array($credentials)){
				$connection->logError("Credentials are not an array: ".QMLog::print_r($credentials));
				return QMArr::toArray($credentials);
			}
			return $credentials;
		}
        $arr = $this->fetch();
        if(!$arr){
            debugger("No credentials found for user ".$this->userId." and connector ".$this->connectorId);
            return [];
        }
        return $this->credentials = $arr;
    }
    private function encryptAndStoreTestCredentialsResponseFromFirebase(): void{
        if(AppMode::isTestingOrStaging() && !AppMode::isStagingUnitTesting()){
            $unSerializedTestCredentialsOriginalUnencryptedResponse = $this->getTestCredentials();
            $this->store($unSerializedTestCredentialsOriginalUnencryptedResponse);
        }
    }
	/**
	 * Returns an associative array containing credentials as key=>value
	 * @return array|null
	 */
    private function fetch(): ?array{
		if(!$this->userId){le("No userId provided to setCredentialsFromDatabase!");}
        $this->encryptAndStoreTestCredentialsResponseFromFirebase();
		$credentials = $this->getConnection()->credentials;
		if($credentials){
			return $credentials;
		}
        //if ($this->getCredentialsFromMemcached()) {return $this->credentials;}
        $this->getConnection()->logDebug("Getting credentials from database");
        $qb = self::readonly()
            ->select('attr_key as attrKey', self::readonly()->raw("AES_DECRYPT(attr_value, '".
                self::ENCRYPTION_KEY."') as attrValue"))
            ->where('user_id', $this->getUserId())
            ->where('connector_id', $this->connectorId);
        $qb->columns[] = 'created_at as createdAt';
        $qb->columns[] = 'updated_at as updatedAt';
        /** @var StdClass[] $rows */
        $rows = $qb->getArray();
        $arr = $this->validateAndMergeCredentialRowsFromDb($rows);
        if(!$arr){
            $this->logError("No credentials found for user ".$this->getConnection()->getUser());
	        $this->credentials = false;
			return null;
		}
        $this->setCredentials($arr);
        return $this->credentials;
    }
    /**
     * @return string
     */
    private function getTestCredentialFbKey(): string {
        return 'test_connector_credentials/'.$this->connectorId;
    }
    /**
     * Returns true if credentials for this connector exist, false if not
     * @return \App\Slim\Model\DBModel|array|stdClass|string|null
     * @throws InvalidArgumentException
     */
    public function hasCredentials() {
	    $connection = $this->getConnection();
	    $connection->logDebug('Checking existence of credentials');
		$credentials = $connection->credentials;
		if($credentials){
			return $credentials;
		}
        $entry = self::readonly()
            ->where('user_id', $this->userId)
            ->where('connector_id', $this->connectorId)
            ->first();
        return $entry;
    }
    /**
     * @param int $userId
     * @param int $connectorId
     * @param string $status
     * @param string|null $message
     * @return int
     */
    public static function updateStatus(int $userId, int $connectorId, string $status, string $message = null): int{
        return Writable::db()->table('credentials')
            ->where('user_id', $userId)
            ->where('connector_id', $connectorId)
            ->update([
                'status'  => $status,
                'message' => $message
            ]);
    }
    /**
     * @return bool
     */
    private function isMikeOrTestUser(): bool{
        return in_array($this->userId, [
            UserIdProperty::USER_ID_DEMO,
            UserIdProperty::USER_ID_MIKE,
            18535
        ], true);
    }
    /**
     * @param array $unencrypted
     */
    private function saveTestCredentials(array $unencrypted): void {
        if(!$this->isMikeOrTestUser()){return;}
        //if(AppMode::isUnitOrStagingUnitTest()){return false;} // We have to store during tests so that refreshed tokens are stored as well
        //if(!AppMode::isApiRequest()){return false;} // We have to store during tests so that refreshed tokens are stored as well
        try {
            $result =
                FirebaseGlobalPermanent::set($this->getTestCredentialFbKey(),
                    serialize($unencrypted));
        } catch (TooBigForCacheException $e) {
            /** @var LogicException $e */
            throw $e;
        }
        if(!$result){le("Could not save test credentials!  Maybe caching is disabled?");}
    }
    /**
     * @return Connection
     */
    public function getConnection(): Connection{
        return $this->connection;
    }

    /**
     * @param array $rows
     * @return array
     */
    private function validateAndMergeCredentialRowsFromDb(array $rows): array {
        $credentials = [];
        foreach($rows as $row){
            $value = $row->attrValue;
            if(str_contains($value, ':i')){
                $this->getConnection()->logError("Credentials not properly de-serialized! attrValue is $value",
                    ['row'  => $row]);
            }
            // encrypted empty string
            if(!$value && $value !== ""){ // Sometimes we store an empty string for zip code
                $this->getConnection()->logError("Credentials not properly de-serialized! attrValue is ".
                    "empty after deserialization but was $value",
                    ['row'  => $row]);
                le("credentialsResult->attrValue is null after de-encryption");
            }
            $credentials[$row->attrKey] = $value;
        }
        return $this->credentials = $credentials;
    }
    /**
     * @return mixed|null
     */
    public function getTestCredentials(): mixed{
        $serializedUnencrypted = FirebaseGlobalPermanent::get($this->getTestCredentialFbKey());
        if($serializedUnencrypted && is_string($serializedUnencrypted)){
            /** @noinspection UnserializeExploitsInspection */
            return unserialize($serializedUnencrypted);
        } else {
            \App\Logging\ConsoleLog::info("Trying to get test credentials again...");
            sleep(1);
            $serializedUnencrypted = FirebaseGlobalPermanent::get($this->getTestCredentialFbKey());
            if($serializedUnencrypted && is_string($serializedUnencrypted)){
                /** @noinspection UnserializeExploitsInspection */
                return unserialize($serializedUnencrypted);
            }
        }
        throw new InvalidTestCredentialsException($this->getConnection());
    }
    /**
     * @return void
     */
    public function deleteTestCredentials(): void{
        FirebaseGlobalPermanent::delete($this->getTestCredentialFbKey());
        throw new InvalidTestCredentialsException($this->getConnection());
    }
    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void{
        $this->userId = $userId;
    }
    /**
     * @return QMQB
     */
    public static function readonly(): QMQB{
        return ReadonlyDB::getBuilderByTable(self::TABLE);
    }
    /**
     * @return QMQB
     */
    public static function writable(): QMQB{
        return Writable::getBuilderByTable(self::TABLE);
    }
    /**
     * @param $formattedCredentials
     * @param $endOfLife
     */
    private function insert($formattedCredentials, $endOfLife): void{
	    $connection = $this->getConnection();
		$connection->credentials = $formattedCredentials;
		$connection->save();
		return;
		
        $db = Writable::db();
        $query = 'INSERT INTO `credentials` (user_id, connector_id, attr_key, attr_value, expires_at, created_at, updated_at) VALUES(:userId, :connectorId, :key, AES_ENCRYPT(:value, :encryptionKey), :expires_at, NOW(), NOW()) ON DUPLICATE KEY UPDATE attr_value = VALUES(attr_value), expires_at = VALUES(expires_at), updated_at = NOW()';
        try {
            $db->beginTransaction();
        } catch (Throwable $e) {
	        le("Could not start credentials transaction: ".$e->getMessage());
        }
	    $connection->getCredentialStorageFromMemory()->setCredentials($formattedCredentials);
        /** @var StdOAuth2Token $value */
        foreach($formattedCredentials as $key => $value){
            if(is_object($value) || is_array($value)){
                $valueString = serialize($value);
                if(empty($valueString)){le("Credential value is empty after serialization!");}
            } else {
                $valueString = $value;
                if(empty($valueString)){le("Credential value is empty!");}
            }
            $bytes = strlen($valueString);
            if($bytes > 2999){le("Credentials too big.  Increase attr_value max or something");}
            $expiresAt = null;
            if($endOfLife && $endOfLife > 0){
                try {
                    $expiresAt = db_date($endOfLife);
                } catch (InvalidTimestampException $e) {
                    le($e);
                }
            }
            $db->statement($query, [
                'userId'        => $this->getUserId(),
                'connectorId'   => $this->getConnectorId(),
                'key'           => $key,
                'value'         => $valueString,
                'encryptionKey' => self::ENCRYPTION_KEY,
                'expires_at'    => $expiresAt,
            ]);
        }
	    try {
		    $db->commit();
	    } catch (Throwable $e) {
			le("Could not commit credentials transaction because ".$e->getMessage());
	    }
    }
    /**
     * @throws ProtectedDatabaseException
     */
    public static function truncate(): void{
        self::writable()->truncate();
    }
	/**
	 * @return int|null
	 */
    public function getUserId(): ?int{
        return $this->userId;
    }
    /**
     * @return int
     */
    public function getConnectorId(): int{
        return $this->connectorId;
    }

    /**
     * @param array $credentials
     * @return CredentialStorage
     */
    public function setCredentials(array $credentials): CredentialStorage
    {
        $this->credentials = $credentials;
        return $this;
    }

    public function __toString()
    {
        return $this->getConnection()->getNameOrTitle()." credentials";
    }

    public function getId()
    {
        return $this->getUserId().'-'.$this->getConnectorId();
    }
}
