<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\SolutionProviders;
use Facade\Ignition\SolutionProviders\MissingImportSolutionProvider;
use Facade\Ignition\Support\ComposerClassMap;
use Throwable;
class QMMissingImportSolutionProvider extends MissingImportSolutionProvider
{
    /** @var string */
    public $foundClass;
    public function canSolve(Throwable $throwable): bool{
        return parent::canSolve($throwable);
    }
    public function getSolutions(Throwable $throwable): array{
        $solutions = parent::getSolutions($throwable);
        return $solutions;
    }
    public function search(string $missingClass){
        if(!$this->composerClassMap){
            $this->composerClassMap = new ComposerClassMap();
        }
        // TODO: Fix this maybe?  It's really slow to search through all directories
        // See \Tests\UnitTests\SolutionsTest::testMissingImportSolution
        // Also the parent MissingImportSolutionProvider that makes it fail when looking for test directors in vendor
        // that don't exist.  It was fixed, but it's in a later ignition release
        return parent::search($missingClass);
    }
}
