<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Auth;
use App\Models\OAAccessToken;
use App\Models\OARefreshToken;
use App\Slim\Model\DBModel;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use Carbon\Carbon;
/**
 * @mixin OARefreshToken
 */
class QMRefreshToken extends DBModel {
	public const FIELD_REFRESH_TOKEN = 'refresh_token';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_EXPIRES = 'expires';
	public const FIELD_SCOPE = 'scope';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'oa_refresh_tokens';
	public $refreshToken;
	public $clientId;
	public $expires;
	public $scope;
	public $userId;
	public const LARAVEL_CLASS = OARefreshToken::class;
	/**
	 * QMRefreshToken constructor.
	 * @param null $arrayOrObject
	 */
	public function __construct($arrayOrObject = null){
		$this->populateFieldsByArrayOrObject($arrayOrObject);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @param string $scopes
	 * @param int $expiresInSeconds
	 * @return QMRefreshToken
	 */
	public static function getOrCreateRefreshToken(string $clientId, int $userId,
		string $scopes = 'readmeasurements writemeasurements', int $expiresInSeconds = null): QMRefreshToken{
		if($t = Memory::getRefreshToken($clientId)){
			return $t;
		}
		// Don't do this, it doesn't reflect changes if .env is updated
//		if($t = self::getFromFileCache($clientId, $userId)){
//			return $t;
//		}
		$row = OARefreshToken::whereDate(self::FIELD_EXPIRES, '>', Carbon::now())
			->where('user_id', $userId)
			->where('client_id', $clientId)
			->where('scope', $scopes)
			->first();
		if($row){
			$t = new QMRefreshToken($row);
			self::saveToFileCache($t);
			return $t;
		}
		return self::createAndStoreRefreshTokenArray($clientId, $userId, $scopes, $expiresInSeconds);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @param string $scopes
	 * @param int $expiresInSeconds
	 * @return QMRefreshToken
	 */
	public static function createAndStoreRefreshTokenArray(string $clientId, int $userId,
		string $scopes = 'readmeasurements writemeasurements', int $expiresInSeconds = null): QMRefreshToken{
		$expiresInSeconds = $expiresInSeconds ?: QMAccessToken::ACCESS_TOKEN_LIFETIME_IN_SECONDS;
		$refreshTokenArray = [
			"refresh_token" => QMAccessToken::generateRandomString(),
			"client_id" => $clientId,
			"user_id" => $userId,
			"expires" => date('Y-m-d H:i:s', time() + $expiresInSeconds),
			"scope" => $scopes,
		];
		self::writable()->insert($refreshTokenArray);
		$t = new QMRefreshToken($refreshTokenArray);
		//self::saveToFileCache($t);
		return $t;
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @return mixed
	 * @deprecated This causes too many problems
	 */
	public static function getFromFileCache(string $clientId, int $userId): ?QMRefreshToken{
		// This causes problems and does
		// $row = QMFileCache::get(self::getMemcachedKey($clientId, $userId));
		if(!$row){
			return null;
		}
		$t = new QMRefreshToken($row);
		Memory::setRefreshToken($t);
		return $t;
	}
	/**
	 * @param $clientId
	 * @param $userId
	 * @return string
	 */
	private static function getMemcachedKey(string $clientId, int $userId){
		return self::TABLE . ':' . $clientId . ":" . $userId;
	}
	/**
	 * @param $t
	 * @return void
	 */
	private static function saveToFileCache(QMRefreshToken $t): void{
		Memory::setRefreshToken($t);
		$key = self::getMemcachedKey($t->getClientId(), $t->getUserId());
		QMFileCache::set($key, $t, time() + QMAccessToken::MEMCACHE_EXPIRATION_IN_SECONDS);
	}
	/**
	 * @param string $clientId
	 * @param int $userId
	 * @return int
	 */
	public static function delete(string $clientId, int $userId){
		return self::writable()
			->where(OAAccessToken::FIELD_USER_ID, $userId)
			->where(OAAccessToken::FIELD_CLIENT_ID, $clientId)
			->delete();
	}
	/**
	 * @return mixed
	 */
	public function getRefreshToken(): string{
		return $this->refreshToken;
	}
	/**
	 * @return mixed
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
	public function getTitleAttribute(): string{
		return $this->refreshToken;
	}
}
