<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Correlation;
use App\Logging\QMLog;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Variable\SearchVariableRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
class GetAggregatedCorrelationRequest extends SearchVariableRequest {
	/**
	 * Populate this request's properties from an Application instance.
	 * Note: We exclude the following sources:
	 * world weather online - 38, github - 7, moodimodo - 23, sleep as android - 19, facebook - 30,
	 * whatpulse - 25, rescuetime - 34
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$route = $app->router()->getCurrentRoute();
		$routeParts = explode('/', $route->getPattern());
		$endOfRoute = array_pop($routeParts);
		try {
			$this->setSearch($route->getParam('variableName'));
		} catch (Exception $exception) {
			QMLog::info($exception->getMessage());
		}
		try {
			$this->setSearch($route->getParam('search'));
		} catch (Exception $exception) {
			QMLog::info($exception->getMessage());
		}
		if($endOfRoute == 'causes'){
			$this->setPublicEffectOrCause('effect');
		}
		if($endOfRoute == 'effects'){
			$this->setPublicEffectOrCause('cause');
		}
		$effectOrCauseParam = $this->getParamInArray([
			'effectOrCause',
			'causeOrEffect',
			'publicEffectOrCause',
		]);
		if($effectOrCauseParam === 'cause'){
			$this->setPublicEffectOrCause('cause');
		}
		if($effectOrCauseParam === 'effect'){
			$this->setPublicEffectOrCause('effect');
		}
		$this->setCategoryName($this->getParam('categoryName'));
		$this->setLimit($this->getParamNumeric('limit', 10, 'Limit must be numeric'));
		$this->setOffset($this->getParamNumeric('offset', 0, 'Offset must be numeric'));
		if(Auth::user()){
			$this->setUserId(QMAuth::getAuthenticatedUserOrThrowException()->id);
		}
	}
}
