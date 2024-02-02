<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\SocialInteractionsVariableCategory;

class SocialInteractionsSection extends RootCauseAnalysisSection {
    public $title = "Factors Related to Social Interaction";
    public $predictorVariableCategoryName = SocialInteractionsVariableCategory::NAME;
    public $introductorySentence = "";
}
