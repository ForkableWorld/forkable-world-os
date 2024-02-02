<?php

declare(strict_types=1);

namespace Tests\Fitbit\User;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\User\Gender;
use App\DataSources\Connectors\Fitbit\User\GlucoseUnit;
use App\DataSources\Connectors\Fitbit\User\Profile;
use App\DataSources\Connectors\Fitbit\User\StartDay;
use App\DataSources\Connectors\Fitbit\User\User;

class UserTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $user;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->user = new User($this->fitbit);
    }

    public function testGettingTheCurrentUserProfile()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('profile.json')
            ->andReturn('userProfile');
        $this->assertEquals(
            'userProfile',
            $this->user->getProfile()
        );
    }

    public function testUpdatingTheCurrentUserProfile()
    {
        $profile = (new Profile())->setGender(new Gender(Gender::FEMALE))
          ->setStartDayOfWeek(new StartDay(StartDay::SUNDAY))
          ->setGlucoseUnit(new GlucoseUnit(GlucoseUnit::INTERNATIONAL));
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('profile.json?gender=FEMALE&glucoseUnit=any&startDayOfWeek=Sunday')
            ->andReturn('updatedProfile');
        $this->assertEquals(
            'updatedProfile',
            $this->user->updateProfile($profile)
        );
    }

    public function testGettingTheCurrentUserBadges()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('badges.json')
            ->andReturn('userBadges');
        $this->assertEquals(
            'userBadges',
            $this->user->getBadges()
        );
    }
}
