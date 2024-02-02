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
use App\Models\Study;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
class AnalyzeStudyJob extends BaseJob {
	/**
	 * The number of seconds the job can run before timing out.
	 *
	 * @var int
	 */
	public $timeout = 1200;
	/**
	 * @var Study
	 */
	public $study;
	/**
	 * Create a new job instance.
	 * @param Study $model
	 * @param string $reason
	 */
	public function __construct(Study $model, string $reason){
		$model->status = CorrelationStatusProperty::STATUS_WAITING;
		parent::__construct($model, $reason);
	}
	/**
	 * Execute the job.
	 * @return BaseModel
	 */
	public function handle(){
		$this->exceptionIfAlreadyHandled();
		$study = $this->study; // We pass BaseModel instead of larger DBModels that can cause memory issues
		/** @var QMVariableRelationship $a */
		if(!$study->reason_for_analysis){
			$study->reason_for_analysis = $this->reason;
		}
		try {
			$correlation = $study->getHasCorrelationCoefficient();
			$correlation->analyzeFullyAndSave($study->reason_for_analysis);
			if($study->is_public){
				$study->postToWordPress();
			}
		} catch (TooSlowToAnalyzeException $e) {
			le("How can it be too slow in a job? $e");
		} catch (AlreadyAnalyzedException | ModelValidationException | NotEnoughDataException | AlreadyAnalyzingException | DuplicateFailedAnalysisException | StupidVariableNameException $e) {
			$a->logError(__METHOD__.": ".$e->getMessage());
		}
		return $study;
	}
}
