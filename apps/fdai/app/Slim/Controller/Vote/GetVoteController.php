<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Vote;
use App\Models\Vote;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
class GetVoteController extends GetController {
	public function get(){
		$userId = UserIdProperty::fromRequest(false);
		if(!$userId){
			$userId = QMAuth::id();
		}
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => Vote::insertOrUpdateVote($userId),
		]);
	}
}
