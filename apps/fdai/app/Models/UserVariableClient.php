<?php /** @noinspection SpellCheckingInspection *//*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\DataSources\QMDataSource;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotFoundException;
use App\Models\Base\BaseUserVariableClient;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Traits\HasModel\HasUser;
use App\Types\QMArr;
use App\UI\FontAwesome;
use App\UI\QMColor;
use App\Variables\QMUserVariable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
/**
 * App\Models\UserVariableClient
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
 * @property int $user_variable_id
 * @property int $variable_id Id of variable
 * @method static Builder|UserVariableClient newModelQuery()
 * @method static Builder|UserVariableClient newQuery()
 * @method static Builder|UserVariableClient query()
 * @method static Builder|UserVariableClient whereClientId($value)
 * @method static Builder|UserVariableClient whereCreatedAt($value)
 * @method static Builder|UserVariableClient whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableClient
 *     whereEarliestMeasurementAt($value)
 * @method static Builder|UserVariableClient whereId($value)
 * @method static Builder|UserVariableClient whereLatestMeasurementAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserVariableClient
 *     whereNumberOfMeasurements($value)
 * @method static Builder|UserVariableClient whereUpdatedAt($value)
 * @method static Builder|UserVariableClient whereUserId($value)
 * @method static Builder|UserVariableClient whereUserVariableId($value)
 * @method static Builder|UserVariableClient whereVariableId($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property-read UserVariable $user_variable
 * @property-read Variable $variable
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 */
class UserVariableClient extends BaseUserVariableClient {
	use HasUser;
	//use CacheQueryBuilder
	public const CLASS_DESCRIPTION = "OAuth clients which the user has used to record data for a specific variable. ";
	public const COLOR = QMColor::HEX_PURPLE;
	public const FONT_AWESOME = FontAwesome::ID_CARD;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_VARIABLE_ID,
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
		self::FIELD_USER_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
	];
	/**
	 * @param UserVariable $uv
	 * @return UserVariableClient[]
	 */
	public static function updateByUserVariable($uv): array{
		if($uv instanceof QMUserVariable){
			$uv = $uv->l();
		}
		$forVariable = $uv->getMeasurements();
		if(!$forVariable){
			return [];
		}
		$byClientId = [];
		/** @var Measurement $m */
		foreach($forVariable as $m){
			$at = $m->getStartAtAttribute();
			$clientId = $m->client_id;
			if(!$clientId){
				$clientId = $m->client_id = BaseClientIdProperty::CLIENT_ID_UNKNOWN;
			}
			$byClientId[$clientId][$at] = $m;
		}
		$uvcs = [];
		foreach($byClientId as $clientId => $forClient){
			$uvc = self::updateByMeasurements($forClient);
			$uvcs[$clientId] = $uvc;
		}
		$uv->setRelation('user_variable_clients', collect($uvcs));
		$uv->addToMemory();
		return $uvcs;
	}
	/**
	 * @param Measurement[]|Collection|QMMeasurement[] $measurements
	 * @return UserVariableClient
	 */
	public static function updateByMeasurements($measurements): UserVariableClient{
		if(!$measurements){
			le("No measurements provided to " . __METHOD__);
		}
		$first = QMArr::first($measurements);
		$uv = $first->getUserVariable()->l();
		if(!$first instanceof Measurement){
			$first = $first->l();
		}
		$rel = $uv->user_variable_clients();
		if(empty($first->client_id)){
			le('empty($first->client_id)');
		}
		$uniqueKey = [
			UserVariableClient::FIELD_VARIABLE_ID => $first->variable_id,
			UserVariableClient::FIELD_USER_ID => $first->user_id,
			UserVariableClient::FIELD_CLIENT_ID => $first->client_id,
		];
		//$all = $rel->get();
		/** @var UserVariableClient $uvc */
		$uvc = UserVariableClient::findByData($uniqueKey);
		if(!$uvc){
			$uvc = new UserVariableClient($uniqueKey);
		}
		$uvc->user_variable_id = $uv->id;
		$latestAt = QMArr::max($measurements, Measurement::FIELD_START_AT);
		$earliestAt = QMArr::min($measurements, Measurement::FIELD_START_AT);
		if(!$earliestAt){
			le('!$earliestAt');
		}
		$number = count($measurements);
		if($number < $uv->number_of_measurements){ // Incremental update
			try {
				$uvc->setIfGreaterThanExisting(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT, $latestAt);
			} catch (\Throwable $e) {
				$uvc->setIfGreaterThanExisting(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT, $latestAt);
			}
			$uvc->setIfLessThanExisting(UserVariableClient::FIELD_EARLIEST_MEASUREMENT_AT, $earliestAt);
			$uvc->setIfGreaterThanExisting(UserVariableClient::FIELD_NUMBER_OF_MEASUREMENTS, $number);
		} else{ // Doing a full analysis
			$uvc->setAttribute(UserVariableClient::FIELD_LATEST_MEASUREMENT_AT, $latestAt);
			$uvc->setAttribute(UserVariableClient::FIELD_EARLIEST_MEASUREMENT_AT, $earliestAt);
			$uvc->setAttribute(UserVariableClient::FIELD_NUMBER_OF_MEASUREMENTS, $number);
		}
		try {
			$uvc->save();
		} catch (ModelValidationException $e) {
			le($e);
		}
		return $uvc;
	}
	/**
	 * @return QMDataSource|null
	 */
	public function getQMDataSource(): ?QMDataSource{
		try {
			$ds = QMDataSource::find($this->client_id);
			return $ds;
		} catch (NotFoundException $e) {
			$this->logInfo(__METHOD__.": ".$e->getMessage());
			return null;
		}
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
			$uvc = $m->getUserVariableClient();
		}
		return $uvc;
	}
	public static function findByData($data): ?BaseModel{
		return parent::findByData($data);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
