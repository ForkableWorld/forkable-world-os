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
use App\Models\WpTermTaxonomy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpTerm
 * @property int $term_id
 * @property string $name
 * @property string $slug
 * @property int $term_group
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property Collection|WpTermTaxonomy[] $wp_term_taxonomies
 * @package App\Models\Base

 * @property-read int|null $wp_term_taxonomies_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTerm onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereTermGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereTermId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpTerm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTerm withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpTerm withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpTerm extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_NAME = 'name';
	public const FIELD_SLUG = 'slug';
	public const FIELD_TERM_GROUP = 'term_group';
	public const FIELD_TERM_ID = 'term_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_terms';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'Terms are items of a taxonomy used to classify objects. Taxonomy what? WordPress allows items like posts and custom post types to be classified in various ways. For example, when creating a post in WordPress, by default you can add a category and some tags to it. Both ‘Category’ and ‘Tag’ are examples of a <a href="http://codex.wordpress.org/Taxonomies" target="_blank">taxonomy</a>, basically a way to group things together.';
	protected $primaryKey = 'term_id';
	protected $casts = [
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_SLUG => 'string',
		self::FIELD_TERM_GROUP => 'int',
		self::FIELD_TERM_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_NAME => 'nullable|max:200',
		self::FIELD_SLUG => 'nullable|max:200',
		self::FIELD_TERM_GROUP => 'nullable|numeric',
		self::FIELD_TERM_ID => 'required|numeric|min:0|unique:wp_terms,term_id',
	];
	protected $hints = [
		self::FIELD_TERM_ID => 'Unique number assigned to each term.',
		self::FIELD_NAME => 'The name of the term.',
		self::FIELD_SLUG => 'The URL friendly slug of the name.',
		self::FIELD_TERM_GROUP => 'Ability for themes or plugins to group terms together to use aliases. Not populated by WordPress core itself.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'wp_term_taxonomies' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpTermTaxonomy::class,
			'foreignKey' => WpTermTaxonomy::FIELD_TERM_ID,
			'localKey' => WpTermTaxonomy::FIELD_TERM_ID,
			'methodName' => 'wp_term_taxonomies',
		],
	];
	public function wp_term_taxonomies(): HasMany{
		return $this->hasMany(WpTermTaxonomy::class, WpTermTaxonomy::FIELD_TERM_ID, WpTermTaxonomy::FIELD_TERM_ID);
	}
}
