<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DemoExamples;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class GradeReportExampleButton extends QMButton {
	public $fontAwesome = FontAwesome::STUDY;
	public $image = ImageUrls::STUDY;
	public $link = "https://static.quantimo.do/demo/daily-average-grade-for-super-dude-grade-report.html";
	public $title = 'Example Root Cause Analysis Report';
	public $tooltip = "Automated import via scraping of academic grade reporting systems now allows for generation of daily grade reports with immediate feedback and rewards for academic performance. ";
}
