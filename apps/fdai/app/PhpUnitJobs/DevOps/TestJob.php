<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\DevOps;
use App\Computers\ThisComputer;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\PhpUnitJobs\JobTestCase;
use App\Types\QMStr;
use Symfony\Component\Process\Process;
class TestJob extends JobTestCase
{
    const XDEBUG_PARAMS = "-dxdebug.remote_enable=1 -dxdebug.remote_mode=req -dxdebug.remote_port=9000 -dxdebug.client_host=192.168.1.113";
    public function testReplaceInHtml(){
        FileHelper::replaceInProjectFiles("tocbot/4.12.3", "tocbot/4.12.3");
    }
    /**
     * @return string[]
     */
    private static function getTestFilesThatCheckHtml(): array {
        $files = FileFinder::getFilesContaining('tests', "->compareHtml", true, '.php');
        $keep = [];
        foreach($files as $file){
            if(stripos($file, "Traits") === false){
                $keep[] = $file;
            }
        }
        return $keep;
    }
    public static function createHtmlUpdateScript(bool $debug = false){
        $files = self::getTestFilesThatCheckHtml();
        $script = "#!/usr/bin/env bash
SCRIPT_PATH=\"\$(cd \"\$(dirname \"\${BASH_SOURCE[0]}\")\" && pwd)/\$(basename \"\${BASH_SOURCE[0]}\")\"
TEST_FOLDER=`dirname \${SCRIPT_PATH}` && cd \${TEST_FOLDER} && cd .. && export QM_API=\"\$PWD\"
set -x
";
        foreach($files as $filePath){
            if(stripos($filePath, "Traits") !== false){continue;}
            $cmd = self::phpUnitCommand4File($filePath, $debug);
            $script .= "
$cmd
";
        }
        FileHelper::writeByFilePath('tests/update_html_fixtures.sh', $script);
    }
    public static function phpUnitCommand4File(string $filePath, bool $debug = false): string{
		$folder = FileHelper::getFolderFromPath($filePath);
		$name = FileHelper::getFileNameFromPath($filePath);
		$ns = FileHelper::filePathToNamespace($filePath);
		$ns = str_replace('\\', '\\\\', $ns);
		$pre = self::xdebugPrefix();
        return "$pre --filter '$ns' --test-suffix $name $folder --teamcity";
    }
	private static function xdebugPrefix(): string{
		$abs = abs_path();
		return"export TERM=xterm && export IDE_PHPUNIT_CUSTOM_LOADER=$abs/vendor/autoload.php && export XDEBUG_CONFIG=idekey=17046 && cd $abs/tests && /usr/bin/php -dxdebug.mode=debug -dxdebug.client_port=9000 -dxdebug.client_host=127.0.0.1 $abs/vendor/phpunit/phpunit/phpunit --configuration $abs/phpunit.xml";
	}
	private static function runPrefix(): string{
		$abs = abs_path();
		return"export TERM=xterm && export IDE_PHPUNIT_CUSTOM_LOADER=$abs/vendor/autoload.php && cd $abs/tests && /usr/bin/php $abs/vendor/phpunit/phpunit/phpunit --configuration $abs/phpunit.xml";
	}
    public static function phpUnitCommand4Test(string $class, string $testName, bool $debug = false): string{
        $filePath = FileHelper::classToPath($class, true);
		$fileName =  FileHelper::toFileName($filePath);
		$folder = FileHelper::getFolderFromPath($filePath);
        $class = QMStr::removeIfFirstCharacter("\\", $class);
        $class = QMStr::doubleForwardSlash($class);
		$suffix = " --filter '/($class::$testName)( .*)?$/' --test-suffix $fileName $folder --teamcity";
		if($debug){return self::xdebugPrefix().$suffix;}
        return self::runPrefix().$suffix;
    }
    /**
     * @param string $file
     * @param bool $debug
     */
    public static function testFile(string $file, bool $debug = false): void{
        $cmd = self::phpUnitCommand4File($file, $debug);
        ThisComputer::execInIsolation($cmd);
    }
	/**
	 * @param string $class
	 * @param string $function
	 * @param bool $debug
	 * @return Process
	 */
    public static function testPHPUnitFunction(string $class, string $function, bool $debug = false, array $env = []): Process{
        $cmd = self::phpUnitCommand4Test($class, $function, $debug);
        return ThisComputer::execInIsolation($cmd, $env);
    }
}
