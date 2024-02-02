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
use App\Models\GlobalVariableRelationship;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\UserVariableRelationship;
use App\Models\SentEmail;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Models\WpTermRelationship;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseWpPost
 * @property int $ID
 * @property int $post_author
 * @property Carbon $post_date
 * @property Carbon $post_date_gmt
 * @property string $post_content
 * @property string $post_title
 * @property string $post_excerpt
 * @property string $post_status
 * @property string $comment_status
 * @property string $ping_status
 * @property string $post_password
 * @property string $post_name
 * @property string $to_ping
 * @property string $pinged
 * @property Carbon $post_modified
 * @property Carbon $post_modified_gmt
 * @property string $post_content_filtered
 * @property int $post_parent
 * @property string $guid
 * @property int $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property int $comment_count
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property int $record_size_in_kb
 * @property \App\Models\User $user
 * @property Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property Collection|Application[] $applications
 * @property Collection|Connection[] $connections
 * @property Collection|Connector[] $connectors
 * @property Collection|UserVariableRelationship[] $user_variable_relationships
 * @property Collection|SentEmail[] $sent_emails
 * @property Collection|\App\Models\SpreadsheetImporter[] $spreadsheet_importers
 * @property Collection|UserVariable[] $user_variables
 * @property Collection|VariableCategory[] $variable_categories
 * @property Collection|Variable[] $variables
 * @property Collection|WpPostmetum[] $wp_postmeta
 * @property Collection|WpTermRelationship[] $wp_term_relationships
 * @property Collection|\App\Models\User[] $users
 * @package App\Models\Base
 * @property-read int|null $global_variable_relationships_count
 * @property-read int|null $applications_count
 * @property-read int|null $connections_count
 * @property-read int|null $connectors_count
 * @property-read int|null $correlations_count
 * @property-read int|null $sent_emails_count
 * @property-read int|null $spreadsheet_importers_count
 * @property-read int|null $user_variables_count
 * @property-read int|null $users_count
 * @property-read int|null $variable_categories_count
 * @property-read int|null $variables_count
 * @property-read int|null $wp_comments_where_comment_post__i_d_count
 * @property-read int|null $wp_postmeta_count
 * @property-read int|null $wp_term_relationships_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPost onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereCommentCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereCommentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereMenuOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePinged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostContentFiltered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostDateGmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostModified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostModifiedGmt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost wherePostType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereRecordSizeInKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereToPing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseWpPost whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPost withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseWpPost withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseWpPost extends BaseModel {
	use SoftDeletes;
	public const FIELD_ID = 'ID';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COMMENT_COUNT = 'comment_count';
	public const FIELD_COMMENT_STATUS = 'comment_status';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_GUID = 'guid';
	public const FIELD_MENU_ORDER = 'menu_order';
	public const FIELD_PING_STATUS = 'ping_status';
	public const FIELD_PINGED = 'pinged';
	public const FIELD_POST_AUTHOR = 'post_author';
	public const FIELD_POST_CONTENT = 'post_content';
	public const FIELD_POST_CONTENT_FILTERED = 'post_content_filtered';
	public const FIELD_POST_DATE = 'post_date';
	public const FIELD_POST_DATE_GMT = 'post_date_gmt';
	public const FIELD_POST_EXCERPT = 'post_excerpt';
	public const FIELD_POST_MIME_TYPE = 'post_mime_type';
	public const FIELD_POST_MODIFIED = 'post_modified';
	public const FIELD_POST_MODIFIED_GMT = 'post_modified_gmt';
	public const FIELD_POST_NAME = 'post_name';
	public const FIELD_POST_PARENT = 'post_parent';
	public const FIELD_POST_PASSWORD = 'post_password';
	public const FIELD_POST_STATUS = 'post_status';
	public const FIELD_POST_TITLE = 'post_title';
	public const FIELD_POST_TYPE = 'post_type';
	public const FIELD_RECORD_SIZE_IN_KB = 'record_size_in_kb';
	public const FIELD_TO_PING = 'to_ping';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'wp_posts';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = 'The posts table is arguably the most important table in the WordPress database. Its name sometimes throws people who believe it purely contains their blog posts. However, albeit badly named, it is an extremely powerful table that stores various types of content including posts, pages, menu items, media attachments and any custom post types that a site uses.';
	protected $primaryKey = 'ID';
	protected $casts = [
        self::FIELD_POST_DATE => 'datetime',
        self::FIELD_POST_DATE_GMT => 'datetime',
        self::FIELD_POST_MODIFIED => 'datetime',
        self::FIELD_POST_MODIFIED_GMT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COMMENT_COUNT => 'int',
		self::FIELD_COMMENT_STATUS => 'string',
		self::FIELD_GUID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_MENU_ORDER => 'int',
		self::FIELD_PINGED => 'string',
		self::FIELD_PING_STATUS => 'string',
		self::FIELD_POST_AUTHOR => 'int',
		self::FIELD_POST_CONTENT => 'string',
		self::FIELD_POST_CONTENT_FILTERED => 'string',
		self::FIELD_POST_EXCERPT => 'string',
		self::FIELD_POST_MIME_TYPE => 'string',
		self::FIELD_POST_NAME => 'string',
		self::FIELD_POST_PARENT => 'int',
		self::FIELD_POST_PASSWORD => 'string',
		self::FIELD_POST_STATUS => 'string',
		self::FIELD_POST_TITLE => 'string',
		self::FIELD_POST_TYPE => 'string',
		self::FIELD_RECORD_SIZE_IN_KB => 'int',
		self::FIELD_TO_PING => 'string',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_COMMENT_COUNT => 'nullable|numeric',
		self::FIELD_COMMENT_STATUS => 'nullable|max:20',
		self::FIELD_GUID => 'nullable|max:255',
		self::FIELD_ID => 'required|numeric|min:0|unique:wp_posts,ID',
		self::FIELD_MENU_ORDER => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_PINGED => 'nullable|max:65535',
		self::FIELD_PING_STATUS => 'nullable|max:20',
		self::FIELD_POST_AUTHOR => 'nullable|numeric|min:0',
		self::FIELD_POST_CONTENT => 'nullable',
		self::FIELD_POST_CONTENT_FILTERED => 'nullable',
		self::FIELD_POST_DATE => 'required|date',
		self::FIELD_POST_DATE_GMT => 'required|date',
		self::FIELD_POST_EXCERPT => 'nullable|max:65535',
		self::FIELD_POST_MIME_TYPE => 'nullable|max:100',
		self::FIELD_POST_MODIFIED => 'required|date',
		self::FIELD_POST_MODIFIED_GMT => 'required|date',
		self::FIELD_POST_NAME => 'nullable|max:200',
		self::FIELD_POST_PARENT => 'nullable|numeric|min:0',
		self::FIELD_POST_PASSWORD => 'nullable|max:255',
		self::FIELD_POST_STATUS => 'nullable|max:20',
		self::FIELD_POST_TITLE => 'nullable|max:65535',
		self::FIELD_POST_TYPE => 'nullable|max:20',
		self::FIELD_RECORD_SIZE_IN_KB => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_TO_PING => 'nullable|max:65535',
	];
	protected $hints = [
		self::FIELD_ID => 'Unique number assigned to each post.',
		self::FIELD_POST_AUTHOR => 'The user ID who created it.',
		self::FIELD_POST_DATE => 'Time and date of creation.',
		self::FIELD_POST_DATE_GMT => 'GMT time and date of creation. The GMT time and date is stored so there is no dependency on a site’s timezone in the future.',
		self::FIELD_POST_CONTENT => 'Holds all the content for the post, including HTML, shortcodes and other content.',
		self::FIELD_POST_TITLE => 'Title of the post.',
		self::FIELD_POST_EXCERPT => 'Custom intro or short version of the content.',
		self::FIELD_POST_STATUS => 'Status of the post, e.g. ‘draft’, ‘pending’, ‘private’, ‘publish’. Also a great WordPress <a href="https://poststatus.com/" target="_blank">news site</a>.',
		self::FIELD_COMMENT_STATUS => 'If comments are allowed.',
		self::FIELD_PING_STATUS => 'If the post allows <a href="http://codex.wordpress.org/Introduction_to_Blogging#Pingbacks" target="_blank">ping and trackbacks</a>.',
		self::FIELD_POST_PASSWORD => 'Optional password used to view the post.',
		self::FIELD_POST_NAME => 'URL friendly slug of the post title.',
		self::FIELD_TO_PING => 'A list of URLs WordPress should send pingbacks to when updated.',
		self::FIELD_PINGED => 'A list of URLs WordPress has sent pingbacks to when updated.',
		self::FIELD_POST_MODIFIED => 'Time and date of last modification.',
		self::FIELD_POST_MODIFIED_GMT => 'GMT time and date of last modification.',
		self::FIELD_POST_CONTENT_FILTERED => 'Used by plugins to cache a version of post_content typically passed through the ‘the_content’ filter. Not used by WordPress core itself.',
		self::FIELD_POST_PARENT => 'Used to create a relationship between this post and another when this post is a revision, attachment or another type.',
		self::FIELD_GUID => 'Global Unique Identifier, the permanent URL to the post, not the permalink version.',
		self::FIELD_MENU_ORDER => 'Holds the display number for pages and other non-post types.',
		self::FIELD_POST_TYPE => 'The content type identifier.',
		self::FIELD_POST_MIME_TYPE => 'Only used for attachments, the MIME type of the uploaded file.',
		self::FIELD_COMMENT_COUNT => 'Total number of comments, pingbacks and trackbacks.',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_RECORD_SIZE_IN_KB => '',
	];
	protected array $relationshipInfo = [
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'post_author',
			'foreignKey' => WpPost::FIELD_POST_AUTHOR,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'post_author',
			'ownerKey' => WpPost::FIELD_POST_AUTHOR,
			'methodName' => 'user',
		],
		'global_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => GlobalVariableRelationship::class,
			'foreignKey' => GlobalVariableRelationship::FIELD_WP_POST_ID,
			'localKey' => GlobalVariableRelationship::FIELD_ID,
			'methodName' => 'global_variable_relationships',
		],
		'applications' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Application::class,
			'foreignKey' => Application::FIELD_WP_POST_ID,
			'localKey' => Application::FIELD_ID,
			'methodName' => 'applications',
		],
		'connections' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connection::class,
			'foreignKey' => Connection::FIELD_WP_POST_ID,
			'localKey' => Connection::FIELD_ID,
			'methodName' => 'connections',
		],
		'connectors' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Connector::class,
			'foreignKey' => Connector::FIELD_WP_POST_ID,
			'localKey' => Connector::FIELD_ID,
			'methodName' => 'connectors',
		],
		'user_variable_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariableRelationship::class,
			'foreignKey' => UserVariableRelationship::FIELD_WP_POST_ID,
			'localKey' => UserVariableRelationship::FIELD_ID,
			'methodName' => 'user_variable_relationships',
		],
		'sent_emails' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => SentEmail::class,
			'foreignKey' => SentEmail::FIELD_WP_POST_ID,
			'localKey' => SentEmail::FIELD_ID,
			'methodName' => 'sent_emails',
		],
		'spreadsheet_importers' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => \App\Models\SpreadsheetImporter::class,
			'foreignKey' => \App\Models\SpreadsheetImporter::FIELD_WP_POST_ID,
			'localKey' => \App\Models\SpreadsheetImporter::FIELD_ID,
			'methodName' => 'spreadsheet_importers',
		],
		'user_variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => UserVariable::class,
			'foreignKey' => UserVariable::FIELD_WP_POST_ID,
			'localKey' => UserVariable::FIELD_ID,
			'methodName' => 'user_variables',
		],
		'variable_categories' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => VariableCategory::class,
			'foreignKey' => VariableCategory::FIELD_WP_POST_ID,
			'localKey' => VariableCategory::FIELD_ID,
			'methodName' => 'variable_categories',
		],
		'variables' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => Variable::class,
			'foreignKey' => Variable::FIELD_WP_POST_ID,
			'localKey' => Variable::FIELD_ID,
			'methodName' => 'variables',
		],
		'wp_postmeta' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpPostmetum::class,
			'foreignKey' => WpPostmetum::FIELD_POST_ID,
			'localKey' => WpPostmetum::FIELD_ID,
			'methodName' => 'wp_postmeta',
		],
		'wp_term_relationships' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => WpTermRelationship::class,
			'foreignKey' => WpTermRelationship::FIELD_OBJECT_ID,
			'localKey' => WpTermRelationship::FIELD_ID,
			'methodName' => 'wp_term_relationships',
		],
		'wp_users' => [
			'relationshipType' => 'HasMany',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKey' => \App\Models\User::FIELD_WP_POST_ID,
			'localKey' => \App\Models\User::FIELD_ID,
			'methodName' => 'wp_users',
		],
	];
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, WpPost::FIELD_POST_AUTHOR, \App\Models\User::FIELD_ID,
			WpPost::FIELD_POST_AUTHOR);
	}
	public function global_variable_relationships(): HasMany{
		return $this->hasMany(GlobalVariableRelationship::class, GlobalVariableRelationship::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function applications(): HasMany{
		return $this->hasMany(Application::class, Application::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function connections(): HasMany{
		return $this->hasMany(Connection::class, Connection::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function connectors(): HasMany{
		return $this->hasMany(Connector::class, Connector::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function correlations(): HasMany{
		return $this->hasMany(UserVariableRelationship::class, UserVariableRelationship::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function sent_emails(): HasMany{
		return $this->hasMany(SentEmail::class, SentEmail::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function spreadsheet_importers(): HasMany{
		return $this->hasMany(\App\Models\SpreadsheetImporter::class, \App\Models\SpreadsheetImporter::FIELD_WP_POST_ID,
			static::FIELD_ID);
	}
	public function user_variables(): HasMany{
		return $this->hasMany(UserVariable::class, UserVariable::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function variable_categories(): HasMany{
		return $this->hasMany(VariableCategory::class, VariableCategory::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function variables(): HasMany{
		return $this->hasMany(Variable::class, Variable::FIELD_WP_POST_ID, static::FIELD_ID);
	}
	public function wp_postmeta(): HasMany{
		return $this->hasMany(WpPostmetum::class, WpPostmetum::FIELD_POST_ID, static::FIELD_ID);
	}
	public function wp_term_relationships(): HasMany{
		return $this->hasMany(WpTermRelationship::class, WpTermRelationship::FIELD_OBJECT_ID, static::FIELD_ID);
	}
	public function users(): HasMany{
		return $this->hasMany(\App\Models\User::class, \App\Models\User::FIELD_WP_POST_ID, static::FIELD_ID);
	}
}
