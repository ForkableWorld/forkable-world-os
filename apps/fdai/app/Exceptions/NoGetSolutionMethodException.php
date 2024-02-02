<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\CreateException;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Throwable;
class NoGetSolutionMethodException extends UnspecifiedSolutionException implements ProvidesSolution {
    /**
     * @var string
     */
    public $exceptionClassWithoutSolution;
    public function __construct(Throwable $previous, array $meta = null){
        parent::__construct($previous, $meta);
    }
    /**
     * @return CreateException
     */
    public function getSolution(): Solution{
        return new CreateException($this);
    }
}
