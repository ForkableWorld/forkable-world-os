<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UnitCategoriesTableSeeder extends AbstractSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('unit_categories')->delete();
        
        \DB::table('unit_categories')->insert(array (
            0 => 
            array (
                'id' => '1',
                'name' => 'Duration',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            1 => 
            array (
                'id' => '2',
                'name' => 'Distance',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            2 => 
            array (
                'id' => '3',
                'name' => 'Weight',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            3 => 
            array (
                'id' => '4',
                'name' => 'Volume',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            4 => 
            array (
                'id' => '5',
                'name' => 'Rating',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '0',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            5 => 
            array (
                'id' => '6',
                'name' => 'Miscellany',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            6 => 
            array (
                'id' => '7',
                'name' => 'Energy',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            7 => 
            array (
                'id' => '8',
                'name' => 'Proportion',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '0',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            8 => 
            array (
                'id' => '9',
                'name' => 'Frequency',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '0',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            9 => 
            array (
                'id' => '10',
                'name' => 'Pressure',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '0',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            10 => 
            array (
                'id' => '11',
                'name' => 'Temperature',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '0',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
            11 => 
            array (
                'id' => '12',
                'name' => 'Currency',
                'created_at' => '2020-01-01 00:00:00',
                'updated_at' => '2020-01-01 00:00:00',
                'can_be_summed' => '1',
                'deleted_at' => NULL,
                'sort_order' => '0',
            ),
        ));
        
        
    }
}
