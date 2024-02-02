<?php

declare(strict_types=1);

namespace Tests\Fitbit\Subscriptions;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Subscriptions\CollectionPath;
use App\DataSources\Connectors\Fitbit\Subscriptions\Subscriptions;

class SubscriptionsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $subscriptions;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->subscriptions = new Subscriptions($this->fitbit);
    }

    public function testGettingAllSubscriptions()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('apiSubscriptions.json')
            ->andReturn('allSubscriptionsList');
        $this->assertEquals(
            'allSubscriptionsList',
            $this->subscriptions->getAll()
        );
    }

    public function testGettingCollectionSubscriptions()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('foods/apiSubscriptions.json')
            ->andReturn('foodsSubscriptionsList');
        $this->assertEquals(
            'foodsSubscriptionsList',
                        $this->subscriptions->getCollection(
                            new CollectionPath(CollectionPath::FOODS)
                        )
        );
    }

    public function testAddingSubscriptionToAllCollections()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('apiSubscriptions/subscriptionId.json')
            ->andReturn('addedSubscription');
        $this->assertEquals(
            'addedSubscription',
                        $this->subscriptions->addAll(
                            'subscriptionId'
                        )
        );
    }

    public function testAddingSubscriptionToACollection()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/apiSubscriptions/subscriptionId.json')
            ->andReturn('addedSubscription');
        $this->assertEquals(
            'addedSubscription',
                        $this->subscriptions->addCollection(
                            'subscriptionId',
                            new CollectionPath(CollectionPath::FOODS)
                        )
        );
    }

    public function testRemovingSubscriptionToAllCollections()
    {
        $this->fitbit->shouldReceive('delete')
            ->once()
            ->with('apiSubscriptions/subscriptionId.json')
            ->andReturn('');
        $this->assertEquals(
            '',
                        $this->subscriptions->removeAll(
                            'subscriptionId'
                        )
        );
    }

    public function testRemovingSubscriptionToACollection()
    {
        $this->fitbit->shouldReceive('delete')
            ->once()
            ->with('foods/apiSubscriptions/subscriptionId.json')
            ->andReturn('');
        $this->assertEquals(
            '',
                        $this->subscriptions->removeCollection(
                            'subscriptionId',
                            new CollectionPath(CollectionPath::FOODS)
                        )
        );
    }
}
