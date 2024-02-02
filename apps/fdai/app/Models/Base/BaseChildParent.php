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
use App\Models\ChildParent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseChildParent
 * @property int $id
 * @property int $child_user_id
 * @property int $parent_user_id
 * @property string $scopes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property \App\Models\User $child_user
 * @property \App\Models\User $parent_user
 * @package App\Models\Base
 */
abstract class BaseChildParent extends BaseModel {
	use SoftDeletes;
	public const FIELD_CHILD_USER_ID = 'child_user_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_PARENT_USER_ID = 'parent_user_id';
	public const FIELD_SCOPES = 'scopes';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'child_parents';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CHILD_USER_ID => 'int',
		self::FIELD_ID => 'int',
		self::FIELD_PARENT_USER_ID => 'int',
		self::FIELD_SCOPES => 'string',	];
	protected array $rules = [
		self::FIELD_CHILD_USER_ID => 'required|numeric|min:0',
		self::FIELD_PARENT_USER_ID => 'required|numeric|min:0',
		self::FIELD_SCOPES => 'required|max:2000',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_CHILD_USER_ID => 'The child who has granted data access to the parent. ',
		self::FIELD_PARENT_USER_ID => 'The parent who has been granted access to the child data.',
		self::FIELD_SCOPES => 'Whether the parent has read access and/or write access to the data.',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'child_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'child_user_id',
			'foreignKey' => ChildParent::FIELD_CHILD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'child_user_id',
			'ownerKey' => ChildParent::FIELD_CHILD_USER_ID,
			'methodName' => 'child_user',
		],
		'parent_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'parent_user_id',
			'foreignKey' => ChildParent::FIELD_PARENT_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'parent_user_id',
			'ownerKey' => ChildParent::FIELD_PARENT_USER_ID,
			'methodName' => 'parent_user',
		],
	];
	public function child_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, ChildParent::FIELD_CHILD_USER_ID, \App\Models\User::FIELD_ID,
			ChildParent::FIELD_CHILD_USER_ID);
	}
	public function parent_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, ChildParent::FIELD_PARENT_USER_ID, \App\Models\User::FIELD_ID,
			ChildParent::FIELD_PARENT_USER_ID);
	}
}
