<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseWpTerm;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpTerm
 * @property int $term_id
 * @property string $name
 * @property string $slug
 * @property int $term_group
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @property-read Collection|WpTermTaxonomy[] $wp_term_taxonomies
 * @property-read int|null $wp_term_taxonomies_count
 * @method static Builder|WpTerm newModelQuery()
 * @method static Builder|WpTerm newQuery()
 * @method static Builder|WpTerm query()
 * @method static Builder|WpTerm whereClientId($value)
 * @method static Builder|WpTerm whereCreatedAt($value)
 * @method static Builder|WpTerm whereDeletedAt($value)
 * @method static Builder|WpTerm whereName($value)
 * @method static Builder|WpTerm whereSlug($value)
 * @method static Builder|WpTerm whereTermGroup($value)
 * @method static Builder|WpTerm whereTermId($value)
 * @method static Builder|WpTerm whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|WpPost[] $posts
 * @property-read int|null $posts_count
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpTerm extends BaseWpTerm {
	public const FIELD_ID = self::FIELD_TERM_ID;
	public const CLASS_DESCRIPTION = 'Terms are items of a taxonomy used to classify objects. Taxonomy what? WordPress allows items like posts and custom post types to be classified in various ways. For example, when creating a post in WordPress, by default you can add a category and some tags to it. Both ‘Category’ and ‘Tag’ are examples of a <a href="http://codex.wordpress.org/Taxonomies" target="_blank">taxonomy</a>, basically a way to group things together.';
	public const FONT_AWESOME = FontAwesome::BOOK_SOLID;
	public const SLUG_UNCATEGORIZED = 'uncategorized';
	protected array $rules = [
		//'term_id' => 'numeric|min:0', //|unique:wp_terms,term_id', // Unique checks too slow
		'name' => 'required|max:200',
		'slug' => 'required|max:200',
		'term_group' => 'nullable|numeric',
		'client_id' => 'nullable|max:255',
	];

	/**
	 * @return WpTerm|Model
	 */
	public static function uncategorized(){
		return static::whereSlug(self::SLUG_UNCATEGORIZED)->first();
	}
	/**
	 * @return BelongsToMany
	 */
	public function posts(){
		return $this->belongsToMany(WpPost::class)->using(WpTermRelationship::class)->withPivot([
			'term_order',
			'object_id',
		]);
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_TERM_ID];
	}
}
