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
/** Class BaseMedium
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $responsive_images
 * @property int $order_column
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereCollectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereCustomProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereManipulations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereResponsiveImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseMedium whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseMedium extends BaseModel {
	public const FIELD_COLLECTION_NAME = 'collection_name';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_CUSTOM_PROPERTIES = 'custom_properties';
	public const FIELD_DISK = 'disk';
	public const FIELD_FILE_NAME = 'file_name';
	public const FIELD_ID = 'id';
	public const FIELD_MANIPULATIONS = 'manipulations';
	public const FIELD_MIME_TYPE = 'mime_type';
	public const FIELD_MODEL_ID = 'model_id';
	public const FIELD_MODEL_TYPE = 'model_type';
	public const FIELD_NAME = 'name';
	public const FIELD_ORDER_COLUMN = 'order_column';
	public const FIELD_RESPONSIVE_IMAGES = 'responsive_images';
	public const FIELD_SIZE = 'size';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'media';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_COLLECTION_NAME => 'string',
		self::FIELD_CUSTOM_PROPERTIES => 'json',
		self::FIELD_DISK => 'string',
		self::FIELD_FILE_NAME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_MANIPULATIONS => 'json',
		self::FIELD_MIME_TYPE => 'string',
		self::FIELD_MODEL_ID => 'int',
		self::FIELD_MODEL_TYPE => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_ORDER_COLUMN => 'int',
		self::FIELD_RESPONSIVE_IMAGES => 'json',
		self::FIELD_SIZE => 'int',	];
	protected array $rules = [
		self::FIELD_COLLECTION_NAME => 'required|max:255',
		self::FIELD_CUSTOM_PROPERTIES => 'required|json',
		self::FIELD_DISK => 'required|max:255',
		self::FIELD_FILE_NAME => 'required|max:255',
		self::FIELD_MANIPULATIONS => 'required|json',
		self::FIELD_MIME_TYPE => 'nullable|max:255',
		self::FIELD_MODEL_ID => 'required|numeric|min:0',
		self::FIELD_MODEL_TYPE => 'required|max:255',
		self::FIELD_NAME => 'required|max:255',
		self::FIELD_ORDER_COLUMN => 'nullable|integer|min:0|max:2147483647',
		self::FIELD_RESPONSIVE_IMAGES => 'required|json',
		self::FIELD_SIZE => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_MODEL_TYPE => '',
		self::FIELD_MODEL_ID => '',
		self::FIELD_COLLECTION_NAME => '',
		self::FIELD_NAME => '',
		self::FIELD_FILE_NAME => '',
		self::FIELD_MIME_TYPE => '',
		self::FIELD_DISK => '',
		self::FIELD_SIZE => '',
		self::FIELD_MANIPULATIONS => '',
		self::FIELD_CUSTOM_PROPERTIES => '',
		self::FIELD_RESPONSIVE_IMAGES => '',
		self::FIELD_ORDER_COLUMN => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
	];
	protected array $relationshipInfo = [];
}
