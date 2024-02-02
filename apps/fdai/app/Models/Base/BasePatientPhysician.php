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
use App\Models\PatientPhysician;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BasePatientPhysician
 * @property int $id
 * @property int $patient_user_id
 * @property int $physician_user_id
 * @property string $scopes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property \App\Models\User $patient_user
 * @property \App\Models\User $physician_user
 * @package App\Models\Base
 */
abstract class BasePatientPhysician extends BaseModel {
	use SoftDeletes;
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_PATIENT_USER_ID = 'patient_user_id';
	public const FIELD_PHYSICIAN_USER_ID = 'physician_user_id';
	public const FIELD_SCOPES = 'scopes';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const TABLE = 'patient_physicians';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => 'int',
		self::FIELD_PATIENT_USER_ID => 'int',
		self::FIELD_PHYSICIAN_USER_ID => 'int',
		self::FIELD_SCOPES => 'string',	];
	protected array $rules = [
		self::FIELD_PATIENT_USER_ID => 'required|numeric|min:0',
		self::FIELD_PHYSICIAN_USER_ID => 'required|numeric|min:0',
		self::FIELD_SCOPES => 'required|max:2000',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_PATIENT_USER_ID => 'The patient who has granted data access to the physician. ',
		self::FIELD_PHYSICIAN_USER_ID => 'The physician who has been granted access to the patients data.',
		self::FIELD_SCOPES => 'Whether the physician has read access and/or write access to the data.',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
	];
	protected array $relationshipInfo = [
		'patient_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'patient_user_id',
			'foreignKey' => PatientPhysician::FIELD_PATIENT_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'patient_user_id',
			'ownerKey' => PatientPhysician::FIELD_PATIENT_USER_ID,
			'methodName' => 'patient_user',
		],
		'physician_user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'physician_user_id',
			'foreignKey' => PatientPhysician::FIELD_PHYSICIAN_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'physician_user_id',
			'ownerKey' => PatientPhysician::FIELD_PHYSICIAN_USER_ID,
			'methodName' => 'physician_user',
		],
	];
	public function patient_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, PatientPhysician::FIELD_PATIENT_USER_ID,
			\App\Models\User::FIELD_ID, PatientPhysician::FIELD_PATIENT_USER_ID);
	}
	public function physician_user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, PatientPhysician::FIELD_PHYSICIAN_USER_ID,
			\App\Models\User::FIELD_ID, PatientPhysician::FIELD_PHYSICIAN_USER_ID);
	}
}
