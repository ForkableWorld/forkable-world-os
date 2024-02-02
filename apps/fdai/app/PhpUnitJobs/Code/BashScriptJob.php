<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Code;
use App\Files\Bash\BashScriptFile;
use App\PhpUnitJobs\JobTestCase;
use App\Repos\HomesteadRepo;
class BashScriptJob extends JobTestCase
{
	public function testGenerateProvisionScript(){
		//$i->updateFirewall();
		HomesteadRepo::generateProvisionScript();
		HomesteadRepo::copyScripts();
	}
    public function testAddLogStartLogEnd(){
	    BashScriptFile::addGlobalLinesToAll();
        BashScriptFile::appendToAllIfMissing("log_end_of_script");
    }
}
