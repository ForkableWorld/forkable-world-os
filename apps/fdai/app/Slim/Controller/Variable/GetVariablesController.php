<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
namespace App\Slim\Controller\Variable;
use App\Exceptions\NotEnoughMeasurementsException;
use App\Exceptions\NotFoundException;
use App\Http\Parameters\IncludeChartsParam;
use App\Models\Variable;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Slim\View\Request\Variable\GetVariableRequest;
use App\Utils\UrlHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\VariableSearchResult;
/** Class GetVariablesController
 * @package App\Slim\Controller\Variable
 */
class GetVariablesController extends GetController {
	public function get(){
		$userId = QMAuth::id(false);
		if(QMRequest::autocomplete()){
			$variables = VariableSearchResult::get();
			return $this->writeJsonWithGlobalFields(200, ['variables' => $variables]);
		} elseif(GetVariableRequest::requestIsSimple()){
            return $this->getVariablesSimple();
		} elseif($this->getUpcParam()){
			return $this->getByUpc();
		} elseif(GetController::getCommonOnly()){
			return $this->getCommonVariablesFormatAndReturn();
		} elseif($name = qm_request('name')){
            $v = Variable::findByName($name);
			if(!$v){
				throw new NotFoundException("Variable named $name not found");
			}
			if(UrlHelper::urlContains("userVariables")){
				$uv = $v->getUserVariable(QMAuth::getUserId());
				$dbm = $uv->getDBModel();
			} else {
				$dbm = $v->getDBModel();
			}
			if(IncludeChartsParam::get(false)){
				$charts = $dbm->getChartGroup();
				$charts->getOrSetHighchartConfigs();
			}
			return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables([$dbm]);
        }
		if($userId){
			$QMUserVariables = $this->getUserVariables($userId);
			$combined = $this->addCommonVariablesIfNecessary($QMUserVariables);
		} else {
			return $this->getCommonVariablesFormatAndReturn();
		}
		return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables($combined);
	}
	/**
	 * @param QMCommonVariable[]|QMUserVariable[] $variables
	 */
	public function unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables(array $variables){
		$this->analyzeIfNecessary($variables);
		//$variables = ObjectHelper::debugAndAnalyzePropertySizes($variables);
		$this->addChartsIfNecessary($variables);
		$this->unsetNullTagProperties($variables);
		return $this->returnVariables($variables);
	}
	/**
	 * @return array|QMCommonVariable[]|QMUserVariable[]
	 */
	protected function getUserVariables(int $userId): array{
		$userVariable = $this->getUserVariable();
		if($userVariable){
			return [$userVariable];
		}
		$params = request()->all();
		$variables =
			QMUserVariable::getUserVariables($userId, $params, false, false);
		return $variables;
	}
	protected function getByUpc() {
		$variables = QMVariable::getCommonOrUserVariablesFromUpc($this->getUpcParam());
		if($variables){
            return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables($variables);
		} else{
			return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables([]);
		}
	}
	protected function getCommonVariablesFormatAndReturn(){
		$this->setCacheControlHeader(86400);
		$input = QMRequest::getInput();
		if(isset($input['name'])){
			$input['name'] = str_replace("+", " ", $input['name']);
		}
		$variables = QMCommonVariable::getCommonVariables($input);
		return $this->unsetNullTagPropertiesAnalyzeAddChartsAndReturnVariables($variables);
	}
	public function getVariablesSimple(){
		$variables = GetVariableRequest::getVariablesSimple();
		$this->unsetNullTagProperties($variables);
		return $this->returnVariables($variables);
	}
	/**
	 * @param array $variables
	 */
	private function addChartsIfNecessary(array $variables): void{
		if(count($variables) === 1){
			$includeCharts = IncludeChartsParam::includeCharts();
			$refresh = QMRequest::refresh();
			if($includeCharts && $refresh){
				$variables[0]->setHighchartConfigs();
			} elseif($includeCharts){
				try {
					$variables[0]->getOrSetHighchartConfigs();
				} catch (NotEnoughMeasurementsException $e) {
					$variables[0]->logError(__METHOD__.": ".$e->getMessage());
				}
			}
		}
	}
	/**
	 * @param array $variables
	 */
	private function analyzeIfNecessary(array $variables): void{
		if($update = static::getRequestParam(QMRequest::PARAM_UPDATE)){
			foreach($variables as $v){
				$v->analyzeFully(__FUNCTION__);
			}
		}
	}
	/**
	 * @param array $userVariables
	 * @param int|string $search
	 * @param array $combined
	 * @return array
	 */
	public function addCommonVariablesIfNecessary(array $userVariables, array $params = null): array {
		$combined = [];
		foreach($userVariables as $uv){
			$combined[$uv->getVariableIdAttribute()] = $uv;
		}
		$limit = QMVariable::getLimitFromRequestOrModelDefault();
		$num = count($userVariables);
		if($num < $limit){
			$params = $params ?? request()->all();
			$commonVariables = QMCommonVariable::getCommonVariables($params);
			if($commonVariables){
				foreach($commonVariables as $v){
					$id = $v->getVariableIdAttribute();
					if(!isset($combined[$id])){$combined[$id] = $v;}
				}
			}
		}
		return array_values($combined);
	}
}
