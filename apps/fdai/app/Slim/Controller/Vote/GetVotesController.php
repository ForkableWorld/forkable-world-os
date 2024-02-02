<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Vote;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
class GetVotesController extends GetController {
	public function get(){
		$user = QMAuth::getQMUser();
		$cards = $user->getVoteCards(true);
		return $this->writeJsonWithGlobalFields(200, [
			'status' => 200,
			'success' => true,
			'cards' => $cards,
		]);
	}
}
