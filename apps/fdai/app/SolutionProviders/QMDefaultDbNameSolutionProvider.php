<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\SolutionProviders;

use Facade\Ignition\Solutions\SuggestUsingCorrectDbNameSolution;
use Facade\IgnitionContracts\HasSolutionsForThrowable;
use Illuminate\Support\Facades\DB;
use Throwable;

class QMDefaultDbNameSolutionProvider implements HasSolutionsForThrowable
{
	private static ?bool $result = null;
	public function canSolve(Throwable $throwable): bool
    {
        if ($this->canTryDatabaseConnection()) {
			if(static::$result !== null){return static::$result;}
            try {
                DB::connection()->select('SELECT 1');
            } catch (\Exception $e) {
                return static::$result = in_array(env('DB_DATABASE'), ['homestead', 'laravel']);
            }
        }

        return false;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new SuggestUsingCorrectDbNameSolution()];
    }

    protected function canTryDatabaseConnection()
    {
        return version_compare(app()->version(), '5.6.28', '>');
    }
}
