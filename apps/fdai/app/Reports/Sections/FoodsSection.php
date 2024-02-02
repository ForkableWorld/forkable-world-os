<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\FoodsVariableCategory;

class FoodsSection extends RootCauseAnalysisSection {
    public $title = "Dietary Factors";
    public $predictorVariableCategoryName = FoodsVariableCategory::NAME;
    public $introductorySentence = "";
}
