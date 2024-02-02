<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseSubscription;
use App\Traits\HasModel\HasUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Subscription
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $stripe_id
 * @property string $stripe_plan
 * @property int $quantity
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereEndsAt($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription whereName($value)
 * @method static Builder|Subscription whereQuantity($value)
 * @method static Builder|Subscription whereStripeId($value)
 * @method static Builder|Subscription whereStripePlan($value)
 * @method static Builder|Subscription whereTrialEndsAt($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereUserId($value)
 * @mixin \Eloquent
 * @property-read User $user
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|Subscription whereClientId($value)
 * @method static Builder|Subscription whereDeletedAt($value)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class Subscription extends BaseSubscription {
    use HasFactory;

	use HasUser;
	public const CLASS_DESCRIPTION = "A QuantiModo Plus subscription. ";
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_NAME => 'required|max:255',
		self::FIELD_STRIPE_ID => 'required|max:255',
		self::FIELD_STRIPE_PLAN => 'required|max:255',
		self::FIELD_QUANTITY => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_TRIAL_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_ENDS_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
	];

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
