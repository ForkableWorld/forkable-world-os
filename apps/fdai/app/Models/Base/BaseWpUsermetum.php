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
use App\Models\WpUsermetum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpUsermetum
 * @property int $umeta_id
 * @property int $user_id
 * @property string $meta_key
 * @property string $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpUsermetum onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereUmetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpUsermetum whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpUsermetum withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpUsermetum withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpUsermetum extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_META_KEY = 'meta_key';
	public const FIELD_META_VALUE = 'meta_value';
	public const FIELD_UMETA_ID = 'umeta_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'wp_usermeta';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'This table stores any further information related to the users. You will see other user profile fields for a user in the dashboard that are stored here.';
	protected $primaryKey = 'umeta_id';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_META_KEY => 'string',
		self::FIELD_META_VALUE => 'string',
		self::FIELD_UMETA_ID => 'int',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_META_KEY => 'nullable|max:255',
		self::FIELD_META_VALUE => 'nullable',
		self::FIELD_UMETA_ID => 'required|numeric|min:0|unique:wp_usermeta,umeta_id',
		self::FIELD_USER_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_UMETA_ID => 'Unique number assigned to each row of the table.',
		self::FIELD_USER_ID => 'ID of the related user.',
		self::FIELD_META_KEY => 'An identifying key for the piece of data.',
		self::FIELD_META_VALUE => 'The actual piece of data.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => WpUsermetum::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => WpUsermetum::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, WpUsermetum::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			WpUsermetum::FIELD_USER_ID);
	}
}
