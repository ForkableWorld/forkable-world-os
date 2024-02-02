<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\WordPress;
use App\Logging\QMLog;
use App\Properties\Base\BasePostStatusProperty;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
class QMWordPressApi {
	public const WP_BASE_HOSTNAME = 'science.quantimo.do';
	public const wordPressApiBaseUri = '/wp-json/wp/v2/';
	private static $draftPosts = [];
	private static $allPosts;
	public function __construct(){
	}
	/**
	 * @param $path
	 * @return string
	 */
	private static function getApiUrlForPath(string $path): string{
		return self::getSiteUrl() . self::wordPressApiBaseUri . $path;
	}
	/**
	 * @return Client
	 */
	public static function getLaravelGuzzleClient(): Client{
		$base64 = base64_encode(self::WP_USER_NAME . ":" . self::WP_PASSWORD);
		$client = new Client([
			'base_uri' => self::getSiteUrl() . self::wordPressApiBaseUri,
			'timeout' => 10,
			'headers' => [
				'Content-Type' => 'application/json',
				"Accept" => "application/json",
				'Authorization' => "Basic " . $base64,
			],
		]);
		return $client;
	}
	/**
	 * @param string $title
	 * @param string $skipTrash
	 */
	public static function deleteWithTitle(string $title, string $skipTrash = 'true'){
		QMLog::info("Deleting WP posts with title $title");
		foreach(self::getDraftPosts() as $existingPost){
			$existingTitle = $existingPost->getTitleAttribute();
			if($existingTitle === $title){
				self::deleteById($existingPost->getId(), $skipTrash);
			}
		}
	}
	/**
	 * @return WPPostApi[]
	 */
	public static function setDraftPosts(){
		return self::$draftPosts =
			self::getAllPostsMatchingQuery([WPPostApi::FIELD_STATUS => BasePostStatusProperty::STATUS_DRAFT]);
	}
	/**
	 * @return WPPostApi[]
	 */
	public static function getDraftPosts(){
		return self::$draftPosts ?: self::setDraftPosts();
	}
	/**
	 * @return WPPostApi[]
	 */
	public static function setAllPosts(){
		return self::$allPosts = self::getAllPostsMatchingQuery([]);
	}
	/**
	 * @param array $params
	 * @return array
	 */
	protected static function getAllPostsMatchingQuery(array $params){
		$all = $instantiated = [];
		$current = [1];
		while(count($current)){
			$params['offset'] = count($all);
			$current = self::get('posts', $params);
			if(!$current){
				break;
			}
			/** @noinspection SlowArrayOperationsInLoopInspection */
			$all = array_merge($all, $current);
		}
		foreach($all as $allPost){
			$instantiated[] = new WPPostApi($allPost);
		}
		return $instantiated;
	}
	/**
	 * @return WPPostApi[]
	 */
	public static function getAllPosts(){
		return self::$allPosts ?: self::setAllPosts();
	}
	/**
	 * @param string $skipTrash
	 */
	public static function deleteAllPosts(string $skipTrash = 'true'){
		$posts = self::getAllPosts();
		foreach($posts as $existingPost){
			$existingPost->delete($skipTrash);
		}
	}
	/**
	 * @param string $slug
	 * @param string $status
	 * @return WPPostApi
	 */
	public static function getPostBySlug(string $slug, string $status = 'publish,future,draft,pending,private'){
		$params = [
			'slug' => $slug,
			//'status' => $status,
		];
		$existingPosts = self::get('posts', $params);
		$convertedPosts = [];
		foreach($existingPosts as $post){
			$convertedPosts[] = new WPPostApi($post);
		}
		return $convertedPosts[0] ?? null;
	}
	/**
	 * @param string $path
	 * @param array $params
	 * @return mixed
	 */
	public static function get(string $path, array $params = []){
		$url = self::getApiUrlForPath($path);
		$url = UrlHelper::addParams($url, $params);
		try {
			$client = self::getLaravelGuzzleClient();
			try {
				$response = $client->get($url);
			} catch (ClientException $e) {
				$response = $e->getResponse();
				$body = json_decode($response->getBody());
				QMLog::error("Could not GET $url.  Retrying...");
				$response = $client->get($url);
			}
			return json_decode($response->getBody());
		} catch (Exception $e) {
			QMLog::error("GET " . $url . " " . $e);
			return false;
		}
	}
	/**
	 * @param string $title
	 * @return int
	 */
	public static function getIdByTitle(string $title){
		foreach(self::getDraftPosts() as $existingPost){
			$existingTitle = $existingPost->getTitleAttribute();
			if($existingTitle === $title){
				return $existingPost->getId();
			}
		}
		return null;
	}
	/**
	 * @param int $id
	 * @param string $skipTrash
	 * @return bool|ResponseInterface
	 */
	public static function deleteById(int $id, string $skipTrash = 'true'){
		QMLog::info("Deleting WP post $id");
		$url = self::getApiUrlForPath('posts/' . $id . '?force=' . $skipTrash);
		try {
			$client = self::getLaravelGuzzleClient();
			return $client->delete($url);
		} catch (Exception $e) {
			QMLog::error("DELETE " . $url . " " . $e->getMessage(), []);
			return false;
		}
	}
	/**
	 * @param string $slug
	 * @param string $skipTrash
	 * @return bool|ResponseInterface
	 */
	public static function deletePostBySlug(string $slug, string $skipTrash = 'true'){
		$post = self::getPostBySlug($slug);
		if(!$post){
			QMLog::error("$slug post not found!");
			return false;
		}
		return $post->delete($skipTrash);
	}
	public static function deleteTestPosts(){
		QMLog::infoWithoutContext('=== ' . __FUNCTION__ . ' ===');
		try {
			self::deleteWithTitle("New Discoveries", 'true');
		} catch (Exception $e) {
			QMLog::error($e->getMessage(), ['exception' => $e]);
		}
	}
	/**
	 * @param string $skipTrash
	 */
	public static function deleteDraftPosts(string $skipTrash = 'true'){
		foreach(self::getDraftPosts() as $existingPost){
			$existingPost->delete($skipTrash);
		}
	}
	/**
	 * @param int $existingPostId
	 * @param array $params
	 * @return mixed
	 */
	public static function postById($existingPostId, $params){
		return self::post('posts/' . $existingPostId, $params);
	}
	/**
	 * @param string $path
	 * @param array|object $params
	 * @return mixed
	 */
	public static function post(string $path, $params){
		$params = (array)$params;
		try {
			$client = self::getLaravelGuzzleClient();
			$url = self::getApiUrlForPath($path);
			try {
				$response = $client->post($url, ['body' => json_encode($params)]);
			} catch (Exception $e) {
				QMLog::error("Could not POST to $url.  Retrying...");
				$response = $client->post($url, ['body' => json_encode($params)]);
			}
			QMLog::debug($response->getBody());
			return json_decode($response->getBody());
		} catch (Exception $e) {
			QMLog::error("POST " . $path . " " . $e->getMessage(), []);
			return false;
		}
	}
	/**
	 * @return string
	 */
	public static function getSiteUrl(): string{
		$hostname = QMWordPressApi::WP_BASE_HOSTNAME;
		if(AppMode::isNonSlimUnitTest() || AppMode::isSlimUnitTest()){
			$hostname = "test-science.quantimo.do";
			//if(\App\Utils\EnvOverride::isLocal()){$hostname = "dev-science.quantimo.do";}
		}
		if(AppMode::isStagingUnitTesting()){
			$hostname = "staging-science.quantimo.do";
			//if(\App\Utils\EnvOverride::isLocal()){$hostname = "dev-science.quantimo.do";}
		}
		return "https://$hostname";
	}
	/**
	 * @param string $slug
	 * @return string
	 */
	public static function getPostUrlBySlug(string $slug): string{
		return self::getSiteUrl() . '/' . $slug;
	}
}
