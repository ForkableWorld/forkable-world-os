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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpTermmetum
 * @property int $meta_id
 * @property int $term_id
 * @property string $meta_key
 * @property string $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermmetum onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereMetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermmetum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermmetum withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermmetum withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpTermmetum extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_META_ID = 'meta_id';
	public const FIELD_META_KEY = 'meta_key';
	public const FIELD_META_VALUE = 'meta_value';
	public const FIELD_TERM_ID = 'term_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_termmeta';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'meta_id';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_META_ID => 'int',
		self::FIELD_META_KEY => 'string',
		self::FIELD_META_VALUE => 'string',
		self::FIELD_TERM_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_META_ID => 'required|numeric|min:0|unique:wp_termmeta,meta_id',
		self::FIELD_META_KEY => 'nullable|max:255',
		self::FIELD_META_VALUE => 'nullable',
		self::FIELD_TERM_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_META_ID => '',
		self::FIELD_TERM_ID => '',
		self::FIELD_META_KEY => '',
		self::FIELD_META_VALUE => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [];
}
