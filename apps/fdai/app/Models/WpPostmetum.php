<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseWpPostmetum;
use App\UI\FontAwesome;
use Corcel\Model\Collection\MetaCollection;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpPostmetum
 * @property int $meta_id
 * @property int $post_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|WpPostmetum newModelQuery()
 * @method static Builder|WpPostmetum newQuery()
 * @method static Builder|WpPostmetum query()
 * @method static Builder|WpPostmetum whereClientId($value)
 * @method static Builder|WpPostmetum whereCreatedAt($value)
 * @method static Builder|WpPostmetum whereDeletedAt($value)
 * @method static Builder|WpPostmetum whereMetaId($value)
 * @method static Builder|WpPostmetum whereMetaKey($value)
 * @method static Builder|WpPostmetum whereMetaValue($value)
 * @method static Builder|WpPostmetum wherePostId($value)
 * @method static Builder|WpPostmetum whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read WpPost $wp_post
 * @property-read mixed $value
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static MetaCollection|static[] all($columns = ['*'])
 * @method static MetaCollection|static[] get($columns = ['*'])
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpPostmetum extends BaseWpPostmetum {
	public const CLASS_DESCRIPTION = 'This table holds any extra information about individual posts. It is a vertical table using key/value pairs to store its data, a technique WordPress employs on a number of tables throughout the database allowing WordPress core, plugins and themes to store unlimited data.';
	public const FIELD_ID = self::FIELD_META_ID;
	public const FONT_AWESOME = FontAwesome::WORDPRESS;
	protected array $rules = [
		//self::FIELD_META_ID => 'required|numeric|min:1', //|unique:wp_postmeta,meta_id', // Unique checks too slow
		self::FIELD_POST_ID => 'required|numeric|min:1',
		self::FIELD_META_KEY => 'required|max:255',
		//self::FIELD_META_VALUE => 'nullable',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];

	/**
	 * @var string
	 */
	protected $primaryKey = 'meta_id';
	/**
	 * @var bool
	 */
	public $timestamps = false;
	/**
	 * @var array
	 */
	protected $appends = ['value'];
	/**
	 * @return mixed
	 */
	public function getValueAttribute(){
		try {
			$value = unserialize($this->meta_value);
			return $value === false && $this->meta_value !== false ? $this->meta_value : $value;
		} catch (Exception $ex) {
			return $this->meta_value;
		}
	}
	/**
	 * @param array $models
	 * @return MetaCollection
	 */
	public function newCollection(array $models = []){
		return new MetaCollection($models);
	}
}
