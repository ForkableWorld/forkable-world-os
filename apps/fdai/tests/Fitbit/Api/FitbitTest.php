<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\OAuth\Client\Client;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class FitbitTest extends \Tests\Fitbit\FitbitTestCase
{
    private $client;
    private $fitbit;

    public function setUp():void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->fitbit = new Fitbit($this->client);
    }

    public function testMakingAGetCall()
    {
        $this->client->shouldReceive('get')
            ->once()
            ->with('https://api.fitbit.com/1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->get('sampleurl')
        );
    }

    public function testMakingAGetCallToANonUserEndpoint()
    {
        $this->client->shouldReceive('get')
            ->once()
            ->with('https://api.fitbit.com/1/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->getNonUserEndpoint('sampleurl')
        );
    }

    public function testMakingAPostCallWithAPostBody()
    {
        $body = [];
        $this->client->shouldReceive('post')
            ->once()
                        ->with(
                            'https://api.fitbit.com/1/user/-/sampleurl',
                            ['json' => $body]
                        )
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->postBody('sampleurl', $body)
        );
    }

    public function testMakingAPostCallToANonUserEndpoint()
    {
        $this->client->shouldReceive('post')
            ->once()
            ->with('https://api.fitbit.com/1/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->postNonUserEndpoint('sampleurl')
        );
    }

    public function testMakingAGetCallToAV11Endpoint()
    {
        $this->client->shouldReceive('get')
            ->once()
            ->with('https://api.fitbit.com/1.1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->getv11Endpoint('sampleurl')
        );
    }

    public function testMakingAPostCallToAV11Endpoint()
    {
        $this->client->shouldReceive('post')
            ->once()
            ->with('https://api.fitbit.com/1.1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->postv11Endpoint('sampleurl')
        );
    }

    public function testMakingAGetCallToAV12Endpoint()
    {
        $this->client->shouldReceive('get')
            ->once()
            ->with('https://api.fitbit.com/1.2/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->getv12Endpoint('sampleurl')
        );
    }

    public function testMakingAPostCallToAV12Endpoint()
    {
        $this->client->shouldReceive('post')
            ->once()
            ->with('https://api.fitbit.com/1.2/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->postv12Endpoint('sampleurl')
        );
    }

    public function testMakingAPostCall()
    {
        $this->client->shouldReceive('post')
            ->once()
            ->with('https://api.fitbit.com/1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->post('sampleurl')
        );
    }

    public function testMakingADeleteCall()
    {
        $this->client->shouldReceive('delete')
            ->once()
            ->with('https://api.fitbit.com/1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->once()
            ->with()
            ->andReturn('responseContent');
        $this->assertEquals(
            'responseContent',
            $this->fitbit->delete('sampleurl')
        );
    }

    public function testSettingTheUser()
    {
        $userId = 23;
        $this->client->shouldReceive('delete')
            ->once()
            ->with('https://api.fitbit.com/1/user/23/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('delete')
            ->once()
            ->with('https://api.fitbit.com/1/user/-/sampleurl')
            ->andReturn($this->client);
        $this->client->shouldReceive('getBody->getContents')
            ->twice()
            ->with()
            ->andReturn('responseContent');

        $this->fitbit->userId($userId);
        $this->assertEquals(
            'responseContent',
            $this->fitbit->delete('sampleurl')
        );

        $this->fitbit->currentUser($userId);
        $this->assertEquals(
            'responseContent',
            $this->fitbit->delete('sampleurl')
        );
    }

    public function testGettingAnActivitiesInstance()
    {
        $this->assertTrue($this->fitbit->activities() instanceof \App\DataSources\Connectors\Fitbit\Api\Activities);
    }

    public function testGettingAUserInstance()
    {
        $this->assertTrue($this->fitbit->user() instanceof \App\DataSources\Connectors\Fitbit\Api\FitbitUser);
    }

    public function testGettingAHeartRateInstance()
    {
        $this->assertTrue($this->fitbit->heartRate() instanceof \App\DataSources\Connectors\Fitbit\Api\HeartRate);
    }

    public function testGettingASleepLogsInstance()
    {
        $this->assertTrue($this->fitbit->sleepLogs() instanceof \App\DataSources\Connectors\Fitbit\Api\SleepLogs);
    }

    public function testGettingAFriendsInstance()
    {
        $this->assertTrue($this->fitbit->friends() instanceof \App\DataSources\Connectors\Fitbit\Api\Friends);
    }

    public function testGettingADevicesInstance()
    {
        $this->assertTrue($this->fitbit->devices() instanceof \App\DataSources\Connectors\Fitbit\Api\Devices);
    }

    public function testGettingABodyInstance()
    {
        $this->assertTrue($this->fitbit->body() instanceof \App\DataSources\Connectors\Fitbit\Api\Body);
    }

    public function testGettingAFoodInstance()
    {
        $this->assertTrue($this->fitbit->food() instanceof \App\DataSources\Connectors\Fitbit\Api\Food);
    }

    public function testGettingASubscriptionsInstance()
    {
        $this->assertTrue($this->fitbit->subscriptions() instanceof \App\DataSources\Connectors\Fitbit\Api\Subscriptions);
    }
}
