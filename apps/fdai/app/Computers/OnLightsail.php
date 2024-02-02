<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Computers;
use App\Exceptions\NotFoundException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\ShellCommands\JenkinsCommands\JenkinsReload;
use App\Traits\HasTestUrl;
use Aws\Lightsail\Exception\LightsailException;
use Aws\Result;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Collection;
trait OnLightsail {
	use HasTestUrl;
	protected $lightsailInstance;
	abstract public static function tags(): array;
	abstract public static function getBlueprintSnapshot():LightsailSnapshot;
	public static function refillInstances(int $total): void {
		$existing = LightsailInstanceResponse::getStartingWith(static::instancePrefix());
		$i = count($existing);
		while($i <= $total){
			static::createInstance();
			$i++;
		}
		(new JenkinsReload)->execute();
	}
	/**
	 * @param string $reason
	 * @return JenkinsSlave
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	public function deleteJenkinsNodeAndLightSailInstance(string $reason): JenkinsSlave{
		parent::deleteJenkinsNode($reason);
		$this->deleteLightsailInstance($reason);
		return $this;
	}
	private function deleteLightsailInstance(string $reason){
		QMLog::error("Deleting $this Lightsail instance because $reason");
		$i = LightsailInstanceResponse::findInMemoryOrApi($this->getNameAttribute());
		if(!$i){
			$this->logInfo("I guess $this was already deleted on Lightsail");
		} else{
			$i->delete();
		}
	}
	/**
	 * @param int $total
	 * @return void
	 */
	public static function createInstances(int $total):void{
		$i = 0;
		while($i <= $total){
			static::createInstance();
			$i++;
		}
		(new JenkinsReload)->execute();
	}
	public static function getInstances():Collection{
		return LightsailInstanceResponse::getStartingWith(static::instancePrefix());
	}
	public static function createInstance(): void {
		$names = static::getInstanceNames();
		$numbers = $names->map(fn($name) => (int)str_replace(static::instancePrefix()."-", "", $name));
		$max = $numbers->max();
		$i = $max+1;
		$name = static::instancePrefix()."-".$i;
		\App\Logging\ConsoleLog::info("Creating $name...");
		$ss = self::getBlueprintSnapshot();
		$ss->tags = self::tags();
		$ss->launchNewInstance($name);
	}
	/**
	 * @return Collection|string[]
	 */
	protected static function getInstanceNames(): Collection {
		$instances = static::getInstances();
		$names = $instances->pluck('name');
		return $names;
	}
	public static function updateFirewalls(){
		LightsailInstanceResponse::updateFirewalls(static::instancePrefix());
	}
	public static function deleteOfflineJenkinsNodes(): void{
		$offlineNodes = static::getSshOffline(static::instancePrefix());
		/** @var static $computer */
		foreach($offlineNodes as $computer){
			$computer->deleteJenkinsNode(__FUNCTION__);
		}
		if($after = static::getSshOffline(static::instancePrefix())){
			le("Still have ".count($after)." after deleting ".count($offlineNodes)."!");
		}
	}
	public static function updatePhpOnAll(){
		static::executeOnMultiple(static::getOnline(), function($slave){
			/** @var static $slave */
			$slave->updatePHP();
		});
	}
	/**
	 * @return string
	 */
	public function getIP(): string{
		try {
			return $this->ip = $this->getLightsailInstance()->getPublicIpAddress();
		} catch (NotFoundException $e){
			return $this->ip = $this->getTagValue(LightsailInstanceResponse::TAG_PUBLIC_IP_ADDRESS);
		}
	}
	/**
	 * @return static[]
	 */
	public static function rebootOffline(): array{
		$offline = static::getSshOrWebsiteOffline();
		/** @var static $computer */
		foreach($offline as $i => $computer){
			try {
				$computer->reboot(__FUNCTION__);
			} catch (LightsailException $e) {
				if($e->getAwsErrorCode() === "NotFoundException"){
					$computer->deleteJenkinsNode(__METHOD__.": ".$e->getMessage());
					unset($offline[$i]);
				}
			}
		}
		$count = count($offline);
		if($offline){QMLog::sleep(30, "waiting for $count severs come back online to re-launch on jenkins");}
		foreach($offline as $computer){$computer->relaunchJenkinsNode();}
		if(!$count){
			ConsoleLog::info("No computers offline! :D");
		}
		return $offline;
	}
	public function getUrl(array $params = []): string{
		return "http://".$this->getIP();
	}
	public function sshOrWebsiteOffline(): bool {
		try {
			$this->getLightsailInstance();
		} catch (NotFoundException $e){
		    return true;
		}
		if($this->sshOffline()){
			return true;
		}
		try {
			return !$this->testUrl();
		} catch (BadResponseException $e){
		    $this->logError(__METHOD__.": ".$e->getMessage());
		    return true;
		}
	}
	/**
	 * @param string $reason
	 */
	public function reboot(string $reason){
		$result = QMLightsailClient::client()->rebootInstance([
			'instanceName' => $this->getNameAttribute(),
			'regionName' => $this->getRegionName(),
		]);
		$arr = $result->toArray();
		$this->logError("Rebooting $this because: $reason\n\tRESULT: " . $arr["operations"][0]['status']);
	}
	/**
	 * @return LightsailInstanceResponse
	 * @throws NotFoundException
	 */
	public function getLightsailInstance(): LightsailInstanceResponse {
		if($this->lightsailInstance){
			return $this->lightsailInstance;
		}
		$i = LightsailInstanceResponse::findInMemory($this->getNameAttribute());
		if(!$i){$i = LightsailInstanceResponse::findInMemoryOrApi($this->getNameAttribute());}
		if(!$i){
			throw new NotFoundException("No lightsail instance for $this");
		}
		return $this->lightsailInstance = $i;
	}
	public static function lastSnapshot(): LightsailSnapshot {
		return LightsailSnapshot::mostRecentStartingWith(static::instancePrefix());
	}
	public static function lastTestedSnapshot(): LightsailSnapshot {
		return LightsailSnapshot::mostRecentStartingWith(static::instancePrefix()."-tested-");
	}
	public function createTestedSnapshot(): Result{
		return $this->createSnapshot("tested"."-".QMAPIRepo::getLongCommitShaHash());
	}
	public function createSnapshot(string $suffix): Result{
		$snapshotName = static::instancePrefix()."-".$suffix;
		$res = QMLightsailClient::client()->createInstanceSnapshot([
			'instanceName' => $this->getNameAttribute(),
			"instanceSnapshotName" => $snapshotName
		]);
		return $res;
	}
	/**
	 * @param mixed $lightsailInstance
	 */
	public function setLightsailInstance(LightsailInstanceResponse $lightsailInstance){
		$this->lightsailInstance = $lightsailInstance;
	}
	public function getUser(): string{
		try {
			return $this->user = $this->getLightsailInstance()->getUser();
		} catch (NotFoundException $e){
		    return $this->getTagValue(LightsailInstanceResponse::TAG_USERNAME);
		}
	}
}
