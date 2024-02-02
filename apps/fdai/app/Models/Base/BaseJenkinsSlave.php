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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BaseJenkinsSlave
 *
 * @property int $id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property string $_class
 * @property string $actions
 * @property string $assigned_labels
 * @property string $description
 * @property string $display_name
 * @property string $executors
 * @property string $icon
 * @property string $icon_class_name
 * @property bool $idle
 * @property bool $jnlp_agent
 * @property bool $launch_supported
 * @property string $load_statistics
 * @property bool $manual_launch_allowed
 * @property string $monitor_data
 * @property int $num_executors
 * @property bool $offline
 * @property string $offline_cause_reason
 * @property string $one_off_executors
 * @property bool $temporarily_offline
 * @property string $absolute_remote_path
 * @property int $lightsail_instance_id
 *
 * @property LightsailInstance $lightsail_instance
 *
 * @package App\Models\Base
 */
class BaseJenkinsSlave extends BaseModel
{
	use SoftDeletes;
	public const FIELD__CLASS = '_class';
	public const FIELD_ABSOLUTE_REMOTE_PATH = 'absolute_remote_path';
	public const FIELD_ACTIONS = 'actions';
	public const FIELD_ASSIGNED_LABELS = 'assigned_labels';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_DISPLAY_NAME = 'display_name';
	public const FIELD_EXECUTORS = 'executors';
	public const FIELD_ICON = 'icon';
	public const FIELD_ICON_CLASS_NAME = 'icon_class_name';
	public const FIELD_ID = 'id';
	public const FIELD_IDLE = 'idle';
	public const FIELD_JNLP_AGENT = 'jnlp_agent';
	public const FIELD_LAUNCH_SUPPORTED = 'launch_supported';
	public const FIELD_LIGHTSAIL_INSTANCE_ID = 'lightsail_instance_id';
	public const FIELD_LOAD_STATISTICS = 'load_statistics';
	public const FIELD_MANUAL_LAUNCH_ALLOWED = 'manual_launch_allowed';
	public const FIELD_MONITOR_DATA = 'monitor_data';
	public const FIELD_NUM_EXECUTORS = 'num_executors';
	public const FIELD_OFFLINE = 'offline';
	public const FIELD_OFFLINE_CAUSE_REASON = 'offline_cause_reason';
	public const FIELD_ONE_OFF_EXECUTORS = 'one_off_executors';
	public const FIELD_TEMPORARILY_OFFLINE = 'temporarily_offline';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	protected $connection = 'tddd';
	public const TABLE = 'jenkins_slaves';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';

	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ABSOLUTE_REMOTE_PATH => 'string',
		self::FIELD_ACTIONS => 'string',
		self::FIELD_ASSIGNED_LABELS => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_DESCRIPTION => 'string',
		self::FIELD_DISPLAY_NAME => 'string',
		self::FIELD_EXECUTORS => 'string',
		self::FIELD_ICON => 'string',
		self::FIELD_ICON_CLASS_NAME => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IDLE => 'bool',
		self::FIELD_JNLP_AGENT => 'bool',
		self::FIELD_LAUNCH_SUPPORTED => 'bool',
		self::FIELD_LIGHTSAIL_INSTANCE_ID => 'int',
		self::FIELD_LOAD_STATISTICS => 'string',
		self::FIELD_MANUAL_LAUNCH_ALLOWED => 'bool',
		self::FIELD_MONITOR_DATA => 'string',
		self::FIELD_NUM_EXECUTORS => 'int',
		self::FIELD_OFFLINE => 'bool',
		self::FIELD_OFFLINE_CAUSE_REASON => 'string',
		self::FIELD_ONE_OFF_EXECUTORS => 'string',
		self::FIELD_TEMPORARILY_OFFLINE => 'bool',
		self::FIELD_USER_ID => 'int',
		self::FIELD__CLASS => 'string'
	];

	protected array $rules = [
		self::FIELD_ABSOLUTE_REMOTE_PATH => 'required|max:255',
		self::FIELD_ACTIONS => 'required',
		self::FIELD_ASSIGNED_LABELS => 'required',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_DESCRIPTION => 'required|max:255',
		self::FIELD_DISPLAY_NAME => 'required|max:255|unique:jenkins_slaves,display_name',
		self::FIELD_EXECUTORS => 'required',
		self::FIELD_ICON => 'required|max:255',
		self::FIELD_ICON_CLASS_NAME => 'required|max:255',
		self::FIELD_IDLE => 'required|boolean',
		self::FIELD_JNLP_AGENT => 'required|boolean',
		self::FIELD_LAUNCH_SUPPORTED => 'required|boolean',
		self::FIELD_LIGHTSAIL_INSTANCE_ID => 'nullable|integer|min:0|max:2147483647|unique:jenkins_slaves,lightsail_instance_id',
		self::FIELD_LOAD_STATISTICS => 'required',
		self::FIELD_MANUAL_LAUNCH_ALLOWED => 'required|boolean',
		self::FIELD_MONITOR_DATA => 'required',
		self::FIELD_NUM_EXECUTORS => 'required|integer|min:-2147483648|max:2147483647',
		self::FIELD_OFFLINE => 'required|boolean',
		self::FIELD_OFFLINE_CAUSE_REASON => 'required|max:255',
		self::FIELD_ONE_OFF_EXECUTORS => 'required',
		self::FIELD_TEMPORARILY_OFFLINE => 'required|boolean',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD__CLASS => 'required|max:255'
	];
	protected $hints = [
		self::FIELD_ID => 'Automatically generated unique id for the jenkins slave',
		self::FIELD_CLIENT_ID => 'The ID for the API client that created the record',
		self::FIELD_CREATED_AT => 'The time the record was originally created',
		self::FIELD_DELETED_AT => 'The time the record was deleted',
		self::FIELD_UPDATED_AT => 'The time the record was last modified',
		self::FIELD_USER_ID => 'The user ID for the owner of the record',
		self::FIELD__CLASS => 'Example: hudson.slaves.SlaveComputer',
		self::FIELD_ACTIONS => 'Example: []',
		self::FIELD_ASSIGNED_LABELS => 'Example: [{name:APP_ENV=testing},{name:ARN=arn:aws:lightsail:us-east-1:335072289018:Instance/1ebbd6d9-37d0-4cf8-acd9-53ac15b8e497},{name:BLUEPRINT_ID=ubuntu_18_04},{name:BLUEPRINT_NAME=Ubuntu},{name:BUNDLE_ID=small_2_0},{name:DISK_SIZE_IN_GB=60},{name:HARDWARE_CPU_COUNT=1},{name:HARDWARE_RAM_SIZE_IN_GB=2},{name:IP_ADDRESS_TYPE=dualstack},{name:LOCATION_AVAILABILITY_ZONE=us-east-1a},{name:LOCATION_REGION_NAME=us-east-1},{name:NAME=phpunit-1},{name:PLATFORM=lightsail},{name:PORT_22=0.0.0.0/0},{name:PORT_80=0.0.0.0/0},{name:PRIVATE_IP_ADDRESS=172.26.5.208},{name:PUBLIC_IP_ADDRESS=54.221.91.117},{name:RESOURCE_TYPE=Instance},{name:SSH_KEY_NAME=qm-aws-20160528},{name:SUPPORT_CODE=102336889266/i-066699cc3e4dd41ba},{name:USERNAME=ubuntu},{name:docker},{name:nodejs},{name:phpunit},{name:phpunit-1},{name:phpunit-jobs},{name:staging-phpunit},{name:tideways}]',
		self::FIELD_DESCRIPTION => 'Example: http://54.221.91.117:8888',
		self::FIELD_DISPLAY_NAME => 'Example: phpunit-1',
		self::FIELD_EXECUTORS => 'Example: [{}]',
		self::FIELD_ICON => 'Example: computer.png',
		self::FIELD_ICON_CLASS_NAME => 'Example: icon-computer',
		self::FIELD_IDLE => 'Example: 1',
		self::FIELD_JNLP_AGENT => 'Example: ',
		self::FIELD_LAUNCH_SUPPORTED => 'Example: 1',
		self::FIELD_LOAD_STATISTICS => 'Example: {_class:hudson.model.Label$1}',
		self::FIELD_MANUAL_LAUNCH_ALLOWED => 'Example: 1',
		self::FIELD_MONITOR_DATA => 'Example: {hudson.node_monitors.SwapSpaceMonitor:{_class:hudson.node_monitors.SwapSpaceMonitor$MemoryUsage2,availablePhysicalMemory:412086272,availableSwapSpace:1916530688,totalPhysicalMemory:2088685568,totalSwapSpace:2147479552},hudson.node_monitors.TemporarySpaceMonitor:{_class:hudson.node_monitors.DiskSpaceMonitorDescriptor$DiskSpace,timestamp:1635038385117,path:/tmp,size:33616867328},hudson.node_monitors.DiskSpaceMonitor:{_class:hudson.node_monitors.DiskSpaceMonitorDescriptor$DiskSpace,timestamp:1635038382901,path:/home/ubuntu,size:33616867328},hudson.node_monitors.ArchitectureMonitor:Linux (amd64),hudson.node_monitors.ResponseTimeMonitor:{_class:hudson.node_monitors.ResponseTimeMonitor$Data,timestamp:1635038382910,average:605},hudson.node_monitors.ClockMonitor:{_class:hudson.util.ClockDifference,diff:-155}}',
		self::FIELD_NUM_EXECUTORS => 'Example: 1',
		self::FIELD_OFFLINE => 'Example: ',
		self::FIELD_OFFLINE_CAUSE_REASON => 'Example: ',
		self::FIELD_ONE_OFF_EXECUTORS => 'Example: []',
		self::FIELD_TEMPORARILY_OFFLINE => 'Example: ',
		self::FIELD_ABSOLUTE_REMOTE_PATH => 'Example: /home/ubuntu',
		self::FIELD_LIGHTSAIL_INSTANCE_ID => ''
	];
	
}
