<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\DevOps;
use App\DevOps\Jenkins\Jenkins;
use App\Files\FileHelper;
use App\PhpUnitJobs\JobTestCase;
class BackupJob extends JobTestCase
{
    public function testFixJenkinsPermissions(){
        Jenkins::fixPermissions();
    }
    public function testBackupEnvs(){
        FileHelper::backupEnvs("/mnt/c/laragon/www/BookStack");
        FileHelper::backupEnvs("/www/wwwroot/qm-api");
    }
}
