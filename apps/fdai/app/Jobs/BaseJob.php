<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\Exceptions\AlreadyQueuedException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Storage\Memory;
use App\Traits\QMAnalyzableTrait;
use App\Utils\AppMode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;
abstract class BaseJob implements ShouldQueue {    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	/**
	 * @var DBModel|BaseModel
	 */
	public $study;
	/**
	 * @var string
	 */
	public $reason;
	/**
	 * Create a new job instance.
	 * @param DBModel|BaseModel $model
	 * @param string $reason
	 */
	public function __construct($model, string $reason){
		$this->reason = $reason;
		if(AppMode::isWorker()){
			le("We cannot queue from workers!");
		}
		$this->exceptionIfAlreadyQueued($model);
	}
	/**
	 * @param BaseModel|DBModel $model
	 * @throws AlreadyQueuedException
	 */
	protected function exceptionIfAlreadyQueued($model){
		$this->study = $model;
		if(static::alreadyQueued($model)){
			throw new AlreadyQueuedException("Already queued $model");
		}
		$key = static::getQueueKey($model);
		Memory::set($key, $model, static::class);
	}
	/**
	 * @param DBModel|QMAnalyzableTrait|BaseModel $model
	 * @return mixed
	 */
	public static function alreadyQueued($model){
		$key = self::getQueueKey($model);
		$alreadyQueued = Memory::get($key, static::class);
		if($alreadyQueued){
			$model->logError("Already queued");
		}
		return $alreadyQueued;
	}
	/**
	 * @param BaseModel $model
	 * @return string
	 */
	public static function getQueueKey($model): string{
		$id = $model->getUniqueIndexIdsSlug();
		if(method_exists($model, 'getStudyId')){
			$id = $model->getStudyId();
		}
		$key = "Queued-Jobs:" . static::class . ":" . $id;
		return $key;
	}
	protected function exceptionIfAlreadyHandled(){
		$key = "Handled-Jobs:" . $this->getJobName() . ":" . $this->study->getUniqueIndexIdsSlug();
		$alreadyHandled = Memory::get($key, static::class);
		if($alreadyHandled){
			le("Already handled $this->study");
		}
		Memory::set($key, $this, static::class);
		$this->logInfo("Handling: $this->study");
	}
	/**
	 * @param string $message
	 */
	protected function logInfo(string $message){
		QMLog::info($this->getJobName() . ": $message");
	}
	/**
	 * @return string
	 */
	protected function getJobName(): string{
		return (new ReflectionClass($this))->getShortName();
	}
	/**
	 * @return string
	 */
	public function getUniqueIdentifier(): string{
		return static::getQueueKey($this->study);
	}
	public static function queueModel(BaseModel $l, string $reason): ?PendingDispatch{
		if(static::alreadyQueued($l)){
			$l->logError("Already queued");
			return null;
		}
		$l->logInfo("Queueing AnalyzeJob because $reason...");
		try {
			return static::dispatch($l, $reason);
		} catch (\Throwable $e){
		    QMLog::error("Could not queue $l because $reason");
			if(!AppMode::isProductionApiRequest()){
				le($e);
			}
			return null;
		}
	}
}
