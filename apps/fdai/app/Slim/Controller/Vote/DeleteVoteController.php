<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Vote;
use App\Models\Vote;
use App\Exceptions\UnauthorizedException;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
class DeleteVoteController extends PostController {
	/**
	 * @throws UnauthorizedException
	 */
	public function post(){
		$success = Vote::deleteVote(QMAuth::id(), $this->getCauseVariableName(), $this->getEffectVariableName());
		if($success){
			return $this->writeJsonWithGlobalFields(204, [
				'status' => 204,
				'success' => true,
			]);
		} else{
			return $this->writeJsonWithGlobalFields(404, [
				'status' => 404,
				'success' => false,
				'message' => "Could not delete vote.  Maybe it doesn't exist?",
			]);
		}
	}
}
