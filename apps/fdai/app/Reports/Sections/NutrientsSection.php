<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\NutrientsVariableCategory;

class NutrientsSection extends RootCauseAnalysisSection {
    public $title = "Nutritional Factors";
    public $predictorVariableCategoryName = NutrientsVariableCategory::NAME;
    public $introductorySentence = "";
}
