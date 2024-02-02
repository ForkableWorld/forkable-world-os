<?php
namespace Tests;
use App\Files\FileFinder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Types\QMStr;
use App\PhpUnitJobs\DevOps\TestJob;
use App\PhpUnitJobs\JobTestCase;
use Illuminate\Support\Str;

class UpdateHtmlTestFixturesTest extends JobTestCase
{
    const FEATURE_UPDATED_HTML = "feature/updated-html";
    public const FIREBASE_KEY_HTML_TESTS = 'html_tests';
    public function testUpdateHtmlFixtures(){
	    UpdateHtmlTestFixturesTest::updateHtmlFixturesContaining("default-logo.png");
        //UpdateHtmlTestFixturesTest::updateHtmlTestFixtures(false);
        $this->assertTrue(true);
    }
    public static function updateHtmlTestFixtures(bool $commit): void{
        $b = self::FEATURE_UPDATED_HTML;
        if($commit){QMAPIRepo::createFeatureBranch($b);}
        $tests = self::getHtmlFixtureTests();
        QMLog::print($tests, "=== TESTING THESE FROM FIREBASE ===");
        foreach($tests as $test){TestJob::testPHPUnitFunction(Str::before($test, "::"),
	        Str::after($test, "::"));}
        if($commit){
            QMAPIRepo::add("tests/");
            QMAPIRepo::commit("updated HTML", false);
            QMAPIRepo::push($b);
        }
    }
    public static function updateHtmlFixturesContaining(string $htmlContains = null, bool $debug = false): void{
        if($htmlContains){
            foreach(self::getPHPFiles($htmlContains) as $phpFile){
                TestJob::testFile($phpFile, $debug);
                //break;
            }
        } else {
            foreach(self::getTestFilesThatCheckHtml() as $file){
                TestJob::testFile($file, $debug);
            }
        }
    }
    public static function getTestHtmlFixturesContaining(string $pattern): array{
        $files = FileFinder::getFilesContaining('tests', $pattern, true, '.html');
        return $files;
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
    /**
     * @param string $htmlContains
     * @return array
     */
    protected static function getPHPFiles(string $htmlContains): array{
        $phpFiles = [];
        $htmlFiles = self::getTestHtmlFixturesContaining($htmlContains);
        foreach($htmlFiles as $htmlFile){
            ConsoleLog::info("Updating $htmlFile");
            $php = QMStr::before("-test", $htmlFile).".php";
            if(stripos($php, '/tests/fixtures/') !== false){
                continue;
            }
            $phpFiles[] = $php;
        }
        $phpFiles = array_unique($phpFiles);
        return $phpFiles;
    }
    /**
     * @return array
     */
    public static function getHtmlFixtureTests(): array{
        $tests = FirebaseGlobalPermanent::get(self::FIREBASE_KEY_HTML_TESTS);
        if(!$tests){return [];}
        return $tests ?? [];
    }
}
