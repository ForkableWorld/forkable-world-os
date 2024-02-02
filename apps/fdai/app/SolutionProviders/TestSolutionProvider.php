<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\SolutionProviders;
use App\Solutions\OpenTestInPHPStormDebugSolution;
use App\Solutions\ViewModelsInMemorySolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Tests\QMBaseTestCase;
use Throwable;
class TestSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool{
        if (\App\Utils\AppMode::getCurrentTest()) {return true;}
        return false;
    }
    public function getSolutions(Throwable $throwable): array{
        $arr = [new OpenTestInPHPStormDebugSolution($throwable),];
        if(ViewModelsInMemorySolution::generateModelLinks()){$arr[] = new ViewModelsInMemorySolution($throwable);}
        return $arr;
    }
}
