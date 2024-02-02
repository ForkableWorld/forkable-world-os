<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class InvalidVariableSettingException extends InvalidAttributeException implements ProvidesSolution {
    public function getSolution(): Solution{
        $links = ExceptionHandler::getLinkedStackTrace($this);
        return BaseSolution::create("Prevent This From Happening")
            ->setSolutionDescription("Add break points and see what caused this to prevent it in the future. ")
            ->setDocumentationLinks($links);
    }
}
