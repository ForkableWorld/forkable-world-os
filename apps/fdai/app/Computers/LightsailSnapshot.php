<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Computers;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseNameProperty;
use App\ShellCommands\CommandFailureException;
use App\ShellCommands\OfflineException;
use App\Traits\LoggerTrait;
use Illuminate\Support\Collection;
class LightsailSnapshot {
    use LoggerTrait;
	public $name;
	public $arn;
	public $supportCode;
	public $createdAt;
	public $location;
	public $resourceType;
	public $tags;
	public $state;
	public $fromAttachedDisks;
	public $fromInstanceName;
	public $fromInstanceArn;
	public $fromBlueprintId;
	public $fromBundleId;
	public $isFromAutoSnapshot;
	public $sizeInGb;
    /**
     * @param array|object $obj
     */
    public function __construct($obj = null){
        if($obj){
        	foreach($obj as $key => $value){
        		$this->$key = $value;
        		//ConsoleLog::info("public $$key;");
        	}
        }
    }
	public function delete(){
		$result = QMLightsailClient::client()->deleteInstanceSnapshot([
			'instanceName' => $this->getNameAttribute(),
			'regionName' => LightsailInstanceResponse::REGION_NAME_US_EAST_1
		]);
		$arr = $result->toArray();
		\App\Logging\ConsoleLog::info("Deleted $this->name " . $arr["operations"][0]["status"]);
	}
	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public static function find(string $name){
		return BaseNameProperty::findInArray($name, static::all());
	}
	/**
	 * @param string $prefix
	 * @return static[]|Collection
	 */
	public static function getStartingWith(string $prefix): Collection{
		return BaseNameProperty::filterWhereStartsWith($prefix, static::all());
	}
	/**
	 * @return string
	 */
	public function __toString() {
        return $this->name;
    }
	/**
	 * @return string
	 */
	public function getNameAttribute(): string{
		return $this->name;
	}
	/**
	 * @return static[]
	 */
	public static function all(): array{
		return QMLightsailClient::all('InstanceSnapshots', static::class);
	}
	public static function mostRecentStartingWith(string $prefix): ?self {
		$matches = static::getStartingWith($prefix);
		$sorted = $matches->sortByDesc(fn(LightsailSnapshot $one) => $one->createdAt);
		return $sorted->first();
	}
	/**
	 * @param string $name
	 * @return LightsailInstanceResponse
	 */
	public function launchNewInstance(string $name): LightsailInstanceResponse {
		$data = [
			'blueprintId'          => $this->fromBlueprintId,
			'blueprintTab'         => 0,
			'bundleId'             => $this->fromBundleId,
			'copies'               => 1,
			'keyPairName'          => 'qm-aws-20160528',
			'location'             => [
				'regionName'        => LightsailInstanceResponse::REGION_NAME_US_EAST_1,
				'availabilityZone'  => LightsailInstanceResponse::AVAILABILITY_ZONE_US_EAST_1A,
				'regionDisplayName' => 'Virginia',
				'flagName'          => 'usa',
			],
			'name'                 => $name,
			'platform'             => 'LINUX_UNIX',
			'userData'             => '',
			'tags'                 => $this->tags,
			'addOns'               => [],
			'blueprintVersionCode' => '1',
			'blueprintName'        => 'Ubuntu',
			'minPower'             => 500,
			'attachedDiskMapping'  => [
				$name => [],
			],
			'instanceSnapshotName' => $this->getNameAttribute(),
			'instanceNames'        => [$name],
			'availabilityZone'     => LightsailInstanceResponse::AVAILABILITY_ZONE_US_EAST_1A,
		];
		$result = QMLightsailClient::client()->createInstancesFromSnapshot($data);
		if($result->get('@metadata')['statusCode'] === 200){
			ConsoleLog::info("Created $name!");
		} else {
			le($result);
		}
		$i = LightsailInstanceResponse::findInMemoryOrApi($name);
		while(!$i || !$i->publicIpAddress){
			QMLog::sleep(5, "waiting for IP for $name");
			$i = LightsailInstanceResponse::findInApi($name);
		}
		$i->createJenkinsNode(false);
		//QMLog::sleep(30, "waiting to update firewall for $name");
		//$i->updateFirewall();
		QMLog::importantInfo("Make sure to run \App\PhpUnitJobs\DevOps\LightSailJob::testUpdateFirewallsAndHostnames on $name");
		//$i->getComputer()->updateHostname();
		return $i;
	}
}
