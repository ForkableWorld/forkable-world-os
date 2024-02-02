<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Models;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Buttons\RelationshipButtons\WpPost\WpPostUserButton;
use App\Charts\BarChartButton;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\ModelValidationException;
use App\Files\MimeContentTypeHelper;
use App\Logging\QMLog;
use App\Models\Base\BaseWpPost;
use App\Models\Cards\PreviewCard;
use App\Properties\Base\BaseImageUrlProperty;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\Base\BasePostTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\WpPost\WpPostPostContentProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Storage\DB\Writable;
use App\Storage\S3\S3Helper;
use App\Traits\HasModel\HasUser;
use App\Traits\LoggerTrait;
use App\Traits\MetaFieldsTrait;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
use App\Utils\UrlHelper;
use App\Variables\QMVariableCategory;
use Corcel\Concerns\AdvancedCustomFields;
use Corcel\Concerns\Aliases;
use Corcel\Concerns\CustomTimestamps;
use Corcel\Concerns\OrderScopes;
use Corcel\Concerns\Shortcodes;
use Corcel\Corcel;
use Corcel\Model\Builder\PostBuilder;
use Corcel\Model\Comment;
use Corcel\Model\Post;
use Corcel\Model\Taxonomy;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\WpPost
 * @property int $ID
 * @property int $post_author
 * @property int $author_id
 * @property int $user_id
 * @property Carbon|null $post_date
 * @property Carbon|null $post_date_gmt
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
 * @property Carbon|null $post_modified
 * @property Carbon|null $post_modified_gmt
 * @property string $post_content_filtered
 * @property int $post_parent
 * @property string $guid
 * @property int $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property int $comment_count
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|WpPost newModelQuery()
 * @method static Builder|WpPost query()
 * @method static Builder|WpPost whereClientId($value)
 * @method static Builder|WpPost whereCommentCount($value)
 * @method static Builder|WpPost whereCommentStatus($value)
 * @method static Builder|WpPost whereCreatedAt($value)
 * @method static Builder|WpPost whereDeletedAt($value)
 * @method static Builder|WpPost whereGuid($value)
 * @method static Builder|WpPost whereID($value)
 * @method static Builder|WpPost whereMenuOrder($value)
 * @method static Builder|WpPost wherePingStatus($value)
 * @method static Builder|WpPost wherePinged($value)
 * @method static Builder|WpPost wherePostAuthor($value)
 * @method static Builder|WpPost wherePostContent($value)
 * @method static Builder|WpPost wherePostContentFiltered($value)
 * @method static Builder|WpPost wherePostDate($value)
 * @method static Builder|WpPost wherePostDateGmt($value)
 * @method static Builder|WpPost wherePostExcerpt($value)
 * @method static Builder|WpPost wherePostMimeType($value)
 * @method static Builder|WpPost wherePostModified($value)
 * @method static Builder|WpPost wherePostModifiedGmt($value)
 * @method static Builder|WpPost wherePostName($value)
 * @method static Builder|WpPost wherePostParent($value)
 * @method static Builder|WpPost wherePostPassword($value)
 * @method static Builder|WpPost wherePostStatus($value)
 * @method static Builder|WpPost wherePostTitle($value)
 * @method static Builder|WpPost wherePostType($value)
 * @method static Builder|WpPost whereToPing($value)
 * @method static Builder|WpPost whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read User $user
 * @property-read Collection|WpComment[] $wp_comments
 * @property-read int|null $wp_comments_count
 * @property-read Collection|WpPostmetum[] $wp_postmeta
 * @property-read int|null $wp_postmeta_count
 * @property-read Collection|GlobalVariableRelationship[] $global_variable_relationships
 * @property-read int|null $global_variable_relationships_count
 * @property-read Collection|Application[] $applications
 * @property-read int|null $applications_count
 * @property-read Collection|Connection[] $connections
 * @property-read int|null $connections_count
 * @property-read Collection|Connector[] $connectors
 * @property-read int|null $connectors_count
 * @property-read Collection|UserVariableRelationship[] $user_variable_relationships
 * @property-read int|null $correlations_count
 * @property-read Collection|SentEmail[] $sent_emails
 * @property-read int|null $sent_emails_count
 * @property-read Collection|UserVariable[] $user_variables
 * @property-read int|null $user_variables_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property-read Collection|VariableCategory[] $variable_categories
 * @property-read int|null $variable_categories_count
 * @property-read Collection|Variable[] $variables
 * @property-read int|null $variables_count
 * @property-read Collection|WpTermRelationship[] $wp_term_relationships
 * @property-read int|null $wp_term_relationships_count
 * @property-read Collection|Post[] $attachment
 * @property-read int|null $attachment_count
 * @property-read \Corcel\Model\User|null $author
 * @property-read Collection|Post[] $children
 * @property-read int|null $children_count
 * @property-read Collection|Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read Collection|WpPostmetum[] $fields
 * @property-read int|null $fields_count
 * @property-read string $content
 * @property-read string $excerpt
 * @property-read string $image
 * @property-read array $keywords
 * @property-read string $keywords_str
 * @property-read string $main_category_slug
 * @property-read array $terms
 * @property-read Collection|WpPostmetum[] $meta
 * @property-read int|null $meta_count
 * @property-read Post|null $parent
 * @property-read Collection|Post[] $revision
 * @property-read int|null $revision_count
 * @property-read Collection|Taxonomy[] $taxonomies
 * @property-read int|null $taxonomies_count
 * @method static Builder|WpPost hasMeta($meta, $value = null, $operator = '=')
 * @method static Builder|WpPost hasMetaLike($meta, $value = null)
 * @method static PostBuilder|WpPost newQuery()
 * @method static Builder|WpPost newest()
 * @method static Builder|WpPost oldest()
 * @property-read WpTerm $category_term
 * @property-read WpTerm $parent_category_term
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property-read string $main_category_name
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read Collection|WpComment[] $wp_comments_where_comment_post__i_d
 * @property-read int|null $wp_comments_where_comment_post__i_d_count

 * @property int|null $record_size_in_kb
 * @property-read Collection|SpreadsheetImporter[] $spreadsheet_importers
 * @property-read int|null $spreadsheet_importers_count
 * @method static Builder|WpPost whereRecordSizeInKb($value)
 * @property mixed $raw
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class WpPost extends BaseWpPost {
    use HasFactory;

	public const CLASS_DESCRIPTION = 'Published studies at the Journal of Citizen Science. ';
	public const FONT_AWESOME = FontAwesome::WORDPRESS;
	use Aliases;
	use AdvancedCustomFields;
	use MetaFieldsTrait;
	use Shortcodes;
	use OrderScopes;
	use CustomTimestamps;
	use LoggerTrait;
	use HasUser;
	//public const META_FEATURED_IMAGE_URL = "fifu_image_url";
	protected static $parentIds;
	public const CATEGORY_COHORT_GROUP_STUDIES = "Cohort Group Studies";
	public const CATEGORY_COHORT_GROUP_VARIABLE_OVERVIEWS = "Cohort Group Variable Overviews";
	public const CATEGORY_GLOBAL_POPULATION_STUDIES = "Global Population Studies";
	public const CATEGORY_GLOBAL_POPULATION_VARIABLE_OVERVIEWS = "Global Population Variable Overviews";
	public const CATEGORY_GRADE_REPORTS = "Grade Reports";
	public const CATEGORY_INDIVIDUAL_CASE_STUDIES = "Individual Case Studies";
	public const CATEGORY_INDIVIDUAL_DATA_OVERVIEWS = "Individual Data Overviews";
	public const CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS = "Individual Participant Variable Overviews";
	public const CATEGORY_PATIENT_OVERVIEW_REPORTS = "Patient Overview Reports";
	public const CATEGORY_ROOT_CAUSE_ANALYSES_REPORTS = "Root Cause Analyses";
	public const CATEGORY_SCIENTISTS = "Scientists";
	public const CATEGORY_TIME_IS_MONEY_REPORTS = "Time is Money Reports";
	public const COMMENT_STATUS_CLOSED = "closed";
	public const COMMENT_STATUS_OPEN = "open";
	public const CREATED_AT = 'post_date';
	public const DEFAULT_IMAGE = ImageUrls::CROWD_SOURCING_UTOPIA_BRAIN_ICON;
	public const META_FEATURED_IMAGE_ALT = "fifu_image_alt";
	public const META_FEATURED_IMAGE_URL = "_knawatfibu_url";
	public const META_THUMBNAIL_ID = "_thumbnail_id";
	public const PARENT_CATEGORY_HUMANS = "Humans";
	public const PARENT_CATEGORY_REPORTS = "Reports";
	public const PARENT_CATEGORY_STUDIES = "Studies";
	public const PARENT_CATEGORY_VARIABLE_OVERVIEWS = "Variable Overviews";
	// Revisions that WordPress saves automatically while you are editing.
	// Incomplete post viewable by anyone with proper user role.
	// Scheduled to be published in a future date.
	// Used with a child post (such as Attachments and Revisions) to determine the actual status from the parent post.
	// Awaiting a user with the publish_posts capability (typically a user assigned the Editor role) to publish.
	// Viewable only to WordPress users at Administrator level.
	// Viewable by everyone.
	// Posts in the Trash
	public const UPDATED_AT = 'post_modified';
	public const MINIMUM_POST_CONTENT_LENGTH = 280;
	public const LARGE_FIELDS = [
		self::FIELD_POST_CONTENT,
		self::FIELD_POST_CONTENT_FILTERED,
	];
	public const CATEGORY_STRUCTURE = [
		self::PARENT_CATEGORY_REPORTS => [
			self::CATEGORY_INDIVIDUAL_DATA_OVERVIEWS,
			self::CATEGORY_TIME_IS_MONEY_REPORTS,
			self::CATEGORY_ROOT_CAUSE_ANALYSES_REPORTS,
			self::CATEGORY_GRADE_REPORTS,
			self::CATEGORY_INDIVIDUAL_DATA_OVERVIEWS,
		],
		self::PARENT_CATEGORY_VARIABLE_OVERVIEWS => [
			self::CATEGORY_GLOBAL_POPULATION_VARIABLE_OVERVIEWS,
			self::CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS,
			self::CATEGORY_COHORT_GROUP_VARIABLE_OVERVIEWS,
		],
		self::PARENT_CATEGORY_STUDIES => [
			self::CATEGORY_INDIVIDUAL_CASE_STUDIES,
			self::CATEGORY_COHORT_GROUP_STUDIES,
			self::CATEGORY_GLOBAL_POPULATION_STUDIES,
		],
		self::PARENT_CATEGORY_HUMANS => [
			self::CATEGORY_SCIENTISTS,
		],
	];
	protected array $rules = [
		self::FIELD_POST_CONTENT => 'required|string|min:' . self::MINIMUM_POST_CONTENT_LENGTH . '.|max:2147483647',
		self::FIELD_POST_DATE => 'required|date|after_or_equal:2000-01-01',
		self::FIELD_POST_DATE_GMT => 'required|date|after_or_equal:2000-01-01',
		//self::FIELD_POST_MODIFIED => 'required|date|after_or_equal:yesterday',
		//self::FIELD_POST_MODIFIED_GMT => 'required|date|after_or_equal:yesterday',
		self::FIELD_POST_MODIFIED => 'required|date',
		self::FIELD_POST_MODIFIED_GMT => 'required|date',
		self::FIELD_POST_NAME => 'required|string|max:200|min:3',
		self::FIELD_POST_TITLE => 'required|string|min:3',
		// self::FIELD_ID => 'required|numeric|min:1', //|unique:wp_posts,ID', // Unique checks too slow
		self::FIELD_POST_AUTHOR => 'required|numeric|min:1',
		self::FIELD_POST_EXCERPT => 'nullable|min:10|max:65535',
		self::FIELD_POST_STATUS => 'required|in:publish,future,draft,pending,private',
		self::FIELD_COMMENT_STATUS => 'nullable|max:20',
		self::FIELD_PING_STATUS => 'nullable|max:20',
		self::FIELD_POST_PASSWORD => 'nullable|max:255',
		self::FIELD_TO_PING => 'nullable|max:65535',
		self::FIELD_PINGED => 'nullable|max:65535',
		self::FIELD_POST_CONTENT_FILTERED => 'string|nullable',
		self::FIELD_POST_PARENT => 'nullable|numeric|min:0',
		self::FIELD_GUID => 'required|max:255',
		self::FIELD_MENU_ORDER => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_POST_TYPE => 'required|string|min:3',
		self::FIELD_POST_MIME_TYPE => 'required|string|min:3',
		self::FIELD_COMMENT_COUNT => 'nullable|numeric',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];
	protected $hidden = [
		self::FIELD_POST_PASSWORD,
	];
	/**
	 * @var array
	 */
	protected static $postTypes = [];
	/**
	 * @var array
	 */
	protected static $aliases = [
		'title' => 'post_title',
		'content' => 'post_content',
		'excerpt' => 'post_excerpt',
		'slug' => 'post_name',
		'type' => 'post_type',
		'mime_type' => 'post_mime_type',
		'url' => 'guid',
		'author_id' => 'post_author',
		'user_id' => 'post_author',
		'parent_id' => 'post_parent',
		'created_at' => 'post_date',
		'updated_at' => 'post_modified',
		'status' => 'post_status',
	];
	/**
	 * @var array
	 */
	protected $appends = [
		'title',
		'slug',
		'content',
		'type',
		'mime_type',
		'url',
		'author_id',
		'parent_id',
		'created_at',
		'updated_at',
		'excerpt',
		'status',
		'image',
		'terms',
		'main_category',
		'keywords',
		'keywords_str',
	];
	public const STUPID_CATEGORY_NAMES = [
		"Study Reports" => self::PARENT_CATEGORY_STUDIES,
		"StudyReports" => self::PARENT_CATEGORY_STUDIES,
		"GlobalVariableRelationships" => self::CATEGORY_GLOBAL_POPULATION_STUDIES,
		"ColumnCharts" => null,
		"Models" => null,
		"GradeReports" => self::CATEGORY_GRADE_REPORTS,
		"UserVariables" => self::CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS,
		"UserVariableRelationships" => self::CATEGORY_INDIVIDUAL_CASE_STUDIES,
	];
	/**
	 * @param string $name
	 * @param string $parent
	 */
	public static function validateCategoryName(string $name, string $parent): void{
		$structure = self::CATEGORY_STRUCTURE;
		$parents = array_keys($structure);
		if(!in_array($parent, $parents)){
			le("$parent is not a valid parent category name.  Select one from " . \App\Logging\QMLog::print_r($parents, true));
		}
		$kids = $structure[$parent];
		if($kids === QMVariableCategory::class){
			$kids = QMVariableCategory::getVariableCategoryNames();
		}
		if(!in_array($name, $kids)){
			le("$name is not a valid category name for parent category $parent.  Select one from " .
				\App\Logging\QMLog::print_r($kids, true));
		}
		foreach(self::STUPID_CATEGORY_NAMES as $stupid => $good){
			if(stripos($name, $stupid) !== false){
				le("Category name cannot contain $stupid but is $name");
			}
		}
	}
	public static function generatePostUrl(string $slug): string{
		return QMWordPressApi::getPostUrlBySlug($slug);
	}
	public static function getSiteUrl(): string{
		return QMWordPressApi::getSiteUrl();
	}
	public static function userStudyPosts(): Builder{
		return static::whereCategory(self::CATEGORY_INDIVIDUAL_CASE_STUDIES);
	}
	/**
	 * @return User
	 */
	public function getUser(): User{
		return User::findInMemoryOrDB($this->post_author);
	}
	/**
	 * @return QMUser
	 */
	public function getQMUser(): QMUser{
		return QMUser::find($this->post_author);
	}
	public function setPostCreationDateIfEmpty(){
		if(!$this->post_date_gmt){
			$this->post_date_gmt = $this->post_date = now_at();
		}
	}
	/**
	 * @return Collection|self[]
	 */
	public static function getUncategorized(): Collection {
		$term = WpTerm::uncategorized();
		$posts = $term->posts()->get();
		return $posts;
	}
	public static function whereCategory(string $name): Builder{
		$category = self::findCategory($name);
		$postIds = $category->wp_term_relationships()->pluck(WpTermRelationship::FIELD_OBJECT_ID);
		$qb = static::query()->whereIn(static::FIELD_ID, $postIds);
		return $qb;
	}
	public static function userVariablePosts(): Builder{
		return static::whereCategory(self::CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS);
	}
	public static function populationStudyPosts(): Builder{
		return static::whereCategory(self::CATEGORY_GLOBAL_POPULATION_STUDIES);
	}
	public static function deleteUserVariableUserStudyPopulationStudyPosts(): void{
		$num = WpPost::userStudyPosts()->count();
		QMLog::info("Deleting $num user study posts...");
		WpPost::userStudyPosts()->forceDelete();
		$num = WpPost::userVariablePosts()->count();
		QMLog::info("Deleting $num user variable posts...");
		WpPost::userVariablePosts()->forceDelete();
		$num = WpPost::populationStudyPosts()->count();
		QMLog::info("Deleting $num user variable posts...");
		WpPost::populationStudyPosts()->forceDelete();
	}
	public static function getCategoryCounts(): array{
		return WpTermTaxonomy::getCategoryCounts();
	}
	public static function findCategory(string $name): WpTermTaxonomy{
		$slug = Str::slug($name);
		$term = WpTerm::whereSlug($slug)->first();
		return WpTermTaxonomy::where(WpTermTaxonomy::FIELD_TAXONOMY, 'category')
			->where(WpTermTaxonomy::FIELD_TERM_ID, $term->term_id)->first();
	}
	public static function registerEventListeners(): void{
		static::deleting(function($post){ // before delete() method call this
			/** @var WpPost $post */
			$post->beforeDelete();
		});
		static::saving(function($post){ // before save() method call this
			/** @var WpPost $post */
			$post->beforeSave();
		});
	}
	/**
	 * @param string $slug
	 * @return WpPost
	 */
	public static function firstOrNewWhereSlug(string $slug): WpPost{
		$post = self::firstOrNew([self::FIELD_POST_NAME => $slug]);
		if(!$post->post_date){
			$post->setDates();
		}
		return $post;
	}
	/**
	 * @param string $slug
	 * @return WpPost
	 */
	public static function firstWhereSlug(string $slug): ?WpPost{
		$post = self::wherePostName($slug)->first();
		return $post;
	}
	/**
	 * @param string $url
	 * @return WpPost
	 */
	public static function firstByUrl(string $url): ?self{
		return self::whereGuid($url)->first();
	}
	/**
	 * @return string
	 */
	public function getGuidOrWpUrlWithPostName(): string{
		if($this->guid){
			return $this->guid;
		}
		return $this->getWpUrlWithPostName();
	}
	// this is a recommended way to declare event handlers
	public static function boot(){
		parent::boot();
		self::registerEventListeners();
	}
	public static function deleteAllPosts(){
		$instances = self::wherePostType('post')->get();
		foreach($instances as $instance){
			try {
				$instance->delete();
			} catch (Exception $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->post_title . "(" . $this->post_name . ") ";
	}
	/**
	 * @param string $name
	 * @return WpPost|Builder
	 */
	public static function findWhereName(string $name): ?WpPost{
		$slug = QMStr::slugify($name);
		return static::wherePostName($slug)->first();
	}
	/**
	 * @param string $url
	 * @return static
	 */
	public static function firstOrNewByUrl(string $url): WpPost{
		$s3FilePath = S3Helper::getS3FilePathFromUrl($url);
		$slug = QMStr::slugify($s3FilePath);
		$p = static::wherePostName($slug)->first();
		if($p){
			return $p;
		}
		$p = new static();
		$p->post_name = $slug;
		$p->post_title = QMStr::urlToTitle($url);
		$p->guid = $url;
		$userId = S3Helper::getUserIdFromS3Path($s3FilePath);
		if($userId){
			$p->post_author = $userId;
			//$p->post_password = QMUser::getById($userId)->getEncryptedPasswordHash();
			$p->post_status = BasePostStatusProperty::STATUS_PRIVATE;
		} else{
			$p->post_author = UserIdProperty::USER_ID_SYSTEM;
			$p->post_status = BasePostStatusProperty::STATUS_PUBLISH;
		}
		$p->setDates();
		return $p;
	}
	protected function beforeSave(){
		$this->setDates();
		// Observer should already do this, I think? $this->isValidOrFail();
		if(strlen($this->post_content) > 100 * 1024 && stripos($this->post_name, 'user') !== false &&
			$this->post_status !== BasePostStatusProperty::STATUS_PUBLISH){
			le("We should not save large private user posts!");
		}
	}
	protected function beforeDelete(){
		QMLog::info("Deleting " . $this->post_name);
		/** @var WpPost $post */
		$this->meta()->delete();
	}
	public function setDates(): void{
		if(!$this->post_date){
			$this->post_date_gmt = $this->post_date = now_at();
		}
		try {
			TimeHelper::universalConversionToUnixTimestamp($this->post_date->toString());
		} catch (InvalidTimestampException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			$this->post_date_gmt = $this->post_date = now_at();
		}
		$this->setModifiedDates();
	}
	/**
	 * @param string $url
	 * @return int
	 */
	protected static function getParentSlugFromUrl(string $url): int{
		if(isset(self::$parentIds[$url])){
			return self::$parentIds[$url];
		}
		$arr = explode('/', $url);
		$parentPostName = $arr[count($arr) - 2];
		return $parentPostName;
	}
	/**
	 * @param string $url
	 * @param string $content
	 * @param string|null $excerpt
	 * @return WpPost
	 * @throws ModelValidationException
	 */
	public static function firstOrCreateByUrl(string $url, string $content, string $excerpt = null): WpPost{
		$p = self::firstOrNewByUrl($url);
		$p->post_title = QMStr::urlToTitle($url);
		if(stripos($url, '.pdf') === false){
			$imageType = UrlHelper::getImageTypeFromUrl($url);
			if($imageType){
				$content = ImageHelper::imageDataToHtml($imageType, $content, $p->post_title, $p->post_title,
					$p->post_name . "-" . $imageType);
			}
			$content = self::addCssAndCustomHtmlBlockTags($content);
		}
		if($p->post_content === $content){
			return $p;
		}
		//$p->post_parent = self::getParentIdFromUrl($url);
		$p->post_content = $content;
		$userId = S3Helper::getUserIdFromS3Path($url);
		if(!$userId){
			$userId = UserIdProperty::USER_ID_SYSTEM;
		}
		$p->post_author = $userId;
		$p->post_mime_type = MimeContentTypeHelper::mimetype_from_extension($url);
		$p->post_type = BasePostTypeProperty::TYPE_POST;
		$p->guid = $url;
		if($excerpt){
			$p->post_excerpt = $excerpt;
		}
		$categoryName = S3Helper::getCategoryNameFromS3Path($url);
		$p->validateAndSave($categoryName);
		return $p;
	}
	/**
	 * @param string $categoryName
	 * @param string|null $categoryDescription
	 * @param string|null $parentCategoryName
	 * @throws InvalidAttributeException
	 * @throws ModelValidationException
	 */
	public function validateAndSave(string $categoryName, string $categoryDescription = null,
		string $parentCategoryName = null){
		$this->setDates();
		$this->validate();
		$this->saveOrFail();
		$this->logPostUrl("Saved:");
		$this->addCategory($categoryName, $categoryDescription, $parentCategoryName);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function validateTitle(){
		$this->validateAttribute(self::FIELD_POST_TITLE);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function validatePostName(){
		$this->validateAttribute(self::FIELD_POST_NAME);
	}
	/**
	 * @param string|null $title
	 * @return string
	 */
	public function getWpLink(string $title = null): string{
		if(!$title){
			$title = $this->getTitleAttribute();
		}
		return HtmlHelper::getLinkAnchorHtml($title, UrlHelper::getWordpressPostUrl($this->post_name));
	}
	public function getNameAttribute(): string{
		return $this->post_title ?? static::getClassNameTitle();
	}
	/**
	 * @param string $html
	 * @return string
	 */
	public static function htmlBlock(string $html): string{
		if(strpos($html, "\n<!-- wp:html") !== false){
			return $html;
		}
		return "\n<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->\n";
	}
	public function setModifiedDates(){
		$this->post_modified = $this->post_modified_gmt = now_at();
	}
	/**
	 * @param string $name
	 * @param string|null $description
	 * @param string|null $parentCategoryName
	 * @return WpTermRelationship
	 */
	public function addCategory(string $name, string $description = null,
		string $parentCategoryName = null): WpTermRelationship{
		$name = Str::plural($name);
		$taxonomy = self::firstOrCreateWpPostCategory($name, $description, $parentCategoryName);
		$category = WpTermRelationship::firstOrCreate([
			WpTermRelationship::FIELD_OBJECT_ID => $this->ID,
			WpTermRelationship::FIELD_TERM_TAXONOMY_ID => $taxonomy->term_taxonomy_id,
		]);
		$this->refresh();
		return $category;
	}
	/**
	 * @param string $name
	 * @return mixed|void|null
	 */
	public function removeCategory(string $name){
		$term = self::getCategoryTermIfExists($name);
		if(!$term){
			return null;
		}
		$taxonomy = $term->wp_term_taxonomies->first();
		if(!$taxonomy){
			return null;
		}
		$result = WpTermRelationship::where([
			WpTermRelationship::FIELD_OBJECT_ID => $this->ID,
			WpTermRelationship::FIELD_TERM_TAXONOMY_ID => $taxonomy->term_taxonomy_id,
		])->forceDelete();
		$this->refresh();
		return $result;
	}
	/**
	 * @param string $name
	 * @param string|null $description
	 * @param string|null $parentCategoryName
	 * @return WpTermTaxonomy
	 */
	public static function firstOrCreateWpPostCategory(string $name, string $description = null,
		string $parentCategoryName = null): WpTermTaxonomy{
		self::validateCategoryName($name, $parentCategoryName);
		$slug = Str::slug($name);
		$term = WpTerm::firstOrCreate([
			WpTerm::FIELD_NAME => $name,
			WpTerm::FIELD_SLUG => $slug,
		]);
		$taxonomyData = [
			WpTermTaxonomy::FIELD_TAXONOMY => WpTermTaxonomy::TAXONOMY_CATEGORY,
			WpTermTaxonomy::FIELD_TERM_ID => $term->term_id,
		];
		if($description){
			$taxonomyData[WpTermTaxonomy::FIELD_DESCRIPTION] = $description;
		}
		if($parentCategoryName){
			$parentTerm = WpTerm::firstOrCreate([
				WpTerm::FIELD_NAME => $parentCategoryName,
				WpTerm::FIELD_SLUG => Str::slug($parentCategoryName),
			]);
			$taxonomyData[WpTermTaxonomy::FIELD_PARENT] = $parentTerm->term_id;
		}
		return WpTermTaxonomy::updateOrCreate([WpTermTaxonomy::FIELD_TERM_ID => $term->term_id], $taxonomyData);
	}
	/**
	 * @param string $name
	 * @return WpTerm
	 */
	public static function getCategoryTermIfExists(string $name): ?WpTerm{
		return WpTerm::whereName($name)->first();
	}
	/**
	 * @return Taxonomy[]|Collection
	 */
	public function getCategories(){
		return $this->taxonomies;
	}
	/**
	 * @param string $categoryName
	 * @return bool
	 */
	public function hasCategory(string $categoryName): bool{
		return $this->hasTerm(WpTermTaxonomy::TAXONOMY_CATEGORY, $categoryName);
	}
	/**
	 * Gets the first term of the first taxonomy found.
	 * @return string
	 */
	public function getMainCategorySlugAttribute(): string{
		$mainCategorySlug = 'uncategorized';
		if(!empty($this->terms)){
			$taxonomies = array_values($this->terms);
			if(!empty($taxonomies[0])){
				$terms = array_keys($taxonomies[0]);
				$mainCategorySlug = $terms[0];
			}
		}
		return $mainCategorySlug;
	}
	/**
	 * Gets the first term of the first taxonomy found.
	 * @return WpTerm
	 */
	public function getCategoryTermAttribute(): WpTerm{
		return $this->getCategoryTerm();
	}
	/**
	 * Gets the first term of the first taxonomy found.
	 * @return WpTerm
	 */
	public function getParentCategoryTermAttribute(): WpTerm{
		return $this->getParentCategoryTerm();
	}
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		$meta['title'] = QMStr::truncate($this->post_title, 45);
		$userName = $this->user->display_name;
		if(empty($userName)){
			le("No user name!");
		}
		$meta['user'] = $userName;
		$meta['slug'] = QMStr::truncate($this->post_name, 45);
		$meta['main category'] = QMStr::truncate($this->main_category_name, 15);
		return $meta;
	}
	public function getRectangleButtonHtml(string $text = null): string{
		if(!$text){
			$text = $this->post_title;
		}
		$url = $this->getGuidOrWpUrlWithPostName();
		$button = $this->getAstralButton();
		$button->setTextAndTitle($text);
		$button->setUrl($url);
		return $button->getRectangleWPButton();
	}
	public function getRoundedImageButtonHtml(string $text = null, string $image = null): string{
		if(!$text){
			$text = $this->post_title;
		}
		if(!$image){
			$image = $this->image;
		}
		$url = $this->getGuidOrWpUrlWithPostName();
		return BarChartButton::generateHtml($text, $url, $image, $this->post_excerpt);
	}
	/**
	 * @return string
	 */
	public function getTitleHtml(): string{
		$the_title = $this->post_title;
		$titleHtml = "<h1 class=\"entry-title\">$the_title</h1>";
		return $titleHtml;
	}
	public function logPostUrl(string $prefix): void{
		\App\Logging\ConsoleLog::info($prefix);
		QMLog::logLocalLinkButton($this->getWpUrlWithPostName(), $this->post_title);
	}
	protected function validateCategory(): void{
		$category = $this->main_category_name;
		if(!$category || $category === "Uncategorized"){
			le("main_category_name is required!");
		}
	}
	/**
	 * @param array $attributes
	 * @return array
	 */
	protected function getPostInstance(array $attributes){
		$class = static::class;
		// Check if it should be instantiated with a custom post type class
		if(isset($attributes['post_type']) && $attributes['post_type']){
			if(isset(static::$postTypes[$attributes['post_type']])){
				$class = static::$postTypes[$attributes['post_type']];
			} elseif(Corcel::isLaravel()){
				$postTypes = config('corcel.post_types');
				if(is_array($postTypes) && isset($postTypes[$attributes['post_type']])){
					$class = $postTypes[$attributes['post_type']];
				}
			}
		}
		return new $class();
	}
	/**
	 * @param \Illuminate\Database\Query\Builder $query
	 * @return PostBuilder
	 */
	public function newEloquentBuilder($query): PostBuilder{
		return new PostBuilder($query);
	}
	/**
	 * @return BelongsToMany
	 */
	public function taxonomies(): BelongsToMany{
		return $this->belongsToMany(Taxonomy::class, 'term_relationships', 'object_id', 'term_taxonomy_id');
	}
	/**
	 * @return HasMany
	 */
	public function comments(): HasMany{
		return $this->hasMany(Comment::class, 'comment_post_ID');
	}
	/**
	 * @return BelongsTo
	 */
	public function author(): BelongsTo{
		return $this->belongsTo(\Corcel\Model\User::class, 'post_author');
	}
	/**
	 * @return BelongsTo
	 */
	public function parent(): BelongsTo{
		return $this->belongsTo(Post::class, 'post_parent');
	}
	/**
	 * @return HasMany
	 */
	public function children(): HasMany{
		return $this->hasMany(Post::class, 'post_parent');
	}
	/**
	 * @return HasMany
	 */
	public function attachment(): HasMany{
		return $this->hasMany(Post::class, 'post_parent')->where('post_type', 'attachment');
	}
	/**
	 * @return HasMany
	 */
	public function revision(): HasMany{
		return $this->hasMany(Post::class, 'post_parent')->where('post_type', 'revision');
	}
	/**
	 * Whether the post contains the term or not.
	 * @param string $taxonomy
	 * @param string $term
	 * @return bool
	 */
	public function hasTerm(string $taxonomy, string $term): bool{
		return isset($this->terms[$taxonomy]) && isset($this->terms[$taxonomy][$term]);
	}
	/**
	 * @return string
	 */
	public function getContentAttribute(): string{
		return $this->stripShortcodes($this->post_content);
	}
	/**
	 * @return string
	 */
	public function getExcerptAttribute(): string{
		return $this->stripShortcodes($this->post_excerpt);
	}
	/**
	 * Gets the featured image if any
	 * @return string
	 */
	public function getImageAttribute(): string{
		$url = $this->getMeta(self::META_FEATURED_IMAGE_URL);
		if($url){
			return $url;
		}
		return static::DEFAULT_IMAGE;
	}
	/**
	 * Gets all the terms arranged taxonomy => terms[].
	 * @return array
	 */
	public function getTermsAttribute(): array{
		return $this->taxonomies->groupBy(fn($taxonomy) => $taxonomy->taxonomy == 'post_tag' ? 'tag' : $taxonomy->taxonomy)->map(fn(Collection $group) => $group->mapWithKeys(fn($item) => [$item->term->slug => $item->term->name]))->toArray();
	}
	/**
	 * Gets the first term of the first taxonomy found.
	 * @return string
	 */
	public function getMainCategoryNameAttribute(): string{
		$mainCategory = 'Uncategorized';
		if(!empty($this->terms)){
			$taxonomies = array_values($this->terms);
			if(!empty($taxonomies[0])){
				$terms = array_values($taxonomies[0]);
				$mainCategory = $terms[0];
			}
		}
		if(empty($mainCategory)){
			le("No main category!");
		}
		return $mainCategory;
	}
	/**
	 * Gets the keywords as array.
	 * @return array
	 */
	public function getKeywordsAttribute(): array{
		return collect($this->terms)->map(function($taxonomy){
			return collect($taxonomy)->values();
		})->collapse()->toArray();
	}
	/**
	 * Gets the keywords as string.
	 * @return string
	 */
	public function getKeywordsStrAttribute(): string{
		return implode(',', $this->keywords);
	}
	/**
	 * @param string $name The post type slug
	 * @param string $class The class to be instantiated
	 */
	public static function registerPostType(string $name, string $class){
		static::$postTypes[$name] = $class;
	}
	/**
	 * Clears any registered post types.
	 */
	public static function clearRegisteredPostTypes(){
		static::$postTypes = [];
	}
	/**
	 * Get the post format, like the WP get_post_format() function.
	 * @return bool|string
	 */
	public function getFormat(){
		$taxonomy = $this->taxonomies()->where('taxonomy', 'post_format')->first();
		if($taxonomy && $taxonomy->term){
			return str_replace('post-format-', '', $taxonomy->term->slug);
		}
		return false;
	}
	/**
	 * Set the user's first name.
	 * @param string $value
	 * @return void
	 */
	public function setPostTitleAttribute(string $value){
		$value = QMStr::titleCaseSlow($value);
		$this->attributes[self::FIELD_POST_TITLE] = $value;
	}
	/**
	 * @param string $value
	 * @return void
	 */
	public function setPostContentAttribute(string $value){
		$value = self::addCssAndCustomHtmlBlockTags($value);
		$this->attributes[self::FIELD_POST_CONTENT] = $value;
	}
	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setPostContentFilteredAttribute($value){
		if($value){
			le("Don't use post_content_filtered because WP always deletes it");
		}
		$this->attributes[self::FIELD_POST_CONTENT_FILTERED] = $value;
	}
	/**
	 * @param string $pattern
	 * @return int
	 */
	public static function forceDeleteWherePostNameLike(string $pattern): int{
		return static::forceDeleteWhereLike(self::FIELD_POST_NAME, $pattern);
	}
	/**
	 * @param string $pattern
	 * @return Builder
	 */
	public static function wherePostNameLike(string $pattern): Builder{
		return static::whereLike(self::FIELD_POST_NAME, $pattern);
	}
	/**
	 * @return PreviewCard
	 */
	public function getPreviewCard(): PreviewCard{
		$c = new PreviewCard();
		try {
			$this->validateTitle();
			$c->title = $this->post_title;
			$this->validateAttribute(self::FIELD_GUID);
		} catch (InvalidAttributeException $e) {
			le($e);
		}
		$c->link = $this->guid;
		try {
			$this->validateImage();
		} catch (InvalidStringException $e) {
			le($e);
		}
		$c->image = $this->image;
		$c->content = $this->excerpt;
		return $c;
	}
	/**
	 * @param string $new
	 * @param string $old
	 */
	public function replaceCategory(string $new, string $old): void{
		$this->logInfo("Changing category for $old to $new...");
		$this->removeCategory($old);
		$this->addCategory($new);
	}
	/**
	 * @return string
	 */
	public function getParentCategoryName(): ?string{
		return $this->getParentCategoryTerm()->name;
	}
	/**
	 * @return string
	 */
	public function getCategoryName(): string{
		return $this->getCategoryTerm()->name;
	}
	/**
	 * @return WpTerm|Collection|Model
	 */
	private function getParentCategoryTerm(){
		$tt = $this->getCategoryTermTaxonomy();
		$parentId = $tt->parent;
		return WpTerm::findInMemoryOrDB($parentId);
	}
	/**
	 * @return WpTerm|Collection|Model
	 */
	private function getCategoryTerm(): WpTerm{
		$tt = $this->getCategoryTermTaxonomy();
		$id = $tt->term_id;
		return WpTerm::findInMemoryOrDB($id);
	}
	/**
	 * @return WpTermTaxonomy
	 */
	private function getCategoryTermTaxonomy(): WpTermTaxonomy{
		return $this->getCategoryTermTaxonomies()->first();
	}
	/**
	 * @return Collection
	 */
	private function getCategoryTermTaxonomies(): Collection{
		return $this->taxonomies->where(WpTermTaxonomy::FIELD_TAXONOMY, WpTermTaxonomy::TAXONOMY_CATEGORY);
	}
	/**
	 * @param string $html
	 * @return string
	 */
	public static function addCssAndCustomHtmlBlockTags(string $html): string{
		//$html = HtmlHelper::addGlobalCssAsLinkTags($html);
		//$html = HtmlHelper::addGlobalCssAsEmbeddedStyleTags($html);
		//$html = BarChartTableRowForEmail::convertToWpHtml($html);
		return WpPost::htmlBlock($html);
	}
	/**
	 * @param string $imageUrl
	 * @param string $alt
	 * @return bool
	 * Requires "wpackagist-plugin/featured-image-from-url"
	 * @throws InvalidStringException
	 */
	public function addFeaturedImageFromUrl(string $imageUrl, string $alt): bool{
		BaseImageUrlProperty::assertIsImageUrl($imageUrl, "featured image");
		return $this->saveMeta([
			self::META_FEATURED_IMAGE_ALT => $alt,
			self::META_FEATURED_IMAGE_URL => $imageUrl,
			self::META_THUMBNAIL_ID => 0,
			// I guess this indicates that there's no post ID for the thumbnail because it's an external image
		]);
	}
	/**
	 * @param string $reason
	 * @return bool|null
	 */
	public function hardDeleteWithRelations(string $reason): ?bool{
		$this->logError(__FUNCTION__ . " because $reason");
		WpPostmetum::wherePostId($this->getId())->forceDelete();
		return $this->forceDelete();
	}
	/**
	 * @throws InvalidStringException
	 */
	protected function validateImage(): void{
		$image = $this->image;
		if(!$image){
			le("No image on $this");
		}
		BaseImageUrlProperty::assertIsImageUrl($image, "featured image");
	}
	/**
	 * @param string $status
	 * @throws InvalidAttributeException
	 * @throws InvalidStringException
	 */
	public static function validatePostStatus(string $status){
		QMStr::assertStringContainsOneOf($status, BasePostStatusProperty::POST_STATUSES,
            self::FIELD_POST_STATUS);
		(new static())->validateAttribute(self::FIELD_POST_STATUS);
	}
	/**
	 * @throws InvalidAttributeException
	 */
	protected function validatePostContent(){
		$this->validateAttribute(self::FIELD_POST_CONTENT);
	}
	/**
	 * @return string
	 */
	public function getWpUrlWithPostName(): string{
		$siteUrl = QMWordPressApi::getSiteUrl();
		return $siteUrl . '/' . $this->post_name . '/';
	}
	public static function fixBadPosts(){
		$invalid = WpPost::getInvalidRecords();
		foreach($invalid as $post){
			if($post->post_type === BasePostTypeProperty::TYPE_POST){
				$post->hardDeleteWithRelations("invalid");
			}
		}
		$posts = static::whereLike(self::FIELD_POST_TITLE, '%for system%')->get();
		foreach($posts as $post){
			$post->post_title = str_replace("for System", "for Population", $post->post_title);
			$post->post_content = str_replace("for System", "for Population", $post->post_title);
			$post->post_excerpt = str_replace("for System", "for Population", $post->post_title);
			$post->post_author = UserIdProperty::USER_ID_POPULATION;
			$post->save();
		}
	}
	/**
	 * @return string
	 */
	public function getCategoryLink(): string{
		$name = $this->main_category_name;
		$slug = QMStr::slugify($name);
		return "
            <div class=\"entry-categories\">
				<span class=\"screen-reader-text\">Categories</span>
				<div class=\"entry-categories-inner\">
					<a href=\"https://science.quantimo.do/category/studies/$slug/\"
					    rel=\"category tag\">
					    $name
					</a>
                </div><!-- .entry-categories-inner -->
			</div>
        ";
	}
	public function getExcerptHtml(): string{
		$the_excerpt = $this->post_excerpt;
		return "
            <div class=\"intro-text section-inner max-percentage small\">
                $the_excerpt
            </div>
        ";
	}
	public function getHeaderHtml(): string{
		$the_excerpt = $this->getExcerptHtml();
		$categoryLink = $this->getCategoryLink();
		$the_title = $this->getTitleHtml();
		return "
            <header class=\"entry-header has-text-align-center\">
                <div class=\"entry-header-inner section-inner medium\">
                    $categoryLink
                    $the_title
                    $the_excerpt
                </div><!-- .entry-header-inner -->
            </header><!-- .entry-header -->
        ";
	}
	/**
	 * @param string $by
	 * @return string
	 */
	public function getBioHtml(string $by = "Principal Investigator"): string{
		if($this->author_id === UserIdProperty::USER_ID_SYSTEM){
			$u = User::mike();
		} else{
			$u = $this->getUser();
		}
		return $u->getBioHtml($by);
	}
	public function contentInvalid(): ?string{
		try {
			$this->validatePostContent();
			return false;
		} catch (\Throwable $e) {
			$m = $e->getMessage();
			$this->logError($m);
			return $m;
		}
	}
	public function getHtmlHeadBody(): string{
		$head = $this->getHead();
		$body = $this->getHtmlHeaderImageContentBio();
		return "
            <html class=\"js\" lang=\"en-US\">
                $head
                $body
            </html>
        ";
	}
	public function getSiteHeader(): string{
		return "
        <header id=\"site-header\" class=\"header-footer-group\" role=\"banner\">
			<div class=\"header-inner section-inner\">
				<div class=\"header-titles-wrapper\">
						<button class=\"toggle search-toggle mobile-search-toggle\" data-toggle-target=\".search-modal\" data-toggle-body-class=\"showing-search-modal\" data-set-focus=\".search-modal .search-field\" aria-expanded=\"false\">
							<span class=\"toggle-inner\">
								<span class=\"toggle-icon\">
									<svg class=\"svg-icon\" aria-hidden=\"true\" role=\"img\" focusable=\"false\" xmlns=\"http://www.w3.org/2000/svg\" width=\"23\" height=\"23\" viewBox=\"0 0 23 23\"><path d=\"M38.710696,48.0601792 L43,52.3494831 L41.3494831,54 L37.0601792,49.710696 C35.2632422,51.1481185 32.9839107,52.0076499 30.5038249,52.0076499 C24.7027226,52.0076499 20,47.3049272 20,41.5038249 C20,35.7027226 24.7027226,31 30.5038249,31 C36.3049272,31 41.0076499,35.7027226 41.0076499,41.5038249 C41.0076499,43.9839107 40.1481185,46.2632422 38.710696,48.0601792 Z M36.3875844,47.1716785 C37.8030221,45.7026647 38.6734666,43.7048964 38.6734666,41.5038249 C38.6734666,36.9918565 35.0157934,33.3341833 30.5038249,33.3341833 C25.9918565,33.3341833 22.3341833,36.9918565 22.3341833,41.5038249 C22.3341833,46.0157934 25.9918565,49.6734666 30.5038249,49.6734666 C32.7048964,49.6734666 34.7026647,48.8030221 36.1716785,47.3875844 C36.2023931,47.347638 36.2360451,47.3092237 36.2726343,47.2726343 C36.3092237,47.2360451 36.347638,47.2023931 36.3875844,47.1716785 Z\" transform=\"translate(-20 -31)\"></path></svg>								</span>
								<span class=\"toggle-text\">Search</span>
							</span>
						</button><!-- .search-toggle -->
					<div class=\"header-titles\">
						<div class=\"site-title faux-heading\"><a href=\"https://science.quantimo.do/\">The Journal of Citizen Science</a></div><div class=\"site-description\">A better world through data.</div><!-- .site-description -->
					</div><!-- .header-titles -->
					<button class=\"toggle nav-toggle mobile-nav-toggle\" data-toggle-target=\".menu-modal\" data-toggle-body-class=\"showing-menu-modal\" aria-expanded=\"false\" data-set-focus=\".close-nav-toggle\">
						<span class=\"toggle-inner\">
							<span class=\"toggle-icon\">
								<svg class=\"svg-icon\" aria-hidden=\"true\" role=\"img\" focusable=\"false\" xmlns=\"http://www.w3.org/2000/svg\" width=\"26\" height=\"7\" viewBox=\"0 0 26 7\"><path fill-rule=\"evenodd\" d=\"M332.5,45 C330.567003,45 329,43.4329966 329,41.5 C329,39.5670034 330.567003,38 332.5,38 C334.432997,38 336,39.5670034 336,41.5 C336,43.4329966 334.432997,45 332.5,45 Z M342,45 C340.067003,45 338.5,43.4329966 338.5,41.5 C338.5,39.5670034 340.067003,38 342,38 C343.932997,38 345.5,39.5670034 345.5,41.5 C345.5,43.4329966 343.932997,45 342,45 Z M351.5,45 C349.567003,45 348,43.4329966 348,41.5 C348,39.5670034 349.567003,38 351.5,38 C353.432997,38 355,39.5670034 355,41.5 C355,43.4329966 353.432997,45 351.5,45 Z\" transform=\"translate(-329 -38)\"></path></svg>							</span>
							<span class=\"toggle-text\">Menu</span>
						</span>
					</button><!-- .nav-toggle -->
				</div><!-- .header-titles-wrapper -->
				<div class=\"header-navigation-wrapper\">
							<nav class=\"primary-menu-wrapper\" aria-label=\"Horizontal\" role=\"navigation\">
								<ul class=\"primary-menu reset-list-style\">
								<li id=\"menu-item-123198\" class=\"menu-item menu-item-type-taxonomy menu-item-object-category menu-item-123198\"><a href=\"https://science.quantimo.do/category/humans/scientists/\">Scientists</a></li>
<li id=\"menu-item-123199\" class=\"menu-item menu-item-type-taxonomy menu-item-object-category menu-item-has-children menu-item-123199\"><a href=\"https://science.quantimo.do/category/studies/\">Studies</a><span class=\"icon\"></span>
<ul class=\"sub-menu\">
	<li id=\"menu-item-123202\" class=\"menu-item menu-item-type-taxonomy menu-item-object-category menu-item-123202\"><a href=\"https://science.quantimo.do/category/reports/root-cause-analyses/\">Root Cause Analyses</a></li>
	<li id=\"menu-item-123200\" class=\"menu-item menu-item-type-taxonomy menu-item-object-category menu-item-123200\"><a href=\"https://science.quantimo.do/category/studies/global-population-studies/\">Global Population Studies</a></li>
	<li id=\"menu-item-123201\" class=\"menu-item menu-item-type-taxonomy menu-item-object-category current-post-ancestor current-menu-parent current-post-parent menu-item-123201\"><a href=\"https://science.quantimo.do/category/studies/individual-case-studies/\">Individual Case Studies</a></li>
</ul>
</li>
<li id=\"menu-item-123748\" class=\"menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-123748\"><a href=\"https://science.quantimo.do/shop/managed-web-hosting-ssl-domain-company-email-cloud-storage-calendar-and-video-conferencing/\">Services</a><span class=\"icon\"></span>
<ul class=\"sub-menu\">
	<li id=\"menu-item-123749\" class=\"menu-item menu-item-type-post_type menu-item-object-page menu-item-123749\"><a href=\"https://science.quantimo.do/shop/managed-web-hosting-ssl-domain-company-email-cloud-storage-calendar-and-video-conferencing/\">Web Hosting, SSL Domain, and Company Email Package</a></li>
</ul>
</li>
								</ul>
							</nav><!-- .primary-menu-wrapper -->
						<div class=\"header-toggles hide-no-js\">
							<div class=\"toggle-wrapper nav-toggle-wrapper has-expanded-menu\">
								<button class=\"toggle nav-toggle desktop-nav-toggle\" data-toggle-target=\".menu-modal\" data-toggle-body-class=\"showing-menu-modal\" aria-expanded=\"false\" data-set-focus=\".close-nav-toggle\">
									<span class=\"toggle-inner\">
										<span class=\"toggle-text\">Menu</span>
										<span class=\"toggle-icon\">
											<svg class=\"svg-icon\" aria-hidden=\"true\" role=\"img\" focusable=\"false\" xmlns=\"http://www.w3.org/2000/svg\" width=\"26\" height=\"7\" viewBox=\"0 0 26 7\"><path fill-rule=\"evenodd\" d=\"M332.5,45 C330.567003,45 329,43.4329966 329,41.5 C329,39.5670034 330.567003,38 332.5,38 C334.432997,38 336,39.5670034 336,41.5 C336,43.4329966 334.432997,45 332.5,45 Z M342,45 C340.067003,45 338.5,43.4329966 338.5,41.5 C338.5,39.5670034 340.067003,38 342,38 C343.932997,38 345.5,39.5670034 345.5,41.5 C345.5,43.4329966 343.932997,45 342,45 Z M351.5,45 C349.567003,45 348,43.4329966 348,41.5 C348,39.5670034 349.567003,38 351.5,38 C353.432997,38 355,39.5670034 355,41.5 C355,43.4329966 353.432997,45 351.5,45 Z\" transform=\"translate(-329 -38)\"></path></svg>										</span>
									</span>
								</button><!-- .nav-toggle -->
							</div><!-- .nav-toggle-wrapper -->
							<div class=\"toggle-wrapper search-toggle-wrapper\">
								<button class=\"toggle search-toggle desktop-search-toggle\" data-toggle-target=\".search-modal\" data-toggle-body-class=\"showing-search-modal\" data-set-focus=\".search-modal .search-field\" aria-expanded=\"false\">
									<span class=\"toggle-inner\">
										<svg class=\"svg-icon\" aria-hidden=\"true\" role=\"img\" focusable=\"false\" xmlns=\"http://www.w3.org/2000/svg\" width=\"23\" height=\"23\" viewBox=\"0 0 23 23\"><path d=\"M38.710696,48.0601792 L43,52.3494831 L41.3494831,54 L37.0601792,49.710696 C35.2632422,51.1481185 32.9839107,52.0076499 30.5038249,52.0076499 C24.7027226,52.0076499 20,47.3049272 20,41.5038249 C20,35.7027226 24.7027226,31 30.5038249,31 C36.3049272,31 41.0076499,35.7027226 41.0076499,41.5038249 C41.0076499,43.9839107 40.1481185,46.2632422 38.710696,48.0601792 Z M36.3875844,47.1716785 C37.8030221,45.7026647 38.6734666,43.7048964 38.6734666,41.5038249 C38.6734666,36.9918565 35.0157934,33.3341833 30.5038249,33.3341833 C25.9918565,33.3341833 22.3341833,36.9918565 22.3341833,41.5038249 C22.3341833,46.0157934 25.9918565,49.6734666 30.5038249,49.6734666 C32.7048964,49.6734666 34.7026647,48.8030221 36.1716785,47.3875844 C36.2023931,47.347638 36.2360451,47.3092237 36.2726343,47.2726343 C36.3092237,47.2360451 36.347638,47.2023931 36.3875844,47.1716785 Z\" transform=\"translate(-20 -31)\"></path></svg>										<span class=\"toggle-text\">Search</span>
									</span>
								</button><!-- .search-toggle -->
							</div>
						</div><!-- .header-toggles -->
				</div><!-- .header-navigation-wrapper -->
			</div><!-- .header-inner -->
			<div class=\"search-modal cover-modal header-footer-group\" data-modal-target-string=\".search-modal\">
	<div class=\"search-modal-inner modal-inner\">
		<div class=\"section-inner\">
			<form role=\"search\" aria-label=\"Search for:\" method=\"get\" class=\"search-form\" action=\"https://science.quantimo.do/\">
	<label for=\"search-form-1\">
		<span class=\"screen-reader-text\">Search for:</span>
		<input type=\"search\" id=\"search-form-1\" class=\"search-field\" placeholder=\"Search …\" value=\"\" name=\"s\">
	</label>
	<input type=\"submit\" class=\"search-submit\" value=\"Search\">
</form>
			<button class=\"toggle search-untoggle close-search-toggle fill-children-current-color\" data-toggle-target=\".search-modal\" data-toggle-body-class=\"showing-search-modal\" data-set-focus=\".search-modal .search-field\" aria-expanded=\"false\">
				<span class=\"screen-reader-text\">Close search</span>
				<svg class=\"svg-icon\" aria-hidden=\"true\" role=\"img\" focusable=\"false\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" viewBox=\"0 0 16 16\"><polygon fill=\"\" fill-rule=\"evenodd\" points=\"6.852 7.649 .399 1.195 1.445 .149 7.899 6.602 14.352 .149 15.399 1.195 8.945 7.649 15.399 14.102 14.352 15.149 7.899 8.695 1.445 15.149 .399 14.102\"></polygon></svg>			</button><!-- .search-toggle -->
		</div><!-- .section-inner -->
	</div><!-- .search-modal-inner -->
</div><!-- .menu-modal -->
		</header>
        ";
	}
	public function getHtmlHeaderImageContentBio(): string{
		$header = $this->getHeaderHtml();
		$image = HtmlHelper::getImageHtml($this->image, $this->post_title);
		$content = $this->post_content;
		$bio = $this->getBioHtml();
		$html = "
            <body style='background-color: white;'>
                <main id=\"site-content\" role=\"main\">
                    <article>
                        $header
                        $image
                        <div class=\"post-inner thin\">
                            <div class=\"entry-content\">
                                $content
                            </div><!-- .entry-content -->
                        </div><!-- .post-inner -->
                        <div class=\"section-inner\">
                            $bio
                        </div><!-- .section-inner -->
                    </article><!-- .post -->
                </main>
            </body>
        ";
		$this->post_content = $html;
		try {
			$this->validateAttribute(self::FIELD_POST_CONTENT);
		} catch (InvalidAttributeException $e) {
			le($e);
		}
		return $html;
	}
	public function getHead(): string{
		$user = QMAuth::getQMUser();
		if($user){
			$drift = $user->getDriftIdentifyScript();
		} else{
			$drift = '<!-- No logged in user for Drift -->';
		}
		return "
            <head>
                <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <link rel=\"profile\" href=\"https://gmpg.org/xfn/11\">
                <title>The Journal of Citizen Science – A better world through data.</title>
                <meta name=\"robots\" content=\"noindex,nofollow\">
                <link rel=\"dns-prefetch\" href=\"//science.quantimo.do\">
                <link rel=\"dns-prefetch\" href=\"//s.w.org\">
                <link rel=\"alternate\" type=\"application/rss+xml\" title=\"The Journal of Citizen Science » Feed\" href=\"https://science.quantimo.do/feed/\">
                <link rel=\"alternate\" type=\"application/rss+xml\" title=\"The Journal of Citizen Science » Comments Feed\" href=\"https://science.quantimo.do/comments/feed/\">
                        <script type=\"text/javascript\" async=\"\" src=\"https://js.driftt.com/include/1582322100000/pt53uxxsr2gg.js\"></script><script>
                            window._wpemojiSettings = {\"baseUrl\":\"https:\/\/s.w.org\/images\/core\/emoji\/12.0.0-1\/72x72\/\",\"ext\":\".png\",\"svgUrl\":\"https:\/\/s.w.org\/images\/core\/emoji\/12.0.0-1\/svg\/\",\"svgExt\":\".svg\",\"source\":{\"concatemoji\":\"https:\/\/science.quantimo.do\/wp-includes\/js\/wp-emoji-release.min.js?ver=5.3.2\"}};
                            !function(e,a,t){var r,n,o,i,p=a.createElement(\"canvas\"),s=p.getContext&&p.getContext(\"2d\");function c(e,t){var a=String.fromCharCode;s.clearRect(0,0,p.width,p.height),s.fillText(a.apply(this,e),0,0);var r=p.toDataURL();return s.clearRect(0,0,p.width,p.height),s.fillText(a.apply(this,t),0,0),r===p.toDataURL()}function l(e){if(!s||!s.fillText)return!1;switch(s.textBaseline=\"top\",s.font=\"600 32px Arial\",e){case\"flag\":return!c([127987,65039,8205,9895,65039],[127987,65039,8203,9895,65039])&&(!c([55356,56826,55356,56819],[55356,56826,8203,55356,56819])&&!c([55356,57332,56128,56423,56128,56418,56128,56421,56128,56430,56128,56423,56128,56447],[55356,57332,8203,56128,56423,8203,56128,56418,8203,56128,56421,8203,56128,56430,8203,56128,56423,8203,56128,56447]));case\"emoji\":return!c([55357,56424,55356,57342,8205,55358,56605,8205,55357,56424,55356,57340],[55357,56424,55356,57342,8203,55358,56605,8203,55357,56424,55356,57340])}return!1}function d(e){var t=a.createElement(\"script\");t.src=e,t.defer=t.type=\"text/javascript\",a.getElementsByTagName(\"head\")[0].appendChild(t)}for(i=Array(\"flag\",\"emoji\"),t.supports={everything:!0,everythingExceptFlag:!0},o=0;o<i.length;o++)t.supports[i[o]]=l(i[o]),t.supports.everything=t.supports.everything&&t.supports[i[o]],\"flag\"!==i[o]&&(t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&t.supports[i[o]]);t.supports.everythingExceptFlag=t.supports.everythingExceptFlag&&!t.supports.flag,t.DOMReady=!1,t.readyCallback=function(){t.DOMReady=!0},t.supports.everything||(n=function(){t.readyCallback()},a.addEventListener?(a.addEventListener(\"DOMContentLoaded\",n,!1),e.addEventListener(\"load\",n,!1)):(e.attachEvent(\"onload\",n),a.attachEvent(\"onreadystatechange\",function(){\"complete\"===a.readyState&&t.readyCallback()})),(r=t.source||{}).concatemoji?d(r.concatemoji):r.wpemoji&&r.twemoji&&(d(r.twemoji),d(r.wpemoji)))}(window,document,window._wpemojiSettings);
                        </script><script src=\"https://static.quantimo.do/wp-includes/js/wp-emoji-release.min.js?ver=5.3.2\" type=\"text/javascript\" defer=\"\"></script>
                <style type=\"text/css\">
                    img.wp-smiley,
                    img.emoji {
                        display: inline !important;
                        border: none !important;
                        box-shadow: none !important;
                        height: 1em !important;
                        width: 1em !important;
                        margin: 0 .07em !important;
                        vertical-align: -0.1em !important;
                        background: none !important;
                        padding: 0 !important;
                    }
                </style>

                <link rel=\"stylesheet\" id=\"wp-block-library-css\" href=\"https://static.quantimo.do/wp-includes/css/dist/block-library/style.min.css?ver=5.3.2\" media=\"all\">
                <link rel=\"stylesheet\" id=\"twentytwenty-style-css\" href=\"https://static.quantimo.do/wp-content/themes/twentytwenty/style.css?ver=1.1\" media=\"all\">
                <style id=\"twentytwenty-style-inline-css\">
                .color-accent,.color-accent-hover:hover,.color-accent-hover:focus,:root .has-accent-color,.has-drop-cap:not(:focus):first-letter,.wp-block-button.is-style-outline,a { color: #e22658; }blockquote,.border-color-accent,.border-color-accent-hover:hover,.border-color-accent-hover:focus { border-color: #e22658; }button:not(.toggle),.button,.faux-button,.wp-block-button__link,.wp-block-file .wp-block-file__button,input[type=\"button\"],input[type=\"reset\"],input[type=\"submit\"],.bg-accent,.bg-accent-hover:hover,.bg-accent-hover:focus,:root .has-accent-background-color,.comment-reply-link { background-color: #e22658; }.fill-children-accent,.fill-children-accent * { fill: #e22658; }:root .has-background-color,button,.button,.faux-button,.wp-block-button__link,.wp-block-file__button,input[type=\"button\"],input[type=\"reset\"],input[type=\"submit\"],.wp-block-button,.comment-reply-link,.has-background.has-primary-background-color:not(.has-text-color),.has-background.has-primary-background-color *:not(.has-text-color),.has-background.has-accent-background-color:not(.has-text-color),.has-background.has-accent-background-color *:not(.has-text-color) { color: #ffffff; }:root .has-background-background-color { background-color: #ffffff; }body,.entry-title a,:root .has-primary-color { color: #000000; }:root .has-primary-background-color { background-color: #000000; }cite,figcaption,.wp-caption-text,.post-meta,.entry-content .wp-block-archives li,.entry-content .wp-block-categories li,.entry-content .wp-block-latest-posts li,.wp-block-latest-comments__comment-date,.wp-block-latest-posts__post-date,.wp-block-embed figcaption,.wp-block-image figcaption,.wp-block-pullquote cite,.comment-metadata,.comment-respond .comment-notes,.comment-respond .logged-in-as,.pagination .dots,.entry-content hr:not(.has-background),hr.styled-separator,:root .has-secondary-color { color: #6d6d6d; }:root .has-secondary-background-color { background-color: #6d6d6d; }pre,fieldset,input,textarea,table,table *,hr { border-color: #dbdbdb; }caption,code,code,kbd,samp,.wp-block-table.is-style-stripes tbody tr:nth-child(odd),:root .has-subtle-background-background-color { background-color: #dbdbdb; }.wp-block-table.is-style-stripes { border-bottom-color: #dbdbdb; }.wp-block-latest-posts.is-grid li { border-top-color: #dbdbdb; }:root .has-subtle-background-color { color: #dbdbdb; }body:not(.overlay-header) .primary-menu > li > a,body:not(.overlay-header) .primary-menu > li > .icon,.modal-menu a,.footer-menu a, .footer-widgets a,#site-footer .wp-block-button.is-style-outline,.wp-block-pullquote:before,.singular:not(.overlay-header) .entry-header a,.archive-header a,.header-footer-group .color-accent,.header-footer-group .color-accent-hover:hover { color: #e22658; }.social-icons a,#site-footer button:not(.toggle),#site-footer .button,#site-footer .faux-button,#site-footer .wp-block-button__link,#site-footer .wp-block-file__button,#site-footer input[type=\"button\"],#site-footer input[type=\"reset\"],#site-footer input[type=\"submit\"] { background-color: #e22658; }.social-icons a,body:not(.overlay-header) .primary-menu ul,.header-footer-group button,.header-footer-group .button,.header-footer-group .faux-button,.header-footer-group .wp-block-button:not(.is-style-outline) .wp-block-button__link,.header-footer-group .wp-block-file__button,.header-footer-group input[type=\"button\"],.header-footer-group input[type=\"reset\"],.header-footer-group input[type=\"submit\"] { color: #ffffff; }#site-header,.footer-nav-widgets-wrapper,#site-footer,.menu-modal,.menu-modal-inner,.search-modal-inner,.archive-header,.singular .entry-header,.singular .featured-media:before,.wp-block-pullquote:before { background-color: #ffffff; }.header-footer-group,body:not(.overlay-header) #site-header .toggle,.menu-modal .toggle { color: #000000; }body:not(.overlay-header) .primary-menu ul { background-color: #000000; }body:not(.overlay-header) .primary-menu > li > ul:after { border-bottom-color: #000000; }body:not(.overlay-header) .primary-menu ul ul:after { border-left-color: #000000; }.site-description,body:not(.overlay-header) .toggle-inner .toggle-text,.widget .post-date,.widget .rss-date,.widget_archive li,.widget_categories li,.widget cite,.widget_pages li,.widget_meta li,.widget_nav_menu li,.powered-by-wordpress,.to-the-top,.singular .entry-header .post-meta,.singular:not(.overlay-header) .entry-header .post-meta a { color: #6d6d6d; }.header-footer-group pre,.header-footer-group fieldset,.header-footer-group input,.header-footer-group textarea,.header-footer-group table,.header-footer-group table *,.footer-nav-widgets-wrapper,#site-footer,.menu-modal nav *,.footer-widgets-outer-wrapper,.footer-top { border-color: #dbdbdb; }.header-footer-group table caption,body:not(.overlay-header) .header-inner .toggle-wrapper::before { background-color: #dbdbdb; }
                </style>
                <link rel=\"stylesheet\" id=\"twentytwenty-print-style-css\" href=\"https://static.quantimo.do/wp-content/themes/twentytwenty/print.css?ver=1.1\" media=\"print\">
                <link rel=\"stylesheet\" id=\"wpra_front_css-css\" href=\"https://static.quantimo.do/wp-content/plugins/wp-reactions-child/assets/css/front.css?v=1.1.3&amp;ver=5.3.2\" media=\"all\">
                <link rel=\"stylesheet\" id=\"wpra_common_css-css\" href=\"https://static.quantimo.do/wp-content/plugins/wp-reactions-child/assets/css/common.css?v=1.1.3&amp;ver=5.3.2\" media=\"all\">


                <script src=\"https://static.quantimo.do/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp\"></script>
                <script src=\"https://static.quantimo.do/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1\"></script>
                <script src=\"https://static.quantimo.do/wp-content/themes/twentytwenty/assets/js/index.js?ver=1.1\" async=\"\"></script>
                <link rel=\"https://api.w.org/\" href=\"https://science.quantimo.do/wp-json/\">
                <link rel=\"EditURI\" type=\"application/rsd+xml\" title=\"RSD\" href=\"https://science.quantimo.do/xmlrpc.php?rsd\">
                <link rel=\"wlwmanifest\" type=\"application/wlwmanifest+xml\" href=\"https://static.quantimo.do/wp-includes/wlwmanifest.xml\">
                <meta name=\"generator\" content=\"WordPress 5.3.2\">
                <!-- Start Drift By WP-Plugin: Drift -->
                <!-- Start of Async Drift Code -->
                <script type=\"application/javascript\">
                \"use strict\";
                !function() {
                  var t = window.driftt = window.drift = window.driftt || [];
                  if (!t.init) {
                    if (t.invoked) return void (window.console && console.error && console.error(\"Drift snippet included twice.\"));
                    t.invoked = !0, t.methods = [ \"identify\", \"config\", \"track\", \"reset\", \"debug\", \"show\", \"ping\", \"page\", \"hide\", \"off\", \"on\" ],
                    t.factory = function(e) {
                      return function() {
                        var n = Array.prototype.slice.call(arguments);
                        return n.unshift(e), t.push(n), t;
                      };
                    }, t.methods.forEach(function(e) {
                      t[e] = t.factory(e);
                    }), t.load = function(t) {
                      var e = 3e5, n = Math.ceil(new Date() / e) * e, o = document.createElement(\"script\");
                      o.type = \"text/javascript\", o.async = !0, o.crossorigin = \"anonymous\", o.src = \"https://js.driftt.com/include/\" + n + \"/\" + t + \".js\";
                      var i = document.getElementsByTagName(\"script\")[0];
                      i.parentNode.insertBefore(o, i);
                    };
                  }
                }();
                drift.SNIPPET_VERSION = '0.3.1';
                drift.load('pt53uxxsr2gg');
                </script>
                <!-- End of Async Drift Code --><!-- end: Drift Code. -->
                $drift
                    <script>document.documentElement.className = document.documentElement.className.replace( 'no-js', 'js' );</script>
                    <style id=\"custom-background-css\">
                body.custom-background { background-color: #ffffff; }
                </style>
                <style id=\"wp-custom-css\">
                .singular .featured-media {
                    margin-top: 0;
                    display: none;
                }
                .powered-by-wordpress {
                    margin-top: 0;
                    display: none;
                }
                .singular .entry-header {
                    padding: 0;
                }
                .post-inner {
                    padding-top: 0;
                }
                .search-modal .search-field {
                    border: none;
                    font-size: 3.2rem;
                    height: 14rem;
                    outline: 0 !important;
                }
                .header-inner .toggle {
                    align-items: center;
                    display: flex;
                    overflow: visible;
                    padding: 0 2rem;
                    outline: 0 !important;
                }
                .menu-modal .toggle {
                    color: #000000;
                    outline: 0 !important;
                }		</style>
        </head>
        ";
	}
	public static function getLargePosts(): array{
		return Writable::selectStatic("
            select post_title,
               post_name,
               round(length(post_content) / 1024) as kB
            from wp_posts
            where post_modified > now() - interval 1 hour
            order by KB desc
        ");
	}
	public static function deleteLargePosts(){
		WpPostPostContentProperty::fixTooLong();
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_POST_AUTHOR];
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new WpPostUserButton($this),
		];
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
}
