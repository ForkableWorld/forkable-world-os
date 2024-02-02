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
/** Class BaseFavorite
 * @property int $id
 * @property int $user_id
 * @property string $favoriteable_type
 * @property int $favoriteable_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $is_public
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite query()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereFavoriteableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereFavoriteableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseFavorite whereUserId($value)
 * @mixin \Eloquent
 */
abstract class BaseFavorite extends BaseModel {
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_FAVORITEABLE_ID = 'favoriteable_id';
	public const FIELD_FAVORITEABLE_TYPE = 'favoriteable_type';
	public const FIELD_ID = 'id';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'favorites';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_FAVORITEABLE_ID => 'int',
		self::FIELD_FAVORITEABLE_TYPE => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IS_PUBLIC => 'bool',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_FAVORITEABLE_ID => 'required|numeric|min:0',
		self::FIELD_FAVORITEABLE_TYPE => 'required|max:255',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => 'user_id',
		self::FIELD_FAVORITEABLE_TYPE => '',
		self::FIELD_FAVORITEABLE_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_IS_PUBLIC => '',
	];
	protected array $relationshipInfo = [];
}
