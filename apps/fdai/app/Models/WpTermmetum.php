<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseWpTermmetum;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpTermmetum
 * @property int $meta_id
 * @property int $term_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|WpTermmetum newModelQuery()
 * @method static Builder|WpTermmetum newQuery()
 * @method static Builder|WpTermmetum query()
 * @method static Builder|WpTermmetum whereClientId($value)
 * @method static Builder|WpTermmetum whereCreatedAt($value)
 * @method static Builder|WpTermmetum whereDeletedAt($value)
 * @method static Builder|WpTermmetum whereMetaId($value)
 * @method static Builder|WpTermmetum whereMetaKey($value)
 * @method static Builder|WpTermmetum whereMetaValue($value)
 * @method static Builder|WpTermmetum whereTermId($value)
 * @method static Builder|WpTermmetum whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpTermmetum extends BaseWpTermmetum {
	public const FONT_AWESOME = FontAwesome::BOOK_SOLID;
	protected array $rules = [
		self::FIELD_META_ID => 'required|numeric|min:1', //|unique:wp_termmeta,meta_id', // Unique checks too slow
		self::FIELD_TERM_ID => 'required|numeric|min:1',
		self::FIELD_META_KEY => 'nullable|max:255',
		self::FIELD_META_VALUE => 'nullable',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];

}
