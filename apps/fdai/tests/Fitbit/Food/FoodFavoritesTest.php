<?php

declare(strict_types=1);

namespace Tests\Fitbit\Food;

use Mockery;
use App\DataSources\Connectors\Fitbit\Api\Fitbit;
use App\DataSources\Connectors\Fitbit\Food\Favorite\Favorites;

class FoodFavoritesTest extends \Tests\Fitbit\FitbitTestCase
{
    private $fitbit;
    private $favorites;

    public function setUp():void
    {
        parent::setUp();
        $this->fitbit = Mockery::mock(Fitbit::class);
        $this->favorites = new Favorites($this->fitbit);
    }

    public function testGettingAllFavorites()
    {
        $this->fitbit->shouldReceive('get')
            ->once()
            ->with('foods/log/favorite.json')
            ->andReturn('favorites');
        $this->assertEquals(
            'favorites',
            $this->favorites->get()
        );
    }

    public function testAddingAFoodFavorite()
    {
        $this->fitbit->shouldReceive('post')
            ->once()
            ->with('foods/log/favorite/foodId.json')
            ->andReturn('addedFavoriteFood');
        $this->assertEquals(
            'addedFavoriteFood',
            $this->favorites->add('foodId')
        );
    }

    public function testRemovingAFoodFavorite()
    {
        $this->fitbit->shouldReceive('delete')
            ->once()
            ->with('foods/log/favorite/foodId.json')
            ->andReturn('');
        $this->assertEquals(
            '',
            $this->favorites->remove('foodId')
        );
    }
}
