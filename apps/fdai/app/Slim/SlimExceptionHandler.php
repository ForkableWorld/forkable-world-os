<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim;
use App\Exceptions\ExceptionHandler;
use App\Utils\AppMode;
use App\Utils\Env;
use Exception;
use Throwable;
class SlimExceptionHandler {
	public const JSON_FIELD_ERROR = 'error';
	public const JSON_FIELD_ERROR_MESSAGE = 'message';
	public const JSON_FIELD_ERROR_FILE = 'file';
	public const JSON_FIELD_ERROR_STACKTRACE = 'stackTrace';
	private $application;
	public function register(QMSlim $application){
		// Clear this handler from an existing Application if one is set.
		if(isset($this->application)){
			$this->getApplication()->error(null);
		}
		$this->application = $application;
		$this->application->error([$this, 'handleException']);
	}
	/**
	 * Handle the given error and respond appropriately.
	 * @param Throwable $e The exception to handle.
	 * @throws Throwable
	 */
	public function handleException(Throwable $e): void{
		ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
		if(!ExceptionHandler::isExpectedException($e) && AppMode::isUnitOrStagingUnitTest()){
			throw $e;
		}
		$r = $this->exceptionToJsonObject($e);
		$code = $r['code'] ?? $e->getCode();
		if(is_string($code)){
			$code = 500;
		} // Database returns "HY" string codes
		if($code < 100 || $code > 599){
			$code = 500;
		}
		$this->getApplication()->writeJsonWithGlobalFields($code, $r);
	}
	/**
	 * Render response body with debug information
	 * @param Exception $exception
	 * @return array
	 */
	private function exceptionToJsonObject(Throwable $exception): array{
		$r = [
			self::JSON_FIELD_ERROR => [self::JSON_FIELD_ERROR_MESSAGE => $exception->getMessage()],
			'code' => $exception->getCode(),
			'status' => $exception->getMessage(),
			'success' => false,
		];
		if(!AppMode::isProductionApiRequest()){
			$r[self::JSON_FIELD_ERROR][self::JSON_FIELD_ERROR_STACKTRACE] =
				explode(PHP_EOL, $exception->getTraceAsString());
			$r[self::JSON_FIELD_ERROR][self::JSON_FIELD_ERROR_FILE] = $exception->getFile();
		}
		return $r;
	}
	/**
	 * @return QMSlim
	 */
	public function getApplication(): QMSlim{
		return $this->application;
	}
	public function wantsHTML(): bool{
		$acceptHeader = $this->getApplication()->request->headers('Accept', null);
		// If the accept header is set AND the client accepts HTML we respond with an HTML error.
		$wantsHTML = !is_null($acceptHeader) && strpos($acceptHeader, 'text/html') !== false;
		// If the client didn't specify text/html as accepted we respond with good old JSON.
		return $wantsHTML;
	}
}
