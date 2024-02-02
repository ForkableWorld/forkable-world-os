<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Models\VariableCategory;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\Solution;
use App\Variables\QMVariableCategory;
class NoVariableCategoryException extends BaseException
{
    public function __construct(){
        parent::__construct("Missing Variable Category");
    }
    public function getSolution(): Solution{
        return BaseSolution::create("Provide Variable Category")
            ->setSolutionDescription("Available categories are ".
                QMVariableCategory::getStringListOfVariableCategoryNames())
            ->setDocumentationLinks(["Variable Category Info" => VariableCategory::getDataLabIndexUrl()]);
    }
}
