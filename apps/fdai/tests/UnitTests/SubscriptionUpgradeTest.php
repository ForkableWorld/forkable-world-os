<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Storage\DB\TestDB;

use Tests\UnitTestCase;
class SubscriptionUpgradeTest extends UnitTestCase
{

	/**
	 * @param string $subscriptionProvider
	 * @param string $productId
	 */
    public function updateSingleSubscriptionProvider(string $subscriptionProvider, string $productId){
        $this->setAuthenticatedUser(1);
        $body = $this->postApiV6('userSettings', [
            'subscriptionProvider' => $subscriptionProvider,
            'productId' => $productId
        ], 201, true);
        $this->assertEquals($productId, $body->user->stripePlan);
        $this->assertTrue($body->user->stripeActive);
        $this->assertGreaterThan(0, $body->purchaseId);
        $this->setAuthenticatedUser(1);
        $user = $this->getApiV6('me', [], true);
        /** @var User $user */
        $this->assertEquals($subscriptionProvider, $user->subscription_provider);
        $this->assertEquals($productId, $user->stripe_plan);
    }
    public function testSetSubscriptionProvider(){
		TestDB::resetTestDB();
		$count = OAClient::query()->count();
		$this->assertEquals(51, $count, "We should have 51 clients in test DB");
		$this->assertNotNull(OAClient::find(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT));
        $this->updateSingleSubscriptionProvider('google', 'monthly7');
        $this->updateSingleSubscriptionProvider('apple', 'monthly7');
        $this->updateSingleSubscriptionProvider('stripe', 'monthly7');
        //$this->updateSingleSubscriptionProvider(null, null);
        $this->assertQueryCountLessThan(20);
    }
}
