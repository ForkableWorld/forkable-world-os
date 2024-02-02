<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\AppSettings\PhysicianApplication;
use App\Logging\QMLog;
use App\Slim\Model\User\PhysicianUser;
use App\PhpUnitJobs\JobTestCase;
class PhysicianCleanupJobTest extends JobTestCase {
    public function testFixPhysicianClientIds(){
        $apps = PhysicianApplication::getAll();
        $physicians = [];
        $wrong = [];
        foreach($apps as $app){
            $user = $app->getQmUser();
            $physician = PhysicianUser::instantiateIfNecessary($user);
            $appClientId = $app->getClientId();
            $physicianClientId = $physician->getPhysicianClientId();
            if($appClientId !== $physicianClientId){
                $wrong[] = $app;
            }
        }
        if($wrong){
            $total = count($wrong);
            QMLog::infoWithoutContext("$total physician apps have wrong client id");
            $i = 0;
            foreach($wrong as $app){
                $i++;
                QMLog::infoWithoutContext("=== Fixing $i of $total physician apps === ");
                $user = $app->getQmUser();
                $physician = PhysicianUser::instantiateIfNecessary($user);
                $appClientId = $app->getClientId();
                $physicianClientId = $physician->getPhysicianClientId();
                if($appClientId !== $physicianClientId){
                    try {
                        $app->updateClientId($physicianClientId, false);
                    } catch (\Throwable $e){
                        QMLog::info(__METHOD__.": ".$e->getMessage());
                        $app->softDelete([], "bad physician client id");
                    }
                }
            }
        }
        $this->assertGreaterThan(0, count($physicians));
    }
    public function testCreateReminders(){
        $physicians = PhysicianApplication::getAll();
        foreach($physicians as $physician){
            $patients = $physician->getUsers();
            foreach($patients as $patient){
                $physician->logInfo("Creating reminders for $patient");
                //$client->createReminders($user);
            }
        }
    }
}
