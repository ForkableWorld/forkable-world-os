<?php

declare(strict_types=1);

namespace Tests\Fitbit\Body;

use Carbon\Carbon;
use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Body\Goals\Goals;
use App\DataSources\Connectors\Fitbit\Body\Goals\GoalType;
use App\DataSources\Connectors\Fitbit\Body\Goals\WeightGoal;

class BodyGoalsTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $goals;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->goals = new Goals($this->fitbit);
    }

    public function testGettingAGoal()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('body/log/fat/goal.json')
            ->andReturn('fatGoal');
        $this->assertEquals(
            'fatGoal',
            $this->goals->get(new GoalType(GoalType::FAT))
        );
    }

    public function testUpdatingTheFatGoal()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('body/log/fat/goal.json?fat=12.5')
            ->andReturn('fatGoal');
        $this->assertEquals(
            'fatGoal',
            $this->goals->updateFat(1250)
        );
    }

    public function testUpdatingTheWeightGoal()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
                        ->with('body/log/weight/goal.json?startDate=2019-03-21' .
                        '&startWeight=65.3&weight=63.9')
            ->andReturn('weightGoal');
        $this->assertEquals(
            'weightGoal',
                        $this->goals->updateWeight(
                            new WeightGoal(Carbon::now(), 6530, 6390)
                        )
        );
    }
}
