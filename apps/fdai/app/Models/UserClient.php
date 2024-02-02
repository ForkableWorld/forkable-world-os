<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseUserClient;
use App\Properties\User\UserIdProperty;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use App\UI\QMColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\UserClient
 * @property int $id
 * @property string|null $client_id
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $earliest_measurement_at Earliest measurement time for this variable and
 *     client
 * @property Carbon|null $latest_measurement_at Earliest measurement time for this variable and
 *     client
 * @property int|null $number_of_measurements
 * @property Carbon $updated_at
 * @property int $user_id
 * @method static Builder|UserClient newModelQuery()
 * @method static Builder|UserClient newQuery()
 * @method static Builder|UserClient query()
 * @method static Builder|UserClient whereClientId($value)
 * @method static Builder|UserClient whereCreatedAt($value)
 * @method static Builder|UserClient whereDeletedAt($value)
 * @method static Builder|UserClient whereEarliestMeasurementAt($value)
 * @method static Builder|UserClient whereId($value)
 * @method static Builder|UserClient whereLatestMeasurementAt($value)
 * @method static Builder|UserClient whereNumberOfMeasurements($value)
 * @method static Builder|UserClient whereUpdatedAt($value)
 * @method static Builder|UserClient whereUserId($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class UserClient extends BaseUserClient {
	use HasUser;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	//public $with = ['user'];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	public static $group = Connector::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION = "OAuth clients which the user has used to record data. ";
	public const COLOR = QMColor::HEX_PURPLE;
	public const FONT_AWESOME = FontAwesome::ID_CARD;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_CLIENT_ID,
		];
	}
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_EARLIEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_LATEST_MEASUREMENT_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_NUMBER_OF_MEASUREMENTS => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_USER_ID => 'required|numeric|min:1',
	];

	/**
	 * @param int $userId
	 * @param string $clientId
	 * @param string $earliestAt
	 * @param string $latestAt
	 * @param int $numberOfMeasurements
	 * @return UserClient
	 */
	public static function updateFromMeasurements(int $userId, string $clientId, string $earliestAt, string $latestAt,
		int $numberOfMeasurements): self{
		$uc = static::firstOrNew([
			self::FIELD_USER_ID => $userId,
			self::FIELD_CLIENT_ID => $clientId,
		]);
		$uc->setIfLessThanExisting(UserClient::FIELD_EARLIEST_MEASUREMENT_AT, $earliestAt);
		$uc->setIfGreaterThanExisting(UserClient::FIELD_LATEST_MEASUREMENT_AT, $latestAt);
		$uc->setIfGreaterThanExisting(UserClient::FIELD_NUMBER_OF_MEASUREMENTS, $numberOfMeasurements);
		$uc->save();
		return $uc;
	}
	/**
	 * @return self
	 */
	public static function firstOrFakeNew(): BaseModel{
		$first = static::whereUserId(UserIdProperty::USER_ID_DEMO)->first();
		if($first){
			return $first;
		}
		if(!$first){
			/** @var Measurement $m */
			$m = Measurement::firstOrFakeSave();
			$uvc = $m->getUserClient();
		}
		return $uvc;
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
