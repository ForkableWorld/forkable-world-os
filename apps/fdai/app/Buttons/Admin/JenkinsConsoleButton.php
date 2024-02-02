<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\DevOps\Jenkins\Build;
use App\DevOps\Jenkins\JenkinsJob;
use App\UI\ImageUrls;
use App\UI\Markdown;
use App\UI\QMColor;
class JenkinsConsoleButton extends QMButton {
	public $markdownBadgeLogo = Markdown::JENKINS;
	public $color = QMColor::HEX_RED;
	public $tooltip = "Go to Job Page";
	public $image = ImageUrls::DEVELOPMENT_068_MONITOR;
	public function __construct(){
		$job = JenkinsJob::getCurrentJobName();
		parent::__construct("Build Log");
		$this->tooltip = "View $job Build Log";
		$this->setUrl(Build::getConsoleUrl());
	}
	public static function generateUrl(): string{
		return (new static)->getUrl();
	}
}
