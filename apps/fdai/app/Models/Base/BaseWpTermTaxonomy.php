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
use App\Models\WpTerm;
use App\Models\WpTermRelationship;
use App\Models\WpTermTaxonomy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpTermTaxonomy
 * @property int $term_taxonomy_id
 * @property int $term_id
 * @property string $taxonomy
 * @property string $description
 * @property int $parent
 * @property int $count
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property WpTerm $wp_term
 * @property Collection|WpTermRelationship[] $wp_term_relationships
 * @package App\Models\Base

 * @property-read int|null $wp_term_relationships_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermTaxonomy onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereTaxonomy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereTermTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermTaxonomy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermTaxonomy withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermTaxonomy withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpTermTaxonomy extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COUNT = 'count';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_PARENT = 'parent';
	public const FIELD_TAXONOMY = 'taxonomy';
	public const FIELD_TERM_ID = 'term_id';
	public const FIELD_TERM_TAXONOMY_ID = 'term_taxonomy_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_term_taxonomy';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Following the wp_terms example above, the terms ‘Guide’, ‘database’ and ‘mysql’ that are stored in wp_terms don’t exist yet as a ‘Category’ and as ‘Tags’ unless they are given context. Each term is assigned a taxonomy using this table.';
	protected $primaryKey = 'term_taxonomy_id';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COUNT => 'int',
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_PARENT => 'int',
		self::FIELD_TAXONOMY => 'string',
		self::FIELD_TERM_ID => 'int',
		self::FIELD_TERM_TAXONOMY_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_COUNT => 'nullable|numeric',
		self::FIELD_DESCRIPTION => 'nullable',
		self::FIELD_PARENT => 'nullable|numeric|min:0',
		self::FIELD_TAXONOMY => 'nullable|max:32',
		self::FIELD_TERM_ID => 'nullable|numeric|min:0',
		self::FIELD_TERM_TAXONOMY_ID => 'required|numeric|min:0|unique:wp_term_taxonomy,term_taxonomy_id',
	];
	protected $hints = [
		self::FIELD_TERM_TAXONOMY_ID => 'Unique number assigned to each row of the table.',
		self::FIELD_TERM_ID => 'The ID of the related term.',
		self::FIELD_TAXONOMY => 'The slug of the taxonomy. This can be the <a href="http://codex.wordpress.org/Taxonomies#Default_Taxonomies" target="_blank">built in taxonomies</a> or any taxonomy registered using <a href="http://codex.wordpress.org/Function_Reference/register_taxonomy" target="_blank">register_taxonomy()</a>.',
		self::FIELD_DESCRIPTION => 'Description of the term in this taxonomy.',
		self::FIELD_PARENT => 'ID of a parent term. Used for hierarchical taxonomies like Categories.',
		self::FIELD_COUNT => 'Number of post objects assigned the term for this taxonomy.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'wp_term' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpTerm::class,
			'foreignKeyColumnName' => 'term_id',
			'foreignKey' => WpTermTaxonomy::FIELD_TERM_ID,
			'otherKeyColumnName' => 'term_id',
			'otherKey' => WpTerm::FIELD_TERM_ID,
			'ownerKeyColumnName' => 'term_id',
			'ownerKey' => WpTermTaxonomy::FIELD_TERM_ID,
			'methodName' => 'wp_term',
		],
		'wp_term_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpTermRelationship::class,
			'foreignKey' => WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			'localKey' => WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			'methodName' => 'wp_term_relationships',
		],
	];
	public function wp_term(): BelongsTo{
		return $this->belongsTo(WpTerm::class, WpTermTaxonomy::FIELD_TERM_ID, WpTerm::FIELD_TERM_ID,
			WpTermTaxonomy::FIELD_TERM_ID);
	}
	public function wp_term_relationships(): HasMany{
		return $this->hasMany(WpTermRelationship::class, WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			WpTermRelationship::FIELD_TERM_TAXONOMY_ID);
	}
}
