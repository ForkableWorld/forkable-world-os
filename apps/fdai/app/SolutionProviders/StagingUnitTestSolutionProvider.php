<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\SolutionProviders;
use App\Solutions\GenerateStagingUnitTestSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use App\Utils\AppMode;
use Throwable;
class StagingUnitTestSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool{
        $canSolve = AppMode::isApiRequest() && !AppMode::isUnitOrStagingUnitTest();
        return $canSolve;
    }
    public function getSolutions(Throwable $throwable): array{
        return [new GenerateStagingUnitTestSolution()];
    }
}
