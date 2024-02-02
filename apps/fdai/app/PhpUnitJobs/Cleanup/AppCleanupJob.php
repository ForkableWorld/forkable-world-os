<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\Models\OAClient;
use App\DataSources\QMClient;
use App\Storage\DB\Writable;
use App\Logging\QMLog;
use App\PhpUnitJobs\JobTestCase;
class AppCleanupJob extends JobTestCase {
    public function testDeleteTestApps(){
        self::deleteTestAppsCreatedMoreThan24HoursAgo();
    }
    public static function deleteTestAppsCreatedMoreThan24HoursAgo(){
        QMLog::infoWithoutContext('=== '.__FUNCTION__.' ===');
        $underscore =
            OAClient::where(QMClient::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), "%test_app%")
                ->where(QMClient::FIELD_CREATED_AT, '<', db_date(time() - 86400))
                ->get();
        $dash =
            OAClient::where(QMClient::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), "%test-app%")
                ->where(QMClient::FIELD_CREATED_AT, '<', db_date(time() - 86400))
                ->get();
        $clients = $underscore->concat($dash);
        \App\Logging\ConsoleLog::info($clients->count()." test apps");
        /** @var OAClient $client */
        foreach($clients as $client){
            $client->hardDeleteWithRelations(__FUNCTION__);
        }
    }
}
