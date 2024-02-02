<?php

declare(strict_types=1);

namespace Tests\Fitbit\Food;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Food\Foods\CaloriesGoal;
use App\DataSources\Connectors\Fitbit\Food\Foods\Goals;
use App\DataSources\Connectors\Fitbit\Food\Foods\Intensity;
use App\DataSources\Connectors\Fitbit\Food\Foods\IntensityGoal;

class FoodGoalsTest extends \Tests\Fitbit\FitbitTestCase
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
            ->with('foods/log/goal.json')
            ->andReturn('foodGoals');
        $this->assertEquals(
            'foodGoals',
            $this->goals->get()
        );
    }

    public function testSettingAnIntensityGoal()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/goal.json?intensity=KINDAHARD&personalized=true')
            ->andReturn('updatedFoodGoals');
        $this->assertEquals(
            'updatedFoodGoals',
            $this->goals->update(new IntensityGoal(new Intensity(Intensity::KINDAHARD), true))
        );
    }

    public function testSettingACaloriesGoal()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/goal.json?calories=5000&personalized=false')
            ->andReturn('updatedFoodGoals');
        $this->assertEquals(
            'updatedFoodGoals',
            $this->goals->update(new CaloriesGoal(5000, false))
        );
    }
}
