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
use App\Models\CtgIntervention;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/** Class BaseCtgIntervention
 * @property int $id
 * @property string $nct_id
 * @property string $intervention_type
 * @property string $name
 * @property string $description
 * @property int $variable_id
 * @property Variable $variable
 * @package App\Models\Base
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgIntervention newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgIntervention newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseCtgIntervention query()
 * @mixin \Eloquent
 * @method static Builder|BaseCtgIntervention whereDescription($value)
 * @method static Builder|BaseCtgIntervention whereId($value)
 * @method static Builder|BaseCtgIntervention whereInterventionType($value)
 * @method static Builder|BaseCtgIntervention whereName($value)
 * @method static Builder|BaseCtgIntervention whereNctId($value)
 * @method static Builder|BaseCtgIntervention whereVariableId($value)
 */
abstract class BaseCtgIntervention extends BaseModel {
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_ID = 'id';
	public const FIELD_INTERVENTION_TYPE = 'intervention_type';
	public const FIELD_NAME = 'name';
	public const FIELD_NCT_ID = 'nct_id';
	public const FIELD_VARIABLE_ID = 'variable_id';
	public const TABLE = 'ctg_interventions';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $primaryKey = 'id';
	public $incrementing = false;
	public $timestamps = false;
	protected $casts = [
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_INTERVENTION_TYPE => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NCT_ID => 'string',
		self::FIELD_VARIABLE_ID => 'int',
	];
	protected array $rules = [
		self::FIELD_DESCRIPTION => 'nullable|max:65535',
		self::FIELD_INTERVENTION_TYPE => 'nullable|max:4369',
		self::FIELD_NAME => 'nullable|max:4369',
		self::FIELD_NCT_ID => 'nullable|max:4369',
		self::FIELD_VARIABLE_ID => 'nullable|integer|min:0|max:2147483647|unique:ctg_interventions,variable_id',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_NCT_ID => '',
		self::FIELD_INTERVENTION_TYPE => '',
		self::FIELD_NAME => '',
		self::FIELD_DESCRIPTION => '',
		self::FIELD_VARIABLE_ID => '',
	];
	protected array $relationshipInfo = [
		'variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'variable_id',
			'foreignKey' => CtgIntervention::FIELD_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'variable_id',
			'ownerKey' => CtgIntervention::FIELD_VARIABLE_ID,
			'methodName' => 'variable',
		],
	];
	public function variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CtgIntervention::FIELD_VARIABLE_ID, Variable::FIELD_ID,
			CtgIntervention::FIELD_VARIABLE_ID);
	}
}
