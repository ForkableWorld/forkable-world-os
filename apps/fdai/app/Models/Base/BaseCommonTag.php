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
use App\Models\CommonTag;
use App\Models\OAClient;
use App\Models\Unit;
use App\Models\Variable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCommonTag
 * @property int $id
 * @property int $tagged_variable_id
 * @property int $tag_variable_id
 * @property int $number_of_data_points
 * @property float $standard_error
 * @property int $tag_variable_unit_id
 * @property int $tagged_variable_unit_id
 * @property float $conversion_factor
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property OAClient $oa_client
 * @property Variable $tag_variable
 * @property Unit $tag_variable_unit
 * @property Variable $tagged_variable
 * @property Unit $tagged_variable_unit
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCommonTag onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereConversionFactor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereNumberOfDataPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereStandardError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereTagVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereTagVariableUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereTaggedVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereTaggedVariableUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCommonTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCommonTag withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCommonTag withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseCommonTag extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONVERSION_FACTOR = 'conversion_factor';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NUMBER_OF_DATA_POINTS = 'number_of_data_points';
	public const FIELD_STANDARD_ERROR = 'standard_error';
	public const FIELD_TAG_VARIABLE_ID = 'tag_variable_id';
	public const FIELD_TAG_VARIABLE_UNIT_ID = 'tag_variable_unit_id';
	public const FIELD_TAGGED_VARIABLE_ID = 'tagged_variable_id';
	public const FIELD_TAGGED_VARIABLE_UNIT_ID = 'tagged_variable_unit_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'common_tags';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONVERSION_FACTOR => 'float',
		self::FIELD_ID => 'int',
		self::FIELD_NUMBER_OF_DATA_POINTS => 'int',
		self::FIELD_STANDARD_ERROR => 'float',
		self::FIELD_TAGGED_VARIABLE_ID => 'int',
		self::FIELD_TAGGED_VARIABLE_UNIT_ID => 'int',
		self::FIELD_TAG_VARIABLE_ID => 'int',
		self::FIELD_TAG_VARIABLE_UNIT_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_CONVERSION_FACTOR => 'required|numeric',
		self::FIELD_NUMBER_OF_DATA_POINTS => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_STANDARD_ERROR => 'nullable|numeric',
		self::FIELD_TAGGED_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_TAGGED_VARIABLE_UNIT_ID => 'nullable|integer|min:0|max:65535',
		self::FIELD_TAG_VARIABLE_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_TAG_VARIABLE_UNIT_ID => 'nullable|integer|min:0|max:65535',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_TAGGED_VARIABLE_ID => 'This is the id of the variable being tagged with an ingredient or something.',
		self::FIELD_TAG_VARIABLE_ID => 'This is the id of the ingredient variable whose value is determined based on the value of the tagged variable.',
		self::FIELD_NUMBER_OF_DATA_POINTS => 'The number of data points used to estimate the mean. ',
		self::FIELD_STANDARD_ERROR => 'Measure of variability of the
mean value as a function of the number of data points.',
		self::FIELD_TAG_VARIABLE_UNIT_ID => 'The id for the unit of the tag (ingredient) variable.',
		self::FIELD_TAGGED_VARIABLE_UNIT_ID => 'The unit for the source variable to be tagged.',
		self::FIELD_CONVERSION_FACTOR => 'Number by which we multiply the tagged variable\'s value to obtain the tag variable\'s value',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => CommonTag::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => CommonTag::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'tag_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'tag_variable_id',
			'foreignKey' => CommonTag::FIELD_TAG_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'tag_variable_id',
			'ownerKey' => CommonTag::FIELD_TAG_VARIABLE_ID,
			'methodName' => 'tag_variable',
		],
		'tag_variable_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'tag_variable_unit_id',
			'foreignKey' => CommonTag::FIELD_TAG_VARIABLE_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'tag_variable_unit_id',
			'ownerKey' => CommonTag::FIELD_TAG_VARIABLE_UNIT_ID,
			'methodName' => 'tag_variable_unit',
		],
		'tagged_variable' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Variable::class,
			'foreignKeyColumnName' => 'tagged_variable_id',
			'foreignKey' => CommonTag::FIELD_TAGGED_VARIABLE_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Variable::FIELD_ID,
			'ownerKeyColumnName' => 'tagged_variable_id',
			'ownerKey' => CommonTag::FIELD_TAGGED_VARIABLE_ID,
			'methodName' => 'tagged_variable',
		],
		'tagged_variable_unit' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Unit::class,
			'foreignKeyColumnName' => 'tagged_variable_unit_id',
			'foreignKey' => CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Unit::FIELD_ID,
			'ownerKeyColumnName' => 'tagged_variable_unit_id',
			'ownerKey' => CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID,
			'methodName' => 'tagged_variable_unit',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, CommonTag::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			CommonTag::FIELD_CLIENT_ID);
	}
	public function tag_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CommonTag::FIELD_TAG_VARIABLE_ID, Variable::FIELD_ID,
			CommonTag::FIELD_TAG_VARIABLE_ID);
	}
	public function tag_variable_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, CommonTag::FIELD_TAG_VARIABLE_UNIT_ID, Unit::FIELD_ID,
			CommonTag::FIELD_TAG_VARIABLE_UNIT_ID);
	}
	public function tagged_variable(): BelongsTo{
		return $this->belongsTo(Variable::class, CommonTag::FIELD_TAGGED_VARIABLE_ID, Variable::FIELD_ID,
			CommonTag::FIELD_TAGGED_VARIABLE_ID);
	}
	public function tagged_variable_unit(): BelongsTo{
		return $this->belongsTo(Unit::class, CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID, Unit::FIELD_ID,
			CommonTag::FIELD_TAGGED_VARIABLE_UNIT_ID);
	}
}
