<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\DigitalOcean;
use App\Computers\PhpUnitComputer;
use App\DevOps\Jenkins\Jenkins;
use App\Logging\QMLog;
use App\Types\QMStr;
use DigitalOceanV2\Api\Droplet;
use DigitalOceanV2\Entity\AbstractEntity;
use DigitalOceanV2\Entity\Droplet as DropletEntity;
use DigitalOceanV2\Entity\Image;
use DigitalOceanV2\Entity\Kernel;
use DigitalOceanV2\Entity\Network;
use DigitalOceanV2\Entity\NextBackupWindow;
use DigitalOceanV2\Entity\Region;
use DigitalOceanV2\Entity\Size;
use Illuminate\Support\Collection;
class QMDroplet extends AbstractEntity {
	const NAME_PREFIX = "homestead-docker-";
	public function __construct($parameters = null){
		parent::__construct();
		foreach($parameters as $key => $value){
			$this->$key = $value;
		}
	}
	public const ssh_key_ids = [
		0 => 18627844,
		1 => 1520317,
		2 => 814465,
		3 => 639342,
		4 => 635758,
		5 => 181601,
		6 => 135410,
		7 => 134765,
		8 => 90524,
	];
	public const SIZE_ID_2GB = 's-1vcpu-2gb';
	public const HOMESTEAD_IMAGE_ID = 59243633;
	public const TAGS = ['qm', 'phpunit', 'docker', 'homestead'];
	public const REGION = 'nyc3';
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var int
	 */
	public $memory;
	/**
	 * @var int
	 */
	public $vcpus;
	/**
	 * @var int
	 */
	public $disk;
	/**
	 * @var Region
	 */
	public $region;
	/**
	 * @var Image
	 */
	public $image;
	/**
	 * @var Kernel
	 */
	public $kernel;
	/**
	 * @var Size
	 */
	public $size;
	/**
	 * @var string
	 */
	public $sizeSlug;
	/**
	 * @var bool
	 */
	public $locked;
	/**
	 * @var string
	 */
	public $createdAt;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * @var array
	 */
	public $tags = [];
	/**
	 * @var Network[]
	 */
	public $networks = [];
	/**
	 * @var int[]
	 */
	public $backupIds = [];
	/**
	 * @var string[]
	 */
	public $volumeIds = [];
	/**
	 * @var int[]
	 */
	public $snapshotIds = [];
	/**
	 * @var string[]
	 */
	public $features = [];
	/**
	 * @var bool
	 */
	public $backupsEnabled;
	/**
	 * @var bool
	 */
	public $privateNetworkingEnabled;
	/**
	 * @var bool
	 */
	public $ipv6Enabled;
	/**
	 * @var bool
	 */
	public $virtIOEnabled;
	/**
	 * @var NextBackupWindow
	 */
	public $nextBackupWindow;
	public static function getDropletConfig(): array{
		return [
			'droplet' => [
				'name' => 'homestead-docker',
				'size_id' => 106,
				'region_id' => 8,
				'image_id' => self::HOMESTEAD_IMAGE_ID,
				'ssh_key_ids' => self::ssh_key_ids,
				'volumes' => [],
				'tags' => self::TAGS,
				'fleet_uuid' => 'e5920904-c953-4fcd-af1e-a3fa92575477',
				'vpc_uuid' => null,
				'private_networking' => true,
				'install_agent' => true,
			],
		];
	}
	/**
	 * @param string $namePrefix
	 * @return Collection|QMDroplet[]
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public static function getWhereStartsWith(string $namePrefix){
		$all = QMDroplet::getAll();
		return collect($all)->filter(function($one) use ($namePrefix){
			/** @var DropletEntity $one */
			return stripos($one->name, $namePrefix) === 0;
		});
	}
	public static function destroyWhereStartsWith(string $namePrefix){
		$droplets = self::getWhereStartsWith($namePrefix);
		foreach($droplets as $droplet){
			$droplet->delete();
		}
	}
	/**
	 * @return Droplet
	 */
	public static function api(): Droplet{
		return QMDigitalOcean::droplet();
	}
	public function delete(){
		\App\Logging\ConsoleLog::info("Deleting $this->name");
		self::api()->delete($this->id);
	}
	/**
	 * @param int $totalNeeded
	 * @return QMDroplet[]
	 */
	public static function createDroplets(int $totalNeeded): array{
		$new = [];
		$namePrefix = self::NAME_PREFIX;
		$existing = QMDroplet::getWhereStartsWith($namePrefix);
		$totalBefore = count($existing);
		$highest = collect($existing)->pluck('name')->max();
		if($highest){
			$maxId = QMStr::after($namePrefix, $highest, null);
		} else{
			$maxId = 0;
		}
		$idNumber = (int)$maxId;
		$totalNow = $totalBefore;
		while($totalNow < $totalNeeded){
			$idNumber++;
			$idStr = sprintf('%03d', $idNumber);
			$name = $namePrefix . $idStr;
			$new[] = QMDroplet::createHomesteadDroplet($name);
			$totalNow++;
		}
		return $new;
	}
	public static function createHomesteadDroplet(string $name): DropletEntity{
		\App\Logging\ConsoleLog::info("Creating droplet named $name...");
		$droplet = QMDigitalOcean::droplet();
		// create and return the created Droplet entity using an image slug
		$created =
			$droplet->create($name, self::REGION, self::SIZE_ID_2GB, self::HOMESTEAD_IMAGE_ID, false, false, true,
				self::ssh_key_ids, "", true, [], self::TAGS, true);
		$id = $created->id;
		\App\Logging\ConsoleLog::info("Created $created->name droplet at https://cloud.digitalocean.com/droplets/$id/graphs?i=14a8d8&period=sixHrs");
		return $created;
	}
	/**
	 * @param array $parameters
	 */
	public function build(array $parameters){
		foreach($parameters as $property => $value){
			switch($property) {
				case 'networks':
					if(is_object($value)){
						if(property_exists($value, 'v4')){
							foreach($value->v4 as $subProperty => $subValue){
								$subValue->version = 4;
								$this->networks[] = new Network($subValue);
							}
						}
						if(property_exists($value, 'v6')){
							foreach($value->v6 as $subProperty => $subValue){
								$subValue->version = 6;
								$subValue->cidr = $subValue->netmask;
								$subValue->netmask = null;
								$this->networks[] = new Network($subValue);
							}
						}
					}
					unset($parameters[$property]);
					break;
				case 'kernel':
					if(is_object($value)){
						$this->kernel = new Kernel($value);
					}
					unset($parameters[$property]);
					break;
				case 'size':
					if(is_object($value)){
						$this->size = new Size($value);
					}
					unset($parameters[$property]);
					break;
				case 'region':
					if(is_object($value)){
						$this->region = new Region($value);
					}
					unset($parameters[$property]);
					break;
				case 'image':
					if(is_object($value)){
						$this->image = new Image($value);
					}
					unset($parameters[$property]);
					break;
				case 'next_backup_window':
					$this->nextBackupWindow = new NextBackupWindow($value);
					unset($parameters[$property]);
					break;
			}
		}
		parent::build($parameters);
		if(is_array($this->features) && count($this->features)){
			$this->backupsEnabled = in_array('backups', $this->features);
			$this->virtIOEnabled = in_array('virtio', $this->features);
			$this->privateNetworkingEnabled = in_array('private_networking', $this->features);
			$this->ipv6Enabled = in_array('ipv6', $this->features);
		}
	}
	/**
	 * @param string $createdAt
	 */
	public function setCreatedAt(string $createdAt){
		$this->createdAt = static::convertDateTime($createdAt);
	}
	public function getIP(): string{
		return $this->networks[0]->ipAddress;
	}
	public function getOrCreateJenkinsNode(): void{
		Jenkins::createJenkinsNode('do-' . $this->name, $this->getIP(), PhpUnitComputer::ALL_LABELS, "ubuntu");
	}
	/**
	 * @return QMDroplet[]
	 */
	public static function getAll(): array{
		$all = QMDigitalOcean::droplet()->getAll();
		$qm = [];
		foreach($all as $one){
			$qm[] = new QMDroplet($one);
		}
		return $qm;
	}
}
