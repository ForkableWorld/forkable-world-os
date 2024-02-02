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
use App\Models\MeasurementImport;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseMeasurementImport
 * @property int $id
 * @property int $user_id
 * @property string $file
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $status
 * @property string $error_message
 * @property string $source_name
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property Carbon $import_started_at
 * @property Carbon $import_ended_at
 * @property string $reason_for_import
 * @property string $user_error_message
 * @property string $internal_error_message
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementImport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereImportEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereImportStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereInternalErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereReasonForImport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereSourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport
 *     whereUserErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementImport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementImport withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementImport withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseMeasurementImport extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ERROR_MESSAGE = 'error_message';
	public const FIELD_FILE = 'file';
	public const FIELD_ID = 'id';
	public const FIELD_IMPORT_ENDED_AT = 'import_ended_at';
	public const FIELD_IMPORT_STARTED_AT = 'import_started_at';
	public const FIELD_INTERNAL_ERROR_MESSAGE = 'internal_error_message';
	public const FIELD_REASON_FOR_IMPORT = 'reason_for_import';
	public const FIELD_SOURCE_NAME = 'source_name';
	public const FIELD_STATUS = 'status';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ERROR_MESSAGE = 'user_error_message';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'measurement_imports';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_IMPORT_STARTED_AT => 'datetime',
        self::FIELD_IMPORT_ENDED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ERROR_MESSAGE => 'string',
		self::FIELD_FILE => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'string',
		self::FIELD_REASON_FOR_IMPORT => 'string',
		self::FIELD_SOURCE_NAME => 'string',
		self::FIELD_STATUS => 'string',
		self::FIELD_USER_ERROR_MESSAGE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_FILE => 'required|max:255',
		self::FIELD_IMPORT_ENDED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_IMPORT_STARTED_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_INTERNAL_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_REASON_FOR_IMPORT => 'nullable|max:255',
		self::FIELD_SOURCE_NAME => 'nullable|max:80',
		self::FIELD_STATUS => 'required|max:25',
		self::FIELD_USER_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_FILE => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_STATUS => '',
		self::FIELD_ERROR_MESSAGE => '',
		self::FIELD_SOURCE_NAME => 'Name of the application or device',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_IMPORT_STARTED_AT => 'datetime',
		self::FIELD_IMPORT_ENDED_AT => 'datetime',
		self::FIELD_REASON_FOR_IMPORT => '',
		self::FIELD_USER_ERROR_MESSAGE => '',
		self::FIELD_INTERNAL_ERROR_MESSAGE => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => MeasurementImport::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => MeasurementImport::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => MeasurementImport::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => MeasurementImport::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, MeasurementImport::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			MeasurementImport::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, MeasurementImport::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			MeasurementImport::FIELD_USER_ID);
	}
}
