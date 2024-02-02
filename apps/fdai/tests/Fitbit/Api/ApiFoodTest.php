<?php

declare(strict_types=1);

namespace Tests\Fitbit\Api;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Api\Food;

class ApiFoodTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $food;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->food = new Food($this->fitbit);
    }

    public function testGettingAFoodsInstance()
    {
        $this->assertTrue($this->food->foods() instanceof \App\DataSources\Connectors\Fitbit\Food\Foods\Foods);
    }

    public function testGettingAWaterLogsInstance()
    {
        $this->assertTrue($this->food->waterLogs() instanceof \App\DataSources\Connectors\Fitbit\Food\Water\Logs);
    }

    public function testGettingATimeSeriesInstance()
    {
        $this->assertTrue($this->food->timeSeries() instanceof \App\DataSources\Connectors\Fitbit\Food\TimeSeries);
    }

    public function testGettingAMealInstance()
    {
        $this->assertTrue($this->food->meals() instanceof \App\DataSources\Connectors\Fitbit\Food\Meals\Meals);
    }

    public function testGettingAGoalsInstance()
    {
        $this->assertTrue($this->food->goals() instanceof \App\DataSources\Connectors\Fitbit\Food\Foods\Goals);
    }

    public function testGettingALogsInstance()
    {
        $this->assertTrue($this->food->logs() instanceof \App\DataSources\Connectors\Fitbit\Food\Foods\Logs);
    }

    public function testGettingAWaterGoalsInstance()
    {
        $this->assertTrue($this->food->waterGoals() instanceof \App\DataSources\Connectors\Fitbit\Food\Water\Goals);
    }

    public function testGettingAFavoritesInstance()
    {
        $this->assertTrue($this->food->favorites() instanceof \App\DataSources\Connectors\Fitbit\Food\Favorite\Favorites);
    }
}
