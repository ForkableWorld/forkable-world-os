<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Computers;
use App\DevOps\Jenkins\Jenkins;
use App\Exceptions\InvalidStringException;
use App\Exceptions\RateLimitConnectorException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\JenkinsCommands\JenkinsReload;
use App\ShellCommands\OfflineException;
use App\Storage\Memory;
use App\Traits\ConstantGenerator;
use App\Traits\HasClassName;
use App\Traits\HasMemory;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
use App\Utils\APIHelper;
use App\Utils\IPHelper;
use Aws\Api\DateTimeResult;
use Aws\Lightsail\Exception\LightsailException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;
/**
 * @package App\Computers
 * @mixin JenkinsSlave
 */
class LightsailInstanceResponse {
	use HasMemory;
	use LoggerTrait;
	use ConstantGenerator;
	use ForwardsCalls;
	use HasClassName;
	public const IP_PUBLIC = '0.0.0.0/0';
	public const IP_MIKE = IPHelper::IP_MIKE.'/32';
	public const AVAILABILITY_ZONE_US_EAST_1A = 'us-east-1a';
	public const BASE_QM_API_BLUEPRINT = 'base-qm-api-blueprint';
	public const BUNDLE_ID_2GB = 'small_2_0';
	public const LABEL_LIGHTSAIL = 'lightsail';
	public const NAME_CC_WIKI = 'cc-wiki';
	public const REGION_NAME_US_EAST_1 = 'us-east-1';
	public const TAG_APP_ENV = 'APP_ENV';
	public const TAG_ARN = 'ARN';
	public const TAG_BLUEPRINT_ID = 'BLUEPRINT_ID';
	public const TAG_BLUEPRINT_NAME = 'BLUEPRINT_NAME';
	public const TAG_BUNDLE_ID = 'BUNDLE_ID';
	public const TAG_DISK_SIZE_IN_GB = 'DISK_SIZE_IN_GB';
	public const TAG_HARDWARE_CPU_COUNT = 'HARDWARE_CPU_COUNT';
	public const TAG_HARDWARE_RAM_SIZE_IN_GB = 'HARDWARE_RAM_SIZE_IN_GB';
	public const TAG_HEALTH_CHECK_STRING = 'HEALTH_CHECK_STRING';
	public const TAG_HEALTH_CHECK_URL = 'HEALTH_CHECK_URL';
	public const TAG_IP_ADDRESS_TYPE = 'IP_ADDRESS_TYPE';
	public const TAG_LOCATION_AVAILABILITY_ZONE = 'LOCATION_AVAILABILITY_ZONE';
	public const TAG_LOCATION_REGION_NAME = 'LOCATION_REGION_NAME';
	public const TAG_NAME = 'NAME';
	public const TAG_PLATFORM = 'PLATFORM';
	public const TAG_PORT_22 = 'PORT_22';
	public const TAG_PORT_80 = 'PORT_80';
	public const TAG_PORT_443 = 'PORT_443';
	public const TAG_PORT_3306 = 'PORT_3306';
	public const TAG_PORT_6379 = 'PORT_6379';
	public const TAG_PRIVATE_IP_ADDRESS = 'PRIVATE_IP_ADDRESS';
	public const TAG_PUBLIC_IP_ADDRESS = 'PUBLIC_IP_ADDRESS';
	public const TAG_RESOURCE_TYPE = 'RESOURCE_TYPE';
	public const TAG_SSH_KEY_NAME = 'SSH_KEY_NAME';
	public const TAG_SUPPORT_CODE = 'SUPPORT_CODE';
	public const TAG_USERNAME = 'USERNAME';
	const TAG_DELETE_ME = 'delete-me';
	const AAPANEL_MASTER_PREFIX = "aapanel-master";
	/** @var string */
	public $name;
	/** @var string */
	public $arn;
	/** @var string */
	public $supportCode;
	/** @var DateTimeResult */
	public $createdAt;
	/** @var string[] */
	public $location;
	/** @var string */
	public $resourceType;
	/** @var array */
	public $tags;
	/** @var string */
	public $blueprintId;
	/** @var string */
	public $blueprintName;
	/** @var string */
	public $bundleId;
	/** @var array[] */
	public $addOns;
	/** @var boolean */
	public $isStaticIp;
	/** @var string */
	public $privateIpAddress;
	/** @var string */
	public $publicIpAddress;
	/** @var string[] */
	public $ipv6Addresses;
	/** @var string */
	public $ipAddressType;
	/** @var integer[] */
	public $hardware;
	/** @var array[] */
	public $networking;
	/** @var integer[] */
	public $state;
	/** @var string */
	public $username;
	/** @var string */
	public $sshKeyName;
	public $jenkinsLabels = [];
	/** @var JenkinsSlave|null */
	private ?JenkinsSlave $computer = null;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj = null){
		if($obj){foreach($obj as $key => $value){$this->$key = $value;}}
		$this->addToMemory();
	}
	public static function findInMemoryOrApi(string $name): ?self{
		return BaseNameProperty::findInArray($name, static::allFromMemoryOrApi());
	}
	public static function findInApi(string $name): ?self{
		return BaseNameProperty::findInArray($name, self::allFromApi());
	}
	/**
	 * @param string $key
	 * @param null $operator
	 * @param null $value
	 * @return Collection|static[]
	 */
	public static function where(string $key, $operator = null, $value = null): Collection{
		$all = static::allFromMemoryOrApi();
		return $all->where($key, $operator, $value);
	}
	public static function findByIp(string $ip): ?self{
		return static::where("publicIpAddress", "=", $ip)->first();
	}
	/**
	 * @return void
	 * @throws InvalidStringException
	 */
	public static function checkWildCardSite(){
		try {
			$response = APIHelper::getRequest("https://medimodo.quantimodo.com/");
		} catch (RateLimitConnectorException $e) {
			le($e);
		}
		QMStr::assertContains($response, "qmHelpers.js", __FUNCTION__);
	}
	/**
	 * @return LightsailInstanceResponse[]|Collection
	 */
	public static function allFromMemoryOrApi(): Collection{
		$mem = Memory::get(__METHOD__);
		if($mem){return $mem;}
		$i = self::allFromApi();
		return $i;
	}
	public static function updateFirewalls(string $prefix = null): void{
		if($prefix){
			$instances = self::getStartingWith($prefix);
		} else {
			$instances = static::allFromMemoryOrApi();
		}
		foreach($instances as $computer){
			try {
				$computer->updateFirewall();
			} catch (LightsailException $e) {
				QMLog::info($e->getAwsErrorMessage());
			}
		}
	}
	public static function updateHostNames(string $prefix = null): void{
		if($prefix){
			$instances = self::getStartingWith($prefix);
		} else {
			$instances = static::allFromMemoryOrApi();
		}
		foreach($instances as $computer){
			try {
				$computer->updateHostname();
			} catch (LightsailException|CommandFailureException|OfflineException $e) {
				QMLog::info($e->getAwsErrorMessage());
			}
		}
	}
	public function updateFirewall(): void{
		$result = QMLightsailClient::client()->putInstancePublicPorts([
			'instanceName' => $this->getNameAttribute(),
			'regionName' => self::REGION_NAME_US_EAST_1,
			'portInfos' => LightsailInstanceResponse::getDefaultPortSettings($this->getPublicPorts()),
		]);
		$arr = $result->toArray();
		\App\Logging\ConsoleLog::info("Updated firewall for $this->name ".$arr["operation"]["status"]);
		$this->refresh();
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
	 * @return static
	 */
	public static function first(): LightsailInstanceResponse{
		$all = static::allFromMemoryOrApi();
		return $all->first();
	}
	/**
	 * @param int $port
	 * @return array
	 */
	private static function portData(int $port): array{
		return [
			'protocol' => 'tcp',
			'fromPort' => $port,
			'toPort' => $port,
			'cidrListAliases' => [],
			'ipv6Cidrs' => [],
		];
	}
	/**
	 * @return array
	 */
	private static function sshPort(): array{
		return [
			'protocol' => 'tcp',
			'fromPort' => 22,
			'toPort' => 22,
			'cidrListAliases' => [
				0 => 'lightsail-connect',
			],
			'ipv6Cidrs' => [],
		];
	}
	/**
	 * @param $needles
	 * @return Collection
	 */
	public static function whereTagNamesContain($needles): Collection{
		if(is_string($needles)){$needles = [$needles];}
		$all = static::allFromMemoryOrApi();
		$matches = $all->filter(function(LightsailInstanceResponse $i) use ($needles){
			$tags = $i->getTags();
			$tags = array_keys($tags);
			foreach($needles as $needle){
				if(!in_array($needle, $tags)){
					return false;
				}
			}
			return $i;
		});
		return $matches;
	}
	/**
	 * @return Collection|\Tightenco\Collect\Support\Collection
	 */
	private static function allFromApi(){
		$i = collect(QMLightsailClient::all('Instances', static::class));
		Memory::set(__METHOD__, $i);
		return $i;
	}
	public function delete(){
		$result = QMLightsailClient::client()->deleteInstance([
			'instanceName' => $this->getNameAttribute(),
			'regionName' => self::REGION_NAME_US_EAST_1
		]);
		$arr = $result->toArray();
		\App\Logging\ConsoleLog::info("Deleted $this->name ".$arr["operations"][0]["status"]);
	}
	public static function deleteAndRecreateAllJenkinsNodes(){
		$jenkinsNodes = JenkinsSlave::whereLabeled(JenkinsSlave::LABEL_LIGHTSAIL);
		$lightsailInstances = static::allFromMemoryOrApi();
		$lightsailNames = $lightsailInstances->pluck('name');
		foreach($jenkinsNodes as $node){
			if(!$lightsailNames->contains($node->getNameAttribute())){
				$node->deleteJenkinsNode(__FUNCTION__);
			}
		}
		$jenkinsNames = $jenkinsNodes->pluck('displayName');
		$createdNodes = [];
		foreach($lightsailInstances as $instance){
			if(!$jenkinsNames->contains($instance->getNameAttribute())){
				$createdNodes[] = $instance->createJenkinsNode(false);
			}
		}
		if($createdNodes){
			JenkinsReload::exec();
		}
	}
	/**
	 * @param string $prefix
	 * @return LightsailInstanceResponse[]|Collection
	 */
	public static function getStartingWith(string $prefix): Collection{
		$instances = LightsailInstanceResponse::allFromMemoryOrApi();
		return BaseNameProperty::filterWhereStartsWith($prefix, $instances);
	}
	/**
	 * @param string $key
	 * @param null $value
	 */
	private function addJenkinsLabel(string $key, $value = null){
		if(!empty($value)){
			$this->jenkinsLabels[] = $key."=".$value;
		} else{
			$this->jenkinsLabels[] = $key;
		}
	}
	/**
	 * @return array
	 */
	public function getJenkinsLabels(): array {
		foreach($this->getTags() as $key => $value){
			$this->addJenkinsLabel($key, $value);
		}
		if($this->isPHPUnit()){
		    $this->jenkinsLabels = array_unique(array_merge($this->getPHPUnitLabels(), $this->jenkinsLabels));
		}
		return $this->jenkinsLabels;
	}
	public function getComputer(): JenkinsSlave{
		if($this->computer){
			return $this->computer;
		}
		/** @var OnLightsail|JenkinsSlave $c */
		$c = PhpUnitComputer::newFromLightsail($this);
		return $this->computer = $c;
	}
	/**
	 * @return string
	 */
	public function getPrivateIpAddress(): string{
		return $this->privateIpAddress;
	}
	/**
	 * @return string
	 */
	public function getPublicIpAddress(): string{
		return $this->publicIpAddress;
	}
	/**
	 * @return array
	 */
	public function getTags(): array{
		foreach($this->tags as $key => $value){
			if(is_array($value)){
				unset($this->tags[$key]);
				$this->addTag($value['key'], $value['value'] ?? null);
			}
		}
		foreach($this as $key => $val){
			if(is_string($val) || is_numeric($val)){
				$key = QMStr::toScreamingSnakeCase($key);
				$this->addTag($key, $val);
			}
		}
		foreach($this->location as $key => $val){
			if(is_string($val) || is_numeric($val)){
				$key = "LOCATION_".QMStr::toScreamingSnakeCase($key);
				$this->addTag($key, $val);
			}
		}
		foreach($this->hardware as $key => $val){
			if(is_string($val) || is_numeric($val)){
				$key = "HARDWARE_".QMStr::toScreamingSnakeCase($key);
				$this->addTag($key, $val);
			}
		}
		foreach($this->networking["ports"] as $port){
			$this->addTag("PORT_".$port["toPort"], implode(",", $port['cidrs']));
		}
		if(isset($this->hardware["disks"][0])){
			$this->addTag('DISK_SIZE_IN_GB', $this->hardware["disks"][0]["sizeInGb"]);
		}
		$this->addTag("PLATFORM", self::LABEL_LIGHTSAIL);
		return $this->tags;
	}
	/**
	 * @param string $name
	 * @param $val
	 */
	public function addTag(string $name, $val){
		$this->tags[$name] = $val;
	}
	public function getTagNames(): array{
		return array_keys($this->getTags());
	}
	public function ssh(): Ssh{
		return Ssh::create($this->getUser(), $this->getHost())
		    ->disableStrictHostKeyChecking()
		    ->disablePasswordAuthentication()
		    ->usePrivateKey($this->getPrivateKey());
	}
	/**
	 * @return string
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function getPHPVersion(): string{
		$process = $this->execute("php -v");
		$out = $process->getOutput();
		$success = $process->isSuccessful();
		$this->logInfo($out);
		return $out;
	}
	/**
	 * @return void
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function installPHP74IfNecessary(){
		$v = $this->getPHPVersion();
		if($v !== "7.4"){
		    $this->installPHP74();
		}
	}
	public function getUser(): string{
		return $this->username;
	}
	protected function getHost(): string{
		return $this->publicIpAddress;
	}
	protected function getPrivateKey(): string{
		return "/home/".ThisComputer::user()."/.ssh/id_rsa";
	}
	/**
	 * @return JenkinsSlave
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function enableRootLogin(): JenkinsSlave{
		$c = $this->getComputer();
		if($c->rootLoginEnabled()){
			$this->logInfo("Root login already enabled");
		} else {
			$c->enableRootLogin();
		}
		$this->logInfo("Updating firewall for safety since root login is enabled");
		$this->updateFirewall();
		return $c;
	}
	public function getUserName(): string{
		return $this->getUser();
	}
	private function isPHPUnit(): bool{
		return strpos($this->name, "phpunit-") === 0;
	}
	private function getPHPUnitLabels(): array{
		return ["phpunit", "staging-phpunit", "nodejs", "tideways", "docker", "phpunit-jobs"];
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	public function getSnapshots(){
		QMLightsailClient::client()->getInstanceSnapshots();
	}
	public function getIP(): string{
		return $this->getPublicIpAddress();
	}
	public function createJenkinsNode(bool $reload): JenkinsSlave{
		if(!$this->publicIpAddress){
			le("no public ip on ".\App\Logging\QMLog::print_r($this, true));
		}
		Jenkins::createJenkinsNode($this->name, $this->publicIpAddress,
			implode(" ", array_unique($this->getJenkinsLabels())), $this->getUserName());
		if($reload){
			(new JenkinsReload)->execute();
		}
		return $this->getComputer();
	}
	public function isHealthy(){
		$url = $this->getHealthCheckUrl();
	}
	private function getHealthCheckUrl(): string{
		return $this->getTag(self::TAG_HEALTH_CHECK_URL);
	}
	/**
	 * @return bool
	 */
	public function isStaticIp(): bool{
		return $this->isStaticIp;
	}
	/**
	 * @return Process
	 * @throws CommandFailureException
	 * @throws OfflineException
	 */
	public function provision(): Process{
		if($this->getTag(self::TAG_PORT_22) === self::IP_PUBLIC){
			$this->updateFirewall();
		}
		$c = $this->getComputer();
		return $c->provision();
	}
	/**
	 * @return array[]
	 */
	public static function getDefaultPortSettings(array $publicPorts): array{
		$ports = [
			20, // AAPANEL
			// This messes up port 22 21, // AAPANEL
			80,
			443,
			27017, // Mongo
			888, // AAPANEL
			3306, // MySQL
			6379, // Redis
			8000, // Ajenti
			7777, // AAPANEL
			9600, // Minio
			19999, // Netdata
		];
		$arr = [];
		foreach($ports as $port){$arr[] = self::portData($port);}
		$arr[] = self::sshPort();
		foreach($arr as $i => $info){
			if(in_array($info['toPort'], $publicPorts)){
				$arr[$i]['cidrs'] = [self::IP_PUBLIC];
			} else {
				$arr[$i]['cidrs'] = [self::IP_MIKE];
			}
		}
		return array_values($arr);
	}
	public function toArray(): array{
		$arr = [];
		foreach($this as $key => $value){$arr[$key] = $value;}
		return $arr;
	}
	private function getHealthCheckString(): string{
		return $this->getTag(self::TAG_HEALTH_CHECK_STRING);
	}
	public function getTag(string $name): string{
		$tags = $this->getTags();
		if(!isset($tags[$name])){
			le("Please set $name tag for $this->name at:\n\t".$this->getLightsailConfigUrl());
		}
		return $tags[$name];
	}
	public function getLightsailConfigUrl(): string{
		return "https://lightsail.aws.amazon.com/ls/webapp/us-east-1/instances/".$this->getNameAttribute();
	}
	public function getDeleteUrl(): string{
		return $this->getLightsailConfigUrl()."/delete";
	}
	public static function generateConstantName(string $str): string{
		return "TAG_".QMStr::toConstantName($str);
	}
	protected function getTagsWithValues(): array{
		$haveValues = [];
		$tags = $this->getTags();
		foreach($tags as $key => $value){
			if($value !== null){
				$haveValues[$key] = $value;
			}
		}
		return $haveValues;
	}
	protected static function generateConstantValues(): array{
		$tags = [];
		foreach(static::allFromMemoryOrApi() as $i){
			$tags = array_merge($tags, $i->getTags());
		}
		return array_unique(array_keys($tags));
	}
	private function refresh(): void{
		$updated = static::findInMemoryOrApi($this->getNameAttribute());
		foreach($updated as $key => $value){
			$this->$key = $value;
		}
	}
	public function needToReboot(): ?string{
		return $this->getComputer()->needToReboot();
	}
	public function isWeb(): bool{
		if(QMStr::contains($this->name, "phpunit")){
			return false;
		}
		if(QMStr::contains($this->name, "web") ||
		       QMStr::contains($this->name, "wp") ||
		   QMStr::contains($this->name, ".org")||
		   QMStr::contains($this->name, ".com")
		){
			return true;
		}
		return false;
	}
	/**
	 * Handle dynamic method calls into the model.
	 * @param string $method
	 * @param array  $parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters){
		if($c = $this->computer){
			return $this->forwardCallTo($c, $method, $parameters);
		} else {
			le("this->computer not set to forward call to $method");
		}
	}
	public static function getUniqueIndexColumns(): array{return ['name'];}
	public function getId(): string{return $this->getNameAttribute();}
	protected static function hasNonIdUniqueIndex():bool{return false;}
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
				if(isset($result)){
					return $result;
				}
			}
			return null;
		}
		$camel = QMStr::camelize($key);
		if(!isset($this->$camel)){
			return null;
		}
		return $this->$camel;
	}
	public function isAaPanelMaster(): bool{
		return stripos($this->name, self::AAPANEL_MASTER_PREFIX) !== false;
	}
}
