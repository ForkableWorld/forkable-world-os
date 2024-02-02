<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\FitbitUser;

class ApiUserTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $user;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->user = new FitbitUser($this->fitbit);
    }

    public function testGettingAUserInstance()
    {
        $this->assertTrue($this->user->user() instanceof \App\DataSources\Connectors\Fitbit\User\User);
    }
}
