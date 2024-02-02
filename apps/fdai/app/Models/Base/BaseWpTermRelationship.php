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
use App\Models\WpPost;
use App\Models\WpTermRelationship;
use App\Models\WpTermTaxonomy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpTermRelationship
 * @property int $object_id
 * @property int $term_taxonomy_id
 * @property int $term_order
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property WpPost $wp_post
 * @property WpTermTaxonomy $wp_term_taxonomy
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermRelationship onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereTermOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship
 *     whereTermTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTermRelationship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermRelationship withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTermRelationship withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpTermRelationship extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_OBJECT_ID = 'object_id';
	public const FIELD_TERM_ORDER = 'term_order';
	public const FIELD_TERM_TAXONOMY_ID = 'term_taxonomy_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_term_relationships';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'So far we have seen how terms and their taxonomies are stored in the database, but have yet to see how WordPress stores the critical data when it comes to using taxonomies. This post exists in wp_posts and when we actually assign the category and tags through the WordPress dashboard this is the <a href="http://en.wikipedia.org/wiki/Junction_table" target="_blank">junction table</a> that records that information. Each row defines a relationship between a post (object) in wp_posts and a term of a certain taxonomy in wp_term_taxonomy.';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_OBJECT_ID => 'int',
		self::FIELD_TERM_ORDER => 'int',
		self::FIELD_TERM_TAXONOMY_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_OBJECT_ID => 'required|numeric|min:0',
		self::FIELD_TERM_ORDER => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_TERM_TAXONOMY_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_OBJECT_ID => 'The ID of the post object.',
		self::FIELD_TERM_TAXONOMY_ID => 'The ID of the term / taxonomy pair.',
		self::FIELD_TERM_ORDER => 'Allow ordering of terms for an object, not fully used.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'object_id',
			'foreignKey' => WpTermRelationship::FIELD_OBJECT_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'object_id',
			'ownerKey' => WpTermRelationship::FIELD_OBJECT_ID,
			'methodName' => 'wp_post',
		],
		'wp_term_taxonomy' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpTermTaxonomy::class,
			'foreignKeyColumnName' => 'term_taxonomy_id',
			'foreignKey' => WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			'otherKeyColumnName' => 'term_taxonomy_id',
			'otherKey' => WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID,
			'ownerKeyColumnName' => 'term_taxonomy_id',
			'ownerKey' => WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			'methodName' => 'wp_term_taxonomy',
		],
	];
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, WpTermRelationship::FIELD_OBJECT_ID, WpPost::FIELD_ID,
			WpTermRelationship::FIELD_OBJECT_ID);
	}
	public function wp_term_taxonomy(): BelongsTo{
		return $this->belongsTo(WpTermTaxonomy::class, WpTermRelationship::FIELD_TERM_TAXONOMY_ID,
			WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID, WpTermRelationship::FIELD_TERM_TAXONOMY_ID);
	}
}
