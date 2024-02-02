<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\Friends;

class ApiFriendsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $friends;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->friends = new Friends($this->fitbit);
    }

    public function testGettingAFriendsInstance()
    {
        $this->assertTrue($this->friends->friends() instanceof \App\DataSources\Connectors\Fitbit\Friends\Friends);
    }
}
