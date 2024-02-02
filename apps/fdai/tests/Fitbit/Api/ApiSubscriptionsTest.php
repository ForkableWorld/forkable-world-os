<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\Subscriptions;

class ApiSubscriptionsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $subscriptions;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->subscriptions = new Subscriptions($this->fitbit);
    }

    public function testGettingASubscriptionsInstance()
    {
        $this->assertTrue($this->subscriptions->subscriptions() instanceof \App\DataSources\Connectors\Fitbit\Subscriptions\Subscriptions);
    }
}
