<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TrackingReminderNotificationsTableSeeder extends AbstractSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('tracking_reminder_notifications')->delete();
        
        \DB::table('tracking_reminder_notifications')->insert(array (
            0 => 
            array (
                'id' => '1',
                'tracking_reminder_id' => '1',
                'created_at' => '2022-10-02 20:20:46',
                'updated_at' => '2022-10-02 20:20:46',
                'deleted_at' => NULL,
                'user_id' => '1',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 01:00:00',
                'user_variable_id' => '23',
            ),
            1 => 
            array (
                'id' => '2',
                'tracking_reminder_id' => '2',
                'created_at' => '2022-10-02 20:20:46',
                'updated_at' => '2022-10-02 20:20:46',
                'deleted_at' => NULL,
                'user_id' => '2',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 01:00:00',
                'user_variable_id' => '25',
            ),
            2 => 
            array (
                'id' => '3',
                'tracking_reminder_id' => '3',
                'created_at' => '2022-10-02 20:20:47',
                'updated_at' => '2022-10-02 20:20:47',
                'deleted_at' => NULL,
                'user_id' => '4',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 01:00:00',
                'user_variable_id' => '27',
            ),
            3 => 
            array (
                'id' => '4',
                'tracking_reminder_id' => '4',
                'created_at' => '2022-10-02 20:20:48',
                'updated_at' => '2022-10-02 20:20:48',
                'deleted_at' => NULL,
                'user_id' => '5',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 01:00:00',
                'user_variable_id' => '28',
            ),
            4 => 
            array (
                'id' => '5',
                'tracking_reminder_id' => '5',
                'created_at' => '2022-10-02 20:20:50',
                'updated_at' => '2022-10-02 20:20:50',
                'deleted_at' => NULL,
                'user_id' => '8',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 20:00:00',
                'user_variable_id' => '29',
            ),
            5 => 
            array (
                'id' => '6',
                'tracking_reminder_id' => '6',
                'created_at' => '2022-10-02 20:20:51',
                'updated_at' => '2022-10-02 20:20:51',
                'deleted_at' => NULL,
                'user_id' => '230',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 20:00:00',
                'user_variable_id' => '30',
            ),
            6 => 
            array (
                'id' => '7',
                'tracking_reminder_id' => '7',
                'created_at' => '2022-10-02 20:20:52',
                'updated_at' => '2022-10-02 20:20:52',
                'deleted_at' => NULL,
                'user_id' => '11000',
                'notified_at' => NULL,
                'received_at' => NULL,
                'client_id' => 'system',
                'variable_id' => '1398',
                'notify_at' => '2022-10-02 20:00:00',
                'user_variable_id' => '31',
            ),
        ));
        
        
    }
}
