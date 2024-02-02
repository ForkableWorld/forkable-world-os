<?php

declare(strict_types=1);

namespace Tests\Fitbit\Food;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Food\Water\Goals;
use App\DataSources\Connectors\Fitbit\Food\Water\WaterGoal;

class WaterGoalsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $goals;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->goals = new Goals($this->fitbit);
    }

    public function testGettingGoals()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('foods/log/water/goal.json')
            ->andReturn('waterGoals');
        $this->assertEquals(
            'waterGoals',
            $this->goals->get()
        );
    }

    public function testSettingAWaterGoal()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/water/goal.json?target=5.5')
            ->andReturn('updatedWaterGoal');
        $this->assertEquals(
            'updatedWaterGoal',
            $this->goals->update(new WaterGoal(55))
        );
    }
}
