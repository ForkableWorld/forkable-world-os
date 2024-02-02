<?php

namespace Facade\Ignition\SolutionProviders;

use Facade\Ignition\Solutions\SuggestImportSolution;
use Facade\Ignition\Support\ComposerClassMap;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use App\Logging\QMLog;
use Throwable;

class MissingImportSolutionProvider implements HasSolutionsForThrowable
{
    /** @var string */
    protected $foundClass;

    /** @var \Facade\Ignition\Support\ComposerClassMap */
    protected $composerClassMap;

    public function canSolve(Throwable $throwable): bool
    {
        $pattern = '/Class \'([^\s]+)\' not found/m';

        if (! preg_match($pattern, $throwable->getMessage(), $matches)) {
            return false;
        }

        $class = $matches[1];

        $this->composerClassMap = new ComposerClassMap();

        $this->search($class);

        return ! is_null($this->foundClass);
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new SuggestImportSolution($this->foundClass)];
    }

    protected function search(string $missingClass)
    {
        QMLog::info("Searching ClassMap for missing import...");
        $this->foundClass = $this->composerClassMap->searchClassMap($missingClass);

        QMLog::info("Searching srMaps for missing import...");
        if (is_null($this->foundClass)) {
            $this->foundClass = $this->composerClassMap->searchPsrMaps($missingClass);
        }
    }
}
