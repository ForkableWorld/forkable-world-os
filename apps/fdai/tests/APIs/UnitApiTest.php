<?php namespace Tests\APIs;
use App\Units\CountUnit;
use Tests\ApiTestTrait;
use App\Models\Unit;
use Tests\UnitTestCase;

class UnitApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_unit()
    {
        $unit = Unit::factory()->make()->toArray();
        $unit['name'] = 'Test Unit';
        $editedUnit = Unit::factory()->make()->toArray();
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser1(
            'POST',
            '/api/v6/units',
            $editedUnit
        );
        $r->assertStatus(401);
        $r = $this->jsonAsUser1(
            'GET',
            '/api/v6/units'
        );
        $r->assertStatus(200);
        $this->assertNames(array (
            0 => '% Recommended Daily Allowance',
            1 => '-4 to 4 Rating',
            2 => '0 to 1 Rating',
            3 => '0 to 5 Rating',
            4 => '1 to 10 Rating',
            5 => '1 to 3 Rating',
            6 => '1 to 5 Rating',
            7 => 'Applications',
            8 => 'Beats per Minute',
            9 => 'Calories',
            10 => 'Capsules',
            11 => 'Centimeters',
            12 => 'Count',
            13 => 'Decibels',
            14 => 'Degrees Celsius',
            15 => 'Degrees East',
            16 => 'Degrees Fahrenheit',
            17 => 'Degrees North',
            18 => 'Dollars',
            19 => 'Doses',
            20 => 'Drops',
            21 => 'Event',
            22 => 'Feet',
            23 => 'Gigabecquerel',
            24 => 'Grams',
            25 => 'Hectopascal',
            26 => 'Hours',
            27 => 'Inches',
            28 => 'Index',
            29 => 'International Units',
            30 => 'Kilocalories',
            31 => 'Kilograms',
            32 => 'Kilometers',
            33 => 'Liters',
            34 => 'Meters',
            35 => 'Meters per Second',
            36 => 'Micrograms',
            37 => 'Micrograms per decilitre',
            38 => 'Miles',
            39 => 'Miles per Hour',
            40 => 'Millibar',
            41 => 'Milligrams',
            42 => 'Milliliters',
            43 => 'Millimeters',
            44 => 'Millimeters Merc',
            45 => 'Milliseconds',
            46 => 'Minutes',
            47 => 'Ounces',
            48 => 'Parts per Million',
            49 => 'Pascal',
            50 => 'Percent',
            51 => 'Pieces',
            52 => 'Pills',
            53 => 'Pounds',
            54 => 'Puffs',
            55 => 'Quarts',
            56 => 'Seconds',
            57 => 'Serving',
            58 => 'Sprays',
            59 => 'Tablets',
            60 => 'Torr',
            61 => 'Units',
            62 => 'Yes/No',
            63 => 'per Minute',
        ), $this->getJsonResponseData());
        $r = $this->jsonAsUser1(
            'GET',
            '/api/v6/units/'.CountUnit::ID
        );
        $r->assertStatus(200);
        $this->assertApiResponse(array (
            'abbreviated_name' => 'count',
            'advanced' => false,
            'conversion_steps' =>
                array (
                    0 =>
                        array (
                            'operation' => 'MULTIPLY',
                            'value' => 1,
                        ),
                ),
            'filling_type' => 'zero',
            'filling_value' => 0,
            'id' => 23,
            'manual_tracking' => true,
            'maximum_daily_value' => NULL,
            'maximum_value' => NULL,
            'minimum_value' => 0,
            'name' => 'Count',
            'number_of_variables_where_default_unit' => NULL,
            'scale' => 'ratio',
            'slug' => 'count',
            'sort_order' => 0,
            'synonyms' => NULL,
            'title' => 'Count',
            'unit_category_id' => 13,
        ));
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser1(
            'PUT',
            '/api/v6/units/'.CountUnit::ID,
            ['name' => 'Test Unit']
        );

        $r->assertStatus(401);
        $this->expectUnauthorizedException();

        $r = $this->jsonAsUser1(
            'DELETE',
             '/api/v6/units/'.CountUnit::ID,
         );
        $r->assertStatus(401);
    }

}
