<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\Slim\Controller\Report;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Model\User\QMUser;
class GetReportController extends GetController {
	/**
	 * Handle the GET request.
	 */
	public function get(){
		$u = QMUser::find(UserIdProperty::USER_ID_MIKE);
		$html = $u->getLastReportHtml();
		if($html){
			$this->outputHtmlToBrowser($html);
			return;
		}
		$v = $u->getPrimaryOutcomeQMUserVariable();
		$a = $v->getRootCauseAnalysis();
		$a->setMaximumCorrelations(50);
		$html = $a->getOrGenerateHtmlWithHead();
		$this->outputHtmlToBrowser($html);
	}
}
