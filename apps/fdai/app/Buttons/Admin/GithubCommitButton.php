<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\Markdown;
class GithubCommitButton extends QMButton {
	public function __construct(string $message, string $url){
		$this->markdownBadgeLogo = Markdown::GITHUB;
		parent::__construct("Commit: " . $message, $url, "black", IonIcon::socialGithub);
		$this->tooltip = "See diff";
		$this->setImage(ImageUrls::DATA_SOURCES_GITHUB_SMALL_MFUESC);
	}
}
