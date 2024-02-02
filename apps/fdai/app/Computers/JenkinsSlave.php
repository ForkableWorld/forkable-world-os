<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Computers;
use App\Buttons\Admin\AAPanelButton;
use App\DevOps\DigitalOcean\QMDroplet;
use App\DevOps\Jenkins\Build;
use App\DevOps\Jenkins\Executor;
use App\DevOps\Jenkins\Jenkins;
use App\DevOps\Jenkins\JenkinsAPI;
use App\DevOps\QMServices;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NotFoundException;
use App\Files\Bash\BashScriptFile;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use App\Repos\QMAPIRepo;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\DynamicCommand;
use App\ShellCommands\JenkinsCommands\JenkinsReload;
use App\ShellCommands\OfflineException;
use App\ShellCommands\RsyncFromLocalToRemoteCommand;
use App\Traits\HasClassName;
use App\Traits\HasMemory;
use App\Traits\HasName;
use App\Traits\HasXmlConfig;
use App\Traits\LoggerTrait;
use App\Types\QMConstantModel;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\UrlHelper;
use Closure;
use Illuminate\Support\Collection;
use RuntimeException;
use Spatie\Ssh\Ssh;
use stdClass;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Tests\UnitTests\Computers\CypressComputer;
use const CURLOPT_RETURNTRANSFER;
abstract class JenkinsSlave extends Ssh {
	use LoggerTrait, HasXmlConfig, HasName, HasMemory;
	use HasClassName;
	const BASE_NODES_PATH = Jenkins::JENKINS_PATH . "/nodes";
	const LABEL_HILLSBORO = "hillsboro";
	const HOME_QM_API = "~/qm-api";
	const LABEL_1GB = '1GB';
	const LABEL_20_GB_HD = '20-GB-HD';
	const LABEL_2GB = '2GB';
	const LABEL_40_GB_HD = '40-GB-HD';
	const LABEL_60_GB_HD = '60-GB-HD';
	const LABEL_BITNAMI = 'bitnami';
	const LABEL_CPU = 'cpu';
	const LABEL_CYPRESS = "cypress";
	const LABEL_DB = 'db';
	const LABEL_DOCKER = 'docker';
	const LABEL_ENVY = 'envy';
	const LABEL_ESSENTIALS = 'essentials';
	const LABEL_HEROKU = 'heroku';
	const LABEL_PLATFORM_HILLSBORO = 'PLATFORM=hillsboro';
	const LABEL_HOMESTEAD = 'homestead';
	const LABEL_IONIC = 'ionic';
	const LABEL_LIGHTSAIL = 'PLATFORM=lightsail';
	const LABEL_MASTER = 'master';
	const LABEL_MEMORY = 'memory';
	const LABEL_MONGO = 'mongo';
	const LABEL_NODE = 'node';
	const LABEL_NODEJS = 'nodejs';
	const LABEL_OPTIPLEX = 'optiplex';
	const LABEL_OSX = 'osx';
	const LABEL_PHPUNIT = "phpunit";
	const LABEL_PHPUNIT_JOBS = "phpunit-jobs";
	const LABEL_PHPUNIT_WIKI = "wiki";
	const LABEL_PRODUCTION = 'production';
	const LABEL_WEB_PRODUCTION = 'APP_ENV=production';
	const LABEL_QM_WP = 'qm-wp';
	const LABEL_SLOW = 'slow';
	const LABEL_STAGING_PHPUNIT = 'staging-phpunit';
	const LABEL_WEB_STAGING = 'APP_ENV=staging';
	const LABEL_TIDEWAYS = 'tideways';
	const LABEL_UBUNTU = 'ubuntu';
	const LABEL_UBUNTU_18_04 = 'ubuntu_18_04';
	const LABEL_US_EAST_1A = 'us-east-1a';
	const LABEL_WEB = 'web';
	const LABEL_WIKI = 'wiki';
	const LABEL_WINDOWS = 'windows';
	const LABEL_WORDPRESS = "wordpress";
	const NAME_MASTER = "master";
	const NODE_TEMPLATE_NAME = 'do-homestead-docker-002';
	public const PORT = 22;
	const USER_ROOT = "root";
	const USER_WWW_DATA = 'www-data';
	const USER_WWW = "www";
	const DEFAULT_WEB_USER = self::USER_WWW;
	const USER_JENKINS = "jenkins";
	const USER_UBUNTU = "ubuntu";
	public const SSH_PRIVATE_KEY_HOME = '~/.ssh/id_rsa';
	public const SSH_PRIVATE_KEY_WINDOWS = 'C:\code\windows-settings\id_rsa.ppk';
	public const SSH_PUBLIC_KEY_HOME = '~/.ssh/id_rsa.pub';
	protected $commands;
	protected $ip;
	protected $label;
	protected $lastCommandProcess;
	protected $launcher;
	protected $outputLines;
	protected $jenkinsResponse;
	protected $timeout = null;
	protected $url;
	protected ?int $port = null;
	public $_class;
	public $absoluteRemotePath;
	public $actions;
	public $assignedLabels;
	public $description;
	public $displayName;
	public $executors;
	public string $host;
	public $icon;
	public $iconClassName;
	public $idle;
	public $jnlpAgent;
	public $labels;
	public $launchSupported;
	public $loadStatistics;
	public $manualLaunchAllowed;
	public $monitorData;
	public $numExecutors;
	public $offline;
	public $offlineCause;
	public $offlineCauseReason;
	public $oneOffExecutors;
	public $password;
	public $temporarilyOffline;
	private ?string $cwd = null;
	private $nonRootUser;
	private ?bool $rootLoginEnabled = null;
	/**
	 * @param object $response
	 */
	public function __construct($response = null){
		$this->configureCommandOutputHandler();
		$this->disableStrictHostKeyChecking();
		if(!AppMode::isApiRequest()){
			$this->usePrivateKey(ThisComputer::getPrivateKey());
		}
		if(!$response){return;}
		if($response instanceof LightsailInstanceResponse){
			$this->populateFromLightsailResponse($response);
		} else{
			$this->populateFromJenkinsResponse($response);
		}
		// $this->populateFromXmlFile(); This is impossible on slaves so just stay consistent
		parent::__construct($this->getUser(), $this->getIP(), $this->getPort());
		$this->addToMemory();
	}
	public static function getRunnerIP(string $runnerName = null): ?string{
		if(!$runnerName){
			$runnerName = self::getRunnerName();
		}
		if(!$runnerName){
			return null;
		}
		$ips = AAPanelButton::RUNNER_IPS;
		$runnerName = str_replace('-vagrant-api-2', '', $runnerName);
		$runnerName = str_replace('-vagrant-api', '', $runnerName);
		$runnerName = str_replace('-api', '', $runnerName);
		$runnerName = str_replace('-ionic', '', $runnerName);
		$runnerName = str_replace('-vagrant', '', $runnerName);
		foreach($ips as $name => $ip){
			if(str_starts_with($name, $runnerName)){
				/** @noinspection HttpUrlsUsage */
				$url = $ip;
			}
		}
		if(empty($url)){
			ConsoleLog::info("No IP found for $runnerName. So just appending .quantimo.do to the end of the runner name.");
			$url = "https://$runnerName.quantimo.do";
		}
		return $url;
	}
	/**
	 * @return string
	 */
	public static function getRunnerName(): ?string {
		return Env::get('RUNNER');
	}
	public static function getAAPanelUrl(string $ip = null){
		if(!$ip){$ip = static::ip();}
		/** @noinspection HttpUrlsUsage */
		return "http://$ip:7777";
	}
	/**
	 * Get an attribute from the model.
	 * @param string|string[] $key
	 * @return mixed
	 * @noinspection DuplicatedCode
	 */
	public function getAttribute($key){
		if(is_array($key)){
			foreach($key as $one){
				$result = $this->getAttribute($one);
				if(isset($result)){return $result;}
			}
			return null;
		}
		$camel = QMStr::camelize($key);
		if(!isset($this->$camel)){return null;}
		return $this->$camel;
	}
	private function configureCommandOutputHandler(): void{
		$this->onOutput(function($type, $line){
			// Don't throw exceptions in here, or you won't get full output
			if(!empty($line)){
				$line = DynamicCommand::stripManInMiddleWarning($line);
				$this->outputLines[] = $out = "$type: $line";
				ConsoleLog::info($out);
			}
		});
	}
	/**
	 * @param LightsailInstanceResponse $i
	 * @return JenkinsSlave|OnLightsail
	 */
	public static function newFromLightsail(LightsailInstanceResponse $i): JenkinsSlave{
		/** @var OnLightsail $computer */
		$computer = static::instantiate($i);
		return $computer;
	}
	/**
	 * @param array|string $command
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 * @noinspection PhpDocRedundantThrowsInspection
	 */
	public function execute($command): Process {
		QMLog::infoWithoutContext("$this CMD:\n\t$command", true);
		$this->configureProcess(function (Process $process) {
			$process->setTimeout($this->timeout);
			$this->setLastCommandProcess($process);
		});
		$proc = parent::execute($command);
		return $proc;
	}
	/**
	 * @param object|static $computer
	 * @return JenkinsSlave
	 */
	private static function instantiate($computer): ?self{
		$labels = [];
		if($computer instanceof LightsailInstanceResponse){
			$labels = $computer->getJenkinsLabels();
			$n = $computer->name;
		} else {
			$n = $computer->displayName;
			foreach($computer->assignedLabels as $obj){
				$labels[] = $obj->name;
			}
		}
		if(in_array(self::NAME_MASTER, $labels)){
			return new MasterComputer($computer);
		} elseif(in_array(self::LABEL_PHPUNIT, $labels) &&
		         in_array(self::LABEL_LIGHTSAIL, $labels)){
			return new PhpUnitComputer($computer);
		} elseif(in_array(self::LABEL_WORDPRESS, $labels)){
			return new WordPressComputer($computer);
		} elseif($n === RedisComputer::NAME){
			return new RedisComputer($computer);
		} elseif(in_array(self::LABEL_CYPRESS, $labels)){
			return new CypressComputer($computer);
		}else{
			QMLog::info("$n: please create Computer class for $n\n\t".
			            "\n\tor add appropriate label at https://lightsail.aws.amazon.com/");
			            //Existing labels: \n\t\t-" .
			            //implode(",\n\t\t-", $labels));
			try {
				if(in_array("PLATFORM=lightsail", $labels)){
					return new UncategorizedLightsailComputer($computer);
				} else {
					return new UncategorizedComputer($computer);
				}
			} catch (\Throwable $e){
				ExceptionHandler::dumpOrNotify($e);
				return null;
			}
		}
	}
	/**
	 * @return string
	 */
	public static function instancePrefix():string{return "";}
	/**
	 * @return object
	 */
	public function getJenkinsResponse(): object{
		return $this->jenkinsResponse;
	}
	/**
	 * @param string $cwd
	 * @return JenkinsSlave
	 */
	public function setCwd(string $cwd): self {
		$this->cwd = $cwd;
		return $this;
	}
	public function getTagValue(string $name): ?string{
		$labels = $this->getLabels();
		foreach($labels as $label){
			if(str_starts_with($label, $name."=")){
				return QMStr::after($name . '=', $label);
			}
		}
		return null;
	}
	/**
	 * @return bool
	 */
	public function isMaster(): bool{
		return $this->getNameAttribute() === self::NAME_MASTER;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		if(!$this->displayName){
			le("no displayName on ".\App\Logging\QMLog::print_r($this, true));
		}
		return $this->displayName;
	}
	/**
	 * @throws OfflineException
	 * @throws CommandFailureException
	 */
	public static function updateHostnames(): void {
		$all = static::all();
		foreach($all as $computer){
			/** @var static $computer */
			$computer->updateHostname();
		}
	}
	public static function rebootEternal(): void {
		while(true){
			$offline = static::getSshOffline();
			foreach($offline as $c){
				try {
					$c->reboot(__FUNCTION__);
				} catch (CommandFailureException | OfflineException $e) {
					if(stripos($e->getMessage(), "UNPROTECTED PRIVATE KEY FILE")){
						le($e);
					}
					$c->logError(__METHOD__.": ".$e->getMessage());
				}
			}
			$count = count($offline);
			QMLog::info("Rebooted $count offline servers");
			$min = 5;
			QMLog::sleep($min*60, "Waiting $min minutes to check if we need to reboot again");
		}
	}
	/**
	 * @return array
	 */
	public static function rebootAllIfNecessary(): array{
		$computers = static::all();
		$errors = [];
		/** @var JenkinsSlave $computer */
		foreach($computers as $computer){
			if($err = $computer->rebootIfNecessary()){
				$errors[] = $err;
			}
		}
		return $errors;
	}
	/**
	 * @return Collection|static[]
	 */
	public static function web(): Collection{
		return self::withTag(self::LABEL_WEB);
	}
	/**
	 * @param string $label
	 * @return Collection
	 */
	public static function withTag(string $label): Collection{
		return self::all()->filter(function($c) use ($label){
			/** @var self $c */
			return $c->hasLabel($label);
		});
	}
	/**
	 * @return static[]|Collection
	 */
	public static function all(): Collection{
		$all = self::getJenkinsSlaves();
		if($prefix = static::instancePrefix()){
			return $all->filter(function($one) use ($prefix){
				/** @var JenkinsSlave $one */
				return stripos($one->getNameAttribute(), $prefix) === 0;
			});
		}
		return $all;
	}
	/**
	 * @return JenkinsSlave[]|Collection
	 */
	public static function getJenkinsSlaves(): Collection{
		$infos = json_decode(JenkinsAPI::curl('/computer/api/json'));
		if(!$infos instanceof stdClass){
			le('Error during json_decode');
		}
		$computers = [];
		foreach($infos->computer as $computer){
			if($instance = self::instantiate($computer)){
				$computers[$instance->getNameAttribute()] = $instance;
			}
		}
		return collect($computers);
	}
	/**
	 * @param $infos
	 */
	private static function outputLabelConstants($infos): void{
		$allLabels = [];
		foreach($infos->computer as $computer){
			foreach($computer->assignedLabels as $obj){
				$allLabels[] = $obj->name;
			}
		}
		$allLabels = array_unique($allLabels);
		foreach($allLabels as $label){
			QMConstantModel::log("LABEL_" . strtoupper($label), $label);
		}
	}
	/**
	 * @param string $label
	 * @return bool
	 */
	public function hasLabel(string $label): bool{
		return $this->labelsContain($label);
	}
	/**
	 * @param string $label
	 * @return bool
	 */
	public function labelsContain(string $label): bool{
		return in_array($label, $this->getLabels());
	}
	/**
	 * @param string $needle
	 * @return bool
	 */
	public function hasLabelLike(string $needle): bool{
		foreach($this->getLabels() as $label){
			if(stripos($label, $needle) !== false){
				return true;
			}
		}
		return false;
	}
	private function isHillsboro(): bool{
		return $this->hasLabelLike(self::LABEL_HILLSBORO);
	}
	/**
	 * @return array
	 */
	public function getLabels(): array{
		if(!$this->labels){
			$this->labels = explode(" ", $this->label);
		}
		return $this->labels;
	}
	/**
	 * @return string|null
	 */
	public function rebootIfNecessary(): ?string{
		if($reason = $this->needToReboot()){
			try {
				$this->reboot($reason);
			} catch (CommandFailureException | OfflineException $e) {
				le($e);
			}
		}
		return $reason;
	}
	/**
	 * @return string|null
	 */
	abstract public function needToReboot(): ?string;
	/**
	 * Sets the process timeout (max. runtime) in seconds.
	 *
	 * To disable the timeout, set this value to null.
	 *
	 * @param int|null $timeout The timeout in seconds
	 *
	 * @return $this
	 *
	 * @throws InvalidArgumentException if the timeout is negative
	 */
	public function setTimeout(?int $timeout): JenkinsSlave{
		$this->timeout = $timeout;
		return $this;
	}
	/**
	 * @param string $path
	 * @param string $perms
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function setPermissions(string $path, string $perms){
		$this->sudo("chmod $perms $path");
	}
	/**
	 * @param string $user
	 * @return $this
	 */
	public function setUser(string $user): JenkinsSlave{
		if($this->user !== self::USER_ROOT){
			$this->setNonRootUser($this->user);
		}
		$this->user = $user;
		return $this;
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function validateRootLogin(): void{
		$this->setRootUser();
		$res = $this->execute("echo \$USER");
		$o = $res->getOutput();
		if(!str_contains($o, self::USER_ROOT) || str_contains($o, "Please login as the user")){
			$this->rootLoginEnabled = false;
			throw new CommandFailureException($this, "expect output root but got $o");
		} else {
			$this->logInfo("root login available");
			$this->rootLoginEnabled = true;
		}
		$this->useNonRootUser();
	}
	/**
	 * @return JenkinsSlave
	 */
	private function useNonRootUser(): JenkinsSlave{
		$this->setUser($this->getNonRootUser());
		return $this;
	}
	/**
	 * @return JenkinsSlave
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function enableRootLogin(): JenkinsSlave{
		if($this->rootLoginEnabled()){
			$this->logInfo("root login already enabled");
			return $this;
		}
		$this->useNonRootUser();
		$keyPath = ".ssh/authorized_keys";
		$rootKey = "/root/$keyPath";
		$user = $this->getNonRootUser();
		$myKey = "/home/$user/$keyPath";
		$this->sudo("cp $rootKey $rootKey.bak.".time());
		$this->sudo("cp $myKey $rootKey");
		$this->setPermissions($rootKey, "600");
		if($this->sshOffline()){le("could not login as root!");}
		$this->validateRootLogin();
		return $this;
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function fixWorkspacePermissions(){
		$this->execInQMAPI("sudo chmod -R 755 configs");
	}
	/**
	 * @return bool
	 * @throws OfflineException
	 */
	public function rootLoginEnabled():bool{
		if($this->rootLoginEnabled !== null){
			return $this->rootLoginEnabled;
		}
		try {
			$this->validateRootLogin();
			return $this->rootLoginEnabled = true;
		} catch (CommandFailureException $e){
		    QMLog::info(__METHOD__.": ".$e->getMessage());
			return $this->rootLoginEnabled = false;
		}
	}
	/**
	 * @return string
	 */
	public function getNonRootUser(): string {
		$u = $this->nonRootUser ?? $this->user;
		if($u === self::USER_ROOT){le("no non-root user!");}
		return $u;
	}
	/**
	 * @param mixed $nonRootUser
	 * @return JenkinsSlave
	 */
	public function setNonRootUser(string $nonRootUser): JenkinsSlave{
		if($nonRootUser === self::USER_ROOT){
			le("setting nonRootUser to ".self::USER_ROOT);
		}
		$this->nonRootUser = $nonRootUser;
		return $this;
	}
	/**
	 * @return array|int[]
	 */
	public function getPublicPorts(): array{
		if($this->isWeb()){
			return [443, 80];
		} else {
			return [];
		}
	}
	/**
	 * @param string $destDir
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function mkdir(string $destDir): Process{
		FileHelper::assertAbsPath($destDir);
		return $this->execute("mkdir -p $destDir");
	}
	/**
	 * @return string
	 */
	protected function getRegionName(): string{
		return LightsailInstanceResponse::REGION_NAME_US_EAST_1;
	}
	/**
	 * @return object|int
	 */
	public function relaunchJenkinsNode(){
		return JenkinsAPI::post(['Submit' => 'Relaunch agent',], "computer/$this->displayName/launchSlaveAgent");
	}
	/**
	 * @param string $computer
	 * @return array
	 * @throws RuntimeException
	 */
	public static function getExecutors(string $computer = '(master)'): array{
		$response = JenkinsAPI::getGeneralData();
		$executors = [];
		for($i = 0; $i < $response->numExecutors; $i++){
			$url = sprintf('%s/computer/%s/executors/%s/api/json', JenkinsAPI::$baseUrl, $computer, $i);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$ret = curl_exec($curl);
			JenkinsAPI::validateCurl($curl, sprintf('Error during getting information for executors[%s@%s] on %s', $i,
			                                        $computer, JenkinsAPI::$baseUrl), $url);
			$infos = json_decode($ret);
			JenkinsAPI::generateStaticModelFromResponse("Executors", $infos);
			if(!$infos instanceof stdClass){
				throw new RuntimeException('Error during json_decode');
			}
			$executors[] = new Executor($infos, $computer, JenkinsAPI::getInstance());
		}
		return $executors;
	}
	/**
	 * @return string
	 */
	public static function getCurrentNodeName(): string{
		return Env::get(JenkinsAPI::NODE_NAME) ?? "NODE_NAME_NOT_FOUND";
	}
	/**
	 * @param string|null $prefix
	 * @return JenkinsSlave[]
	 */
	public static function createForAllDroplets(string $prefix = null): array{
		if($prefix){
			$droplets = QMDroplet::getWhereStartsWith($prefix);
		} else{
			$droplets = QMDroplet::getAll();
		}
		$computers = [];
		foreach($droplets as $droplet){
			$droplet->getOrCreateJenkinsNode();
		}
		(new JenkinsReload)->execute();
		return $computers;
	}
	/**
	 * @param string $reason
	 * @return void
	 * @throws RuntimeException
	 */
	public function deleteJenkinsNode(string $reason){
		$this->logWarning("Deleting $this because $reason");
		$url = sprintf('%s/computer/%s/doDelete', JenkinsAPI::$baseUrl, $this->getNameAttribute());
		$curl = Jenkins::post($url);
		JenkinsAPI::validateCurl($curl, sprintf('Error deleting %s', $this->getNameAttribute()), $url);
	}
	/**
	 * @param string $computerName
	 * @return static
	 */
	public static function find(string $computerName): ?self{
		if(empty($computerName)){le("please provide computerName to ".__METHOD__);}
		$url = sprintf('%s/computer/%s/api/json', JenkinsAPI::$baseUrl, $computerName);
		QMLog::info($url);
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($curl);
		try {
			JenkinsAPI::validateCurl($curl, sprintf('Error during getting information for computer %s on %s', $computerName,
				JenkinsAPI::$baseUrl), $url);
		} catch (NotFoundException $e){
		    QMLog::error(__METHOD__.": ".$e->getMessage());
		    return null;
		}
		$infos = json_decode($ret);
		JenkinsAPI::generateStaticModelFromResponse("Computer", $infos);
		if(!$infos instanceof stdClass){
			return null;
		}
		$i = static::instantiate($infos);
		return $i;
	}
	/**
	 * @param string $ip
	 * @return static|null
	 */
	public static function findByIP(string $ip): ?JenkinsSlave{
		$all = static::all();
		return $all->filter(/**
		 * @param static $one
		 */ fn(JenkinsSlave $one) => $one->getIP() === $ip)->first();
	}
	/**
	 * @return JenkinsSlave[]|Collection
	 */
	public static function master(): Collection{
		return self::getWhereNameLike(self::NAME_MASTER)->first();
	}
	/**
	 * @param string $name
	 * @return Collection|static[]
	 */
	public static function getWhereNameLike(string $name): Collection{
		return BaseNameProperty::filterWhereLike($name, static::all());
	}
	/**
	 * @param string $name
	 * @return Collection|static[]
	 */
	public static function getStartingWith(string $name): Collection{
		return BaseNameProperty::filterWhereStartsWith($name, static::all());
	}
	/**
	 * @param string|null $pattern
	 * @return static[]
	 */
	public static function getSshOrWebsiteOffline(string $pattern = null): array{
		$all = static::all();
		$matches = [];
		foreach($all as $computer){
			if($pattern && stripos($computer->getNameAttribute(), $pattern) === false){
				continue;
			}
			if(!$computer->sshOrWebsiteOffline()){
				continue;
			}
			$matches[$computer->getNameAttribute()] = $computer;
		}
		return $matches;
	}
	/**
	 * @param string|null $pattern
	 * @return static[]
	 */
	public static function getSshOffline(string $pattern = null): array{
		$all = static::all();
		$matches = [];
		foreach($all as $computer){
			if($pattern && stripos($computer->getNameAttribute(), $pattern) === false){
				continue;
			}
			if(!$computer->sshOffline()){
				continue;
			}
			$matches[$computer->getNameAttribute()] = $computer;
		}
		return $matches;
	}
	/**
	 * @param JenkinsSlave[] $nodes
	 * @param \Closure $closure
	 * @throws CommandFailureException
	 */
	public static function executeOnMultiple(array $nodes, Closure $closure){
		$failures = [];
		foreach($nodes as $node){
			try {
				$closure($node);
			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (CommandFailureException $e) {
				$failures[$node->getNameAttribute()][] = $e;
			}
			#break;
		}
		self::handleCommandFailures($failures);
	}
	/**
	 * @param array $failures
	 * @throws CommandFailureException
	 */
	private static function handleCommandFailures(array $failures): void{
		if($failures){
			QMLog::error(implode(", ", array_keys($failures)) . " failed");
			/** @var CommandFailureException $e */
			foreach($failures as $host => $e){
				QMLog::error("$host: ".$e->getMessage());
			}
			if(isset($e)){throw $e;}
		}
	}
	/**
	 * @return JenkinsSlave[]
	 */
	public static function getOnline(): array{
		return JenkinsSlave::getOnlineLike(static::instancePrefix());
	}
	/**
	 * @param string|null $pattern
	 * @return JenkinsSlave[]
	 */
	public static function getOnlineLike(string $pattern = null): array{
		$all = static::all();
		$matches = [];
		foreach($all as $computer){
			if($pattern && stripos($computer->getNameAttribute(), $pattern) === false){
				continue;
			}
			if($computer->offline){
				continue;
			}
			$matches[$computer->getNameAttribute()] = $computer;
		}
		return $matches;
	}
	/**
	 * @return bool
	 */
	abstract public function isWeb(): bool;
	/**
	 * @return string
	 */
	public function getXmlConfigPath(): string{
		$name = $this->getNameAttribute();
		return FileHelper::absPath(Jenkins::getJenkinsPath() . "/nodes/$name/config.xml");
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getJenkinsUrl(array $params = []): string{
		$url = self::getJenkinsUrlByName($this->getNameAttribute());
		return UrlHelper::addParams($url, $params);
	}
	/**
	 * @return string
	 */
	public function getUrl(): string{
		if($this->url){return $this->url;}
		//if(AppMode::isPHPStorm()){return \App\Utils\Env::getAppUrl();}
		/** @noinspection HttpUrlsUsage */
		return $this->url = "http://".$this->getIP();
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public static function getJenkinsUrlByName(string $name): string{
		$path = "computer/$name/";
		return JenkinsAPI::getUrlByPath($path);
	}
	/**
	 * @return bool
	 */
	public function sshOrWebsiteOffline(): bool{
		if(!$this->offline){
			return false;
		}
		return $this->offline = $this->sshOffline();
	}
	/**
	 * @return bool
	 */
	public function sshOffline(): bool {
		$this->setTimeout(10);
		try {
			$response = $this->execute("echo \$USER");
		} catch (OfflineException $e) {
			$this->logError(__METHOD__.": ".$e->getMessage());
			return true;
		} catch (CommandFailureException $e) {
			le($e, $this);
		}
		$output = $response->getOutput();
		$user = $this->getUser();
		if(!str_contains($output, $user)){
			$m = "Got $output from user on $this but expected $user";
			$this->logInfo($m);
			return true;
		}
		return false;
	}
	/**
	 * returns null when computer is launching
	 * returns \stdClass when computer has been put offline
	 * @return null|stdClass
	 */
	public function getOfflineCause(): ?stdClass{
		return $this->offlineCause;
	}
	/**
	 * @return string
	 */
	public function getConfiguration(): string{
		return self::getComputerConfiguration($this->getNameAttribute());
	}
	/**
	 * @param string $computerName
	 * @return string
	 */
	public static function getComputerConfiguration(string $computerName): string{
		return JenkinsAPI::curl(sprintf('/computer/%s/config.xml', $computerName));
	}
	/**
	 * @param string $sha
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function checkoutSha(string $sha): Process{
		QMAPIRepo::createStatus($sha, QMAPIRepo::STATE_pending, Build::getConsoleUrl(),
			"Deploying...", "Deploying...");
		$proc = $this->execute("git checkout -f $sha");
		return $proc;
	}
	/**
	 * @return int
	 */
	public function getPort(): int{
		if(!$this->port){
			$this->port = $this->launcher["port"] ?? null;
		}
		if(!$this->port){
			$this->port = $this->getLabelValue("SSH_PORT");
		}
		if(!$this->port){
			$this->port = 22;
		}
		return $this->port;
	}
	/**
	 * @return string
	 */
	public function getPassword(): ?string{
		return $this->password;
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function clone(): Process{
		$proc = $this->execute("sudo apt-get install -y curl git && curl https://gist.githubusercontent.com/mikepsinn/2f3c69a17e22855421e15541e0f2f973/raw/clone_qm.sh | bash -s " . \App\Utils\Env::get('GITHUB_ACCESS_TOKEN'));
		return $proc;
	}
	/**
	 * @param string $path
	 * @param bool $sudo
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function executeScript(string $path, bool $sudo): Process{
		$cmd = "bash $path";
		if($sudo){$cmd = "sudo $cmd";}
		return $this->execInQMAPI($cmd);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function setupSwap(): Process{
		return $this->executeScript(BashScriptFile::SCRIPT_SWAP, true);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function provision(): Process {
		$this->gitCloneOrPull();
		return $this->runScript(BashScriptFile::SCRIPT_PROVISION, true);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function composer_install(): Process{
		$proc = $this->execute("cd ~/qm-api && composer install");
		return $proc;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getNameAttribute();
	}
	/**
	 * @param string $folder
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function cleanFolder(string $folder){
		$this->execInQMAPI("rm -r $folder || true");
		$this->execInQMAPI("mkdir -p $folder");
	}
	/**
	 * @return string[]
	 */
	public function getReplacements(): array{
		return [
			'host' => $this->getHost(),
			'credentialsId' => $this->getCredentialsId(),
			'labels' => $this->getLabelsString(),
			'name' => $this->getNameAttribute(),
		];
	}
	/**
	 * @return string
	 */
	public function getHost(): string{
		if(!isset($this->host) && isset($this->launcher)){
			$this->host = $this->launcher["host"];
		}
		if(!isset($this->host)){
			le("No hostname on $this launcher: ".\App\Logging\QMLog::print_r($this, true));
		}
		return $this->host;
	}
	/**
	 * @return string
	 */
	public function getCredentialsId(): string{
		return $this->getUser() . "-aws-pem";
	}
	/**
	 * @return string
	 */
	public function getUser(): string{
		if($user = $this->getLabelValue(LightsailInstanceResponse::TAG_USERNAME)){
			$this->user = $user;
		}
		if(!isset($this->user)){
			le("could not get user for $this->displayName");
		}
		return $this->user;
	}
	/**
	 * @return string
	 */
	public function getLabelsString(): string{ return implode(" ", $this->getLabels()); }
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function updatePHP(){
		$this->gitCloneOrPull(QMAPIRepo::getLongCommitShaHashFromGit());
		$this->runScript(BashScriptFile::SCRIPT_PHP_UPDATE, false);
	}
	/**
	 * @param string|null $sha
	 * @return Process[]
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function gitCloneOrPull(string $sha = null): array{
		if(!$sha){$sha = QMAPIRepo::getLongCommitShaHash();}
		return $this->executeMultiple(QMAPIRepo::getCloneOrPullShaCommands($sha, self::HOME_QM_API));
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function ownRepo(): Process{
		return $this->chownRecursive(self::HOME_QM_API);
	}
	/**
	 * @param string $path
	 * @param string|null $user
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function chownRecursive(string $path, string $user = null): Process{
		if(!$user){$user = "\$USER:\$USER";}
		return $this->execute("sudo chown -R $user $path");
	}
	/**
	 * @param string $path
	 * @param string|null $user
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function chown(string $path, string $user = null): Process{
		if(!$user){$user = "\$USER:\$USER";}
		return $this->execute("sudo chown $user $path");
	}
	/**
	 * @param string|array $commands
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function execInQMAPI($commands): Process{
		$this->gitCloneOrPull();
		if(!is_array($commands)){
			$commands = [$commands];
		}
		$cmd = implode(" && ", $commands);
		$proc = $this->execute("cd " . $this->getQmApiPath() . " && $cmd");
		return $proc;
	}
	public function checkStatus(){
	}
	/**
	 * @param string $folder
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function deleteFolder(string $folder): Process{
		$proc = $this->execute("sudo rm -rf $folder || true");
		return $proc;
	}
	/**
	 * @param string $folder
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function createFolder(string $folder): Process{
		$proc = $this->execute("sudo mkdir $folder || true");
		return $proc;
	}
	/**
	 * @param string $folder
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function ownFolder(string $folder): void{
		$this->logInfo("BEFORE changing owner:");
		$this->listPermissions($folder);
		$this->execute("sudo chown -R \$USER:\$USER $folder");
		$this->logInfo("AFTER changing owner:");
		$this->listPermissions($folder);
	}
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logInfo(string $message, $meta = []){
		QMLog::info("$this: $message", $meta);
	}
	/**
	 * @param string $folder
	 * @return string
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function listPermissions(string $folder): string{
		QMLog::infoWithoutContext("$folder permissions:");
		$this->execute("ls -la $folder");
		return $this->getOutputLines();
	}
	/**
	 * @return string
	 */
	protected function getXmlTemplatePath(): string{
		return "app/DevOps/Jenkins/node-config-template.xml";
	}
	/**
	 * @param string $newName
	 */
	public function rename(string $newName){
		$xml = $this->getXmlFile();
		$oldName = $this->getNameAttribute();
		$xml->replace($oldName, $newName);
		$xml->move(self::BASE_NODES_PATH."/$newName");
		$this->logInfo("Renamed $oldName to $newName.  Restart jenkins to take effect. ");
	}
	public function addHostToName(){
		$oldName = $this->getNameAttribute();
		$host = $this->getHost();
		if(stripos($oldName, $host) === false){
			$this->rename($oldName."-".$host);
		}
	}
	/**
	 * @param string $label
	 * @return Collection|static[]
	 */
	public static function whereLabeled(string $label): Collection{
		$all = self::all();
		return $all->filter(/**
		 * @param JenkinsSlave $one
		 * @return bool
		 */ fn(JenkinsSlave $one) => $one->hasLabel($label));
	}
	/**
	 * @param string $label
	 * @return Collection
	 */
	public static function whereLabeledAndOnline(string $label): Collection{
		$all = self::whereLabeled($label);
		return $all->filter(/**
		 * @param JenkinsSlave $one
		 * @return bool
		 */ fn(JenkinsSlave $one) => !$one->offline);
	}
	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getLabelValue(string $name): ?string {
		$labels = $this->getLabels();
		foreach($labels as $label){
			if(stripos($label, $name."=") === 0){
				return str_replace($name."=", "", $label);
			}
		}
		return null;
	}
	public static function ip(): string{
		if($ip = self::getRunnerIP()){
			return $ip;
		}
		return (new static())->getIP();
	}
	/**
	 * @return string
	 */
	public function getIP(): string{
		if($this->ip){return $this->ip;}
		if($runner = self::getRunnerName()){
			return $this->ip = static::getRunnerIP($runner);
		}
		if($ip = $this->getLabelValue(LightsailInstanceResponse::TAG_PUBLIC_IP_ADDRESS)){return $this->ip = $ip;}
		if($this->isHillsboro()){
			if($ip = $this->getLabelValue(LightsailInstanceResponse::TAG_PRIVATE_IP_ADDRESS)){
				return $this->ip = $ip;
			}
		}
		$host = $this->getHost();
		if(str_contains($host, ".")){return $this->ip = $host;}
		le("Could not determine IP for this:", (array)$this);
//		$externalContent = file_get_contents('http://checkip.dyndns.com/');
//		preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
//		$externalIp = $m[1];
//		return $this->ip = $externalIp;
	}
	/**
	 * @param string $reason
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function reboot(string $reason){
		$this->logError("Rebooting $this because $reason");
		$this->sudo("reboot");
	}
	/**
	 * @param string $cmd
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function sudo(string $cmd): Process{
		return $this->execute("sudo $cmd");
	}
	/**
	 * @return static
	 */
	public static function first(): self{
		return static::all()->first();
	}
	/**
	 * @throws InvalidStringException
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function assertHealthy(){
		$m = "I am healthy";
		$this->execute("echo $m");
		$out = $this->getOutputLines();
		QMStr::assertContains($out, $m, "$this health check");
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function etcFilesCopy(){
		$label = static::instancePrefix();
		$this->execInQMAPI("sudo cp -R configs/etc-global/* /etc/");
		$this->execInQMAPI("sudo cp configs/etc-$label/* /etc/ || true");
		$this->restartServices();
		$this->etcKeeperPush();
	}
	/**
	 * @param string $path
	 * @param bool $sudo
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function runScript(string $path, bool $sudo): Process{
		return $this->executeScript($path, $sudo);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installRedis(): Process{
		return $this->runScript(BashScriptFile::SCRIPT_REDIS_INSTALL, true);
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	protected function restartServices(){
		$services = static::services();
		$this->execute("sudo systemctl enable redis-server");
		foreach($services as $service){
			$this->restartService($service);
		}
	}
	/**
	 * @return array
	 */
	protected static function services():array{
		return [
			QMServices::REDIS,
			QMServices::NGINX,
			QMServices::PHP74_FPM,
			QMServices::MYSQL,
			//QMServices::FILEBEAT,
		];
	}
	/**
	 * @param string $service
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	protected function restartService(string $service): Process{
		$proc = $this->execute("sudo service $service restart");
		return $proc;
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installFilebeatAndLogzIO(): Process{
		return $this->runScript(BashScriptFile::SCRIPT_LOGZ_IO_INSTALL, true);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installEtcKeeper(): Process{
		return $this->runScript(BashScriptFile::SCRIPT_ETCKEEPER_INSTALL, true);
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	protected function etcKeeperPush(): Process{
		return $this->executeScript(BashScriptFile::SCRIPT_ETCKEEPER_PUSH, false);
	}
	/**
	 * @return string
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getQmApiAbsPath(): string {
		$proc = $this->execInQMAPI("echo \$PWD");
		return $proc->getOutput();
	}
	/**
	 * @return string
	 * @noinspection PhpUnhandledExceptionInspection
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function getQmApiPath(): string {return self::HOME_QM_API;}
	/**
	 * @param string $sourcePath
	 * @param string $destinationPath
	 * @return Process
	 * @throws CommandFailureException
	 */
	public function upload(string $sourcePath, string $destinationPath): Process{
		$what = "uploading $sourcePath to $destinationPath on " . $this->getTarget();
		QMLog::logStartOfProcess($what);
		FileHelper::assertAbsPath($destinationPath);
		$process = parent::upload($sourcePath, $destinationPath);
		if($process->getExitCode() !== 0){throw new CommandFailureException($this, "Failed: $what");}
		QMLog::logEndOfProcess($what);
		return $process;
	}
	/**
	 * @param string $command
	 * @param string $method
	 * @return Process
	 * @throws OfflineException
	 */
	public function run(string $command, string $method = 'run'): Process{
		$process = Process::fromShellCommandline($command, $this->cwd);
		$this->setLastCommandProcess($process);
		try {
			$process->setTimeout($this->timeout);
			($this->processConfigurationClosure)($process);
			$process->{$method}($this->onOutput);
			$code = $process->getExitCode();
			if($code === 255){throw new OfflineException($this);}
			if($code !== 0){throw new CommandFailureException($this);}
			$out = $process->getOutput();
			if($this->inOutputErrorList($out)){throw new CommandFailureException($this);}
			return $process;
		} catch (ProcessTimedOutException $e){
			throw new OfflineException($this, $e);
		} catch (CommandFailureException $e){
			if(QMStr::contains($e->getMessage(), "Permanently added")){
				$this->logInfo(__METHOD__.": ".$e->getMessage());
				return $process;
			}
			le($e);
		} catch (\Throwable $e){
			QMLog::info(__METHOD__.": ".$e->getMessage());
			le($e);
		}
	}
	/**
	 * @return string
	 */
	public function getOutputString(): string{
		return implode("\n", $this->outputLines);
	}
	/**
	 * @param array|string $command
	 * @return Process[]
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function executeMultiple($command): array{
		$processes = [];
		$commands = $this->wrapArray($command);
		foreach($commands as $command){
			$processes[] = $this->execute($command);
		}
		return $processes;
	}
	/**
	 * @return Process
	 */
	public function getLastCommandProcess(): Process {
		return $this->lastCommandProcess;
	}
	/**
	 * @param string $line
	 * @return bool
	 */
	private function inOutputErrorList(string $line): bool{
		$black = [
			"Segmentation fault",
			"No such file or directory",
			"Call Stack:",
		];
		foreach($black as $item){
			if(stripos($line, $item) !== false){
				return true;
			}
		}
		return false;
	}
	/**
	 * @return string
	 */
	public function getOutputLines(): string{
		$out = "";
		foreach($this->getCommands() as $command){
			$out .= $command->getOutput();
		}
		return $out;
	}
	/**
	 * @return Process[]
	 */
	public function getCommands(): array {
		return $this->commands;
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installPHP74(){
		$this->runScript('scripts/update_php_minor_version.sh', false);
	}
	/**
	 * @param $response
	 */
	protected function populateFromJenkinsResponse($response): void{
		$this->jenkinsResponse = $response;
		foreach($response as $key => $value){
			if(!property_exists($this, $key)){
				QMLog::info("public $$key;");
			}
		}
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		if(isset($response->assignedLabels)){
			foreach($response->assignedLabels as $arr){
				$this->labels[] = $arr->name;
			}
		}
	}
	/**
	 * @param LightsailInstanceResponse $instance
	 */
	private function populateFromLightsailResponse(LightsailInstanceResponse $instance): void{
		$this->jenkinsResponse = $instance;
		//foreach($instance as $key => $value){if(!property_exists($this, $key)){QMLog::info("public $$key;");}}
		foreach($instance as $key => $value){$this->$key = $value;}
		if(isset($instance->assignedLabels)){
			foreach($instance->getTagNames() as $name){
				$this->labels[] = $name;
			}
		}
		$this->user = $instance->username;
		$this->displayName = $instance->name;
		/** @var OnLightsail $this */
		$this->setLightsailInstance($instance);
	}
	private function populateFromXmlFile(): void{
		if(!$this->isMaster()){ // Master doesn't have a config file
			try {
				$this->loadXmlConfig();
			} catch (\Throwable $e) {
				QMLog::info("$this" . $e->getMessage());
			}
		}
	}
	/**
	 * @return JenkinsSlave
	 */
	private function setRootUser(): JenkinsSlave{
		$this->setUser(self::USER_ROOT);
		return $this;
	}
	/**
	 * @param string $src
	 * @param string $dest
	 * @return string
	 * @throws CommandFailureException
	 */
	public function rsyncFromLocalToRemote(string $src, string $dest): string{
		$cmd = new RsyncFromLocalToRemoteCommand($src, $dest, $this);
		$cmd->runOnExecutor();
		return $cmd->getOutputString();
	}
	/**
	 * @param string $path
	 * @return bool
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function deleteFile(string $path): bool {
		FileHelper::assertAbsPath($path);
		try {
			$this->execute("rm $path");
			return true;
		} catch (CommandFailureException $e){
			if(!str_contains($e->getMessage(), "No such file or directory")){
				throw $e;
			}
		    $this->logInfo(__METHOD__.": ".$e->getMessage());
		    return false;
		}
	}
	/**
	 * @param string $path
	 * @return bool
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function fileExists(string $path): bool {
		FileHelper::assertAbsPath($path);
		$p = $this->execute("if [ ! -f $path ]; then echo 'File not found!'; else echo 'File found!'; fi");
		$out = $p->getOutput();
		return str_contains($out, "File found!");
	}
	/**
	 * @return bool
	 */
	public function test():bool{
		return !$this->sshOrWebsiteOffline();
	}
	/**
	 * @param string $sourcePath
	 * @param string $destinationPath
	 * @throws CommandFailureException
	 */
	public static function uploadToAll(string $sourcePath, string $destinationPath){
		$all = static::all();
		$sourcePath = abs_path($sourcePath);
		FileHelper::assertAbsPath($destinationPath);
		foreach($all as $one){
			try {
				$one->upload( $sourcePath, $destinationPath);
			} catch (CommandFailureException $e){
			    QMLog::error(__METHOD__.": ".$e->getMessage());
			}
		}
		if(isset($e)){
			throw $e;
		}
	}
	/**
	 * @param Process $process
	 */
	protected function setLastCommandProcess(Process $process): void{
		$this->lastCommandProcess = $this->commands[] = $process;
	}
	public static function getUniqueIndexColumns(): array{return ['name'];}
	public function getId(): string{return $this->getNameAttribute();}
	protected static function hasNonIdUniqueIndex():bool{return false;}
	/**
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	public function tagValueIs(string $name, string $value): bool{
		return $this->getTagValue($name) === $value;
	}
	public function toArray(): array{
		$arr = [];
		foreach($this as $key => $value){$arr[$key] = $value;}
		return $arr;
	}
	/**
	 * @return void
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function updateHostname(): void{
		$CUR_HOSTNAME = $this->getHostname();
		$NEW_HOSTNAME = $this->getNameAttribute();
		$this->executeMultiple([
"sudo hostnamectl set-hostname $NEW_HOSTNAME",
"sudo hostname $NEW_HOSTNAME",
'sudo sed -i "s/'.$CUR_HOSTNAME.'/'.$NEW_HOSTNAME.'/g" /etc/hosts',
'sudo sed -i "s/'.$CUR_HOSTNAME.'/'.$NEW_HOSTNAME.'/g" /etc/hostname',
		]);
	}
	/**
	 * @return void
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installNetData(): void{
		$this->execInQMAPI("bash scripts/update_hostname.sh {$this->getNameAttribute()}");
	}
	/**
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function getHostname(): string{
		$p = $this->execute("echo $(cat /etc/hostname)");
		$out = $p->getOutput();
		return trim($out);
	}
}
