<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\SleepVariableCategory;

class SleepSection extends RootCauseAnalysisSection {
    public $title = "Sleep-Related Factors";
    public $predictorVariableCategoryName = SleepVariableCategory::NAME;
    public $introductorySentence = "";
}
