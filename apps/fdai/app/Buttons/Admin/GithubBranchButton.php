<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\Repos\QMAPIRepo;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\Markdown;
class GithubBranchButton extends QMButton {
	public function __construct(string $name = null, string $url = null){
		$this->markdownBadgeLogo = Markdown::GITHUB;
		parent::__construct("Branch: " . $name, $url, "black", IonIcon::socialGithub);
		$this->tooltip = "See diff";
		$this->setImage(ImageUrls::DATA_SOURCES_GITHUB_SMALL_MFUESC);
	}
	public function getUrl(array $params = []): string{
		return QMAPIRepo::getGithubBranchUrl();
	}
}
