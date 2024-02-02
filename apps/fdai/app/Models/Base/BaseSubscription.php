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
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseSubscription
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $stripe_id
 * @property string $stripe_plan
 * @property int $quantity
 * @property Carbon $trial_ends_at
 * @property Carbon $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property string $client_id
 * @property User $user
 * @package App\Models\Base
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSubscription onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereStripePlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereTrialEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSubscription whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSubscription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSubscription withoutTrashed()
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 */
abstract class BaseSubscription extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ENDS_AT = 'ends_at';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_QUANTITY = 'quantity';
	public const FIELD_STRIPE_ID = 'stripe_id';
	public const FIELD_STRIPE_PLAN = 'stripe_plan';
	public const FIELD_TRIAL_ENDS_AT = 'trial_ends_at';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	protected $table = 'subscriptions';
	public const TABLE = 'subscriptions';
	protected $casts = [
        self::FIELD_TRIAL_ENDS_AT => 'datetime',
        self::FIELD_ENDS_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_USER_ID => 'int',
		self::FIELD_QUANTITY => 'int',	];
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_NAME => 'required|max:255',
		self::FIELD_STRIPE_ID => 'required|max:255',
		self::FIELD_STRIPE_PLAN => 'required|max:255',
		self::FIELD_QUANTITY => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_TRIAL_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_CLIENT_ID => 'nullable|max:255',
	];
	public function user(): BelongsTo{
		return $this->belongsTo(User::class, Subscription::FIELD_USER_ID, User::FIELD_ID, Subscription::FIELD_USER_ID);
	}
}
