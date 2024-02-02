<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\SolutionProviders;
use App\Solutions\CreateException;
use App\Solutions\CreateSolution;
use App\Solutions\WrapExceptionSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use Throwable;
class UnspecifiedSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool{
        if ($throwable instanceof AssertionFailedError) {
            return false;
        }
        if ($throwable instanceof ExpectationFailedException) {
            return false;
        }
        return !method_exists($throwable, 'getSolution');
    }
    public function getSolutions(Throwable $throwable): array{
        $e = new CreateException($throwable);
        $newExceptionClass = $e->getNewFullClassName();
        if(class_exists($e->getNewFullClassName())){
            $e = new WrapExceptionSolution($throwable, $newExceptionClass);
        }
        return [
            $e,
            new CreateSolution($throwable),
        ];
    }
}
