<?php

declare(strict_types=1);

namespace Tests\Fitbit\Activity;

use Mockery;
use App\DataSources\Connectors\Fitbit\Activity\Favorites;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;

class FavoritesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $favorites;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->favorites = new Favorites($this->fitbit);
    }

    public function testGettingAListOfFavoriteActivities()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('activities/favorite.json')
            ->andReturn('favoriteActivities');
        $this->assertEquals(
            'favoriteActivities',
            $this->favorites->get()
        );
    }

    public function testAddingAFavoriteActivity()
    {
        $activityId = '10190';
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('activities/favorite/10190.json')
            ->andReturn('added');
        $this->assertEquals(
            'added',
            $this->favorites->add($activityId)
        );
    }

    public function testRemovingAFavoriteActivity()
    {
        $activityId = '10190';
        $this->fitbit->shouldReceive('delete')
            ->once()
            ->with('activities/favorite/10190.json')
            ->andReturn('removed');
        $this->assertEquals(
            'removed',
            $this->favorites->remove($activityId)
        );
    }
}
