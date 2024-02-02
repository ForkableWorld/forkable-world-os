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
/** Class BaseNotification
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $data
 * @property Carbon $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseNotification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseNotification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseNotification withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseNotification extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DATA = 'data';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_NOTIFIABLE_ID = 'notifiable_id';
	public const FIELD_NOTIFIABLE_TYPE = 'notifiable_type';
	public const FIELD_READ_AT = 'read_at';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'notifications';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_READ_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_DATA => 'string',
		self::FIELD_ID => 'string',
		self::FIELD_NOTIFIABLE_ID => 'int',
		self::FIELD_NOTIFIABLE_TYPE => 'string',
		self::FIELD_TYPE => 'string',	];
	protected array $rules = [
		self::FIELD_DATA => 'required|max:65535',
		self::FIELD_NOTIFIABLE_ID => 'required|numeric|min:0',
		self::FIELD_NOTIFIABLE_TYPE => 'required|max:255',
		self::FIELD_READ_AT => 'nullable|date', // Works with anything that strtotime will parse https://laravel.com/docs/8.x/validation#rule-date
		self::FIELD_TYPE => 'required|max:255',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_TYPE => '',
		self::FIELD_NOTIFIABLE_TYPE => '',
		self::FIELD_NOTIFIABLE_ID => '',
		self::FIELD_DATA => '',
		self::FIELD_READ_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
