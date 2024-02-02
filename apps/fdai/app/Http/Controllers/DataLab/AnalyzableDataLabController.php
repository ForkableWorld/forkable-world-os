<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers\DataLab;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Http\Controllers\BaseDataLabController;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Slim\Model\DBModel;
use App\Slim\View\Request\QMRequest;
use App\Traits\QMAnalyzableTrait;
use App\UI\Alerter;
class AnalyzableDataLabController extends BaseDataLabController {
	/**
	 * @param $id
	 * @return BaseModel
	 * @throws TooSlowToAnalyzeException
	 * @throws StupidVariableNameException
	 */
	public function find($id): ?BaseModel{
		$model = parent::find($id);
		if(!$model){
			return null;
		}
		if(QMRequest::getParam('analyze')){
			$model = $this->analyze($model);
		}
		if(QMRequest::getParam('correlate')){
			/** @var UserVariable $model */
			$model->correlate();
		}
		return $model;
	}
	/**
	 * @param BaseModel $model
	 * @return BaseModel
	 * @throws StupidVariableNameException
	 */
	protected function analyze(BaseModel $model): BaseModel{
		/** @var QMAnalyzableTrait|DBModel $analyzable */
		$analyzable = $model->getDBModel();
		try {
			$analyzable->analyzeFullyAndSave("Analysis was requested by url parameter");
		} catch (AlreadyAnalyzedException | NotEnoughDataException | DuplicateFailedAnalysisException | AlreadyAnalyzingException $e) {
			Alerter::error($e->getMessage());
		} catch (TooSlowToAnalyzeException $e) {
			Alerter::error($e->getMessage());
			$analyzable->queue($e->getMessage());
		} catch (ModelValidationException $e) {
			$model->logError($e->getMessage());
		}
		$model = $analyzable->l();
		return $model;
	}
}
