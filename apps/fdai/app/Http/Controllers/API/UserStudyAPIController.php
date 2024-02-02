<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\API;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Http\Controllers\BaseAPIController;
use App\Http\Resources\CorrelationResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserVariableResource;
use App\Http\Resources\VariableResource;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseVariableIdProperty;
use App\Properties\UserVariableRelationship\CorrelationEffectVariableIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Studies\QMStudy;
use App\Traits\HttpTraits\SavesMeasurements;
use Illuminate\Http\Request;
use InfyOm\Generator\Utils\ResponseUtil;

/** Class StudyController
 * @package App\Http\Controllers\API
 */
class UserStudyAPIController extends BaseAPIController {
    use SavesMeasurements;
    public function get(Request $request){
        $user = QMAuth::getUser();
        $study = $user->getOrCreateUserStudy(BaseCauseVariableIdProperty::fromRequest(true),
            BaseEffectVariableIdProperty::fromRequest(true));
        return \Response::json(ResponseUtil::makeResponse("Got study", $study), 201);
    }
    public function store(Request $request){
        $data = $request->all();
        try {
            $userFromClientSecret = User::findOrCreateByProviderId($data);
        } catch (\Throwable $e) {
            $userFromClientSecret = null;
        }
        $us = $this->getUserStudy($data);
        /** @var UserVariableRelationship $correlation */
        $correlation = $us->getCreateOrRecalculateStatistics()->l();
        $html = $us->getHtmlPage();
        $response = [
            'analysis' => new CorrelationResource($correlation),
            'html' => $html,
            'outcome_user_variable' => new UserVariableResource($correlation->getEffectUserVariable()),
            'outcome_variable' => new VariableResource($us->getEffectVariable()),
            'predictor_user_variable' => new UserVariableResource($correlation->getCauseUserVariable()),
            'predictor_variable' => new VariableResource($us->getCauseVariable()),
        ];
        if ($userFromClientSecret) {
            $response['user'] = new UserResource($userFromClientSecret);
        }
        return \Response::json(ResponseUtil::makeResponse("Got study", $response), 201);
    }
    /**
     * @param array $data
     * @return void
     */
    protected function savePredictorMeasurements(array $data)
    {
        $data = $data['predictor'] ?? $data['cause'] ?? null;
		if(!$data || is_string($data)){
			return; // We're just requesting the study without saving any measurements
		}
        $this->saveMeasurements($data);
    }

    /**
     * @param array $data
     * @return void
     */
	protected function saveOutcomeMeasurements(array $data){
        $data = $data['outcome'] ?? $data['effect'] ?? null;
		if(!$data || is_string($data)){
			return; // We're just requesting the study without saving any measurements
		}
        $this->saveMeasurements($data);
    }
	/**
	 * @param array $data
	 * @return QMStudy|null
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableNameException
	 * @throws TooSlowToAnalyzeException
	 */
	protected function getUserStudy(array $data): ?\App\Studies\QMStudy{
        $this->savePredictorMeasurements($data);
        $this->saveOutcomeMeasurements($data);
        $u = QMAuth::getUser();
		$causeVariableNameOrId = CorrelationCauseVariableIdProperty::fromRequest(true);
		$effectVariableNameOrId = CorrelationEffectVariableIdProperty::fromRequest(true);
		$us = $u->getOrCreateUserStudy($causeVariableNameOrId, $effectVariableNameOrId);
        return $us;
    }
}
