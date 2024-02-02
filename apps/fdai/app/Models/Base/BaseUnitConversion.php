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
/** Class BaseUnitConversion
 * @property int $unit_id
 * @property int $step_number
 * @property int $operation
 * @property float $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitConversion onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereOperation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereStepNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseUnitConversion whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitConversion withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseUnitConversion withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseUnitConversion extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_OPERATION = 'operation';
	public const FIELD_STEP_NUMBER = 'step_number';
	public const FIELD_UNIT_ID = 'unit_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_VALUE = 'value';
	public const TABLE = 'unit_conversions';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_OPERATION => 'int',
		self::FIELD_STEP_NUMBER => 'int',
		self::FIELD_UNIT_ID => 'int',
		self::FIELD_VALUE => 'float',	];
	protected array $rules = [
		self::FIELD_OPERATION => 'required|boolean',
		self::FIELD_STEP_NUMBER => 'required|boolean',
		self::FIELD_UNIT_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_VALUE => 'required|numeric',
	];
	protected $hints = [
		self::FIELD_UNIT_ID => '',
		self::FIELD_STEP_NUMBER => 'step in the conversion process',
		self::FIELD_OPERATION => '0 is add and 1 is multiply',
		self::FIELD_VALUE => 'number used in the operation',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
