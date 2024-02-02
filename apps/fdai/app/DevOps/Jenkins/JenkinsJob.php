<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins;
use App\DevOps\Jenkins\Build\JenkinsBuildAction;
use App\DevOps\Jenkins\Job\HealthReport;
use App\Logging\QMLog;
use App\Types\QMStr;
use DOMDocument;
use InvalidArgumentException;
use RuntimeException;
use stdClass;
class JenkinsJob {
	public const JOB_NAME = 'JOB_NAME';
	public const JOB_URL = 'JOB_URL';
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var JenkinsBuildAction[]
	 */
	public $actions;
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var string
	 */
	public $displayName;
	/**
	 * @var NULL
	 */
	public $displayNameOrNull;
	/**
	 * @var string
	 */
	public $fullDisplayName;
	/**
	 * @var string
	 */
	public $fullName;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var boolean
	 */
	public $buildable;
	/**
	 * @var Build[]
	 */
	public $builds;
	/**
	 * @var string
	 */
	public $color;
	/**
	 * @var Build
	 */
	public $firstBuild;
	/**
	 * @var HealthReport[]
	 */
	public $healthReport;
	/**
	 * @var boolean
	 */
	public $inQueue;
	/**
	 * @var boolean
	 */
	public $keepDependencies;
	/**
	 * @var Build
	 */
	public $lastBuild;
	/**
	 * @var Build
	 */
	public $lastCompletedBuild;
	/**
	 * @var Build
	 */
	public $lastFailedBuild;
	/**
	 * @var Build
	 */
	public $lastStableBuild;
	/**
	 * @var Build
	 */
	public $lastSuccessfulBuild;
	/**
	 * @var NULL
	 */
	public $lastUnstableBuild;
	/**
	 * @var Build
	 */
	public $lastUnsuccessfulBuild;
	/**
	 * @var integer
	 */
	public $nextBuildNumber;
	/**
	 * @var object[]
	 */
	public $property;
	/**
	 * @var NULL
	 */
	public $queueItem;
	/**
	 * @var boolean
	 */
	public $concurrentBuild;
	/**
	 * @var boolean
	 */
	public $disabled;
	/**
	 * @var NULL[]
	 */
	public $downstreamProjects;
	/**
	 * @var string
	 */
	public $labelExpression;
	/**
	 * @var object
	 */
	public $scm;
	/**
	 * @var NULL[]
	 */
	public $upstreamProjects;
	public $buildsByBranchName;
	/**
	 * @var JenkinsAPI
	 */
	protected $jenkins;
	/**
	 * @param stdClass $obj
	 * @param JenkinsAPI|null $jenkins
	 */
	public function __construct(stdClass $obj, JenkinsAPI $jenkins = null){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->actions)){
			foreach($obj->actions as $i => $item){
				$this->actions[$i] = new JenkinsBuildAction($item);
			}
		}
		if(isset($obj->builds)){
			foreach($obj->builds as $i => $item){
				$this->builds[$i] = new Build($item);
			}
		}
		if(isset($obj->healthReport)){
			foreach($obj->healthReport as $i => $item){
				$this->healthReport[$i] = new HealthReport($item);
			}
		}
		if($jenkins){
			$this->setJenkins($jenkins);
		}
	}
	public static function getJobTitle(): string{
		$name = self::getCurrentJobName();
		$title = str_replace("-", " ", $name);
		if(stripos($title, 'Job') == false){
			$title .= " Job";
		}
		return QMStr::titleCaseSlow($title);
	}
	public static function getCurrentJobName(): ?string{
		return self::getJobNameEnv();
	}
	/**
	 * @return string
	 */
	public static function getJobNameEnv(): ?string{
		return \App\Utils\Env::get(self::JOB_NAME);
	}
	public static function getPHPUnitJobs(): array{
		$jobs = self::getJobsWithNameLike('-phpunit');
		return $jobs;
	}
	/**
	 * @param string $pattern
	 * @return JenkinsJob[]
	 */
	public static function getJobsWithNameLike(string $pattern): array{
		return collect(self::getJobs())->filter(function($item) use ($pattern){
			return stripos($item->name, $pattern) !== false;
		})->all();
	}
	/**
	 * @returnJenkinsJob[]
	 */
	public static function getJobs(): array{
		if(JenkinsAPI::$jobs){
			return JenkinsAPI::$jobs;
		}
		$names = self::getJobNames();
		foreach($names as $name){
			JenkinsAPI::$jobs[$name] = self::getCompleteJobInfoFromAPI($name);
		}
		return JenkinsAPI::$jobs;
	}
	/**
	 * @return string[]
	 */
	public static function getJobNames(): array{
		if(JenkinsAPI::$jobs){
			return JenkinsAPI::$jobs;
		}
		$response = JenkinsAPI::getGeneralData();
		foreach($response->jobs as $job){
			JenkinsAPI::$jobs[$job->name] = new JenkinsJob($job, JenkinsAPI::getInstance());
		}
		return array_keys(JenkinsAPI::$jobs);
	}
	/**
	 * @param string $jobName
	 * @return bool|JenkinsJob
	 * @throws RuntimeException
	 */
	public static function getCompleteJobInfoFromAPI(string $jobName){
		$url = sprintf('%s/job/%s/api/json', JenkinsAPI::$baseUrl, $jobName);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		$response_info = curl_getinfo($curl);
		if(200 != $response_info['http_code']){
			return false;
		}
		JenkinsAPI::validateCurl($curl,
			sprintf('Error during getting information for job %s on %s', $jobName, JenkinsAPI::$baseUrl), $url);
		$infos = json_decode($ret);
		JenkinsAPI::generateStaticModelFromResponse("Job", $infos);
		if(!$infos instanceof stdClass){
			throw new RuntimeException('Error during json_decode');
		}
		return new JenkinsJob($infos, JenkinsAPI::getInstance());
	}
	/**
	 * @param string $jobname
	 * @param DomDocument $document
	 * @deprecated use setJobConfig instead
	 */
	public static function setConfigFromDomDocument(string $jobname, DomDocument $document){
		JenkinsJob::setJobConfig($jobname, $document->saveXML());
	}
	/**
	 * @param string $jobname
	 * @param        $configuration
	 * @internal param string $document
	 */
	public static function setJobConfig(string $jobname, $configuration){
		$url = sprintf('%s/job/%s/config.xml', JenkinsAPI::$baseUrl, $jobname);
		$curl = JenkinsAPI::postXmlConfig($configuration, $url);
		curl_exec($curl);
		JenkinsAPI::validateCurl($curl, sprintf('Error during setting configuration for job %s', $jobname), $url);
	}
	/**
	 * @param JenkinsJob $job
	 */
	public static function addJob(JenkinsJob $job){
		JenkinsAPI::$jobs[$job->getNameAttribute()] = $job;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @param string $jobName
	 * @return void
	 */
	public static function deleteJob(string $jobName){
		$url = sprintf('%s/job/%s/doDelete', JenkinsAPI::$baseUrl, $jobName);
		$curl = Jenkins::post($url);
		JenkinsAPI::validateCurl($curl, sprintf('Error deleting job %s on %s', $jobName, JenkinsAPI::$baseUrl), $url);
	}
	/**
	 * @param string $jobname
	 * @param string $xmlConfiguration
	 * @return bool|string
	 * @throws InvalidArgumentException
	 */
	public static function createJob(string $jobname, string $xmlConfiguration){
		$url = sprintf('%s/createItem?name=%s', JenkinsAPI::$baseUrl, $jobname);
		$curl = JenkinsAPI::postXmlConfig($xmlConfiguration, $url);
		$response = curl_exec($curl);
		if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200){
			throw new InvalidArgumentException(sprintf('Job %s already exists', $jobname));
		}
		if(curl_errno($curl)){
			throw new RuntimeException(sprintf('Error creating job %s', $jobname));
		}
		return $response;
	}
	public static function getJob(string $name): JenkinsJob{
		$jobsByName = self::getJobs();
		return $jobsByName[$name];
	}
	/**
	 * @param string|null $jobName
	 * @return string
	 */
	public static function getJobUrl(string $jobName = null): string{
		if(!$jobName){
			$jobName = JenkinsJob::getCurrentJobName();
		}
		return sprintf('%s/job/%s', JenkinsAPI::$baseUrl, $jobName);
	}
	/**
	 * @param       $jobName
	 * @param array $parameters
	 * @return JenkinsJob
	 * @internal param array $extraParameters
	 */
	public static function launchJob($jobName, array $parameters = []): JenkinsJob{
		if(0 === count($parameters)){
			$url = sprintf('%s/job/%s/build', JenkinsAPI::$baseUrl, $jobName);
		} else{
			$url = sprintf('%s/job/%s/buildWithParameters', JenkinsAPI::$baseUrl, $jobName);
		}
		$data = JenkinsAPI::post($parameters, $url);
		return new JenkinsJob($data);
	}
	public static function getJobNamesLike(string $pattern): array{
		return collect(JenkinsJob::getJobNames())->filter(function($item) use ($pattern){
			return stripos($item, $pattern) !== false;
		})->all();
	}
	public function getBuildsForBranch(string $branchName){
		if(stripos($branchName, 'origin') !== 0){
			$branchName = "origin/$branchName";
		}
		$buildsByBranchName = $this->getBuildsByBranchName();
		$forBranch = $buildsByBranchName->$branchName;
		return $forBranch;
	}
	public function getBuildsByBranchName(){
		if(!$this->buildsByBranchName){
			$this->getBuilds();
		}
		return $this->buildsByBranchName;
	}
	/**
	 * @return Build[]
	 */
	public function getBuilds(): array{
		if($this->builds !== null){
			return $this->builds;
		}
		$info = self::getCompleteJobInfoFromAPI($this->name);
		foreach($info as $key => $value){
			$this->$key = $value;
		}
		$builds = $this->builds;
		if(!is_array($builds)){
			le("Could not get builds!getCompleteJobInfoFromAPI returned: " . \App\Logging\QMLog::print_r($info, true));
		}
		foreach($this->builds as $i => $build){
			$this->builds[$i] = new Build($build, $this->jenkins);
		}
		return $this->builds;
	}
	/**
	 * @param int $buildId
	 * @return Build
	 * @throws RuntimeException
	 */
	public function getJenkinsBuild(int $buildId): Build{
		return Build::getBuildFromAPI($this->getNameAttribute(), $buildId);
	}
	/**
	 * @return JenkinsAPI
	 */
	public function getJenkins(): JenkinsAPI{
		return $this->jenkins;
	}
	/**
	 * @param JenkinsAPI $jenkins
	 * @return JenkinsJob
	 */
	public function setJenkins(JenkinsAPI $jenkins): JenkinsJob{
		$this->jenkins = $jenkins;
		self::addJob($this);
		return $this;
	}
	/**
	 * @return array
	 */
	public function getParametersDefinition(): array{
		$parameters = [];
		foreach($this->actions as $action){
			if(!property_exists($action, 'parameterDefinitions')){
				continue;
			}
			foreach($action->parameterDefinitions as $parameterDefinition){
				$default = property_exists($parameterDefinition, 'defaultParameterValue') &&
				isset($parameterDefinition->defaultParameterValue->value) ? $parameterDefinition->defaultParameterValue->value : null;
				$description =
					property_exists($parameterDefinition, 'description') ? $parameterDefinition->description : null;
				$choices = property_exists($parameterDefinition, 'choices') ? $parameterDefinition->choices : null;
				$parameters[$parameterDefinition->name] = [
					'default' => $default,
					'choices' => $choices,
					'description' => $description,
				];
			}
		}
		return $parameters;
	}
	/**
	 * @return string
	 */
	public function getColor(): string{
		return $this->color;
	}
	/**
	 * @return DOMDocument
	 */
	public function retrieveXmlConfigAsDomDocument(): DOMDocument{
		$document = new DOMDocument;
		$document->loadXML($this->retrieveXmlConfigAsString());
		return $document;
	}
	/**
	 * @return string
	 * @throws RuntimeException
	 */
	public function retrieveXmlConfigAsString(): string{
		return self::retrieveXmlConfigAsStringByJobName($this->getNameAttribute());
	}
	/**
	 * @param string $jobname
	 * @return string
	 * @throws RuntimeException
	 */
	public static function retrieveXmlConfigAsStringByJobName(string $jobname): string{
		return JenkinsJob::getJobConfig($jobname);
	}
	/**
	 * @param string $jobname
	 * @return string
	 */
	public static function getJobConfig(string $jobname): string{
		$url = sprintf('%s/job/%s/config.xml', JenkinsAPI::$baseUrl, $jobname);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		JenkinsAPI::validateCurl($curl, sprintf('Error during getting configuration for job %s', $jobname), $url);
		return $ret;
	}
	/**
	 * @return Build|null
	 */
	public function getLastSuccessfulBuild(): ?Build{
		if(null === $this->lastSuccessfulBuild){
			return null;
		}
		return Build::getBuildFromAPI($this->getNameAttribute(), $this->lastSuccessfulBuild->number);
	}
	/**
	 * @return Build|null
	 */
	public function getLastBuild(): ?Build{
		if(null === $this->lastBuild){
			return null;
		}
		return Build::getBuildFromAPI($this->getNameAttribute(), $this->lastBuild->number);
	}
	/**
	 * @return Build[]
	 */
	public function abortBuilds(): array{
		$builds = $this->getBuilds();
		$aborted = [];
		foreach($builds as $build){
			if(!$build->isRunning()){
				QMLog::infoWithoutContext("$build not running so no need to abort");
				break;
			}
			$build->abort();
			$aborted[] = $build;
		}
		return $aborted;
	}
	public function __toString(){
		return $this->getNameAttribute();
	}
}
