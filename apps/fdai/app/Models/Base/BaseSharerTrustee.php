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
use App\Models\BaseModel;
use App\Models\SharerTrustee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseSharerTrustee
 * @property int $id
 * @property int $sharer_user_id
 * @property int $trustee_user_id
 * @property string $scopes
 * @property string $relationship_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property \App\Models\User $sharer_user
 * @property \App\Models\User $trustee_user
 * @package App\Models\Base
 */
abstract class BaseSharerTrustee extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_RELATIONSHIP_TYPE = 'relationship_type';
	public const FIELD_SCOPES = 'scopes';
	public const FIELD_SHARER_USER_ID = 'sharer_user_id';
	public const FIELD_TRUSTEE_USER_ID = 'trustee_user_id';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'sharer_trustees';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_RELATIONSHIP_TYPE => 'string',
		self::FIELD_SCOPES => 'string',
		self::FIELD_SHARER_USER_ID => 'int',
		self::FIELD_TRUSTEE_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_RELATIONSHIP_TYPE => 'required',
		self::FIELD_SCOPES => 'required|max:2000',
		self::FIELD_SHARER_USER_ID => 'required|numeric|min:0',
		self::FIELD_TRUSTEE_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_SHARER_USER_ID => 'The sharer who has granted data access to the trustee. ',
		self::FIELD_TRUSTEE_USER_ID => 'The trustee who has been granted access to the sharer data.',
		self::FIELD_SCOPES => 'Whether the trustee has read access and/or write access to the data.',
		self::FIELD_RELATIONSHIP_TYPE => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'sharer_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'sharer_user_id',
			'foreignKey' => SharerTrustee::FIELD_SHARER_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'sharer_user_id',
			'ownerKey' => SharerTrustee::FIELD_SHARER_USER_ID,
			'methodName' => 'sharer_user',
		],
		'trustee_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'trustee_user_id',
			'foreignKey' => SharerTrustee::FIELD_TRUSTEE_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'trustee_user_id',
			'ownerKey' => SharerTrustee::FIELD_TRUSTEE_USER_ID,
			'methodName' => 'trustee_user',
		],
	];
	public function sharer_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, SharerTrustee::FIELD_SHARER_USER_ID,
			\App\Models\User::FIELD_ID, SharerTrustee::FIELD_SHARER_USER_ID);
	}
	public function trustee_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, SharerTrustee::FIELD_TRUSTEE_USER_ID,
			\App\Models\User::FIELD_ID, SharerTrustee::FIELD_TRUSTEE_USER_ID);
	}
}
