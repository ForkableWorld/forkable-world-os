<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Logging\QMLog;
use App\Actions\Action;
abstract class QMAction extends Action {
	/**
	 * @var array
	 */
	protected $exceptions = [];
	public function name(): string{
		$name = parent::name();
		return str_replace(" Action", "", $name);
	}
	protected function error(string $message): array{
		QMLog::error($message);
		return Action::danger($message);
	}
	protected function success(string $message = null): array{
		if(!$message){
			$message = $this->name() . " succeeded! :D";
		}
		QMLog::info($message);
		return Action::message($message);
	}
	public function markAsFinished($model): int{
		return parent::markAsFinished($model);
	}
	public function markAsFailed($model, $e = null): int{
		$this->exceptions[] = $e;
		return parent::markAsFailed($model, $e);
	}
	/**
	 * @param string $successMessage
	 * @return array
	 */
	public function respond(string $successMessage = null): array{
		if($exceptions = $this->exceptions){
			$errors = "";
			foreach($exceptions as $e){
				$errors .= "\n" . $e->getMessage() . "\n";
			}
			return $this->error($errors);
		}
		return $this->success($successMessage);
	}
}
