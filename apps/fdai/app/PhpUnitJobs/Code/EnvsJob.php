<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Code;
use App\Files\Env\EnvFile;
use App\PhpUnitJobs\JobTestCase;
use App\Utils\SecretHelper;
use App\Files\PHP\BasePhpUnitTestFile;
class EnvsJob extends JobTestCase {
	public function testGenerateEnvs(){
		EnvFile::generateAll();
	}
	public function testCleanupEnvs(){
		EnvFile::generateEnvGlobal();
		EnvFile::deleteUnusedEnvs();
		BasePhpUnitTestFile::clean();
	}
	public function testReplaceHardCodedEnvs(){
		SecretHelper::replaceHardCodedEnvs();
	}
}
