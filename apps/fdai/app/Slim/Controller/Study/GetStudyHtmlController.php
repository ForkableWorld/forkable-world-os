<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMStudy;
class GetStudyHtmlController extends GetController {
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		QMRequest::setMaximumApiRequestTimeLimit(120);
		$study = QMStudy::getUserStudyAndFallbackToPopulationStudy();
        $this->getApp()->render('StudyPage.php', [
			'studyHtml' => $study->getStudyHtml(),
			'title' => $study->getStudyText()->getStudyTitle(),
		]);
	}
}
