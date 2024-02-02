<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins;
use App\Buttons\Admin\JenkinsJobButton;
use App\Computers\JenkinsSlave;
use App\Computers\ThisComputer;
use App\DevOps\Jenkins\Build\JenkinsBuildAction;
use App\Logging\QMLog;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use Illuminate\Support\Arr;
use RuntimeException;
use stdClass;
class Build {
	use LoggerTrait;
	/**
	 * @var string
	 */
	const ABORTED = 'ABORTED';
	/**
	 * @var string
	 */
	const FAILURE = 'FAILURE';
	/**
	 * @var string
	 */
	const RUNNING = 'RUNNING';
	/**
	 * @var string
	 */
	const SUCCESS = 'SUCCESS';
	/**
	 * @var string
	 */
	const UNSTABLE = 'UNSTABLE';
	/**
	 * @var string
	 */
	const WAITING = 'WAITING';
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var JenkinsBuildAction[]
	 */
	public $actions;
	/**
	 * @var NULL[]
	 */
	public $artifacts;
	/**
	 * @var boolean
	 */
	public $building;
	/**
	 * @var NULL
	 */
	public $description;
	/**
	 * @var string
	 */
	public $displayName;
	/**
	 * @var integer
	 */
	public $duration;
	/**
	 * @var integer
	 */
	public $estimatedDuration;
	/**
	 * @var NULL
	 */
	public $executor;
	/**
	 * @var string
	 */
	public $fullDisplayName;
	/**
	 * @var string
	 */
	public $id;
	/**
	 * @var boolean
	 */
	public $keepLog;
	/**
	 * @var integer
	 */
	public $number;
	/**
	 * @var integer
	 */
	public $queueId;
	/**
	 * @var string
	 */
	public $result;
	/**
	 * @var integer
	 */
	public $timestamp;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var string
	 */
	public $builtOn;
	/**
	 * @var ChangeSet
	 */
	public $changeSet;
	/**
	 * @var NULL[]
	 */
	public $culprits;
	/**
	 * @var JenkinsAPI
	 */
	public $jenkins;
	/**
	 * @var string
	 */
	protected $jobName;
	protected $sha1;
	/**
	 * @param stdClass|Build $response
	 * @param JenkinsAPI|null $jenkins
	 */
	public function __construct($response, JenkinsAPI $jenkins = null){
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		if(isset($response->actions)){
			foreach($response->actions as $i => $item){
				$this->actions[$i] = new JenkinsBuildAction($item);
			}
		}
		if($jenkins){
			$this->setJenkins($jenkins);
		}
	}
	/**
	 * @param string $pattern
	 * @return Build[]
	 */
	public static function abortBuildsForJobsLike(string $pattern): array{
		$jobs = JenkinsJob::getJobsWithNameLike($pattern);
		$aborted = [];
		foreach($jobs as $job){
			if(stripos($job->name, $pattern) !== false){
				$aborted = array_merge($aborted, $job->abortBuilds());
			}
		}
		return $aborted;
	}
	/**
	 * @param Build $build
	 */
	public static function addBuild(Build $build){
		JenkinsAPI::$jobs[$build->fullDisplayName] = $build;
	}
	/**
	 * @param string $jobName
	 * @param int $buildId
	 * @return null|string
	 */
	public static function getUrlBuildByJobAndBuildId(string $jobName, int $buildId): ?string{
		return sprintf('%s/job/%s/%d', JenkinsAPI::$baseUrl, $jobName, $buildId);
	}
	public static function getBuilds(){
		if(JenkinsAPI::$buildsByJob){
			return JenkinsAPI::$buildsByJob;
		}
		$jobs = JenkinsJob::getJobs();
		foreach($jobs as $job){
			JenkinsAPI::$buildsByJob[$job->name] = $job->getBuilds();
		}
		return JenkinsAPI::$buildsByJob;
	}
	public static function consoleMDButton(): string{
		$b = new JenkinsJobButton();
		return $b->getMarkdownBadge();
	}
	/**
	 * @return string
	 */
	public static function getConsoleUrl(): ?string{
		$url = ThisComputer::getBuildConsoleUrl();
		if(!$url){
			return null;
		}
		return $url;
	}
	/**
	 * @param string $jobname
	 * @param string $buildNumber
	 * @return string
	 */
	public static function getConsoleTextBuild(string $jobname, string $buildNumber): string{
		$url = sprintf('%s/job/%s/%s/consoleText', JenkinsAPI::$baseUrl, $jobname, $buildNumber);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		return curl_exec($curl);
	}
	/**
	 * @param string $jobName
	 * @param int $buildId
	 * @return TestReport
	 * @returnTestReport
	 * @internal param string $buildNumber
	 */
	public static function getTestReport(string $jobName, int $buildId): TestReport{
		$url = sprintf('%s/job/%s/%d/testReport/api/json', JenkinsAPI::$baseUrl, $jobName, $buildId);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		$errorMessage =
			sprintf('Error during getting information for build %s#%d on %s', $jobName, $buildId, JenkinsAPI::$baseUrl);
		JenkinsAPI::validateCurl($curl, $errorMessage, $url);
		$infos = json_decode($ret);
		JenkinsAPI::generateStaticModelFromResponse("TestReport", $infos);
		if(!$infos instanceof stdClass){
			throw new RuntimeException($errorMessage);
		}
		return new TestReport(JenkinsAPI::getInstance(), $infos, $jobName, $buildId);
	}
	public static function abortActivePHPUnitBuildsForCommit(string $sha){
		$builds = self::getActivePHPUnitBuildsForCommit($sha);
		foreach($builds as $build){
			$build->abort();
		}
	}
	/**
	 * @param string $shortOrLongSha
	 * @return Build[]
	 */
	public static function getActivePHPUnitBuildsForCommit(string $shortOrLongSha): array{
		$builds = self::getActivePHPUnitBuildsIndexedByJobName();
		$builds = Arr::flatten($builds, 1);
		return collect($builds)->filter(function($build) use ($shortOrLongSha){
			if(is_array($build)){
				throw new \LogicException("Build is array!" . \App\Logging\QMLog::print_r($build, true));
			}
			/** @var Build $build */
			$buildCommit = $build->getLongCommitShaId();
			return strpos($buildCommit, $shortOrLongSha) !== false; // Sometimes we get the short version of sha
		})->all();
	}
	/**
	 * @return Build[]
	 */
	public static function getActivePHPUnitBuildsIndexedByJobName(): array{
		$jobs = JenkinsJob::getPHPUnitJobs();
		$active = [];
		foreach($jobs as $job){
			$all = $job->getBuilds();
			foreach($all as $build){
				if($build->isRunning()){
					$active[$job->getNameAttribute()][] = $build;
				} else{
					break;
				}
			}
		}
		return $active;
	}
	public function getLongCommitShaId(): ?string{
		$actions = $this->actions;
		foreach($actions as $action){
			if(isset($action->lastBuiltRevision->SHA1)){
				return $this->sha1 = $action->lastBuiltRevision->SHA1;
			}
		}
		return $this->sha1 = $this->getChangeSet()->getCommitShaId();
	}
	/**
	 * @return ChangeSet
	 */
	public function getChangeSet(): ChangeSet{
		return new ChangeSet($this->changeSet);
	}
	public function abort(){
		if(!$this->isRunning()){
			QMLog::infoWithoutContext("$this not running so no need to abort");
		}
		self::cancelBuild($this->getJobName(), $this->getId());
	}
	/**
	 * @return array
	 */
	public function getInputParameters(): array{
		$parameters = [];
		if(!property_exists($this->actions[0], 'parameters')){
			return $parameters;
		}
		foreach($this->actions[0]->parameters as $parameter){
			$parameters[$parameter->name] = $parameter->value;
		}
		return $parameters;
	}
	/**
	 * @return int
	 */
	public function getDuration(){
		//division par 1000 => pas de millisecondes
		return $this->duration / 1000;
	}
	/**
	 * Returns remaining execution time (seconds)
	 * @return int|null
	 */
	public function getRemainingExecutionTime(): ?int{
		$remaining = null;
		if(null !== ($estimatedDuration = $this->getEstimatedDuration())){
			//be carefull because time from JK server could be different
			//of time from Jenkins server
			//but i didn't find a timestamp given by Jenkins api
			$remaining = $estimatedDuration - (time() - $this->getTimestamp());
		}
		return max(0, $remaining);
	}
	/**
	 * @return float|null
	 */
	public function getEstimatedDuration(){
		//since version 1.461 estimatedDuration is displayed in jenkins's api
		//we can use it witch is more accurate than calcule ourselves
		//but older versions need to continue to work, so in case of estimated
		//duration is not found we fallback to calcule it.
		if(property_exists($this, 'estimatedDuration')){
			return $this->estimatedDuration / 1000;
		}
		$duration = null;
		$progress = $this->getProgress();
		if(null !== $progress && $progress >= 0){
			$duration = ceil((time() - $this->getTimestamp()) / ($progress / 100));
		}
		return $duration;
	}
	/**
	 * @return null|int
	 */
	public function getProgress(): ?int{
		$progress = null;
		if(null !== ($executor = $this->getExecutor())){
			$progress = $executor->getProgress();
		}
		return $progress;
	}
	/**
	 * @return Executor|null
	 */
	public function getExecutor(): ?Executor{
		if(!$this->isRunning()){
			return null;
		}
		$runExecutor = null;
		foreach(JenkinsSlave::getExecutors() as $executor){
			/** @var Executor $executor */
			if($this->getUrl() === $executor->getBuildUrl()){
				$runExecutor = $executor;
			}
		}
		return $runExecutor;
	}
	/**
	 * @return bool
	 */
	public function isRunning(): bool{
		$res = Build::RUNNING === $this->getResult();
		if($res){
			$this->logInfo("IS running");
		}
		$this->logInfo("status is " . $this->getStatus());
		return $res;
	}
	/**
	 * @return null|string
	 */
	public function getResult(): string{
		if($this->actions === null){
			$fromAPI = self::getBuildFromAPI($this->getJobName(), $this->getId());
			foreach($fromAPI as $key => $value){
				$this->$key = $value;
			}
		}
		if($this->building){
			return self::RUNNING;
		}
		$result = $this->result;
		$description = $this->actions[0]->causes[0]->shortDescription ?? null;
		if(!$result && $description && stripos($description, "Started") !== false){
			return self::RUNNING;
		}
		return $result;
	}
	/**
	 * @param string|null $job
	 * @param int|null $buildId
	 * @param string|null $tree
	 * @returnBuild
	 */
	public static function getBuildFromAPI(string $job = null, int $buildId = null, string $tree = null): ?Build{
		//if(!$tree){$tree = 'actions[parameters,parameters[name,value]],result,duration,timestamp,number,url,estimatedDuration,builtOn';}
		if(!$job){
			$job = JenkinsJob::getJobNameEnv();
		}
		if(!$buildId){
			$buildId = \App\Utils\Env::get(JenkinsAPI::BUILD_ID);
		}
		if($tree !== null){
			$tree = sprintf('?tree=%s', $tree);
		}
		$url = sprintf('%s/job/%s/%d/api/json%s', JenkinsAPI::$baseUrl, $job, $buildId, $tree);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl,
			sprintf('Error during getting information for build %s#%d on %s from url ', $job, $buildId,
				JenkinsAPI::$baseUrl), $url);
		$infos = json_decode($ret);
		JenkinsAPI::generateStaticModelFromResponse("Build", $infos);
		if(!$infos instanceof stdClass){
			return null;
		}
		$build = new Build($infos, JenkinsAPI::getInstance());
		$job = JenkinsJob::getJob($build->getJobName());
		$actions = $build->actions;
		$action = collect($actions)->filter(function($action){
			return isset($action->buildsByBranchName);
		})->first();
		$job->buildsByBranchName = $action->buildsByBranchName;
		return $build;
	}
	public function getJobName(): string{
		if($this->jobName){
			return $this->jobName;
		}
		if($this->fullDisplayName){
			return $this->jobName = QMStr::before(" #", $this->fullDisplayName);
		} else{
			return $this->jobName = QMStr::between($this->url, '/job/', '/');
		}
	}
	public function getId(){
		if($this->id){
			return $this->id;
		}
		$arr = explode('/', $this->url);
		$id = (int)$arr[count($arr) - 2];
		return $this->id = $id;
	}
	public function getStatus(): string{
		return $this->getResult();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return UrlHelper::addParams($this->url, $params);
	}
	/**
	 * @return int
	 */
	public function getTimestamp(){
		//division par 1000 => pas de millisecondes
		return $this->timestamp / 1000;
	}
	public function getLogMetaDataString(): string{
		return $this->__toString();
	}
	public function __toString(){
		return $this->getJobName() . " #" . $this->getId();
	}
	public function getTestResultAction(){
	}
	/**
	 * @return JenkinsAPI
	 */
	public function getJenkins(): JenkinsAPI{
		return $this->jenkins;
	}
	/**
	 * @param JenkinsAPI $jenkins
	 * @return void
	 */
	public function setJenkins(JenkinsAPI $jenkins){
		$this->jenkins = $jenkins;
	}
	/**
	 * @return mixed
	 */
	public function getBuiltOn(): string{
		return $this->builtOn;
	}
	public function cancel(){
		self::cancelBuild($this->getJobName(), $this->getNumber());
	}
	/**
	 * @param string $jobName
	 * @param int $buildNumber
	 * @return bool|string
	 */
	public static function cancelBuild(string $jobName, int $buildNumber){
		$url = JenkinsAPI::$baseUrl . "/job/$jobName/$buildNumber/stop";
		$curl = Jenkins::post($url);
		JenkinsAPI::validateCurl($curl, "Error during stopping $jobName build $buildNumber", $url);
		return $result;
	}
	/**
	 * @return int
	 */
	public function getNumber(): int{
		return $this->number;
	}
	/**
	 * @return ChangedFile[]
	 */
	public function getChangedFiles(): array{
		return $this->getChangeSet()->getChangedFiles();
	}
}
