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
use App\Models\WpPost;
use App\Models\WpPostmetum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpPostmetum
 * @property int $meta_id
 * @property int $post_id
 * @property string $meta_key
 * @property string $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property WpPost $wp_post
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPostmetum onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereMetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPostmetum whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPostmetum withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPostmetum withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpPostmetum extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_META_ID = 'meta_id';
	public const FIELD_META_KEY = 'meta_key';
	public const FIELD_META_VALUE = 'meta_value';
	public const FIELD_POST_ID = 'post_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_postmeta';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'This table holds any extra information about individual posts. It is a vertical table using key/value pairs to store its data, a technique WordPress employs on a number of tables throughout the database allowing WordPress core, plugins and themes to store unlimited data.';
	protected $primaryKey = 'meta_id';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_META_ID => 'int',
		self::FIELD_META_KEY => 'string',
		self::FIELD_META_VALUE => 'string',
		self::FIELD_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_META_ID => 'required|numeric|min:0|unique:wp_postmeta,meta_id',
		self::FIELD_META_KEY => 'nullable|max:255',
		self::FIELD_META_VALUE => 'nullable',
		self::FIELD_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_META_ID => 'Unique number assigned to each row of the table.',
		self::FIELD_POST_ID => 'The ID of the post the data relates to.',
		self::FIELD_META_KEY => 'An identifying key for the piece of data.',
		self::FIELD_META_VALUE => 'The actual piece of data.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'post_id',
			'foreignKey' => WpPostmetum::FIELD_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'post_id',
			'ownerKey' => WpPostmetum::FIELD_POST_ID,
			'methodName' => 'wp_post',
		],
	];
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, WpPostmetum::FIELD_POST_ID, WpPost::FIELD_ID,
			WpPostmetum::FIELD_POST_ID);
	}
}
