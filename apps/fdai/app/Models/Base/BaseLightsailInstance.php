<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\BaseModel;
use App\Models\JenkinsSlave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\ServerMonitor\Models\Host;
/**
 * Class BaseLightsailInstance
 *
 * @property int $id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property string $name
 * @property string $arn
 * @property string $support_code
 * @property string $location
 * @property string $resource_type
 * @property string $tags
 * @property string $blueprint_id
 * @property string $blueprint_name
 * @property string $bundle_id
 * @property string $add_ons
 * @property bool $is_static_ip
 * @property string $private_ip_address
 * @property string $public_ip_address
 * @property string $ipv6_addresses
 * @property string $ip_address_type
 * @property string $hardware
 * @property string $networking
 * @property string $state
 * @property string $username
 * @property string $ssh_key_name
 * @property string $jenkins_labels
 * @property int $jenkins_slave_id
 *
 * @property Host $host
 * @property JenkinsSlave $jenkins_slave
 *
 * @package App\Models\Base
 */
class BaseLightsailInstance extends BaseModel
{
	use SoftDeletes;
	public const FIELD_ADD_ONS = 'add_ons';
	public const FIELD_ARN = 'arn';
	public const FIELD_BLUEPRINT_ID = 'blueprint_id';
	public const FIELD_BLUEPRINT_NAME = 'blueprint_name';
	public const FIELD_BUNDLE_ID = 'bundle_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_HARDWARE = 'hardware';
	public const FIELD_ID = 'id';
	public const FIELD_IP_ADDRESS_TYPE = 'ip_address_type';
	public const FIELD_IPV6_ADDRESSES = 'ipv6_addresses';
	public const FIELD_IS_STATIC_IP = 'is_static_ip';
	public const FIELD_JENKINS_LABELS = 'jenkins_labels';
	public const FIELD_JENKINS_SLAVE_ID = 'jenkins_slave_id';
	public const FIELD_LOCATION = 'location';
	public const FIELD_NAME = 'name';
	public const FIELD_NETWORKING = 'networking';
	public const FIELD_PRIVATE_IP_ADDRESS = 'private_ip_address';
	public const FIELD_PUBLIC_IP_ADDRESS = 'public_ip_address';
	public const FIELD_RESOURCE_TYPE = 'resource_type';
	public const FIELD_SSH_KEY_NAME = 'ssh_key_name';
	public const FIELD_STATE = 'state';
	public const FIELD_SUPPORT_CODE = 'support_code';
	public const FIELD_TAGS = 'tags';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_USERNAME = 'username';
	protected $connection = 'tddd';
	public const TABLE = 'lightsail_instances';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';

	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ADD_ONS => 'string',
		self::FIELD_ARN => 'string',
		self::FIELD_BLUEPRINT_ID => 'string',
		self::FIELD_BLUEPRINT_NAME => 'string',
		self::FIELD_BUNDLE_ID => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_HARDWARE => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IPV6_ADDRESSES => 'string',
		self::FIELD_IP_ADDRESS_TYPE => 'string',
		self::FIELD_IS_STATIC_IP => 'bool',
		self::FIELD_JENKINS_LABELS => 'string',
		self::FIELD_JENKINS_SLAVE_ID => 'int',
		self::FIELD_LOCATION => 'string',
		self::FIELD_NAME => 'string',
		self::FIELD_NETWORKING => 'string',
		self::FIELD_PRIVATE_IP_ADDRESS => 'string',
		self::FIELD_PUBLIC_IP_ADDRESS => 'string',
		self::FIELD_RESOURCE_TYPE => 'string',
		self::FIELD_SSH_KEY_NAME => 'string',
		self::FIELD_STATE => 'string',
		self::FIELD_SUPPORT_CODE => 'string',
		self::FIELD_TAGS => 'string',
		self::FIELD_USERNAME => 'string',
		self::FIELD_USER_ID => 'int'
	];

	protected array $rules = [
		self::FIELD_ADD_ONS => 'required',
		self::FIELD_ARN => 'required|max:255',
		self::FIELD_BLUEPRINT_ID => 'required|max:255',
		self::FIELD_BLUEPRINT_NAME => 'required|max:255',
		self::FIELD_BUNDLE_ID => 'required|max:255',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_HARDWARE => 'required',
		self::FIELD_IPV6_ADDRESSES => 'required',
		self::FIELD_IP_ADDRESS_TYPE => 'required|max:255',
		self::FIELD_IS_STATIC_IP => 'required|boolean',
		self::FIELD_JENKINS_LABELS => 'required',
		self::FIELD_JENKINS_SLAVE_ID => 'nullable|integer|min:0|max:2147483647|unique:lightsail_instances,jenkins_slave_id',
		self::FIELD_LOCATION => 'required',
		self::FIELD_NAME => 'required|max:255|unique:lightsail_instances,name',
		self::FIELD_NETWORKING => 'required',
		self::FIELD_PRIVATE_IP_ADDRESS => 'required|max:255',
		self::FIELD_PUBLIC_IP_ADDRESS => 'required|max:255',
		self::FIELD_RESOURCE_TYPE => 'required|max:255',
		self::FIELD_SSH_KEY_NAME => 'required|max:255',
		self::FIELD_STATE => 'required',
		self::FIELD_SUPPORT_CODE => 'required|max:255',
		self::FIELD_TAGS => 'required',
		self::FIELD_USERNAME => 'required|max:255',
		self::FIELD_USER_ID => 'required|numeric|min:0'
	];
	protected $hints = [
		self::FIELD_ID => 'Automatically generated unique id for the lightsail instance',
		self::FIELD_CLIENT_ID => 'The ID for the API client that created the record',
		self::FIELD_CREATED_AT => 'The time the record was originally created',
		self::FIELD_DELETED_AT => 'The time the record was deleted',
		self::FIELD_UPDATED_AT => 'The time the record was last modified',
		self::FIELD_USER_ID => 'The user ID for the owner of the record',
		self::FIELD_NAME => 'Example: cc-wp',
		self::FIELD_ARN => 'Example: arn:aws:lightsail:us-east-1:335072289018:Instance/14eb6cec-1c74-429a-96f5-8f8f5e5fbbc1',
		self::FIELD_SUPPORT_CODE => 'Example: 102336889266/i-005d61af88d99927e',
		self::FIELD_LOCATION => 'Example: {availabilityZone:us-east-1a,regionName:us-east-1}',
		self::FIELD_RESOURCE_TYPE => 'Example: Instance',
		self::FIELD_TAGS => 'Example: [{key:wordpress},{key:HEALTH_CHECK_URL,value:https://CrowdsourcingCures.org}]',
		self::FIELD_BLUEPRINT_ID => 'Example: wordpress',
		self::FIELD_BLUEPRINT_NAME => 'Example: WordPress',
		self::FIELD_BUNDLE_ID => 'Example: micro_2_0',
		self::FIELD_ADD_ONS => 'Example: [{name:AutoSnapshot,status:Enabled,snapshotTimeOfDay:00:00}]',
		self::FIELD_IS_STATIC_IP => 'Example: 1',
		self::FIELD_PRIVATE_IP_ADDRESS => 'Example: 172.26.7.226',
		self::FIELD_PUBLIC_IP_ADDRESS => 'Example: 3.224.6.200',
		self::FIELD_IPV6_ADDRESSES => 'Example: [2600:1f18:1ae:1700:c92e:b66f:52cd:5f01]',
		self::FIELD_IP_ADDRESS_TYPE => 'Example: dualstack',
		self::FIELD_HARDWARE => 'Example: {cpuCount:1,disks:[{createdAt:2021-03-22T01:47:10+00:00,sizeInGb:40,isSystemDisk:true,iops:120,path:/dev/xvda,attachedTo:cc-wp,attachmentState:attached}],ramSizeInGb:1}',
		self::FIELD_NETWORKING => 'Example: {monthlyTransfer:{gbPerMonthAllocated:2048},ports:[{fromPort:80,toPort:80,protocol:tcp,accessFrom:Anywhere (0.0.0.0/0),accessType:public,commonName:,accessDirection:inbound,cidrs:[0.0.0.0/0],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:8000,toPort:8000,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[97.91.131.8/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:7777,toPort:7777,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[97.91.131.8/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:6379,toPort:6379,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[97.91.131.8/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:888,toPort:888,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[97.91.131.8/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:20,toPort:20,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,c',
		self::FIELD_STATE => 'Example: {code:16,name:running}',
		self::FIELD_USERNAME => 'Example: bitnami',
		self::FIELD_SSH_KEY_NAME => 'Example: qm-aws-20160528',
		self::FIELD_JENKINS_LABELS => 'Example: []',
		self::FIELD_JENKINS_SLAVE_ID => 'Example: '
	];

	protected array $relationshipInfo = [
		'host' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => Host::class,
			'foreignKey' => 'lightsail_instance_id',
			'localKey' => 'id',
			'methodName' => 'host'
		],
		'jenkins_slave' => [
			'relationshipType' => 'HasOne',
			'qualifiedUserClassName' => JenkinsSlave::class,
			'foreignKey' => JenkinsSlave::FIELD_LIGHTSAIL_INSTANCE_ID,
			'localKey' => JenkinsSlave::FIELD_ID,
			'methodName' => 'jenkins_slave'
		]
	];

	public function host(): HasOne
	{
		return $this->hasOne(Host::class);
	}

	public function jenkins_slave(): HasOne
	{
		return $this->hasOne(JenkinsSlave::class, JenkinsSlave::FIELD_LIGHTSAIL_INSTANCE_ID, JenkinsSlave::FIELD_ID);
	}
}
