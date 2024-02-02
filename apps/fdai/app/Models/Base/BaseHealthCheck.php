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
/** Class BaseHealthCheck
 * @property int $id
 * @property string $resource_name
 * @property string $resource_slug
 * @property string $target_name
 * @property string $target_slug
 * @property string $target_display
 * @property bool $healthy
 * @property string $error_message
 * @property float $runtime
 * @property string $value
 * @property string $value_human
 * @property Carbon $created_at
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereHealthy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereResourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereResourceSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereRuntime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereTargetDisplay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereTargetName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereTargetSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseHealthCheck whereValueHuman($value)
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseHealthCheck extends BaseModel {
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_ERROR_MESSAGE = 'error_message';
	public const FIELD_HEALTHY = 'healthy';
	public const FIELD_ID = 'id';
	public const FIELD_RESOURCE_NAME = 'resource_name';
	public const FIELD_RESOURCE_SLUG = 'resource_slug';
	public const FIELD_RUNTIME = 'runtime';
	public const FIELD_TARGET_DISPLAY = 'target_display';
	public const FIELD_TARGET_NAME = 'target_name';
	public const FIELD_TARGET_SLUG = 'target_slug';
	public const FIELD_VALUE = 'value';
	public const FIELD_VALUE_HUMAN = 'value_human';
	public const TABLE = 'health_checks';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $timestamps = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_ERROR_MESSAGE => 'string',
		self::FIELD_HEALTHY => 'bool',
		self::FIELD_ID => 'int',
		self::FIELD_RESOURCE_NAME => 'string',
		self::FIELD_RESOURCE_SLUG => 'string',
		self::FIELD_RUNTIME => 'float',
		self::FIELD_TARGET_DISPLAY => 'string',
		self::FIELD_TARGET_NAME => 'string',
		self::FIELD_TARGET_SLUG => 'string',
		self::FIELD_VALUE => 'string',
		self::FIELD_VALUE_HUMAN => 'string',	];
	protected array $rules = [
		self::FIELD_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_HEALTHY => 'required|boolean',
		self::FIELD_RESOURCE_NAME => 'required|max:255',
		self::FIELD_RESOURCE_SLUG => 'required|max:255',
		self::FIELD_RUNTIME => 'required|numeric',
		self::FIELD_TARGET_DISPLAY => 'required|max:255',
		self::FIELD_TARGET_NAME => 'required|max:255',
		self::FIELD_TARGET_SLUG => 'required|max:255',
		self::FIELD_VALUE => 'nullable|max:255',
		self::FIELD_VALUE_HUMAN => 'nullable|max:255',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_RESOURCE_NAME => '',
		self::FIELD_RESOURCE_SLUG => '',
		self::FIELD_TARGET_NAME => '',
		self::FIELD_TARGET_SLUG => '',
		self::FIELD_TARGET_DISPLAY => '',
		self::FIELD_HEALTHY => '',
		self::FIELD_ERROR_MESSAGE => '',
		self::FIELD_RUNTIME => '',
		self::FIELD_VALUE => '',
		self::FIELD_VALUE_HUMAN => '',
		self::FIELD_CREATED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
