<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\DevOps;
use App\Computers\PhpUnitComputer;
use App\DevOps\DigitalOcean\QMDroplet;
use App\PhpUnitJobs\JobTestCase;
class ServerJob extends JobTestCase
{
    public function testInstallPHP74(){
        $instances = PhpUnitComputer::getInstances();
        foreach($instances as $instance){
            $instance->installPHP74IfNecessary();
        }
    }
    public function testCreatePhpUnitInstances(){
        PhpUnitComputer::refillInstances(15);
    }
    public function testCreateDroplets(){
        QMDroplet::createDroplets(14);
    }
    public function testDeleteDroplets(){
        QMDroplet::destroyWhereStartsWith('slave-');
    }
}
