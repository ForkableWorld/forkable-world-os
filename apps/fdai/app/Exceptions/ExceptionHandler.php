<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Buttons\Admin\IgnitionButton;
use App\Buttons\Auth\AuthButton;
use App\Buttons\Auth\LoginButton;
use App\Buttons\LinkButton;
use App\Buttons\QMButton;
use App\Cards\ErrorCard;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\GlobalLogMeta;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Override\QMErrorPageHandler;
use App\Slim\Middleware\QMAuth;
use App\Solutions\BaseRunnableSolution;
use App\Traits\HasBaseSolution;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\EnvOverride;
use App\Utils\SecretHelper;
use Auth;
use Bugsnag;
use Bugsnag\Report;
use ErrorException;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use LogicException;
use PHPUnit\Framework\AssertionFailedError;
use Predis\Connection\ConnectionException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestGenerators\ApiTestFile;
use Throwable;
use Whoops\Handler\HandlerInterface;
class ExceptionHandler extends Handler {
	public static ?string $expectedRequestException = null;
	private static Throwable $lastDumped;
	/**
	 * A list of the exception types that are not reported.
	 * @var array
	 */
	protected $dontReport = [//
	];
	/**
	 * A list of the inputs that are never flashed for validation exceptions.
	 * @var array
	 */
	protected $dontFlash = [
		'password',
		'password_confirmation',
	];
	/**
	 * @return string|null
	 */
	public static function getExpectedRequestException(): ?string{
		return self::$expectedRequestException;
	}
	/**
	 * @param Throwable $e
	 * @return string
	 */
	public static function getProblemSolutionString(Throwable $e): string{
		$str = "Problem:\n".trim($e->getMessage())."\n";
		if(method_exists($e, 'getSolution')){
			/** @var ProvidesSolution|Throwable $e */
			$solution = $e->getSolution();
			$str .= $solution->getSolutionDescription()."\n";
			$str .= QMLog::print_r($solution->getDocumentationLinks(), true)."\n";
			return $str;
		}
		return $str."\n Please implement ProvidesSolution on ".get_class($e).
		       " so we can get getProblemAndSolutionString";
	}
	/**
	 * @param Throwable $e
	 * @return LinkButton[]
	 */
	public static function getDocumentationLinkButtons(Throwable $e): array{
		$links = [];
		if(method_exists($e, 'getDocumentationLinks')){
			$links = $e->getDocumentationLinks();
		}
		if(!$links){
			if(method_exists($e, 'getSolution')){
				/** @var ProvidesSolution $e */
				$solution = $e->getSolution();
				$links = $solution->getDocumentationLinks();
			}
		}
		$buttons = QMButton::linksToButtons($links);
		return $buttons;
	}
	/**
	 * @param Throwable $e
	 * @return ErrorCard
	 * @noinspection PhpUnused
	 */
	public static function toErrorCard(Throwable $e): ErrorCard{
		return ErrorCard::fromException($e);
	}
	/**
	 * @param Throwable|\ErrorException $e
	 * @return string
	 */
	/**
	 * @param Throwable[] $exceptions
	 * @return ErrorCard[]
	 * @noinspection PhpUnused
	 */
	public static function toExceptionCards(array $exceptions): array{
		return ErrorCard::fromExceptions($exceptions);
	}
	/**
	 * @param Throwable $e
	 * @return Solution
	 */
	public static function toUserSolution(Throwable $e): Solution{
		return QMErrorPageHandler::getUserSolution($e);
	}
	/**
	 * @param Solution[] $solutions
	 * @return string
	 */
	public static function renderSolutions(array $solutions): string{
		$solutionsHtml = [];
		foreach($solutions as $solution){
			$solutionsHtml[] = ExceptionHandler::renderSolution($solution);
		}
		$solutionsHtml = array_unique($solutionsHtml);
		$html = "
<div id=\"solutions-container\" class=\"solution-content ml-0\" style=\"max-width: 600px; margin: auto; text-align: left;\">
    <h3>Solutions:</h3>
    <div style='text-align: left;'>
".implode('', $solutionsHtml);
		$html .= "
    </div>
</div>
    ";
		return $html;
	}
	public static function renderSolution(Solution $solution): string{
		$desc = $solution->getSolutionDescription();
		if(stripos($desc, '<') !== false){
			return $desc;
		}
		return HtmlHelper::renderView(view('solution', ['solution' => $solution]));
	}
	/**
	 * @param Throwable[] $exceptions
	 * @param string $title
	 * @return string
	 */
	public static function renderExceptions(array $exceptions, string $title = "Issues:"): string{
		$messages = [];
		foreach($exceptions as $e){
			$messages[] = ExceptionHandler::renderHtml($e);
		}
		$html = "
    <div id='exceptions-container' style='max-width: 600px; margin: auto; text-align: left;'>
        <h3>$title</h3>
        <ul style='text-align: left;'>
    ";
		$messages = array_unique($messages);
		foreach($messages as $error){
			$html .= "
            <li style='display:list-item;'>
                ".$error."
            </li>
    ";
		}
		$html .= "
        </ul>
    </div>
";
		return $html;
	}
	/**
	 * @param Throwable $e
	 * @return string
	 */
	public static function renderHtml(Throwable $e): string{
		if(!isset($e->userErrorMessageTitle)){
			$message = $e->getMessage();
			return "
<p>
".$message."
</p>";
		}
		$html = "<h5>$e->userErrorMessageTitle</h5>";
		if(method_exists($e, 'getUserErrorMessageBodyHtml')){
			$html .= $e->getUserErrorMessageBodyHtml();
		}
		return $html;
	}
	/**
	 * @param Throwable $e
	 * @return string
	 */
	public static function getAbbreviatedMessage(Throwable $e): string{
		$ignitionButton = new IgnitionButton($e);
		$message = $e->getMessage();
		if(strlen($message) > 140){
			$message = QMStr::truncate($message, 140, "...\nSee full exception at ".$ignitionButton->getUrl());
		}
		return trim($message);
	}
	public static function getHandler(): self{
		$h = resolve(static::class);
		return $h;
	}
	/**
	 * @param Throwable $e
	 * @return void
	 */
	public static function throwIfNotProductionApiRequest(Throwable $e): void{
		if(!AppMode::isProductionApiRequest()){
			/** @var RuntimeException $e */
			throw $e;
		}
		ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
	}
	/**
	 * @param Throwable $e
	 * @return Solution[]
	 */
	public static function getSolutions(Throwable $e): array{
		return QMErrorPageHandler::getSolutions($e);
	}
	/**
	 * Returns the model with invalid attributes.
	 * @param Throwable $e
	 * @return BaseModel|null
	 */
	public static function getModel(Throwable $e): ?BaseModel{
		return $e->model ?? null;
	}
	public static function logError(Throwable $e){
		QMLog::error(__METHOD__.": ".$e->getMessage());
	}
	/**
	 * Report or log an exception.
	 * @param Throwable $e
	 * @return void
	 * @throws Throwable
	 */
	public function report(Throwable $e): void{
		if($this->shouldntReport($e)){
			return;
		}
        ConsoleLog::error(__METHOD__.": ".$e->getMessage());
		if(AppMode::consoleAvailable()){
			self::dumpOrNotify($e);
		} else{
			if(EnvOverride::isLocal()){
				try {
					ApiTestFile::saveAndNotify($e);
				} catch (\Throwable $e) {
				    error_log("Error saving exception to file: ".$e->getMessage());
				}
			}
		}
		parent::report($e);
	}
	/**
	 * Determine if the exception is in the "do not report" list.
	 * @param \Throwable $e
	 * @return bool
	 */
	protected function shouldntReport(Throwable $e): bool{
		$dontReport = parent::shouldntReport($e);
		if($dontReport){
			return true;
		}
		if(in_array(get_class($e), QMLog::DO_NOT_REPORT)){
			return true;
		}
		if(in_array($e, QMLog::$alreadyReported)){
			return true;
		}
		if(self::isExpectedException($e)){
			return true;
		}
		QMLog::$alreadyReported[] = $e;
		return false;
	}
	/**
	 * @param Throwable|string $e
	 * @return bool
	 */
	public static function isExpectedException($e): bool{
        if(is_string($e)){$e = new $e;}
		$expected = self::getExpectedRequestException();
		if(!$expected){
			return false;
		}
		if($e instanceof $expected){
			return true;
		}
		if($e instanceof AssertionFailedError){
			return false;
		}
		$message = $e->getMessage();
		$actual = get_class($e);
		ConsoleLog::error("Expected exception: $expected but got: $actual with message: $message
".ExceptionHandler::dumpOrNotify($e));
		/** @var LogicException $e */
		throw $e;
	}
	/**
	 * Render nice exception dump with LOG_PREFIX for all lines
	 * @param Throwable $e
	 * @param array $meta
	 */
	public static function dumpOrNotify(Throwable $e, array $meta = []): void{
		if(!AppMode::consoleAvailable()){
			Bugsnag::notifyException($e, function (Report $report) use ($meta) {
				$report->setSeverity('error');
				$report->setMetaData($meta);
			});
		} else{
			self::dumpExceptionToConsole($e);
		}
	}
	/**
	 * @param \Throwable $e
	 */
	public static function dumpExceptionToConsole(Throwable $e): void{
		$last = self::$lastDumped ?? null;
		if($last === $e){
			return;
		}
		self::$lastDumped = $e;
		if(method_exists($e, 'getSolution')){
			try {
				/** @var Solution $s */
				$s = $e->getSolution();
				BaseRunnableSolution::dumpSolution($s);
			} catch (\Throwable $solutionException) {
				ConsoleLog::error("Could not dump solution because:".$solutionException->getMessage());
				//if(!AppMode::isProductionApiRequest()){die($message);}
			}
		} else {
			ConsoleLog::error(get_class($e)."\n".$e->__toString());
		}
	}
	/**
	 * @param Throwable $e
	 * @param array $meta
	 */
	public static function logExceptionOrThrowIfLocalOrPHPUnitTest(Throwable $e, array $meta = []){
		GlobalLogMeta::addCustomGlobalMetaData("meta data from ".get_class($e), $meta);
		$message = $e->getMessage();
		if(str_contains($message, 'Cannot modify header information - headers already sent by') &&
		   AppMode::isTestingOrStaging()){
			QMLog::info($message);
			return;
		}
		if(ExceptionHandler::isExpectedException($e)){
			return;
		}
		self::dumpOrNotify($e, $meta);
	}

    /**
     * Render an exception into an HTTP response.
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
     * @throws QMFileNotFoundException
     */
	public function render($request, Throwable $e){
		$expected = self::isExpectedException($e);
		$e = self::wrapThrowableWithSolvable($e);
		$code = $this->getCode($e);
		$message = $this->exceptionToMessage($e);
        $expectsJson = $request->expectsJson();
		$uri = $_SERVER["REQUEST_URI"] ?? null;
		if($uri && str_starts_with($uri, '/api/')){$expectsJson = true;}
		if($e instanceof \Illuminate\Auth\AuthenticationException) {
			if(AuthButton::shouldRedirectToLoginIfNotAuthenticated($request)){
				if($redirectTo = $e->redirectTo()){
					return redirect($redirectTo);
				}
			}
		}
        $acceptsHtml = $request->acceptsHtml();
		if(AppMode::isAnyKindOfUnitTest() && !$expected){
			QMLog::error("Did not expect exception: ".get_class($e));
			/** @var LogicException $e */
			throw $e;
		}
        if(AuthButton::shouldRedirectToLoginIfNotAuthenticated($request)){
            if($e instanceof TokenMismatchException){
                return LoginButton::getRedirect();
            }
            if($code === 401 && !Auth::user()){
                return AuthButton::getRedirect();
            }
        }
		try {
			$isUnitOrStagingTest = AppMode::isUnitOrStagingUnitTest();
		} catch (\Throwable $appModeException) {
			$isUnitOrStagingTest = false;
		    error_log("Could not determine if unit or staging test because: ".
		              $appModeException->getMessage().
		                $appModeException->getTraceAsString());
		}
		if($code !== 401 && Env::get(Env::APP_DEBUG) &&
			!$isUnitOrStagingTest){ // Forces return of Ignition page even if wantsJson()
			return $this->prepareResponse($request, $e);
		}
		try{
			QMErrorPageHandler::addGlobalContext($e);
		} catch (\Throwable $e){
			ConsoleLog::exception($e);
		}
		if($expectsJson){
			$message = SecretHelper::obfuscateString($message);
			$data = [
				'error' => $message,
				'message' => $message,
				'errors' => [$message],
			];
			$response = response();
			return $response->json($data, $code);
		}
		try{
			if ( ! $e instanceof Throwable) {
				$e = new ErrorException(
	               $e->getMessage(),
	               $e->getCode(),
	               E_ERROR,
	               $e->getFile(),
	               $e->getLine()
               );
            }
			return parent::render($request, $e);
		} catch (\Throwable $e) {
			$html = FileHelper::getContents("public/error/5xx.html");
			echo $html;
			exit();
		}
	}
	/** @noinspection PhpUnused */
	/**
	 * @param $e
	 * @return HasBaseSolution|Throwable
     * Redis connection exceptions can occur outside application code making them un-catchable so we replace with a
	 * exception that has solution instructions here
	 */
	public static function wrapThrowableWithSolvable($e): InvalidRedisCredentialsException|Throwable{
		if($e instanceof ConnectionException){
			return new InvalidRedisCredentialsException($e);
		}
		return $e;
	}
	/**
	 * @param Throwable $e
	 * @return int
	 */
	private function getCode($e): int{
		$status = $e->status ?? null;
		if(is_int($status)){
			$code = $status;
		} else {
			$code = $e->getCode();
		}
		if($e instanceof HttpException){
			$code = $e->getStatusCode();
		}
		if($e instanceof RecordsNotFoundException){
			$code = 404;
		}
		if(empty($code)){
			if($e instanceof AuthenticationException){
				$code = 401;
			} else{
				$code = 500;
			}
		}
		if(!is_int($code)){
			if(is_string($code) && str_starts_with($code, "HY")){
				return 500;
			}
			QMLog::error("Code is not an int but is: ".QMLog::print_r($code). "
			for exception:
			".get_class($e)."\n".
			             $e->getMessage());
			$code = 500;
		}
		return $code;
	}
	/**
	 * @param $e
	 * @return string
	 */
	private function exceptionToMessage($e): string{
		$message = $e->getMessage();
		if(stripos($message, 'The given data was invalid') !== false){
			/** @var ValidationException $e */
			$message = \App\Logging\QMLog::print_r($e->validator->failed(), true);
			$message = str_replace([
				                       '[',
				                       ']',
				                       'Array',
				                       '(',
				                       ')',
			                       ], '', $message);
		}
		return $message;
	}
	/**
	 * @return Application|mixed|\Whoops\Handler\Handler|HandlerInterface
	 */
	protected function whoopsHandler(): mixed{
		try {
			return app(HandlerInterface::class);
		} /** @noinspection PhpRedundantCatchClauseInspection */ catch (BindingResolutionException $e) {
			return parent::whoopsHandler();
		}
	}
}
