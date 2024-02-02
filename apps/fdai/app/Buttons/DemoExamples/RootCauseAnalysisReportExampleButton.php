<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DemoExamples;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class RootCauseAnalysisReportExampleButton extends QMButton {
	public $fontAwesome = FontAwesome::STUDY;
	public $image = ImageUrls::STUDY;
	public $link = "https://images.quantimo.do/root-cause-analysis/root-cause-analysis-overall-mood-example.pdf";
	public $title = 'Example Root Cause Analysis Report';
	public $tooltip = "Root Cause Analysis Reports detail the impacts of hundreds of dietary, activity, environmental, pharmaceutical factors on each outcome of interest.  For example, if someone begins taking a nutritional supplement, they can see the resultant percent change in their symptoms over the duration of action following the onset delay for that predictor’s particular pharmacodynamic model.";
}
