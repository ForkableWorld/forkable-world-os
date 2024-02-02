<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\RunnableSolutionStub;
use Facade\IgnitionContracts\ProvidesSolution;
class ExceptionStub extends BaseException implements ProvidesSolution {
    public function getSolution(): \Facade\IgnitionContracts\Solution{
        return new RunnableSolutionStub();
    }
}
