<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\WordPress;
use App\Logging\QMLog;
use App\Models\User;
use App\Models\WpPost;
use App\Properties\Base\BasePostStatusProperty;
use App\Slim\Model\WordPress\QMWordPressApi;
use App\Utils\APIHelper;
use Corcel\Corcel;
use Illuminate\Support\Facades\DB;
use Tests\SlimStagingTestCase;
/**
 * Class WordPressPostTest
 * @package Tests\Api\Production2
 */
class WordPressPostDatabaseTest extends SlimStagingTestCase{
    public const WP_DISABLED_UNTIL = "2023-04-01"; // WP sucks
    public static $slug = "test-study-slug-production-test-case";
    public static $content = "Test Study Content";
    public static $title = "Test Study Title Production Test";
    public function testWordPressPostSearch(){
	    $this->skipTest("Please implement me!");
        $results = DB::select("
            SELECT SQL_CALC_FOUND_ROWS wp_posts.ID
                FROM wp_posts
                WHERE 1=1
                AND (((wp_posts.post_title LIKE '%remeron%')
                OR (wp_posts.post_excerpt LIKE '%remeron%')
                OR (wp_posts.post_content LIKE '%remeron%')))
                AND wp_posts.post_type = 'post'
                AND (wp_posts.post_status = 'publish'
                OR wp_posts.post_status = 'future'
                OR wp_posts.post_status = 'draft'
                OR wp_posts.post_status = 'pending'
                OR wp_posts.post_status = 'private')
                ORDER BY wp_posts.post_title LIKE '%remeron%' DESC, wp_posts.post_date DESC
                LIMIT 0, 20
        ");
    }
    /**
     */
    public function testPostToWordPressDatabase(){
		$this->skipTest("Please implement me!");
        if(time() < strtotime(self::WP_DISABLED_UNTIL)){ // Might be temporarily broken
            $this->skipTest('I think there is a caching problem');
        }
        $this->checkWordPressUser();
        $wpPost = self::postToWordPressDatabase();
        $this->assertNotNull($wpPost);
        $this->checkForDuplicatePost();
        $postUrl = $this->checkWebPage($wpPost);
        $this->checkDeletion($wpPost, $postUrl);
    }
    /**
     * @return WpPost
     */
    public static function postToWordPressDatabase(): WpPost{
        $isLaravel = Corcel::isLaravel();
        $post = new WpPost();
        $post->post_title = self::$title;
        $post->post_name = self::$slug;
        $post->post_status = BasePostStatusProperty::STATUS_PUBLISH;
        $post->post_content = self::$content;
        $post->post_excerpt = "Test excerpt";
        $post->post_author = QMUser::mike()->getOrCreateWordPressStudiesUser()->getId();
        // TODO: Deal with category and tag id's
        //$post->setTags(['Overall Mood', 'Sleep Quality']);
        //$post->setCategories([VariableCategory::Sleep, VariableCategory::Emotions]);
        //$post->setPostAuthor(User::mike()->getOrCreateWordPressStudiesUser()->getId());
        $post->save();
        return $post;
    }
    /**
     * @param WpPost $wpPost
     * @param string $postUrl
     */
    private function checkDeletion(WpPost $wpPost, string $postUrl): void{
        $wpPost->delete();
        $deletedPost = APIHelper::getRequest($postUrl);
        if (time() > strtotime("2018-12-15")) { // Might be temporarily broken or it might be caching
            $this->assertFalse(stripos($deletedPost, self::$content), "Deleted post at $postUrl should not contain content!");
            $this->assertFalse(stripos($deletedPost, self::$title));
        }
    }
    /**
     * @param WpPost $wpPost
     * @return string
     */
    private function checkWebPage(WpPost $wpPost): string{
        $postUrl = QMWordPressApi::getSiteUrl() . '/' . self::$slug . '/';
        $this->assertEquals($postUrl, $wpPost->getGuidOrWpUrlWithPostName());
        $post = APIHelper::getRequest($postUrl);
        $this->assertNotFalse(stripos($post, self::$content), "Post content not found!");
        $this->assertNotFalse(stripos($post, self::$title));
        return $postUrl;
    }
    private function checkForDuplicatePost(): void{
        $duplicatePostUrl = QMWordPressApi::getSiteUrl() . '/' . self::$slug . '-2/';
        $duplicatePost = APIHelper::getRequest($duplicatePostUrl);
        $this->assertFalse(stripos($duplicatePost, self::$content), "$duplicatePostUrl should not exist");
    }
    private function checkWordPressUser(): void{
        $mike = User::mike();
        QMLog::info("mike");
        QMLog::info(json_encode($mike));
        $wpUser = $mike->getOrCreateWordPressStudiesUser();
        QMLog::info("wp-user");
        QMLog::info(json_encode($wpUser));
        $this->assertEquals(230, $wpUser->getId());
    }
}
