<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\Slim\Controller\Card;
use App\Cards\QMCard;
use App\Slim\Controller\GetController;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
class GetCardController extends GetController {
	/**
	 * Handle the GET request.
	 */
	public function get(){
		$cardType = QMRequest::getParam('type');
		$className = "\App\Cards\\" . QMStr::toClassName($cardType) . "Card";
		/** @var QMCard $card */
		$card = new $className();
		$this->outputHtmlToBrowser($card->getHtml());
	}
}
