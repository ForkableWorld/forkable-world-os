<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseWpUsermetum;
use App\Traits\HasModel\HasUser;
use App\Types\QMStr;
use App\UI\FontAwesome;
use Corcel\Model\Collection\MetaCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpUsermetum
 * @property int $umeta_id
 * @property int $user_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|WpUsermetum newModelQuery()
 * @method static Builder|WpUsermetum newQuery()
 * @method static Builder|WpUsermetum query()
 * @method static Builder|WpUsermetum whereClientId($value)
 * @method static Builder|WpUsermetum whereCreatedAt($value)
 * @method static Builder|WpUsermetum whereDeletedAt($value)
 * @method static Builder|WpUsermetum whereMetaKey($value)
 * @method static Builder|WpUsermetum whereMetaValue($value)
 * @method static Builder|WpUsermetum whereUmetaId($value)
 * @method static Builder|WpUsermetum whereUpdatedAt($value)
 * @method static Builder|WpUsermetum whereUserId($value)
 * @mixin \Eloquent
 * @property-read User $user
 * @property-read mixed $value
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static MetaCollection|static[] all($columns = ['*'])
 * @method static MetaCollection|static[] get($columns = ['*'])
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpUsermetum extends BaseWpUsermetum {
	use HasUser;
	public const FIELD_ID = self::FIELD_UMETA_ID;
	public const FONT_AWESOME = FontAwesome::USER_CIRCLE;
	protected array $rules = [
		//self::FIELD_UMETA_ID => 'required|numeric|min:1', //|unique:wp_usermeta,umeta_id', // Unique checks too slow
		self::FIELD_USER_ID => 'nullable|numeric|min:1',
		self::FIELD_META_KEY => 'nullable|max:255',
		self::FIELD_META_VALUE => 'nullable',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];
	protected $casts = [
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_META_KEY => 'string',
		// Mixed self::FIELD_META_VALUE => 'string',
		self::FIELD_UMETA_ID => 'int',
		self::FIELD_USER_ID => 'int',	
	];
	/**
	 * @var array
	 */
	protected $appends = ['value'];
	protected array $relationshipInfo = [ // Need to override BaseWpUsermetum because relationship is called user not wp_user as it's generated
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => WpUsermetum::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => WpUsermetum::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	/**
	 * @return mixed
	 */
	public function getValueAttribute(){
		$value = $this->attributes[self::FIELD_META_VALUE] ?? null;
		return QMStr::unserializeIfNecessary($value);
	}
	/**
	 * @param array $models
	 * @return MetaCollection
	 */
	public function newCollection(array $models = []){
		return new MetaCollection($models);
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
