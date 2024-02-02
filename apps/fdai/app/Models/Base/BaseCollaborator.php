<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** @noinspection PhpMissingDocCommentInspection */
/** @noinspection PhpUnused */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
/** Created by Reliese Model.
 */
namespace App\Models\Base;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Collaborator;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCollaborator
 * @property int $id
 * @property int $user_id
 * @property int $app_id
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property Application $application
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCollaborator onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCollaborator whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCollaborator withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCollaborator withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseCollaborator extends BaseModel {
	use SoftDeletes;
	public const FIELD_APP_ID = 'app_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'collaborators';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_APP_ID => 'int',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_TYPE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_APP_ID => 'required|integer|min:0|max:2147483647',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_TYPE => 'required',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_APP_ID => '',
		self::FIELD_TYPE => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
	];
	protected array $relationshipInfo = [
		'application' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => Application::class,
			'foreignKeyColumnName' => 'app_id',
			'foreignKey' => Collaborator::FIELD_APP_ID,
			'otherKeyColumnName' => 'id',
			'otherKey' => Application::FIELD_ID,
			'ownerKeyColumnName' => 'app_id',
			'ownerKey' => Collaborator::FIELD_APP_ID,
			'methodName' => 'application',
		],
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Collaborator::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Collaborator::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Collaborator::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Collaborator::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function application(): BelongsTo{
		return $this->belongsTo(Application::class, Collaborator::FIELD_APP_ID, Application::FIELD_ID,
			Collaborator::FIELD_APP_ID);
	}
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Collaborator::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Collaborator::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Collaborator::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Collaborator::FIELD_USER_ID);
	}
}
