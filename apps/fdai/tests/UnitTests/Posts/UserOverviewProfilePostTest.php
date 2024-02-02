<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */
namespace Tests\UnitTests\Posts;
use App\Models\BaseModel;
use App\Models\User;
use App\Models\Variable;
use App\Models\WpPost;
use App\Slim\Model\User\QMUser;
use App\Storage\DB\TestDB;
use App\Traits\PostableTrait;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMUserVariable;

use Database\Seeders\GlobalVariableRelationshipsTableSeeder;
use Tests\Traits\TestsCharts;
use Tests\UnitTestCase;
class UserOverviewProfilePostTest extends UnitTestCase {

	use TestsCharts;
    public const DISABLED_UNTIL = "2021-07-06";
    public const REASON_FOR_SKIPPING = "DO Spaces is broken";
    protected function setupDB(){
	    TestDB::resetTestDB();
	    User::query()->update([User::FIELD_ANALYSIS_ENDED_AT => null]);
        //GlobalVariableRelationshipsTableSeeder::deleteGlobalVariableRelationships();
        Variable::query()->update([Variable::FIELD_CHARTS => null]);
        $this->createTreatmentOutcomeMeasurements();
        //$this->createTreatmentOutcomeMeasurementsFromFixtures();
    }
    public function testListRemoteFiles(){
        $this->skipTest("DO Spaces is too slow!");
        if($this->weShouldSkip()){return;}
        $u = $this->getOrSetAuthenticatedUser(1);
        $buttons = $u->getFileButtons();
        $this->assertGreaterThan(2, count($buttons));
        $this->assertDurationLessThan(2);
    }
    public function testUserOverviewAndCharts(){
        if($this->weShouldSkip()){return;}
        $this->setupDB();
        $v = Variable::find(OverallMoodCommonVariable::ID);
        $uv = $v->findUserVariable(1);
        $casts = $uv->getCasts();
        $dates = $uv->getDates();
        $u = $this->getOrSetAuthenticatedUser(1);
        $this->assertTrue($u->needToAnalyze());
        $u->analyzeFullyIfNecessary("testing");
        $this->assertFalse($u->needToAnalyze());
        $mood = $u->getPrimaryOutcomeQMUserVariable();
        $this->assertFalse($mood->needToAnalyze());
        $this->assertFalse($mood->weShouldCalculateCorrelations());
        $treatment = $this->getTreatmentUserVariable();
        $c = $mood->getBestUserVariableRelationship();
        //$this->compareChartGroup($c->getChartGroup(), 'user-variable-relationship');
        $ac = $c->getOrCreateQMGlobalVariableRelationship();
        $this->compareChartGroup($ac->getChartGroup(), 'global-variable-relationship');
        $this->compareChartGroup($mood->getChartGroup(), 'user-variable');
        $cv = $mood->getCommonVariable();
        $this->assertCount(1, $cv->getPredictors());
        $this->assertCount(1, $treatment->getOutcomes());
        $charts = $cv->getChartGroup();
        $charts->getOrSetHighchartConfigs();
        $this->compareChartGroup($charts, 'common-variable');
        $this->assertFalse($treatment->needToAnalyze(), "We should have already analyzed $treatment during user->analyzeFullyIfNecessaryAndPost");
        $this->assertFalse($treatment->weShouldCalculateCorrelations(), "We should have correlated during analyzeFullyIfNecessaryAndPost.
            We should generally correlate with
            non-outcomes as well because if a user gets a ton of mood measurements, for instance, the measurement count
            will rarely reach the 10% change threshold to calculate with new predictors they've recently started tracking.");
        //$this->checkStrongestPredictorAndEffect($mood, $treatment);
        //$this->checkPosts($u, $mood, $treatment);
    }
    /**
     * @param QMUser $u
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkPosts(QMUser $u, QMUserVariable $mood, QMUserVariable $treatment): void{
        $posts = $u->getWpPosts();
        $this->assertGreaterThan(0, count($posts));
        $this->checkMoodPost($mood, $treatment);
        $this->checkTreatmentPost($treatment, $mood);
        $this->checkStudyPost($u, $mood, $treatment);
        $this->checkRootCausePost($u, $mood, $treatment);
        $this->checkUserPost($u, $mood, $treatment);
    }
    /**
     * @param QMUser $u
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkUserPost(QMUser $u,
                                  QMUserVariable $mood,
                                  QMUserVariable $treatment): void{
        $userPost = $u->getOrCreateWpPost();
        $content = $userPost->post_content;
        $this->assertContains($mood->name, $content);
        $this->assertContains($treatment->name, $content);
    }
    /**
     * @param QMUserVariable $treatment
     * @param QMUserVariable $mood
     */
    public function checkTreatmentPost(QMUserVariable $treatment, QMUserVariable $mood): void{
        $post = $this->getUserPostBySlug($treatment);
        $this->checkUserVariableWpPost($post, $treatment);
        $content = $post->post_content;
        $this->assertContains($mood->name, $content);
    }
    /**
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkMoodPost(QMUserVariable $mood, QMUserVariable $treatment): void{
        $post = $this->getUserPostBySlug($mood);
        $this->checkUserVariableWpPost($post, $mood);
        $content = $post->post_content;
        $this->assertContains($treatment->name, $content);
    }
    /**
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkStrongestPredictorAndEffect(QMUserVariable $mood, QMUserVariable $treatment): void{
        $strongestPredictor = $mood->getStrongestPredictor();
        $this->assertEquals($treatment->name, $strongestPredictor->name);
        $strongestEffect = $treatment->getStrongestEffect();
        $this->assertEquals($mood->name, $strongestEffect->name);
    }
    /**
     * @param QMUser $u
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkStudyPost(QMUser $u, QMUserVariable $mood, QMUserVariable $treatment): void{
        $correlations = $u->getCorrelationsForOutcome(OverallMoodCommonVariable::ID);
        $this->assertCount(1, $correlations);
        $correlation = $correlations[0];
        $charts = $correlation->getChartGroup();
        $this->compareChartGroup($charts, __FUNCTION__);
        $post = $this->getUserPostBySlug($correlation);
        $content = $post->post_content;
        $this->assertContains("Over Onset Delays", $content);
        $this->assertContains($mood->name, $content);
        $this->assertContains($treatment->name, $content);
        $this->assertContains("Higher $treatment->displayName Predicts Moderately Higher $mood->displayName", $content);
    }
    /**
     * @param QMUser $u
     * @param QMUserVariable $mood
     * @param QMUserVariable $treatment
     */
    public function checkRootCausePost(QMUser $u, QMUserVariable $mood, QMUserVariable $treatment): void{
        $a = $u->getRootCauseAnalysis();
        $post = $this->getUserPostBySlug($a);
        $content = $post->post_content;
        $this->assertContains($mood->name, $content);
        $this->assertContains($treatment->name, $content);
        $this->assertContains("Higher $treatment->displayName Predicts Moderately Higher $mood->displayName", $content);
    }
    /**
     * @param BaseModel|PostableTrait $model
     * @return WpPost
     */
    protected function getUserPostBySlug($model): WpPost {
        $post = WpPost::wherePostName($model->getUniqueIndexIdsSlug())->first();
        $this->assertEquals(1, $post->user_id);
        $this->assertEquals($model->getSubtitleAttribute(), $post->excerpt);
        $this->assertEquals($model->getTitleAttribute(), $post->post_title);
        $this->assertEquals($model->getImage(), $post->image);
        $this->assertEquals($model->getUrl(), $post->guid);
        return $post;
    }
	/**
	 * @param WpPost $post
	 * @param QMUserVariable $v
	 */
	protected function checkUserVariableWpPost(WpPost $post, QMUserVariable $v): void{
		$u = $post->getQMUser();
		$content = $post->post_content;
		HtmlHelper::validateHtml($content, "post content");
		$this->assertContainsVariableChartImagesWithInlineStyles($content, $v);
		$this->assertNotContains($u->getDisplayNameAttribute(), $post->post_title);
		$this->assertContains($v->getOrSetVariableDisplayName(), $post->post_title);
		$this->assertContains((string)$u->getId(), $post->post_name);
		$this->assertEquals($v->getImage(), $post->image);
		$this->assertContains($v->name, $content);
		$this->checkPostPreviewCard($post, $v);
		$this->assertEquals(WpPost::PARENT_CATEGORY_VARIABLE_OVERVIEWS, $post->getParentCategoryName());
		$this->assertEquals(WpPost::CATEGORY_INDIVIDUAL_PARTICIPANT_VARIABLE_OVERVIEWS, $post->getCategoryName());
	}
	/**
	 * @param string $html
	 * @param QMUserVariable $v
	 */
	protected function assertContainsVariableChartImagesWithInlineStyles(string $html, QMUserVariable $v): void{
		$slug = QMStr::slugify($v->name);
		$this->assertContains("<img id=\"average-$slug-by-day-of-week", $html);
		$this->assertContains("<img id=\"average-$slug-by-month", $html);
		$this->assertContains("Daily $v->name Distribution", $html);
		$this->assertContains(ImageHelper::CHART_IMAGE_STYLES, $html, "We need these styles inline to avoid blurriness");
	}
	/**
	 * @param WpPost $post
	 * @param PostableTrait $model
	 */
	protected function checkPostPreviewCard(WpPost $post, $model): void{
		$linkBox = $post->getPreviewCard()->html;
		$this->assertContains($post->guid, $linkBox);
		//$this->assertContains($model->getUrl(), $linkBox);
		$this->assertContains($model->getTitleAttribute(), $linkBox);
		$this->assertContains($model->getSubtitleAttribute(), $linkBox);
	}
}
