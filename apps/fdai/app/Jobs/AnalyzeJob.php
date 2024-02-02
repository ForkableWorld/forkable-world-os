<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\VariableRelationships\QMVariableRelationship;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Slim\Model\DBModel;
use App\Traits\QMAnalyzableTrait;
use App\Variables\QMUserVariable;
class AnalyzeJob extends BaseJob {
	/**
	 * @var QMAnalyzableTrait
	 */
	public $study;
	/**
	 * Create a new job instance.
	 * @param QMAnalyzableTrait|DBModel|BaseModel $model
	 * @param string $reason
	 */
	public function __construct($model, string $reason){
		$model->status = CorrelationStatusProperty::STATUS_WAITING;
		parent::__construct($model, $reason);
	}
	/**
	 * Execute the job.
	 * @return BaseModel
	 * @throws AlreadyAnalyzingException
	 * @throws AlreadyAnalyzedException
	 * @throws DuplicateFailedAnalysisException
	 * @throws ModelValidationException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableException
	 */
	public function handle(){
		$this->exceptionIfAlreadyHandled();
		/** @var UserVariableRelationship $bm */
		$bm = $this->study; // We pass BaseModel instead of larger DBModels that can cause memory issues
		/** @var QMVariableRelationship $a */
		$a = $bm->getDBModel();
		if(!$a->reasonForAnalysis){
			$a->reasonForAnalysis = $bm->reason_for_analysis ?? $this->reason;
		}
		try {
			if($a->status = UserVariableStatusProperty::STATUS_CORRELATE && method_exists($a, 'correlate')){
				/** @var QMUserVariable $a */
				$a->correlate();
			} else{
				$a->analyzeFullyAndSave($a->reasonForAnalysis);
			}
			if($a->getIsPublic()){
				$a->postToWordPress();
			}
		} catch (TooSlowToAnalyzeException $e) {
			le("How can it be too slow in a job? $e");
		} catch (AlreadyAnalyzedException | ModelValidationException | NotEnoughDataException | AlreadyAnalyzingException | DuplicateFailedAnalysisException | StupidVariableNameException $e) {
			$a->logError(__METHOD__.": ".$e->getMessage());
		}
		return $bm;
	}
}
