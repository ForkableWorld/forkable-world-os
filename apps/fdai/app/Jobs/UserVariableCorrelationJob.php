<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\Models\UserVariable;
use App\Properties\UserVariable\UserVariableStatusProperty;
class UserVariableCorrelationJob extends BaseJob {
	/**
	 * @var UserVariable
	 */
	public $userVariable;
	/**
	 * @param UserVariable $uv
	 * @param string $reason
	 */
	public function __construct(UserVariable $uv, string $reason){
		$uv->status = UserVariableStatusProperty::STATUS_CORRELATE;
		parent::__construct($uv, $reason);
	}
	/**
	 * Execute the job.
	 */
	public function handle(){
		$this->exceptionIfAlreadyHandled();
		/** @var UserVariable $bm */
		$uv = $this->study; // We pass BaseModel instead of larger DBModels that can cause memory issues
		$uv->correlateAllStale();
	}
}
