<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Events\PostPublished;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Files\MimeContentTypeHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Cards\PreviewCard;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\WPAttachment;
use App\Models\WpPost;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\Base\BasePostTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Storage\DB\QMQB;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\Utils\QMProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LogicException;
use stdClass;
use Throwable;
trait PostableTrait {
	protected $wpPost;
	public $postStatus;
	/**
	 * @return string
	 */
	abstract public function generatePostContent(): string;
	/**
	 * @return string
	 */
	abstract public function getSubtitleAttribute(): string;
	/**
	 * @return string
	 */
	abstract public function getTitleAttribute(): string;
	/**
	 * @return int
	 */
	public function getAuthorUserId(): int{
		if(property_exists($this, 'userId')){
			return $this->userId;
		}
		if(method_exists($this, 'getUserId')){
			return $this->getUserId();
		}
		$this->logInfo("No getUserId defined on " . static::class . " so using SYSTEM USER!");
		return UserIdProperty::USER_ID_SYSTEM;
	}
	/**
	 * @return WpPost
	 */
	public function firstOrCreateWpPost(): WpPost{
		if($this->wpPost){
			return $this->wpPost;
		}
		$p = $this->firstWpPost();
		if($p){
			return $p;
		}
		return $this->postToWordPress();
	}
	/**
	 * @return string
	 */
	public function getTitleWithUserName(): string{
		if($user = $this->getAuthorQMUser()){
			return $this->getTitleAttribute() . " for " . QMStr::titleCaseSlow($user->getDisplayNameAttribute());
		}
		throw new LogicException(__METHOD__ . " not implemented! ");
	}
	/**
	 * @return QMUser
	 */
	public function getAuthorQMUser(): ?QMUser{
		return QMUser::find($this->getAuthorUserId());
	}
	public function addFileAttachments(){
		$parentPost = $this->firstOrCreateWpPost();
		$urls = $this->getFileUrls();
		foreach($urls as $url){
			WPAttachment::firstOrCreateAttachmentByUrl($url, $parentPost->ID);
		}
	}
	/**
	 * @return WpPost
	 */
	public function fistOrNewWpPost(): WpPost{
		$post = WpPost::wherePostName($this->getPostNameSlug())->first();
		if(!$post){
			$post = new WpPost();
		}
		return $post;
	}
	public function getPostNameSlug(): string{
		return $this->getSlugWithPluralClassAndId();
	}
	/**
	 * @return string
	 */
	public function getPlainText(): string{
		return $this->getSubtitleAttribute();
	}
	/**
	 * @return string
	 */
	abstract public function getCategoryName(): string;
	public function getCategoryNames(): array{
		return [$this->getCategoryName()];
	}
	/**
	 * @return string|null
	 */
	abstract public function getCategoryDescription(): string;
	/**
	 * @return string|null
	 */
	abstract public function getParentCategoryName(): ?string;
	/**
	 * @return Builder|QMQB
	 */
	public static function whereNeverPosted(){
		$qb = self::wherePostable();
		$qb->whereNull(static::TABLE . '.' . static::FIELD_WP_POST_ID);
		return $qb;
	}
	/**
	 * @return Builder|QMQB|static[]
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public static function wherePostable();
	/**
	 * @return Builder|\Illuminate\Database\Query\Builder
	 */
	public static function wherePostStale(): Builder|\Illuminate\Database\Query\Builder{
		$qb = DB::table(static::TABLE)->join(WpPost::TABLE, WpPost::TABLE . '.' . WpPost::FIELD_ID, "=",
			static::TABLE . '.' . static::FIELD_WP_POST_ID);
		$qb->whereRaw(WpPost::TABLE . '.' . WpPost::FIELD_POST_MODIFIED . "<" . static::TABLE . '.' .
			static::UPDATED_AT)
            ->where(WpPost::TABLE . '.' . WpPost::FIELD_POST_MODIFIED,
                "<", Carbon::now()->subDay());
		$fields = static::getColumns();
		$qb->columns = [];
		foreach($fields as $field){
			$qb->columns[] = static::TABLE . '.' . $field;
		}
		return $qb;
	}
	/**
	 * @param Builder|\Illuminate\Database\Query\Builder $qb
	 * @param string $reason
	 * @return WpPost[]
	 */
	public static function postByQuery($qb, string $reason): array{
		$before = $qb->count();
		\App\Logging\ConsoleLog::info("$before " . static::TABLE . " where $reason.");
		QueryBuilderHelper::dump($qb);
		static::logDataLabIndexUrls();
		$urls = $posts = [];
		while($before){
			$message = "$before " . static::TABLE . " where $reason";
			\App\Logging\ConsoleLog::info($message);
			/** @var static|stdClass $postable */
			$postable = $qb->first();
			if($postable instanceof stdClass){
				$postable = static::hydrateOne($postable);
			}
			//$postable->validateImage();
			$post = $postable->postToWordPress();
			$posts[] = $post;
			$urls[$post->getTitleAttribute()] = $post->getUrl();
			$before = $after = $qb->count();
			if(JobTestCase::jobDurationExceedsTimeLimit()){
				$max = JobTestCase::getMaxJobDuration();
				$duration = JobTestCase::getJobDurationInSeconds();
				\App\Logging\ConsoleLog::info("Breaking because jobDuration $duration s Exceeds Time Limit of $max s");
				break;
			}
		}
		QMLog::list($urls, "New Posts");
		return $posts;
	}
	/**
	 * @return WpPost
	 */
	public function firstOrNewWpPost(): WpPost{
		if($this->wpPost){
			return $this->wpPost;
		}
		$slug = $this->getPostNameSlug();
		$p = WpPost::firstOrNewWhereSlug($slug);
		return $this->wpPost = $p;
	}
	/**
	 * @return WpPost
	 */
	public function firstWpPost(): ?WpPost{
		if($this->wpPost){
			return $this->wpPost;
		}
		$p = WpPost::firstWhereSlug($this->getPostNameSlug());
		return $this->wpPost = $p;
	}
	/**
	 * @return WpPost
	 */
	public function findWpPost(): ?WpPost{
		if($this->wpPost){
			return $this->wpPost;
		}
		$p = WpPost::findWhereName($this->getPostNameSlug());
		return $this->wpPost = $p;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	abstract public function getUrl(array $params = []): string;
	public function getPostStatus(): string{
		if($this->postStatus){
			return $this->postStatus;
		}
		if($this->getIsPublic()){
			return $this->postStatus = BasePostStatusProperty::STATUS_PUBLISH;
		}
		return $this->postStatus = BasePostStatusProperty::STATUS_PRIVATE;
	}
	public function weShouldPost(): bool{
		$status = $this->getPostStatus();
		if($status !== BasePostStatusProperty::STATUS_PUBLISH){
			$this->logInfo("Not posting because PostStatus is $status and we don't want to explode wp_posts DB table");
			return false; // Don't explode wp_posts DB table
		}
		try {
			$this->exceptionIfWeShouldNotPost();
			return true;
		} catch (Throwable $e) {
			$this->logInfo("Not posting because " . $e->getMessage());
			return false;
		}
	}
	public function publish(){
		$this->saveToStudiesRepo();
	}
	/**
	 * @return View
	 */
	protected function getView(): View{
		$content = $this->getShowContent();
		$view = view('so-simple._layouts.post', [
			'content' => $content,
			'model' => $this,
		]);
		return $view;
	}
	abstract public function getTags(): array;
	/**
	 * @return WpPost
	 */
	public function postToWordPress(): WpPost{
		if($profile = false){
			QMProfile::startLiveProf();
		}
		$p = $this->firstOrNewWpPost();
		$html = $this->generatePostContent();
		\App\Logging\ConsoleLog::info(__METHOD__ . " calling addCssAndCustomHtmlBlockTags...");
		$content = WpPost::addCssAndCustomHtmlBlockTags($html);
		$categoryName = $this->getCategoryName();
		if($this instanceof BaseModel || method_exists($this, 'getUserId')){
			$p->post_author = $this->getAuthorUserId();
		} else{
			$p->post_author = UserIdProperty::USER_ID_SYSTEM;
		}
		$p->post_content = $content;
		//$p->post_content_filtered = $this; WP always deletes this
		$p->post_excerpt = $this->getSubtitleAttribute();
		$p->post_name = $this->getPostNameSlug();
		if($this->getAttribute(UserVariable::FIELD_USER_ID)){
			//$p->post_title = $this->getTitleWithUserName();
			$p->post_title = $this->getReportTitleAttribute(); // Let's not add username to reduce clutter and decrease privacy risks
		} else{
			$p->post_title = $this->getReportTitleAttribute();
		}
		$p->post_type = $this->getWpPostType();
		$p->post_mime_type = MimeContentTypeHelper::HTML;
		$p->guid = $this->getGuid();
		$p->post_status = $this->getPostStatus();
		try {
			$p->validateAndSave($categoryName, $this->getCategoryDescription(), $this->getParentCategoryName());
		} catch (ModelValidationException $e) {
			le($e);
		}
		try {
			$p->addFeaturedImageFromUrl($this->getImage(), $this->getReportTitleAttribute());
		} catch (InvalidStringException $e) {
			le($e);
		}
		$this->setWpPostIdAndSave($p->ID);
		if(PostPublished::BROADCAST){  // Don't use up all of our Pusher requests
			event(new PostPublished($p));
		}
		if($profile){
			QMProfile::endProfile();
		}
		return $this->wpPost = $p;
	}
	public function getGuid(): string{
		return QMWordPressApi::getSiteUrl() . "/" . $this->getUniqueIndexIdsSlug();
	}
	/**
	 * @return string
	 */
	public function getWpPostUrl(): string{
		return $this->getGuid();
	}
	/**
	 * @param string $text
	 * @return string
	 */
	public function getRectanglePostButtonHtml(string $text): string{
		return $this->getOrCreateWpPost()->getRectangleButtonHtml($text);
	}
	/**
	 * @param int $id
	 */
	public function setWpPostIdAndSave(int $id){
		$this->logPostUrl(__FUNCTION__);
		if($this instanceof BaseModel){
			$this->wp_post_id = $id;
			try {
				$this->saveOrFail();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
			$l = $this;
		} else{
			$l = $this->l();
			if(method_exists($this, 'updateDbRow') && static::TABLE){
				$this->updateDbRow(['wp_post_id' => $id], "Need to update post id for analytical report source object");
			} else{
				$this->logInfo("No DB table to save wp_post_id in!");
			}
		}
		$l->unsetRelation(WpPost::TABLE);
	}
	/**
	 * @return WpPost[]
	 */
	public static function postWhereNeverPosted(): array{
		$qb = self::whereNeverPosted();
		return self::postByQuery($qb, "NEVER POSTED: post_count is 0 ");
	}
	/**
	 * @return WpPost[]
	 */
	public static function postWhereStale(): array{
		$qb = self::wherePostStale();
		return self::postByQuery($qb, "STALE: updated_at is greater than post_modified ");
	}
	/**
	 * @return string
	 */
	public function getWpPostType(): string{
		return BasePostTypeProperty::TYPE_POST;
	}
	/**
	 * @return bool
	 */
	abstract public function getIsPublic(): ?bool;
	public function updatePostStatus(string $status): void{
		$this->postStatus = $status;
		$p = $this->getWpPostIfExists();
		if($p && $p->post_status !== $status){
			$p->post_status = $status;
			if(!$p->post_modified){
				$p->setDates();
			}
			try {
				$p->save();
			} catch (ModelValidationException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	public function updatePostStatusOrDeleteIfPrivate(string $status): void{
		$this->postStatus = $status;
		$p = $this->getWpPostIfExists();
		if($p){
			if($status === BasePostStatusProperty::STATUS_PRIVATE){
				try {
					$p->delete();
				} catch (\Exception $e) {
					le($e);
					throw new \LogicException();
				}
				return;
			}
			if($p->post_status !== $status){
				$p->post_status = $status;
				try {
					$p->save();
				} catch (ModelValidationException $e) {
					le($e);
					throw new \LogicException();
				}
			}
		}
	}
	public function getWpPostIfExists(): ?WpPost{
		return $this->firstWpPost();
	}
	/**
	 * @return PreviewCard
	 * @throws InvalidAttributeException
	 */
	public function getPreviewCard(): PreviewCard{
		return $this->getOrCreateWpPost()->getPreviewCard();
	}
	/**
	 * @return WpPost
	 */
	public function getOrCreateWpPost(): WpPost{
		return $this->firstOrCreateWpPost();
	}
	/**
	 * @throws NotEnoughDataException
	 */
	abstract public function exceptionIfWeShouldNotPost(): void;
	public function logPostUrl(string $prefix): void{
		\App\Logging\ConsoleLog::info($prefix);
		QMLog::logLocalLinkButton($this->getWpPostUrl(), $this->getTitleAttribute());
	}
	public function getPostLink(): string{
		$b = $this->getPostButton();
		return $b->getImageLink();
	}
	public function getPostButton(): QMButton{
		$b = new QMButton();
		$b->setFontAwesome(FontAwesome::WORDPRESS);
		if($url = $this->getPostUrl()){
			$name = $this->getTitleAttribute();
			$b->setTextAndTitle("View Post");
			$b->setTooltip("Open Wordpress Post for $name");
			$b->setUrl($url);
			$b->setImage("https://cdn0.iconfinder.com/data/icons/social-network-9/50/27-512.png");
			return $b;
		}
		$postUrl = $this->getPostToWordPressUrl();
		$b->setTextAndTitle("Publish");
		$b->setTooltip("Post to Wordpress");
		$b->setUrl($postUrl);
		$b->setImage("https://cdn0.iconfinder.com/data/icons/social-network-9/50/27-512.png");
		return $b;
	}
	public function getPostUrl(): ?string{
		$id = $this->getAttribute(Variable::FIELD_WP_POST_ID);
		if($id === null){
			return null;
		}
		return WpPost::getSiteUrl() . "?p=" . $id;
	}
	protected function getPostToWordPressUrl(): string{
		return $this->getUrl(['post' => true]);
	}
}
