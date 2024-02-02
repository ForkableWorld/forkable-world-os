<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserVariableClientsTableSeeder extends AbstractSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('user_variable_clients')->delete();
        
        \DB::table('user_variable_clients')->insert(array (
            0 => 
            array (
                'id' => '21',
                'client_id' => 'oauth_test_client',
                'created_at' => '2022-09-30 12:57:50',
                'deleted_at' => NULL,
                'earliest_measurement_at' => '2019-09-03 00:00:00',
                'latest_measurement_at' => '2019-12-31 00:00:00',
                'number_of_measurements' => '120',
                'updated_at' => '2022-09-30 12:57:50',
                'user_id' => '1',
                'user_variable_id' => '23',
                'variable_id' => '1398',
            ),
            1 => 
            array (
                'id' => '22',
                'client_id' => 'oauth_test_client',
                'created_at' => '2022-09-30 12:57:51',
                'deleted_at' => NULL,
                'earliest_measurement_at' => '2019-09-03 00:00:00',
                'latest_measurement_at' => '2019-12-31 00:00:00',
                'number_of_measurements' => '120',
                'updated_at' => '2022-09-30 12:57:51',
                'user_id' => '1',
                'user_variable_id' => '24',
                'variable_id' => '1276',
            ),
            2 => 
            array (
                'id' => '23',
                'client_id' => 'oauth_test_client',
                'created_at' => '2022-09-30 12:57:51',
                'deleted_at' => NULL,
                'earliest_measurement_at' => '2019-09-03 00:00:00',
                'latest_measurement_at' => '2019-12-31 00:00:00',
                'number_of_measurements' => '120',
                'updated_at' => '2022-09-30 12:57:51',
                'user_id' => '2',
                'user_variable_id' => '25',
                'variable_id' => '1398',
            ),
            3 => 
            array (
                'id' => '24',
                'client_id' => 'oauth_test_client',
                'created_at' => '2022-09-30 12:57:51',
                'deleted_at' => NULL,
                'earliest_measurement_at' => '2019-09-03 00:00:00',
                'latest_measurement_at' => '2019-12-31 00:00:00',
                'number_of_measurements' => '120',
                'updated_at' => '2022-09-30 12:57:51',
                'user_id' => '2',
                'user_variable_id' => '26',
                'variable_id' => '1276',
            ),
        ));
        
        
    }
}
