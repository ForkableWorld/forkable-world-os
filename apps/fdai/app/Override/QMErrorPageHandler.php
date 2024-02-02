<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Override;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Logging\ConsoleLog;
use App\Logging\GlobalLogMeta;
use App\Slim\Model\User\QMUser;
use App\Solutions\GetHelpSolution;
use App\Utils\AppMode;
use Facade\FlareClient\Context\RequestContext;
use Facade\FlareClient\Report;
use Facade\Ignition\ErrorPage\ErrorPageHandler;
use Facade\Ignition\ErrorPage\ErrorPageViewModel;
use Facade\IgnitionContracts\Solution;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Tests\Traits\CreatesApplication;
use Tests\QMBaseTestCase;
use Throwable;
class QMErrorPageHandler extends ErrorPageHandler {
	public static $instance;
	/**
	 * @param \Throwable $throwable
	 * @param null $defaultTab
	 * @param array $defaultTabProps
	 * @return string
	 */
	public static function renderHtml(Throwable $throwable, $defaultTab = null, array $defaultTabProps = []): string{
		return self::get()
		           ->getHtml($throwable, $defaultTab, $defaultTabProps);
	}
	/**
	 * @param \Throwable $throwable
	 * @param null $defaultTab
	 * @param array $defaultTabProps
	 * @return string
	 */
	public function getHtml(Throwable $throwable, $defaultTab = null, array $defaultTabProps = []): string{
		$pageArr = $this->getErrorPageData($throwable, $defaultTab, $defaultTabProps);
		foreach($pageArr["report"]["stacktrace"] as $i => $item){
			//$pageArr["report"]["stacktrace"][$i]["file"] = str_replace(abs_path()."/", '\\wsl$\Ubuntu-18
			//.04\www\wwwroot\qm-api', $item["file"]);
		}
		/** @noinspection PhpUnhandledExceptionInspection */
        $html = $this->renderer->render(
            'errorPage',
            $pageArr
        );
        return $html;
    }
    public static function get(Throwable $e = null): self {
        /** @var static $handler */
        if($handler = self::$instance){
            return $handler;
        }
        try {
            self::$instance = resolve(static::class);
        } catch (ReflectionException $e){
            if(AppMode::isUnitOrStagingUnitTest()){
                CreatesApplication::bootstrapApp();
                try {
                    self::$instance = resolve(static::class);
                } catch (ReflectionException $e){
                    ConsoleLog::error("bootstrapApp didn't work");
                    die("bootstrapApp didn't work");
                }
            }
        }
        self::addGlobalContext($e);
        return self::$instance;
    }
    public static function getReport(Throwable $e = null): Report {
        $report = self::createReport($e);
        $req = Request::createFromGlobals();
        $report->useContext(new RequestContext($req)); // For some reason it won't show exception message with the ConsoleContext due to a javascript bug, I think
        return $report;
    }
    /**
     * @param Throwable $e
     * @return array
     */
    public static function getSolutions(Throwable $e): array {
        $solutions = self::get()->solutionProviderRepository->getSolutionsForThrowable($e);
        if(method_exists($e, 'getSolution')){
            $solutions[] = $e->getSolution();
        }
        return $solutions;
    }
    /**
     * @param Throwable $e
     * @return Solution
     */
    public static function getUserSolution(Throwable $e) {
        if(method_exists($e, 'getUserSolution')){
            return $e->getUserSolution();
        } else {
            return new GetHelpSolution();
        }
    }
    public static function addGlobalContext(Throwable $e = null){
	    $arr = GlobalLogMeta::get();
	    $flare = static::get()->flareClient;
        foreach($arr as $key => $value){
            if(is_array($value)){
                foreach($value as $subKey => $subValue){
                    $flare->context($key." - ".$subKey, $subValue);
                }
            } else {
                $flare->context($key, $value);
            }
        }
    }
    /**
     * @param Throwable $throwable
     * @return ErrorPageViewModel
     */
    public function getErrorPageViewModel(Throwable $throwable): ErrorPageViewModel{
        $report = self::getReport($throwable);
        $solutions = $this->solutionProviderRepository->getSolutionsForThrowable($throwable);
        $viewModel = new ErrorPageViewModel($throwable, $this->ignitionConfig, $report, $solutions);
        return $viewModel;
    }
    /**
     * @param Throwable $throwable
     * @param $defaultTab
     * @param array $defaultTabProps
     * @return array
     */
    public function getErrorPageData(Throwable $throwable, $defaultTab = null, array $defaultTabProps = []): array{
        $viewModel = $this->getErrorPageViewModel($throwable);
        $viewModel->defaultTab($defaultTab, $defaultTabProps);
		$pageArr = $viewModel->toArray();
        $url = $pageArr["report"]["context"]["request"]["url"];
        if(strlen($url) < 10 || stripos($url, '/quantimo.do/') === false){
            if(AppMode::isUnitOrStagingUnitTest() && \App\Utils\AppMode::getCurrentTest()){
                $pageArr["report"]["context"]["request"]["url"] = \App\Utils\AppMode::getPHPStormUrlStatic();
            } elseif (AppMode::isJenkins()){
                $pageArr["report"]["context"]["request"]["url"] = JenkinsConsoleButton::generateUrl();
            }
        }
        if(empty($pageArr["report"]["user"])){
            $user = QMUser::getLastFromMemory();
            if($user){
                $pageArr["report"]["user"] = $user->toArray();
            }
        }
        return $pageArr;
    }
    /**
     * @param Throwable|null $e
     * @return Report
     */
    protected static function createReport(Throwable $e = null): Report{
        $report = self::get()->flareClient->createReport($e ?? new \LogicException("Dummy Logic Exception to Create Report"));
        return $report;
    }
}
