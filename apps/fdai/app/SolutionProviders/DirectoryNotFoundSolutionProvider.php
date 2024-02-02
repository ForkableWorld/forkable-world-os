<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\SolutionProviders;
use App\Buttons\Admin\PHPStormButton;
use App\Types\QMStr;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Throwable;
class DirectoryNotFoundSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool{
        return $throwable instanceof \Symfony\Component\Finder\Exception\DirectoryNotFoundException;
    }
    /**
     * @inheritDoc
     */
    public function getSolutions(Throwable $throwable): array{
        $message = $throwable->getMessage();
        $package = QMStr::between($message, "composer/../", " does not exist");
        $arr = explode('/', $package);
        $package = $arr[0].'/'.$arr[1];
        $s = BaseSolution::create("Fix Autoload Paths or Remove Package $package")
            ->setSolutionDescription("Remove composer package that requires ".$message)
            ->setDocumentationLinks([
                "composer.json" => PHPStormButton::redirectUrl("composer.json"),
                "composer.lock" => PHPStormButton::redirectUrl("composer.lock"),
                "$package composer.json" => PHPStormButton::redirectUrl("vendor/$package/composer.json"),
            ]);
        return [$s];
    }
}
