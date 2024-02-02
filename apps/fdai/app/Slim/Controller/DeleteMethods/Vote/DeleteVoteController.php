<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\DeleteMethods\Vote;
use App\Models\Vote;
use App\Exceptions\UnauthorizedException;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Slim\Controller\DeleteController;
use App\Slim\Middleware\QMAuth;
class DeleteVoteController extends DeleteController {
	/**
	 * @throws UnauthorizedException
	 */
	public function delete(){
		$success = Vote::deleteVote(QMAuth::id(), BaseCauseVariableIdProperty::nameFromRequest(),
			BaseEffectVariableIdProperty::nameFromRequest());
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
