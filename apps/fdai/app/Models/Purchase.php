<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BasePurchase;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Purchase
 * @property int $id
 * @property int $subscriber_user_id
 * @property int|null $referrer_user_id
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property string $subscription_provider
 * @property string|null $last_four
 * @property string $product_id
 * @property string|null $subscription_provider_transaction_id
 * @property string|null $coupon
 * @property string|null $client_id
 * @property Carbon|null $refunded_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Purchase newModelQuery()
 * @method static Builder|Purchase newQuery()
 * @method static Builder|Purchase query()
 * @method static Builder|Purchase whereClientId($value)
 * @method static Builder|Purchase whereCoupon($value)
 * @method static Builder|Purchase whereCreatedAt($value)
 * @method static Builder|Purchase whereDeletedAt($value)
 * @method static Builder|Purchase whereId($value)
 * @method static Builder|Purchase whereLastFour($value)
 * @method static Builder|Purchase whereProductId($value)
 * @method static Builder|Purchase whereReferrerUserId($value)
 * @method static Builder|Purchase whereRefundedAt($value)
 * @method static Builder|Purchase whereSubscriberUserId($value)
 * @method static Builder|Purchase whereSubscriptionProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Purchase
 *     whereSubscriptionProviderTransactionId($value)
 * @method static Builder|Purchase whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read User $subscriber_user
 * @property-read OAClient|null $client
 */
class Purchase extends BasePurchase {
    use HasFactory;

	use HasUser;
	public const CLASS_DESCRIPTION = "Purchase of a subscription. ";
	const CLASS_CATEGORY = BillingPlan::CLASS_CATEGORY;
	public const FONT_AWESOME = FontAwesome::MONEY_BILL_ALT;
	protected array $rules = [
		self::FIELD_SUBSCRIBER_USER_ID => 'required|numeric|min:1',
		self::FIELD_REFERRER_USER_ID => 'nullable|numeric|min:1',
		self::FIELD_SUBSCRIPTION_PROVIDER => 'required',
		self::FIELD_LAST_FOUR => 'nullable|max:4',
		self::FIELD_PRODUCT_ID => 'required|max:100',
		self::FIELD_SUBSCRIPTION_PROVIDER_TRANSACTION_ID => 'nullable|max:100',
		self::FIELD_COUPON => 'nullable|max:100',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_REFUNDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
	];

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_SUBSCRIBER_USER_ID];
	}
}
