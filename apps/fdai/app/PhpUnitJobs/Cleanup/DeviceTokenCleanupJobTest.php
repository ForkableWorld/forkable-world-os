<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\Slim\Model\Notifications\QMDeviceToken;
use App\Logging\QMLog;
use App\PhpUnitJobs\JobTestCase;
class DeviceTokenCleanupJobTest extends JobTestCase {
    public static function fixBadErrorMessages(): void{
        $badTokens = QMDeviceToken::qmWhere('error_message', "1");
        $badTokenCount = count($badTokens);
        if($badTokenCount){
            QMLog::error("$badTokenCount tokens with error message 1. Fixing");
            QMDeviceToken::writable()->where('error_message', "1")->update(['error_message' => null]);
        }
    }
}
