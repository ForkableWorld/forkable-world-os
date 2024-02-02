<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\DevOps\Jenkins\JenkinsJob;
use App\UI\ImageUrls;
use App\UI\Markdown;
use App\UI\QMColor;
class JenkinsJobButton extends QMButton {
	public $markdownBadgeLogo = Markdown::JENKINS;
	public $color = QMColor::HEX_RED;
	public $image = ImageUrls::JENKINS;
	public function __construct(){
		$title = JenkinsJob::getJobTitle();
		parent::__construct($title);
		$this->setUrl(JenkinsJob::getJobUrl());
		$this->setTooltip("Go to $title Page");
	}
}
