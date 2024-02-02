<?php namespace App\Models;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
/**
 * App\Models\BillingPlan
 * @property int $id
 * @property string $name
 * @property int $price
 * @property int $request_limit
 * @property float|null $extra_call
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|BillingPlan newModelQuery()
 * @method static Builder|BillingPlan newQuery()
 * @method static Builder|BillingPlan query()
 * @method static Builder|BillingPlan whereClientId($value)
 * @method static Builder|BillingPlan whereCreatedAt($value)
 * @method static Builder|BillingPlan whereDeletedAt($value)
 * @method static Builder|BillingPlan whereDescription($value)
 * @method static Builder|BillingPlan whereExtraCall($value)
 * @method static Builder|BillingPlan whereId($value)
 * @method static Builder|BillingPlan whereName($value)
 * @method static Builder|BillingPlan wherePrice($value)
 * @method static Builder|BillingPlan whereRequestLimit($value)
 * @method static Builder|BillingPlan whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class BillingPlan extends BaseModel {
    const TABLE = false;
    public $table = self::TABLE;
	const CLASS_CATEGORY = "Billing";
	const FIELD_ID = 'id';
	protected $guarded = ['*'];
	public const PLANS = [
		0 => [
			'id' => 1,
			'name' => 'Developer Plan',
			'price' => 0,
			'request_limit' => 15000,
			'extra_call' => null,
			'description' => '["<b>2,000<\\/b> API Calls \\/ Month","Ideal for development","Forum Support"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
		1 => [
			'id' => 11,
			'name' => 'Plan 1',
			'price' => 1900,
			'request_limit' => 20000,
			'extra_call' => 0.0006,
			'description' => '["<b>20,000<\\/b> API calls \\/ Month","Forum Support","$ 0.0006 extra call"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
		2 => [
			'id' => 21,
			'name' => 'Plan 2',
			'price' => 9900,
			'request_limit' => 400000,
			'extra_call' => 0.0005,
			'description' => '["<b>400,000<\\/b> API calls \\/ Month","Forum \\/ Email \\/ Phone Support","$ 0.0005 extra call"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
		3 => [
			'id' => 31,
			'name' => 'Plan 3',
			'price' => 19900,
			'request_limit' => 1000000,
			'extra_call' => 0.0004,
			'description' => '["<b>1,000,000<\\/b> API calls \\/ Month","Forum \\/ Email \\/ Phone Support","$ 0.0004 extra call"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
		4 => [
			'id' => 41,
			'name' => 'Plan 4',
			'price' => 49900,
			'request_limit' => 4000000,
			'extra_call' => 0.0002,
			'description' => '["<b>4,000,000<\\/b> API calls \\/ Month","Forum \\/ Email \\/ Phone Support","$ 0.0002 extra call"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
		5 => [
			'id' => 51,
			'name' => 'Plan 5',
			'price' => 99900,
			'request_limit' => 10000000,
			'extra_call' => 0.0001,
			'description' => '["<b>10,000,000<\\/b> API calls \\/ Month","Forum \\/ Email \\/ Phone Support","$ 0.0001 extra call"]',
			'created_at' => '2000-01-01 00:00:00',
			'updated_at' => '2018-11-19 04:05:30',
			'deleted_at' => null,
			'client_id' => null,
		],
	];
	/**
	 * @param mixed $id
	 * @param array $columns
	 * @return BillingPlan
	 */
	public static function find($id, $columns = []): BillingPlan{
		if(!$id){
			le("No id provided to " . __METHOD__);
		}
		$arr = Arr::first(self::PLANS, function($plan) use ($id){
			return $plan['id'] === $id;
		});
		if(!$arr){
			le("Could not find plan with id: $id");
		}
		self::unguard(true);
		$m = new BillingPlan($arr);
		self::unguard(false);
		return $m;
	}
	/**
	 * @param array|mixed|string[] $columns
	 * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection|self[]
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public static function all($columns = ['*']){
		$plans = [];
		self::unguard(true);
		foreach(self::PLANS as $arr){
			$plans[] = new self($arr);
		}
		self::unguard(false);
		return collect($plans);
	}
	/**
	 * @param array $columns
	 * @return BillingPlan[]|Collection
	 */
	public static function get($columns = []){
		return self::all();
	}
	/**
	 * @return BillingPlan
	 */
	public static function free(): BillingPlan{
		return Arr::first(self::get(), function($plan){
			/** @var BillingPlan $plan */
			return $plan->price === 0;
		});
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
}/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */


