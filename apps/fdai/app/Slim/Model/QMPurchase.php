<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model;
class QMPurchase extends DBModel {
	public const FIELD_SUBSCRIBER_USER_ID = 'subscriber_user_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_REFUNDED_AT = 'refunded_at';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'purchases';
	public function __construct(){
	}
	/**
	 * @param int $userId
	 * @return mixed
	 */
	public static function getLastActivePurchase(int $userId){
		return self::readonly()->where(self::FIELD_SUBSCRIBER_USER_ID, $userId)->whereNull(self::FIELD_REFUNDED_AT)
			->orderBy(self::FIELD_UPDATED_AT, 'DESC')->first();
	}
}
