<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Logging\QMLog;
use App\Models\Base\BaseWpTermTaxonomy;
use App\Types\QMArr;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpTermTaxonomy
 * @property int $term_taxonomy_id
 * @property int $term_id
 * @property string $taxonomy
 * @property string $description
 * @property int $parent
 * @property int $count
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @property-read WpTerm $wp_term
 * @property-read Collection|WpTermRelationship[] $wp_term_relationships
 * @property-read int|null $wp_term_relationships_count
 * @method static Builder|WpTermTaxonomy newModelQuery()
 * @method static Builder|WpTermTaxonomy newQuery()
 * @method static Builder|WpTermTaxonomy query()
 * @method static Builder|WpTermTaxonomy whereClientId($value)
 * @method static Builder|WpTermTaxonomy whereCount($value)
 * @method static Builder|WpTermTaxonomy whereCreatedAt($value)
 * @method static Builder|WpTermTaxonomy whereDeletedAt($value)
 * @method static Builder|WpTermTaxonomy whereDescription($value)
 * @method static Builder|WpTermTaxonomy whereParent($value)
 * @method static Builder|WpTermTaxonomy whereTaxonomy($value)
 * @method static Builder|WpTermTaxonomy whereTermId($value)
 * @method static Builder|WpTermTaxonomy whereTermTaxonomyId($value)
 * @method static Builder|WpTermTaxonomy whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpTermTaxonomy extends BaseWpTermTaxonomy {
	public const FIELD_ID = self::FIELD_TERM_TAXONOMY_ID;
	public const CLASS_DESCRIPTION = 'Following the wp_terms example above, the terms ‘Guide’, ‘database’ and ‘mysql’ that are stored in wp_terms don’t exist yet as a ‘Category’ and as ‘Tags’ unless they are given context. Each term is assigned a taxonomy using this table.';
	public const FONT_AWESOME = FontAwesome::BOOK_SOLID;
	public const TAXONOMY_CATEGORY = 'category';
	public $with = ['wp_term'];
	protected array $rules = [
		//'term_taxonomy_id' => 'required|numeric|min:0', //|unique:wp_term_taxonomy,term_taxonomy_id', // Unique checks too slow
		'term_id' => 'required|numeric|min:0',
		'taxonomy' => 'required|max:32',
		//'description' => 'required',
		'parent' => 'nullable|numeric|min:0',
		//'count' => 'numeric',
		'client_id' => 'nullable|max:255',
	];

	public static function updateCategoryCounts(){
		$all = WpTermTaxonomy::all();
		foreach($all as $taxonomy){
			$term_taxonomy_id = $taxonomy->term_taxonomy_id;
			$count = WpTermRelationship::whereTermTaxonomyId($term_taxonomy_id)->count();
			WpTermTaxonomy::whereTermTaxonomyId($term_taxonomy_id)->update([WpTermTaxonomy::FIELD_COUNT => $count]);
		}
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getNameAttribute();
	}
	public function getNameAttribute(): string{
		return $this->wp_term->name;
	}
	public function getUniqueIndexIdsSlug(): string{
		return $this->wp_term->slug;
	}
	/**
	 * @return \Illuminate\Support\Collection
	 */
	public function getPostIds(): \Illuminate\Support\Collection{
		return $this->wp_term_relationships()->pluck(WpTermRelationship::FIELD_OBJECT_ID);
	}
	/**
	 * @return float
	 */
	public function getPostSizesKB(): float{
		$ids = $this->getPostIds()->all();
		$sum = WpPost::whereIn(WpPost::FIELD_ID, $ids)->sum(WpPost::FIELD_RECORD_SIZE_IN_KB);
		return $sum;
	}
	public function getSizeAndCount(): array{
		$before = $this->count;
		$this->count = $this->wp_term_relationships()->count();
		if($before !== $this->count){
			$this->logInfo("$before before and $this->count after...");
			$this->save();
		}
		$kb = $this->getPostSizesKB();
		return ['name' => $this->getNameAttribute(), 'count' => $this->count, 'kb' => $kb];
	}
	public static function getCategoryCounts(): array{
		$taxonomies = WpTermTaxonomy::query()->get();
		$counts = [];
		foreach($taxonomies as $tt){
			$counts[] = $tt->getSizeAndCount();
		}
		$counts = QMArr::sortAssociativeArrayByFieldDescending($counts, 'count');
		QMLog::table($counts, "Post category sizes");
		return $counts;
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_TERM_TAXONOMY_ID];
	}
}
