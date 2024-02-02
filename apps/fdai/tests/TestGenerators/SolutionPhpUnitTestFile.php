<?php
namespace Tests\TestGenerators;
use App\Files\PHP\BasePhpUnitTestFile;
use App\Logging\SolutionButton;
use App\Types\QMStr;
class SolutionPhpUnitTestFile extends BasePhpUnitTestFile
{
    public const GENERATED_TESTS_PATH = 'Jobs/Solutions';
    /**
     * @param string $class
     * @return string
     */
    public static function generateByClass(string $class): string{
        $content = self::generateContent($class);
        $short = QMStr::toShortClassName($class);
	    $url = self::generateUrl($short, $content);
	    SolutionButton::add($short, $url, $content);
        return $url;
    }
    protected static function generateClassName(string $testNameSuffix): string {
        return $testNameSuffix.'Job';
    }
    /**
     * @param string $class
     * @return string
     */
    public static function generateContent(string $class): string{
        $testNameSuffix = QMStr::toShortClassName($class);
        $testClass = self::generateClassName($testNameSuffix);
		$ns = static::generateNameSpace();
        return "<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace $ns;
use App\\PhpUnitJobs\\JobTestCase;
use $class;
class $testClass extends JobTestCase {
    public function test$testNameSuffix(): void{
        \$s = new $testNameSuffix();
        \$s->run();
    }
}
";
    }
    public static function generateNameSpace(): string{
        return "App\\PhpUnitJobs\\Solutions";
    }
}
