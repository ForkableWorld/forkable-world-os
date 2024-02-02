<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Vote;
use App\Models\Vote;
use App\Properties\UserVariableRelationship\CorrelationCauseVariableIdProperty;
use App\Properties\UserVariableRelationship\CorrelationEffectVariableIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\Vote\VoteValueProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
class PostVoteController extends PostController {
	public function post(){
		$user = QMAuth::getUser();
		$votes = $user->votes();
		$values = [Vote::FIELD_VALUE => VoteValueProperty::fromRequest(true),];
		$attributes = [
			Vote::FIELD_CAUSE_VARIABLE_ID => CorrelationCauseVariableIdProperty::fromRequest(true),
			Vote::FIELD_EFFECT_VARIABLE_ID => CorrelationEffectVariableIdProperty::fromRequest(true),
		];
		$success = $votes->updateOrCreate($attributes, $values);
		$cards = $user->getVoteCards(true);
		return $this->writeJsonWithGlobalFields(201, [
			'status' => 201,
			'success' => $success,
			'cards' => $cards,
		]);
	}
}
