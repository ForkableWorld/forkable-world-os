<?php
namespace Tests\UnitTests;
use App\Models\User;
use App\Models\WpPost;
use App\PhpUnitJobs\JobTestCase;
use App\Storage\DB\TestDB;
use Tests\UnitTestCase;
class UserPostableTest extends UnitTestCase {
    public function testPostableFunctions(){
        $this->skipTest("do we need this?");
	    TestDB::deleteUserData();
        $u = $this->getOrSetAuthenticatedUser(1);
        $this->assertEquals("users-1", $u->getUniqueIndexIdsSlug());
        JobTestCase::setMaximumJobDuration(0.00001);
        JobTestCase::resetStartTime();
        sleep(1);
        TestDB::deleteWpData();
        $this->assertNumberOfPosts(0, "should have 0 posts after deleteWpData");
        User::query()->update([User::FIELD_IS_PUBLIC => true]);
        User::postWhereNeverPosted();
        $this->assertNumberOfPosts(1, "should have 1 post after postWhereNeverPosted");
        $this->assertNumberOfStalePosts(0);
        WpPost::query()->update([
            WpPost::FIELD_POST_MODIFIED => db_date(time() - 2 * 86400)
        ]);
        $this->assertNumberOfPosts(1);
        $this->assertNumberOfStalePosts(1);
        JobTestCase::resetStartTime();
        User::postWhereStale();
        $count = WpPost::count();
        $this->assertGreaterThan(0, $count);
        $post = WpPost::first();
        $this->assertEquals("users-1", $post->post_name);
    }
    private function assertNumberOfPosts(int $expected, string $message = null){
        $names = WpPost::pluck(WpPost::FIELD_POST_NAME);
        $count = $names->count();
        if(!$message){$message = "should have $expected post(s)";}
        lei($count !== $expected,"$message but we have $count. names: ",
               $names->all());
    }
    private function assertNumberOfStalePosts(int $expected){
        $stale = User::wherePostStale()->get();
        $names = $stale->pluck('names');
        $count = $stale->count();
        lei($count !== $expected,
            "should have $count STALE post(s) but we have $count STALE posts . names: ",
            $names->all());
    }
}
