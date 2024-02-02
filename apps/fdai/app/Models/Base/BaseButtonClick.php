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
use App\Models\ButtonClick;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseButtonClick
 * @property string $card_id
 * @property string $button_id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property int $id
 * @property string $input_fields
 * @property string $intent_name
 * @property string $parameters
 * @property Carbon $updated_at
 * @property int $user_id
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButtonClick onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereButtonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereInputFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereIntentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButtonClick whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButtonClick withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButtonClick withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseButtonClick extends BaseModel {
	use SoftDeletes;
	public const FIELD_BUTTON_ID = 'button_id';
	public const FIELD_CARD_ID = 'card_id';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_INPUT_FIELDS = 'input_fields';
	public const FIELD_INTENT_NAME = 'intent_name';
	public const FIELD_PARAMETERS = 'parameters';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'button_clicks';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_BUTTON_ID => 'string',
		self::FIELD_CARD_ID => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_INPUT_FIELDS => 'string',
		self::FIELD_INTENT_NAME => 'string',
		self::FIELD_PARAMETERS => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_BUTTON_ID => 'required|max:80',
		self::FIELD_CARD_ID => 'required|max:80',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_INPUT_FIELDS => 'nullable|max:65535',
		self::FIELD_INTENT_NAME => 'nullable|max:80',
		self::FIELD_PARAMETERS => 'nullable|max:65535',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_CARD_ID => '',
		self::FIELD_BUTTON_ID => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => '',
		self::FIELD_INPUT_FIELDS => '',
		self::FIELD_INTENT_NAME => '',
		self::FIELD_PARAMETERS => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => ButtonClick::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => ButtonClick::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => ButtonClick::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => ButtonClick::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, ButtonClick::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			ButtonClick::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, ButtonClick::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			ButtonClick::FIELD_USER_ID);
	}
}
