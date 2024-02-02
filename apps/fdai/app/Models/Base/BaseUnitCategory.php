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
/** Class BaseUnitCategory
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property bool $can_be_summed
 * @property Carbon $deleted_at
 * @property int $sort_order
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereCanBeSummed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitCategory withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitCategory withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseUnitCategory extends BaseModel {
	use SoftDeletes;
	public const FIELD_CAN_BE_SUMMED = 'can_be_summed';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_SORT_ORDER = 'sort_order';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'unit_categories';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CAN_BE_SUMMED => 'bool',
		self::FIELD_ID => 'int',
		self::FIELD_NAME => 'string',
		self::FIELD_SORT_ORDER => 'int',	];
	protected array $rules = [
		self::FIELD_CAN_BE_SUMMED => 'required|boolean',
		self::FIELD_NAME => 'required|max:64',
		self::FIELD_SORT_ORDER => 'required|integer|min:-2147483648|max:2147483647',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NAME => 'Unit category name',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CAN_BE_SUMMED => '',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_SORT_ORDER => '',
	];
	protected array $relationshipInfo = [];
}
