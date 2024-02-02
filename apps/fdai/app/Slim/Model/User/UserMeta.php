<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\Exceptions\DeletedUserException;
use App\Models\WpUsermetum;
use App\Slim\Model\DBModel;
use App\Types\QMStr;
/**
 * @mixin WpUsermetum
 */
class UserMeta extends DBModel {
	public const TABLE = 'wp_usermeta';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_META_VALUE = 'meta_value';
	public const FIELD_META_KEY = 'meta_key';
	public const KEY_unsubscribe_reason = 'unsubscribe_reason';
	public const KEY_facebook_id = 'facebook_id';
	public const KEY_wp_capabilities = 'wp_capabilities';
	public const KEY_description = 'description';
	public const KEY_done_adding_symptoms = 'done_adding_symptoms';
	public const KEY_done_adding_emotions = 'done_adding_emotions';
	public const KEY_done_adding_foods = 'done_adding_foods';
	public const KEY_done_adding_treatments = 'done_adding_treatments';
	public const KEY_ip_address = 'ip_address';
	public const KEY_geo_location = 'geo_location';
	public const LAST_REPORT = 'last_report';
	public $meta_value;
	public $user_id;
	public $meta_key;
	//private $user;  // Just get global user instead, otherwise there will be duplicates and we can use the user as a storage object
	/**
	 * UserMeta constructor.
	 * @param $row
	 */
	public function __construct($row){
		$this->meta_value = $row->meta_value;
		$this->user_id = $row->user_id;
		$this->meta_key = $row->meta_key;
	}
	/**
	 * @param int $userId
	 * @param string $metaKey
	 * @param $value
	 * @return bool
	 */
	public static function update(int $userId, string $metaKey, $value): bool{
		self::writable()->where(self::FIELD_USER_ID, $userId)->where(self::FIELD_META_KEY, $metaKey)->delete();
		$result = self::writable()->insert([
			self::FIELD_USER_ID => $userId,
			self::FIELD_META_KEY => $metaKey,
			self::FIELD_META_VALUE => $value,
		]);
		return $result;
	}
	/**
	 * @param int $userId
	 * @param string $metaKey
	 * @return mixed
	 */
	public static function getUserMeta(int $userId, string $metaKey){
		$row = WpUsermetum::whereKey($metaKey)->where(WpUsermetum::FIELD_USER_ID, $userId)->first();
		if($row){
			return QMStr::unserializeIfNecessary($row->meta_value);
		}
		return null;
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getUser()->getLoginNameAndIdString() . ": ";
	}
	/**
	 * @return QMUser
	 */
	private function getUser(): QMUser{
		try {
			return QMUser::find($this->user_id);
		} catch (DeletedUserException $e) {
			le($e . "$this", $this);
			throw new \LogicException();
		}
	}
	/**
	 * @param int $userId
	 * @return array
	 */
	public static function getAllForUser(int $userId): array{
		$userMeta = [];
		/** @var UserMeta[] $allMetaData */
		$allMetaData = self::readonly()->where(self::FIELD_USER_ID, $userId)->getArray();
		foreach($allMetaData as $metaDatum){
			$value = $metaDatum->meta_value;
			$value = QMStr::unserializeIfNecessary($value);
			$value = QMStr::decodeIfJson($value);
			$userMeta[$metaDatum->meta_key] = $value;
		}
		return $userMeta;
	}
	/**
	 * @param string $key
	 * @param $value
	 * @return int
	 */
	public static function getUserIdByKeyValue(string $key, $value): ?int{
		$row = WpUsermetum::whereKey($key)->where(WpUsermetum::FIELD_META_VALUE, $value)->first();
		if($row){
			return $row->user_id;
		}
		return null;
	}
}
