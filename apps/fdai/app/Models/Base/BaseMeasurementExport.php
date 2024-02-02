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
use App\Models\MeasurementExport;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseMeasurementExport
 * @property int $id
 * @property int $user_id
 * @property string $client_id
 * @property string $status
 * @property string $type
 * @property string $output_type
 * @property string $error_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementExport onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereOutputType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMeasurementExport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementExport withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseMeasurementExport withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseMeasurementExport extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ERROR_MESSAGE = 'error_message';
	public const FIELD_ID = 'id';
	public const FIELD_OUTPUT_TYPE = 'output_type';
	public const FIELD_STATUS = 'status';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'measurement_exports';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ERROR_MESSAGE => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_OUTPUT_TYPE => 'string',
		self::FIELD_STATUS => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_ERROR_MESSAGE => 'nullable|max:255',
		self::FIELD_OUTPUT_TYPE => 'required',
		self::FIELD_STATUS => 'required|max:32',
		self::FIELD_TYPE => 'required',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_STATUS => 'Status of Measurement Export',
		self::FIELD_TYPE => 'Whether user\'s measurement export request or app users',
		self::FIELD_OUTPUT_TYPE => 'Output type of export file',
		self::FIELD_ERROR_MESSAGE => 'Error message',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => MeasurementExport::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => MeasurementExport::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => MeasurementExport::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => MeasurementExport::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, MeasurementExport::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			MeasurementExport::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, MeasurementExport::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			MeasurementExport::FIELD_USER_ID);
	}
}
