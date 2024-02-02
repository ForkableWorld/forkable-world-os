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
/** Class BaseFailedJob
 * @property int $id
 * @property string $connection
 * @property string $queue
 * @property string $payload
 * @property string $exception
 * @property Carbon $failed_at
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob whereConnection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob whereException($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob whereFailedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseFailedJob whereQueue($value)
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseFailedJob extends BaseModel {
	public const FIELD_CONNECTION = 'connection';
	public const FIELD_EXCEPTION = 'exception';
	public const FIELD_FAILED_AT = 'failed_at';
	public const FIELD_ID = 'id';
	public const FIELD_PAYLOAD = 'payload';
	public const FIELD_QUEUE = 'queue';
	public const TABLE = 'failed_jobs';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $timestamps = false;
	protected $casts = [
        self::FIELD_FAILED_AT => 'datetime',
		self::FIELD_CONNECTION => 'string',
		self::FIELD_EXCEPTION => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_PAYLOAD => 'string',
		self::FIELD_QUEUE => 'string',	];
	protected array $rules = [
		self::FIELD_CONNECTION => 'required|max:65535',
		self::FIELD_EXCEPTION => 'required',
		self::FIELD_FAILED_AT => 'required|date',
		self::FIELD_PAYLOAD => 'required',
		self::FIELD_QUEUE => 'required|max:65535',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CONNECTION => '',
		self::FIELD_QUEUE => '',
		self::FIELD_PAYLOAD => '',
		self::FIELD_EXCEPTION => '',
		self::FIELD_FAILED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
