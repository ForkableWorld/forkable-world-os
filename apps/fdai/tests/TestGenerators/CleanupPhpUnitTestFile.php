<?php
namespace Tests\TestGenerators;
use App\Files\PHP\BasePhpUnitTestFile;
use App\Logging\SolutionButton;
class CleanupPhpUnitTestFile extends BasePhpUnitTestFile
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
        $testCase = 'JobTestCase';
        $ns = static::generateNameSpace();
        $content = '<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace '.$ns.';
use App\PhpUnitJobs\JobTestCase;
use '.$class.';
class '.$testName.'Test extends '.$testCase.'
{
    public function test'.ucfirst($testName).'(): void{'.PHP_EOL;
        $content .= $tab.$tab.$functions.PHP_EOL;
        $content .= $tab.'}'.PHP_EOL;
        $content .= '}'.PHP_EOL;
        return $content;
    }
    public static function generateNameSpace(): string{
        return 'App\PhpUnitJobs\Cleanup';
    }
}
