<?php
namespace Tests\UnitTests;
use App\SolutionProviders\QMMissingImportSolutionProvider;
use App\Files\FileFinder;
use Tests\UnitTestCase;
class SolutionsTest extends UnitTestCase
{
    public function testMissingImportSolution(){
	    $arr = FileFinder::getFilesWithExtension("tests/UnitTests",'php');
        $this->assertGreaterThan(10, count($arr));
        $disabled = "too slow!";
        if(!$disabled){
            $s = new QMMissingImportSolutionProvider();
            $s->search("Tests\DBModel");
            $this->assertNotNull($s->foundClass);
        }
    }
}