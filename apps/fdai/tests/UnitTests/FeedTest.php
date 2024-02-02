<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Models\DeviceToken;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Notifications\QMDeviceToken;

use App\Storage\DB\TestDB;
use Tests\UnitTestCase;
class FeedTest extends UnitTestCase {
    public function testAskQuestion(){
        $this->skipTest("Getting the test tokens keeps failing");
        TestDB::importTestDatabase();
        DeviceToken::deleteAll();
        QMDeviceToken::saveTestTokenToDatabase(BasePlatformProperty::PLATFORM_WEB, UserIdProperty::USER_ID_MIKE);
        $this->setAuthenticatedUser(1);
        $body = $this->postApiV6('feed', [
            'intent' => 'question',
            'text' => 'What are your Painted Picture day/week/month/year goals?'
        ]);
        $this->assertCount(1, $body->cards);
    }
}
