<?php
namespace Tests\UnitTests\Solutions;
use App\Files\FileHelper;
use App\Files\PHP\PhpClassFile;
use App\Solutions\OpenTestInPHPStormDebugSolution;
use App\Traits\FileTraits\IsSolution;
use App\Utils\AppMode;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
use Tests\UnitTestCase;
class SolutionsTest extends UnitTestCase
{
	protected function setUp(): void{
		$this->skipIfNotLocal();
		parent::setUp();
	}
    public function testSolutions(){
	    $this->skipTest("TODO");
        PhpClassFile::addTraitToFiles('app/Solutions', IsSolution::class);
	    $solutions = FileHelper::getClassesInFolder('app/Solutions');
	    foreach($solutions as $class){
		    $this->assertExtends($class, BaseSolution::class,
		                         "$class should extend BaseSolution to ensure documentation links are validated");
		    /** @var Solution $solution */
		    $solution = new $class(new \LogicException());
		    $links = $solution->getDocumentationLinks();
		    $urls = array_values($links);
		    $this->assertUnique($urls);
		    $keys = array_keys($links);
            $this->assertUnique($urls);
        }
    }
    public function testOpenTestInPHPStormDebugSolution(){
        $solution = new OpenTestInPHPStormDebugSolution(new \LogicException());
        $links = $solution->getDocumentationLinks();
        $names = array_keys($links);
        $urls = array_values($links);
        $expectedNames = array (
	        0 => 'IGNITION',
	        1 => 'IGNITION_REPORT',
	        2 => 'Clockwork',
	        3 => 'Open Test',
	        4 => 'Horizon Queue Manager',
	        5 => 'Adminer',
	        6 => 'Run testOpenTestInPHPStormDebugSolution',
	        7 => 'Add Break Point Here',
        );
        if(AppMode::isJenkins()){$expectedNames[] = 'Build Log';}
        $this->assertArrayEquals($expectedNames, array_keys($links), 
                                 "We should have 3 on Jenkins and 2 locally");
        $this->assertValidUrls(array_values($links), __FUNCTION__);
    }
}
