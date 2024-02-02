<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\PhysiqueVariableCategory;

class PhysiqueSection extends RootCauseAnalysisSection {
    public $title = "Factors Related to Physique";
    public $predictorVariableCategoryName = PhysiqueVariableCategory::NAME;
    public $introductorySentence = "";
}
