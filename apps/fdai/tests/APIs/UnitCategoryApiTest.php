<?php namespace Tests\APIs;
use App\UnitCategories\TemperatureUnitCategory;
use Illuminate\Testing\TestResponse;
use Tests\ApiTestTrait;
use App\Models\UnitCategory;
use Tests\UnitTestCase;

class UnitCategoryApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_unit_category()
    {
        $this->createUnitCategory();
        $this->getUnitCategories();
        $this->findUnitCategory();
        $this->updateUnitCategory();
        $this->deleteUnitCategory();
    }

    /**
     * @return TestResponse
     */
    private function createUnitCategory(): TestResponse
    {
        $unit = UnitCategory::factory()->make()->toArray();
        $unit['name'] = 'Test UnitCategory';
        $editedUnitCategory = UnitCategory::factory()->make()->toArray();
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser1(
            'POST',
            '/api/v6/unit_categories',
            $editedUnitCategory
        );
        $r->assertStatus(401);
        return $r;
    }

    /**
     * @return TestResponse
     */
    private function getUnitCategories(): TestResponse
    {
        $r = $this->jsonAsUser1(
            'GET',
            '/api/v6/unit_categories'
        );
        $r->assertStatus(200);
        $this->assertNames([
            0 => 'Currency',
            1 => 'Distance',
            2 => 'Duration',
            3 => 'Energy',
            4 => 'Frequency',
            5 => 'Miscellany',
            6 => 'Pressure',
            7 => 'Proportion',
            8 => 'Rating',
            9 => 'Temperature',
            10 => 'Volume',
            11 => 'Weight',
        ], $this->getJsonResponseData());
        return $r;
    }

    /**
     * @return TestResponse
     */
    private function findUnitCategory(): TestResponse
    {
        $r = $this->jsonAsUser1(
            'GET',
            '/api/v6/unit_categories/' . TemperatureUnitCategory::ID
        );
        $r->assertStatus(200);
        $this->assertApiResponse([
                'can_be_summed' => false,
                'id' => 11,
                'name' => 'Temperature',
                'sort_order' => 0,
	            'subtitle' => 'Type of unit.  Units in the same category can be converted to each other.',
                'title' => 'Temperature',
                'units_count' => NULL,]
        );
        return $r;
    }

    /**
     * @return TestResponse
     */
    private function updateUnitCategory(): TestResponse
    {
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser1(
            'PUT',
            '/api/v6/unit_categories/' . TemperatureUnitCategory::ID,
            ['name' => 'Test UnitCategory']
        );
        $r->assertStatus(401);
        return $r;
    }

    /**
     * @return void
     */
    private function deleteUnitCategory(): void
    {
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser1(
            'DELETE',
            '/api/v6/unit_categories/' . TemperatureUnitCategory::ID,
        );
        $r->assertStatus(401);
    }

}
