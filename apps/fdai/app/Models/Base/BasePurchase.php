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
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BasePurchase
 * @property int $id
 * @property int $subscriber_user_id
 * @property int $referrer_user_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property string $subscription_provider
 * @property string $last_four
 * @property string $product_id
 * @property string $subscription_provider_transaction_id
 * @property string $coupon
 * @property string $client_id
 * @property Carbon $refunded_at
 * @property Carbon $deleted_at
 * @property OAClient $oa_client
 * @property \App\Models\User $subscriber_user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePurchase onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereCoupon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereLastFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereReferrerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereSubscriberUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereSubscriptionProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase
 *     whereSubscriptionProviderTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePurchase withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePurchase withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BasePurchase extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COUPON = 'coupon';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_LAST_FOUR = 'last_four';
	public const FIELD_PRODUCT_ID = 'product_id';
	public const FIELD_REFERRER_USER_ID = 'referrer_user_id';
	public const FIELD_REFUNDED_AT = 'refunded_at';
	public const FIELD_SUBSCRIBER_USER_ID = 'subscriber_user_id';
	public const FIELD_SUBSCRIPTION_PROVIDER = 'subscription_provider';
	public const FIELD_SUBSCRIPTION_PROVIDER_TRANSACTION_ID = 'subscription_provider_transaction_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'purchases';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_REFUNDED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COUPON => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_LAST_FOUR => 'string',
		self::FIELD_PRODUCT_ID => 'string',
		self::FIELD_REFERRER_USER_ID => 'int',
		self::FIELD_SUBSCRIBER_USER_ID => 'int',
		self::FIELD_SUBSCRIPTION_PROVIDER => 'string',
		self::FIELD_SUBSCRIPTION_PROVIDER_TRANSACTION_ID => 'string',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_COUPON => 'nullable|max:100',
		self::FIELD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_PRODUCT_ID => 'required|max:100',
		self::FIELD_REFERRER_USER_ID => 'nullable|numeric|min:0',
		self::FIELD_REFUNDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_SUBSCRIBER_USER_ID => 'required|numeric|min:0',
		self::FIELD_SUBSCRIPTION_PROVIDER => 'required',
		self::FIELD_SUBSCRIPTION_PROVIDER_TRANSACTION_ID => 'nullable|max:100',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_SUBSCRIBER_USER_ID => '',
		self::FIELD_REFERRER_USER_ID => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_SUBSCRIPTION_PROVIDER => '',
		self::FIELD_LAST_FOUR => '',
		self::FIELD_PRODUCT_ID => '',
		self::FIELD_SUBSCRIPTION_PROVIDER_TRANSACTION_ID => '',
		self::FIELD_COUPON => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_REFUNDED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Purchase::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Purchase::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'subscriber_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'subscriber_user_id',
			'foreignKey' => Purchase::FIELD_SUBSCRIBER_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'subscriber_user_id',
			'ownerKey' => Purchase::FIELD_SUBSCRIBER_USER_ID,
			'methodName' => 'subscriber_user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Purchase::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Purchase::FIELD_CLIENT_ID);
	}
	public function subscriber_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Purchase::FIELD_SUBSCRIBER_USER_ID, \App\Models\User::FIELD_ID,
			Purchase::FIELD_SUBSCRIBER_USER_ID);
	}
}
