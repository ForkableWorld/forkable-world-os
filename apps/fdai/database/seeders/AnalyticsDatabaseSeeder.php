<?php

namespace Database\Seeders;

use App\Logging\QMLog;

class AnalyticsDatabaseSeeder extends DatabaseSeeder
{

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //parent::run();  // Run the default seeder separately if necessary to avoid duplicating the variable import
	    // which takes a long time
        QMLog::logStartOfProcess(__METHOD__);
		$db = static::getDB();
	    $db::disableForeignKeyConstraints();
        $this->call(UserVariablesTableSeeder::class);
        $this->call(TrackingRemindersTableSeeder::class);
        $this->call(TrackingReminderNotificationsTableSeeder::class);
        $this->call(MeasurementsTableSeeder::class);
        $this->call(GlobalVariableRelationshipsTableSeeder::class);
        $this->call(CorrelationsTableSeeder::class);
        $this->call(UserVariableClientsTableSeeder::class);
	    $db::enableForeignKeyConstraints();
        QMLog::logEndOfProcess(__METHOD__);
    }
}
