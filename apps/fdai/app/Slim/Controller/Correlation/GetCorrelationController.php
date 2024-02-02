<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Correlation;
use App\VariableRelationships\CorrelationsAndExplanationResponseBody;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Studies\StudyListResponseBody;
use App\Utils\APIHelper;
use App\Variables\QMUserVariable;
use Illuminate\Http\JsonResponse;
use Response;
class GetCorrelationController extends GetController {
	public const DEFAULT_LIMIT = 10;
	/**
	 * @return JsonResponse|\Illuminate\Http\Response|void
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	public function get(){
		$this->setCacheControlHeader(5 * 60);
		if(QMRequest::urlContains("/studies")){
			return $this->handleStudiesRequest();
		} else{
			if(APIHelper::apiVersionIsAbove(3)){
				return $this->writeJsonWithGlobalFields(200, $this->getCorrelationsAndExplanationsResponseBody());
			} elseif(APIHelper::apiVersionIsAbove(2)){
				return $this->writeJsonWithGlobalFields(200, [
					'status' => 'ok',
					'success' => true,
					'data' => $this->getCorrelationsAndExplanationsResponseBody(),
				]);
			} else{
				$correlations = $this->getCorrelations();
				$this->scheduleUpdateIfNecessary($correlations);
				return $this->writeJsonWithoutGlobalFields(200, $correlations);
			}
		}
	}
	/**
	 * @return StudyListResponseBody
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	protected function getStudiesResponseBody(): StudyListResponseBody{
		$r = $this->getCorrelationsAndExplanationsResponseBody();
		$r = new StudyListResponseBody($r->getExplanation());
		return $r;
	}
	/**
	 * @return StudyListResponseBody
	 * @throws UnauthorizedException
	 */
	protected function getPopulationStudiesResponseBody(): StudyListResponseBody{
		$r = $this->getAggregatedCorrelationsWithExplanationResponseBody();
		$r = new StudyListResponseBody($r->getExplanation());
		return $r;
	}
	/**
	 * @return Response|JsonResponse
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	private function handleStudiesRequest(): Response|JsonResponse {
		$r = $this->getStudiesResponseBody();
		return $this->writeJsonWithGlobalFields(200, $r);
	}
	/**
	 * @param array|null $params
	 * @return QMUserVariableRelationship
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	public function getOrCreateUserOrGlobalVariableRelationshipsWithStudyHtmlChartsImages(array $params = null): array{
		if(!$params){
			$params = GetCorrelationController::getCorrelationRequestParams();
		}
		$correlations = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships($params);
		if(QMRequest::urlContains('/studies')){
			return $correlations;
		}  // This will be done in the study instantiation
		foreach($correlations as $c){
			$c->addStudyHtmlChartsImages();
		}
		return $correlations;
	}
	/**
	 * @return QMGlobalVariableRelationship|QMUserVariableRelationship
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	protected function getCorrelations(): array{
		if(request()->input('aggregated')){
			return $this->getAggregatedCorrelations(GetCorrelationController::getCorrelationRequestParams());
		}
		return $this->getOrCreateUserOrGlobalVariableRelationshipsWithStudyHtmlChartsImages();
	}
	/**
	 * @param $correlations
	 * @throws UnauthorizedException
	 */
	protected function scheduleUpdateIfNecessary($correlations){
		if(!$correlations){
			$cause = BaseCauseVariableIdProperty::getCauseUserVariable();
			if($cause && $cause->hasEnoughMeasurementsToCorrelate()){
				$cause->scheduleReCorrelationDynamic('user requested user_variable_relationships but got none');
			}
			$effect = QMRequest::getEffectUserVariable();
			if($effect && $effect->hasEnoughMeasurementsToCorrelate()){
				$effect->scheduleReCorrelationDynamic('user requested user_variable_relationships but got none');
			}
		}
	}
	/**
	 * @return array
	 * @throws UnauthorizedException
	 */
	protected static function getCorrelationRequestParams(): array{
		$params = qm_request()->query();
		//$params = QMRequest::getCauseAndEffectParams();
		if(GetController::getCommonOnly()){
			$params['aggregated'] = true;
		} elseif(QMAuth::id()){
			$params['userId'] = QMAuth::id();
		}
		unset($params['commonOnly'], $params['studyId']);
		$params['fallbackToStudyForCauseAndEffect'] = true;
		$params['fallbackToAggregatedCorrelations'] = self::getFallbackToAggregatedCorrelations();
		if(!isset($params['limit'])){$params['limit'] = 100;}
		if(!isset($params['offset'])){$params['offset'] = 0;}
		return $params;
	}
	/**
	 * @return bool
	 */
	protected static function getFallbackToAggregatedCorrelations(): bool{
		$fallback = QMRequest::getParam('fallbackToAggregatedCorrelations');
		if($fallback === null){
			return true;
		}
		return (bool)$fallback;
	}
	/**
	 * @return CorrelationsAndExplanationResponseBody
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	protected function getCorrelationsAndExplanationsResponseBody(): CorrelationsAndExplanationResponseBody{
		$params = GetCorrelationController::getCorrelationRequestParams();
		$r = $this->getCorrelationsWithExplanationResponseBody($params);
		if(!count($r->correlations) && QMAuth::getQMUser()){
			$this->addNotEnoughDataDescription($r, BaseCauseVariableIdProperty::getCauseUserVariable());
			$this->addNotEnoughDataDescription($r, QMRequest::getEffectUserVariable());
		}
		return $r;
	}
	/**
	 * @param CorrelationsAndExplanationResponseBody $response
	 * @param QMUserVariable $variable
	 */
	protected function addNotEnoughDataDescription(CorrelationsAndExplanationResponseBody $response,
		QMUserVariable $variable){
		try {
			$variable->analyzeFully(__FUNCTION__);
		} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
		}
		$dataRequirementsMessage = $variable->getCorrelationDataRequirementAndCurrentDataQuantityString() . " " .
			NotEnoughMeasurementsForCorrelationException::DATA_REQUIREMENT_FOR_CORRELATIONS_STRING;
		if($variable->hasEnoughMeasurementsToCorrelate()){
			$variable->scheduleReCorrelationDynamic('user requested user_variable_relationships but got none');
			$response->getExplanation()->description = "An analysis of " . $variable->name .
				" has been scheduled and you should see some data in a few hours. " . $dataRequirementsMessage;
		} else{
			$response->getExplanation()->description =
				"We still don't have enough data and/or variance to analyze " . $variable->name . ". " .
				$dataRequirementsMessage;
		}
	}
	/**
	 * @param array $params
	 * @return QMUserVariableRelationship[]
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getUserVariableRelationshipsWithExplanation(array $params = []): array{
		if(!isset($params['fallbackToAggregatedCorrelations'])){
			$params['fallbackToAggregatedCorrelations'] = true;
		}
		$correlations = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships($params);
		return $correlations;
	}
	/**
	 * @param array $params
	 * @return QMGlobalVariableRelationship[]
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getAggregatedCorrelationsWithExplanation(array $params = []): array{
		$correlations = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships($params);
		return $correlations;
	}
	/**
	 * @param array $params
	 * @return CorrelationsAndExplanationResponseBody
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	public function getCorrelationsWithExplanationResponseBody(array $params = []): CorrelationsAndExplanationResponseBody{
		if(GetController::getCommonOnly()){
			$correlations = $this->getAggregatedCorrelations(GetCorrelationController::getCorrelationRequestParams());
		} else{
			$correlations = $this->getOrCreateUserOrGlobalVariableRelationshipsWithStudyHtmlChartsImages();
		}
		return new CorrelationsAndExplanationResponseBody($correlations, $params);
	}
	/**
	 * @param array $params
	 * @return CorrelationsAndExplanationResponseBody
	 * @throws UnauthorizedException
	 */
	public function getAggregatedCorrelationsWithExplanationResponseBody(array $params = []): CorrelationsAndExplanationResponseBody{
		$correlations = $this->getAggregatedCorrelations(GetCorrelationController::getCorrelationRequestParams());
		return new CorrelationsAndExplanationResponseBody($correlations, $params);
	}
	/**
	 * @throws TooSlowToAnalyzeException
	 * @throws UnauthorizedException
	 */
	public function correlateAll(){
		if($this->isAdmin() && static::getRequestParam('correlateAll')){
			$userId = static::getUserIdParamOrAuthenticatedUserId();
			$user = QMUser::find($userId);
			QMRequest::setMaximumApiRequestTimeLimit(600);
			$user->correlateAllStale(true);
		}
	}
}
