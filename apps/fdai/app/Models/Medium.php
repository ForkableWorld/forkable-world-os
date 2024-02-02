<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseMedium;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Medium
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property int $size
 * @property array $manipulations
 * @property array $custom_properties
 * @property array $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Medium newModelQuery()
 * @method static Builder|Medium newQuery()
 * @method static Builder|Medium query()
 * @method static Builder|Medium whereCollectionName($value)
 * @method static Builder|Medium whereCreatedAt($value)
 * @method static Builder|Medium whereCustomProperties($value)
 * @method static Builder|Medium whereDisk($value)
 * @method static Builder|Medium whereFileName($value)
 * @method static Builder|Medium whereId($value)
 * @method static Builder|Medium whereManipulations($value)
 * @method static Builder|Medium whereMimeType($value)
 * @method static Builder|Medium whereModelId($value)
 * @method static Builder|Medium whereModelType($value)
 * @method static Builder|Medium whereName($value)
 * @method static Builder|Medium whereOrderColumn($value)
 * @method static Builder|Medium whereResponsiveImages($value)
 * @method static Builder|Medium whereSize($value)
 * @method static Builder|Medium whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class Medium extends BaseMedium {
	const CLASS_CATEGORY = "Miscellaneous";
	protected array $rules = [
		self::FIELD_MODEL_TYPE => 'required|max:255',
		self::FIELD_MODEL_ID => 'required|numeric|min:1',
		self::FIELD_COLLECTION_NAME => 'required|max:255',
		self::FIELD_NAME => 'required|max:255',
		self::FIELD_FILE_NAME => 'required|max:255',
		self::FIELD_MIME_TYPE => 'nullable|max:255',
		self::FIELD_DISK => 'required|max:255',
		self::FIELD_SIZE => 'required|numeric|min:0',
		self::FIELD_MANIPULATIONS => 'required|json',
		self::FIELD_CUSTOM_PROPERTIES => 'required|json',
		self::FIELD_RESPONSIVE_IMAGES => 'required|json',
		self::FIELD_ORDER_COLUMN => 'nullable|integer|min:0|max:2147483647',
	];
}
