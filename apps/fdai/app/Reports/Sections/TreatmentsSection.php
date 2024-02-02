<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports\Sections;
use App\Reports\RootCauseAnalysisSection;
use App\VariableCategories\TreatmentsVariableCategory;

class TreatmentsSection extends RootCauseAnalysisSection {
    public $title = "Treatment Effectiveness";
    public $predictorVariableCategoryName = TreatmentsVariableCategory::NAME;
}
