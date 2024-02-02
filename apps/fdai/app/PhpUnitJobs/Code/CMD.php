<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\Computers\ThisComputer;
use PHPUnit\Framework\TestCase;
class CMD extends TestCase {
    public function testComposerUpdate(){
	    ThisComputer::exec("cd /home/vagrant/qm-api && composer update");
    }
    public function testKillPHPUnit(){
        ThisComputer::killPhpUnit();
    }
    public function testRestartServices(){
        ThisComputer::restartServices();
    }
}
