<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseAPIController;
use App\Properties\OAClient\OAClientClientIdProperty;
use App\Properties\OAClient\OAClientClientSecretProperty;
use Illuminate\Http\Request;
/** Class StudyController
 * @package App\Http\Controllers\API
 */
class StudyAPIController extends BaseAPIController {
    public array $with = ['cause_variable', 'effect_variable'];
	public function store(Request $request){
		if(OAClientClientSecretProperty::fromRequest(false)){
			$outcome = $request->input('outcome');
			$predictor = $request->input('predictor');
			$data = $request->input();
			$user = \App\Models\User::findOrCreateUserForClient(
				OAClientClientIdProperty::fromRequest(true), $data);
			$study = $user->getOrCreateUserStudy($predictor, $outcome);
			$study->getStudyHtml()->getFullStudyHtml();
			$models = [$study];
			return $this->respondWithJsonResourceCollection($models, 201);
		}
		return parent::store($request);
	}
}
