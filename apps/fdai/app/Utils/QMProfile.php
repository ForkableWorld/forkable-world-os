<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUndefinedFunctionInspection */
/** @noinspection PhpUndefinedConstantInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
namespace App\Utils;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\PhpUnitJobs\JobTestCase;
use App\Repos\LiveprofRepo;
use App\Slim\View\Request\QMRequest;
use App\Types\BoolHelper;
use App\Types\QMStr;
use App\UI\Alerter;
use App\UI\FontAwesome;
use Symfony\Component\HttpKernel\Profiler\Profiler;
/**
 * @package App\Utils
 */
class QMProfile extends Profiler {
	private const TYPE_XHGUI = 'xhgui';
	private const TYPE_LIVEPROF = 'liveprof';
	public static ?string $lastProfileUrl = null;
	/**
	 * @var LiveProfiler|null
	 */
	public static ?LiveProfiler $liveProf = null;
	public static ?Profiler $xhgui = null;
	private static ?string $label;
	private static $alreadyComplained;
	/** @var bool */
	private static $profileStartLocation;
	private static ?string $type = null;
	/**
	 * @var bool
	 */
	private static $waitingForShutdownFunction;
	private static $abbreviationToNameMap = [
		'ct' => "Call Count",
		'wt' => "Wall Time",
		'cpu' => "CPU Time",
		'mu' => "Memory Usage",
		'pmu' => "Peak Memory Usage",
		'queryCount' => "Queries",
	];
	/**
	 * @var int
	 */
	public $queryCount;
	/**
	 * TestProfile constructor.
	 */
	public function __construct(string $name = null){
		self::$label = $name;
		parent::__construct();
	}
	/**
	 * @return string|null
	 */
	public static function profileNameOrUri(): ?string{
		if(self::$label){
			return self::$label;
		}
		$uri = array_key_exists('REQUEST_URI', $_SERVER) ? $_SERVER['REQUEST_URI'] : null;
		if(empty($uri) && isset($_SERVER['argv'])){
			$cmd = basename($_SERVER['argv'][0]);
			$uri = $cmd.' '.implode(' ', array_slice($_SERVER['argv'], 1));
		}
		if($test = \App\Utils\AppMode::getCurrentTestName()){
			$uri = $test;
		}
		if(empty($uri)){
			QMLog::error("data['meta']['url'] is empty");
			$uri = "No REQUEST_URI available";
		}
		return preg_replace('/=\d+/', '', $uri);
	}
	/**
	 * @return mixed
	 */
	public static function rebuildSnapshot(){
		QMLog::info("Rebuilding profile snapshot...");
		$path = LiveprofRepo::getAbsolutePath('src/www/rebuild-snapshot.php '.QMProfile::getAppName()." ".
		                                      QMProfile::getLabel());
		include PROJECT_ROOT."/repos/mikepsinn/liveprof-ui/src/www/rebuild-snapshot.php";
		/** @noinspection PhpUndefinedVariableInspection */
		return $Page->rebuildSnapshot(QMProfile::getAppName(), QMProfile::generateLabel(), date('Y-m-d'));
	}
	/**
	 * @return string
	 */
	public static function getAppName(): string{
		return ReleaseStage::getReleaseStage();
	}
	/**
	 * @return string
	 */
	public static function getLabel(): string{
		if(!self::$label){
			le("no label");
		}
		return self::$label;
	}
	/**
	 * @param string|null $label
	 */
	public static function setLabel(?string $label): void{
		if($label === ""){
			le("provide label");
		}
		self::$label = $label;
	}
	/**
	 * @return string|void|null
	 */
	public static function generateLabel(): string{
		if(AppMode::isApiRequest()){
			$label = qm_request()->getMethod()." ".qm_request()->getRequestUri();
		} else{
			$label = JobTestCase::getJobTaskOrTestName();
		}
		//		if(AppMode::isPHPStorm()){
		//			$label .= "-" . date('H:i:s', time());
		//		} elseif(AppMode::isUnitOrStagingTest()){
		//			if(AppMode::isUnitOrStagingTest()){
		//				$label .= "-" . LiveprofRepo::getShortCommitSha();
		//			}
		//		}
		return $label;
	}
	/**
	 * @param string $type
	 */
	public static function setType(string $type): void{
		if(!in_array($type, [
			self::TYPE_XHGUI,
			self::TYPE_LIVEPROF,
		])){
			le("wrong type $type");
		}
		self::$type = $type;
	}
	/**
	 * @return string
	 */
	public static function getLastProfileUrl(): ?string{
		return self::$lastProfileUrl;
	}
	/**
	 * @param bool $throwException
	 * @param bool $force
	 * @param string|null $label
	 * @return void
	 */
	public static function profileIfEnvSet(bool   $throwException = true, bool $force = false,
	                                       string $label = null): void{
		if(!$force && !self::weShouldProfile($throwException)){
			return;
		}
		$type = EnvOverride::getFormatted('PROFILE');
		if(!$type){
			$type = $_GET[QMRequest::PARAM_PROFILE] ?? \App\Utils\Env::get('PROFILE');
		}
		if($type && !BoolHelper::isFalsey($type)){
			if($type === self::TYPE_XHGUI){
				self::startXhguiProfile();
			} elseif($type === self::TYPE_LIVEPROF){
				self::startLiveProf($label);
			} else{
				QMLog::info("PROFILE value should be one of: ".self::TYPE_XHGUI." or ".self::TYPE_LIVEPROF.
				             ". Defaulting to ".self::TYPE_XHGUI."...");
				self::startXhguiProfile();
			}
		}
	}
	/**
	 * @param bool $throwException
	 * @return bool
	 */
	private static function weShouldProfile(bool $throwException = false): bool{
		if($caller = self::$xhgui){
			QMLog::debug("Already profiling: ".QMLog::var_export($caller, true));
			return false;
		}
		if(!\App\Utils\Env::get(Env::PROFILE) && !EnvOverride::getFormatted(Env::PROFILE)){
			return false;
		}
		if(AppMode::isTravisOrHeroku()){
			ConsoleLog::info("Not profiling because AppMode::isTravis");
			return false;
		}
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ext = get_loaded_extensions();
		if(!self::tideways_installed()){
			self::outputTidewaysInstallInstructionsOrException($throwException);
			return false;
		}
		return true;
	}
	/**
	 * @param bool $throwException
	 */
	private static function outputTidewaysInstallInstructionsOrException(bool $throwException): void{
		$message =
			"Please install tideways_xhprof with sudo bash ".abs_path("scripts/aapanel_tideways_xprof.sh");
		if($throwException){
			le($message);
		}
		if(!self::$alreadyComplained){
			QMLog::error($message);
			self::$alreadyComplained = true;
		}
	}
	/**
	 * @param string|null $name
	 */
	public static function startXhguiProfile(string $name = null){
		self::startProfile($name, self::TYPE_XHGUI);
	}
	/**
	 * @param string|null $label
	 * @param string $type
	 */
	private static function startProfile(?string $label, string $type = self::TYPE_XHGUI){
		if(self::alreadyProfiling()){
			$label = self::profileNameOrUri();
			QMLog::info("Already profiling $label with ".self::$type."!  Initiated at: ",
			            QMLog::print(self::$profileStartLocation));
			return;
		}
		self::$type = $type;
		try {
			xdebug_get_profiler_filename();
			self::$profileStartLocation = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
			if(!$label){
				$label = self::generateLabel();
			}
			if($label = trim($label)){
				$label = QMStr::truncate($label, 60);
			}
			self::setLabel($label);
			self::$liveProf = self::liveProf($label);
		} catch (\Exception $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			self::outputTidewaysInstallInstructionsOrException(true);
			self::$profileStartLocation = null;
			return;
		}
		// Add this block inside some bootstrapper or other "early central point in execution"
		if(AppMode::isApiRequest()){
			self::$waitingForShutdownFunction = true;
		} // Prevent ending profile early in API request because it breaks laravel view response for some reason
		register_shutdown_function(function(){
			self::$profileStartLocation = null;
			self::$waitingForShutdownFunction = false;
			if(!self::$xhgui && !AppMode::isApiRequest()){
				return;
			} // Avoid error warning if we ended profile in CLI
			self::endProfile();
		});
	}
	/**
	 * @param string $label
	 * @return LiveProfiler
	 * @deprecated This kind of sucks.  Use XHGUI instead.
	 */
	private static function liveProf(string $label): LiveProfiler{
		if(!self::tideways_installed()){
			self::outputTidewaysInstallInstructionsOrException(true);
		}
		self::setLabel($label);
		// Start profiling
		$i = LiveProfiler::getInstance();
		$i->setMode(LiveProfiler::MODE_DB) // optional, MODE_DB - save profiles to db, MODE_FILES - save profiles to files, MODE_API - send profiles to http://liveprof.org/
		  ->setConnectionString(LiveprofRepo::DB_URL) // optional, you can also set the connection url in the environment variable
		                                              // LIVE_PROFILER_CONNECTION_URL
		                                              //->setPath('/app/data/') // optional, path to save profiles, you can also set the file path in the
		                                              // environment variable LIVE_PROFILER_PATH
		                                              //->setApiKey('api_key') // optional, api key to send profiles and see demo, you can get it on
		                                              //  http://liveprof.org/
		                                              // optional, current app name to use one profiler in several apps, "Default" by default
		  ->setApp(QMProfile::getAppName()) //ReleaseStage::getReleaseStage())
		  ->setLabel(QMProfile::generateLabel()) // optional, the request name, by default the url path or script name in cli
		  ->setDivider(1)                   // optional, profiling starts for 1 of 700 requests with the same app and label, 1000
		                                    // by default
		                                    //->setTotalDivider(7000) // optional, profiling starts for 1 of 7000 requests with forces label "All",
		                                    // 10000 by default
		  ->setLogger(ConsoleLog::logger()) // optional, a custom logger implemented \Psr\Log\LoggerInterface
		//->setConnection($Connection) // optional, a custom instance of \Doctrine\DBAL\Connection if you can't
		// use the connection url
		//->setDataPacker($DatePacker) // optional, a class implemented \Badoo\LiveProfiler\DataPackerInterface to
		// convert array into string
		//->setStartCallback($profiler_start_callback) // optional, set it if you use custom profiler
		//->setEndCallback($profiler_profiler_callback) // optional, set it if you use custom profiler
		//->useXhprof() // optional, force use xhprof as profiler
		//->useTidyWays() // optional, force use TidyWays as profiler
		//->useSimpleProfiler() // optional, force use internal profiler
		//->useXhprofSample() // optional, force use xhprof in sampling mode
		  ->start();
		return QMProfile::$liveProf = $i;
	}
	/**
	 * @return string
	 */
	public static function endProfile(bool $waitForExport = false): ? string {
        if(!self::available()) {
            ConsoleLog::once("Profiling not available");
            return null;
        }
		if(!self::tideways_installed()){
			self::outputTidewaysInstallInstructionsOrException(true);
		}
		if(self::$waitingForShutdownFunction){
			$msg =
				"Not ending yet because ending profile early in API request breaks laravel view response for some reason.  So waiting for shutdown function...";
			ConsoleLog::info($msg);
			return null;
		}
		if(!self::alreadyProfiling()){
			ConsoleLog::debug("Not profiling so can't endProfileAndSaveResult");
			return null;
		}
		self::$lastProfileUrl = self::endLiveProf($waitForExport);
		self::$liveProf = null;
		if($t = \App\Utils\AppMode::getCurrentTest()){
			$t->setProfileUrl(self::$lastProfileUrl);
		}
		try {
			self::toast(self::$lastProfileUrl);
		} catch (\Throwable $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		self::$profileStartLocation = null;
		return self::$lastProfileUrl;
	}
	/**
	 * @return bool
	 */
	private static function tideways_installed(): bool{
		if(extension_loaded('uprofiler')){
			return true;
		}
		if(extension_loaded('tideways')){
			return true;
		}
		if(extension_loaded('tideways_xhprof')){
			return true;
		}
		return false;
	}
	public static function alreadyProfiling(): bool{ return self::$profileStartLocation !== null; }
	private static function endLiveProf(bool $waitForExport = false): string{
		$i = LiveProfiler::getInstance();
		$i->end();
		// TODO: figure out why this gets stuck self::rebuildSnapshot();
		$app = QMProfile::getAppName();
		$label = self::getLabel();
		self::rebuildSnapshotWithAPI($app, $label, $waitForExport);
		$url = LiveprofRepo::URL."/tree-view.phtml?app=$app&label=$label";
		QMLog::logLink($url, "LiveProfiler PROFILE AT");
		self::setLabel(null);
		return $url;
	}
	public static function rebuildSnapshotWithAPI(string $app, string $label, bool $waitForExport = false){
		$url = \App\Utils\Env::getAppUrl()."/profiler/rebuild-snapshot.json";
		$params = [
			'app' => $app,
			'label' => $label,
		];
		if($waitForExport){
			$result = APIHelper::makePostRequest($url, $params);
			QMLog::info("Response from $url: ".QMLog::print($result), $params);
		} else{
			APIHelper::post_without_wait($url, $params);
		}
	}
	protected static function toast(string $url): void{
		$name = self::profileNameOrUri();
		Alerter::toastWithButton("$name Profile Complete!", $url, "VIEW PROFILE", FontAwesome::HOURGLASS);
	}
	public static function startLiveProf(string $label = null){
		self::startProfile($label, self::TYPE_LIVEPROF);
	}
	/**
	 * @param bool $flush
	 */
	public function start($flush = true){
		ConsoleLog::info("Starting profile: ".QMProfile::profileNameOrUri());
		parent::start($flush);
	}
    public static function available(): bool{
        return BoolHelper::isTruthy(\App\Utils\Env::get('PROFILING_AVAILABLE'));
    }
	public static function deleteProfiles(){
		$dir = self::getProfileDir();
		$files = glob("$dir/*");
		foreach($files as $file){
			if(is_file($file) && str_contains($file, "cachegrind")){
				try {
					unlink($file);
				} catch (\Throwable $e){
				    QMLog::info(__METHOD__.": ".$e->getMessage());
				}
			}
		}
	}
	private static function getProfileDir(){
		return abs_path("storage/xdebug-profiles");
	}
}
