<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\WordPress;
use App\Logging\QMLog;
use App\Properties\Base\BasePostStatusProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\User\QMUser;
use Exception;
use Psr\Http\Message\ResponseInterface;
class WPPostApi extends DBModel {
	public const FIELD_STATUS = 'status';
	private $qmUser;
	private $wpUser;
	public $author; //	The ID for the author of the object.
	public $categories;    // The terms assigned to the object in the category taxonomy.
	public $comment_status; //	Whether or not comments are open on the object.public $One of: open, closed
	public $content; //	The content for the object.
	public $date;    //The date the object was published, in the site's timezone.
	public $date_gmt;    //The date the object was published, as GMT.
	public $excerpt;  //	The excerpt for the object.
	public $featured_media; //	The ID of the featured media for the object.
	public $format; //	The format for the object.public $One of: standard, aside, chat, gallery, link, image, quote, status, video, audio
	public $id;    //The terms assigned to the object in the post_tag taxonomy.
	public $meta; //	Meta fields.
	public $password; //	A password to protect access to the content and excerpt.
	public $ping_status; //	Whether or not the object can be pinged public $One of: open, closed
	public $slug;    //An alphanumeric identifier for the object unique to its type.
	public $status;    //A named status for the object. public $One of: publish, future, draft, pending, private
	public $sticky; //	Whether or not the object should be treated as sticky.
	public $tags;
	public $template; //	The theme file to use to display the object.public $One of:
	public $title; //	The title for the object.
	/**
	 * WPPostApi constructor.
	 * @param null $postFromApi
	 */
	public function __construct($postFromApi = null){
		if($postFromApi){
			foreach($postFromApi as $key => $value){
				$this->$key = $value;
			}
		}
	}
	/**
	 * @param string $title
	 * @param string $content
	 * @param string $slug
	 * @param string $status
	 * @return mixed
	 */
	public static function postByTitleContentSlug(string $title = null, string $content = null, string $slug = null,
		string $status = BasePostStatusProperty::STATUS_PUBLISH){
		$post = new self();
		$post->setTitle($title);
		$post->setContent($content);
		$post->setSlug($slug);
		$post->setStatus($status);
		return $post->updateOrCreate();
	}
	/**
	 * @return mixed
	 */
	public function updateOrCreate(){
		try {
			$id = $this->getExistingPostId();
			$params = $this->getPostRequestParams();
			$response = QMWordPressApi::postById($id, $params);
			$this->logInfo("updated/created");
			$this->setId($response->id);
			return $response;
		} catch (Exception $e) {
			QMLog::error($e->getMessage(), []);
		}
		return false;
	}
	/**
	 * @return array
	 */
	private function getPostRequestParams(){
		$array = [];
		foreach($this as $key => $value){
			if(!empty($value)){
				$array[$key] = $value;
			}
		}
		$array["date"] = date('Y-m-d H:i:s');
		return $array;
	}
	/**
	 * @return int
	 */
	private function getExistingPostId(){
		return $this->id ?: $this->setExistingPostIdByTitleOrSlug();
	}
	/**
	 * @return int
	 */
	private function setExistingPostIdByTitleOrSlug(){
		$existingPost = QMWordPressApi::getPostBySlug($this->getSlug());
		if($existingPost){
			$this->id = $existingPost->id;
		} else{
			$this->id = QMWordPressApi::getIdByTitle($this->getTitleAttribute());
		}
		return $this->id;
	}
	/**
	 * @return mixed
	 */
	public function getTitleAttribute(): string{
		$title = $this->title->rendered ?? null;
		if($title === null){
			$title = $this->title;
		}
		return $title;
	}
	/**
	 * @param string $title
	 * @return string
	 */
	public function setTitle(string $title){
		if(!$this->slug){
			$this->setSlug($title);
		}
		return $this->title = $title;
	}
	/**
	 * @return mixed
	 */
	public function getContent(){
		return $this->content;
	}
	/**
	 * @param string $content
	 * @return string
	 */
	public function setContent(string $content){
		return $this->content = $content;
	}
	/**
	 * @return string
	 */
	public function getSlug(): string{
		return $this->slug;
	}
	/**
	 * @param string $slug
	 * @return string
	 */
	public function setSlug(string $slug): string{
		$slug = strtolower(str_replace(' ', '-', $slug));
		return $this->slug = $slug;
	}
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @param int $id
	 * @return int
	 */
	public function setId($id){
		return $this->id = $id;
	}
	/**
	 * @param string $skipTrash
	 * @return ResponseInterface
	 */
	public function delete(string $skipTrash = 'true'){
		QMLog::info("Deleting WP post with title " . $this->getTitleAttribute() . " and slug " . $this->getSlug());
		return QMWordPressApi::deleteById($this->getId(), $skipTrash);
	}
	/**
	 * @return string
	 */
	public function getStatus(): string{
		return $this->status;
	}
	/**
	 * @param string $status
	 * @return string
	 */
	public function setStatus(string $status): string{
		return $this->status = $status;
	}
	/**
	 * @return array
	 */
	public function getTags(){
		return $this->tags;
	}
	/**
	 * @param mixed $tags
	 */
	public function setTags(array $tags){
		$this->tags = $tags;
	}
	/**
	 * @return int
	 */
	public function getAuthor(){
		return $this->author;
	}
	/**
	 * @param QMUser $QMUser
	 * @return int|string
	 */
	public function setAuthor(QMUser $QMUser){
		$wpUser = $QMUser->getOrCreateWordPressStudiesUser();
		return $this->author = $wpUser->getId();
	}
	/**
	 * @return mixed
	 */
	public function getFeaturedMedia(){
		return $this->featured_media;
	}
	/**
	 * @param mixed $featured_media
	 */
	public function setFeaturedMedia($featured_media): void{
		$this->featured_media = $featured_media;
	}
	/**
	 * @return array
	 */
	public function getCategories(){
		return $this->categories;
	}
	/**
	 * @param mixed $categories
	 */
	public function setCategories(array $categories){
		$this->categories = $categories;
	}
	/**
	 * @return string
	 */
	public function getPostUrl(){
		return QMWordPressApi::getPostUrlBySlug($this->getSlug());
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getPostUrl();
	}
	/**
	 * @return string
	 */
	public function getExcerpt(){
		return $this->excerpt;
	}
	/**
	 * @param string $excerpt
	 */
	public function setExcerpt(string $excerpt){
		$this->excerpt = $excerpt;
	}
	/**
	 * @return mixed
	 */
	public function getWpUser(){
		return $this->wpUser;
	}
	/**
	 * @return mixed
	 */
	public function getQmUser(){
		return $this->qmUser;
	}
	/**
	 * @param mixed $qmUser
	 */
	public function setQmUser($qmUser): void{
		$this->qmUser = $qmUser;
	}
}
