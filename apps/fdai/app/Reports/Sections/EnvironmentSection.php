<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\EnvironmentVariableCategory;
class EnvironmentSection extends RootCauseAnalysisSection {
    public $title = "Environmental Factors";
    public $predictorVariableCategoryName = EnvironmentVariableCategory::NAME;
    public $introductorySentence = "";
}
