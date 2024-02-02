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
use App\Models\CtgCondition;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/** Class BaseCtgCondition
 * @property int $id
 * @property string $nct_id
 * @property string $name
 * @property string $downcase_name
 * @property int $variable_id
 * @property Variable $variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgCondition query()
 * @mixin \Eloquent
 * @method static Builder|BaseCtgCondition whereDowncaseName($value)
 * @method static Builder|BaseCtgCondition whereId($value)
 * @method static Builder|BaseCtgCondition whereName($value)
 * @method static Builder|BaseCtgCondition whereNctId($value)
 * @method static Builder|BaseCtgCondition whereVariableId($value)
 */
abstract class BaseCtgCondition extends BaseModel {
	public const FIELD_DOWNCASE_NAME = 'downcase_name';
	public const FIELD_ID = 'id';
	public const FIELD_NAME = 'name';
	public const FIELD_NCT_ID = 'nct_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'ctg_conditions';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'id';
	public $incrementing = false;
	public $timestamps = false;
	protected $casts = [
		self::FIELD_DOWNCASE_NAME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_NAME => 'string',
		self::FIELD_NCT_ID => 'string',
		self::FIELD_VARIABLE_ID => 'int',
	];
	protected array $rules = [
		self::FIELD_DOWNCASE_NAME => 'nullable|max:4369',
		self::FIELD_NAME => 'nullable|max:4369',
		self::FIELD_NCT_ID => 'nullable|max:4369',
		self::FIELD_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647|unique:ctg_conditions,variable_id',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NCT_ID => '',
		self::FIELD_NAME => '',
		self::FIELD_DOWNCASE_NAME => '',
		self::FIELD_VARIABLE_ID => '',
	];
	protected array $relationshipInfo = [
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => CtgCondition::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => CtgCondition::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtgCondition::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			CtgCondition::FIELD_VARIABLE_ID);
	}
}
