<?php
namespace Tests\TestGenerators;
use App\Files\PHP\BasePhpUnitTestFile;
use App\Logging\SolutionButton;
class StagingJobTestFile extends BasePhpUnitTestFile
{
    /**
     * @param string $testName
     * @param string $functions
     * @param string $class
     * @return string
     */
    public static function getUrl(string $testName, string $functions, string $class): string{
        $content = self::getTestContent($testName, $functions, $class);
	    $url = self::generateUrl($testName, $content);
	    SolutionButton::add($testName, $url, $content);
        return $url;
    }
    /**
     * @param string $testName
     * @param string $functions
     * @param string $class
     * @return string
     */
    public static function getTestContent(string $testName, string $functions, string $class): string{
        $tab = chr(9);
        $testCase = 'SlimStagingTestCase';
        $ns = static::generateNameSpace();
        $content = '<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace '.$ns.';
use Tests\\'.$testCase.';
use '.$class.';
class '.$testName.'Test extends '.$testCase.'
{
    public function test'.ucfirst($testName).'(): void{'.PHP_EOL;
        $content .= $tab.$tab.$functions.PHP_EOL;
        $content .= $tab.$tab.'$this->checkTestDuration(10);'.PHP_EOL;
        $content .= $tab.$tab.'$this->checkQueryCount(5);'.PHP_EOL;
        $content .= $tab.'}'.PHP_EOL;
        $content .= '}'.PHP_EOL;
        return $content;
    }
    public static function generateNameSpace(): string{
        return 'Tests\StagingUnitTests';
    }
}
