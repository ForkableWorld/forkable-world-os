<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Traits\DataLabTrait;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class NoIdException extends \LogicException implements ProvidesSolution
{
    /**
     * NoIdException constructor.
     * @param BaseModel|DataLabTrait|UserVariable $model
     * @param string $message
     */
    public function __construct($model, string $message){
        parent::__construct($message." No id on $model");
    }
    public function getSolution(): Solution{
        return BaseSolution::create("Do it without ID")
            ->setSolutionDescription("Figure out a way to accomplish the attempted task without an id")
            ->setDocumentationLinks([]);
    }
}
