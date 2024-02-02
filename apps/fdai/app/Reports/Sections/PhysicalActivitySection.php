<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\PhysicalActivityVariableCategory;

class PhysicalActivitySection extends RootCauseAnalysisSection {
    public $title = "Factors Related to Physical Activity";
    public $predictorVariableCategoryName = PhysicalActivityVariableCategory::NAME;
    public $introductorySentence = "";
}
