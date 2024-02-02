<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseWpTermRelationship;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\WpTermRelationship
 * @property int $object_id
 * @property int $term_taxonomy_id
 * @property int $term_order
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @property-read WpTermTaxonomy $wp_term_taxonomy
 * @method static Builder|WpTermRelationship newModelQuery()
 * @method static Builder|WpTermRelationship newQuery()
 * @method static Builder|WpTermRelationship query()
 * @method static Builder|WpTermRelationship whereClientId($value)
 * @method static Builder|WpTermRelationship whereCreatedAt($value)
 * @method static Builder|WpTermRelationship whereDeletedAt($value)
 * @method static Builder|WpTermRelationship whereObjectId($value)
 * @method static Builder|WpTermRelationship whereTermOrder($value)
 * @method static Builder|WpTermRelationship whereTermTaxonomyId($value)
 * @method static Builder|WpTermRelationship whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read WpPost $wp_post
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpTermRelationship extends BaseWpTermRelationship {
	public const CLASS_DESCRIPTION = 'So far we have seen how terms and their taxonomies are stored in the database, but have yet to see how WordPress stores the critical data when it comes to using taxonomies. This post exists in wp_posts and when we actually assign the category and tags through the WordPress dashboard this is the <a href="http://en.wikipedia.org/wiki/Junction_table" target="_blank">junction table</a> that records that information. Each row defines a relationship between a post (object) in wp_posts and a term of a certain taxonomy in wp_term_taxonomy.';
	public const FIELD_ID = self::FIELD_TERM_TAXONOMY_ID;
	public const FONT_AWESOME = FontAwesome::BOOK_SOLID;
	protected $primaryKey = self::FIELD_TERM_TAXONOMY_ID;
	protected $fillable = [
		self::FIELD_OBJECT_ID,
		self::FIELD_TERM_TAXONOMY_ID,
		self::FIELD_TERM_ORDER,
		self::FIELD_CLIENT_ID,
	];
	protected array $rules = [
		'object_id' => 'required|numeric|min:0',
		'term_taxonomy_id' => 'required|numeric|min:0',
		'term_order' => 'integer|min:0|max:2147483647',
		'client_id' => 'max:255',
	];
}
