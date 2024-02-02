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
use App\Models\OAClient;
use App\Models\Phrase;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BasePhrase
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property int $id
 * @property string $image
 * @property string $text
 * @property string $title
 * @property string $type
 * @property Carbon $updated_at
 * @property string $url
 * @property int $user_id
 * @property int $responding_to_phrase_id
 * @property int $response_phrase_id
 * @property string $recipient_user_ids
 * @property int $number_of_times_heard
 * @property float $interpretative_confidence
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePhrase onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase
 *     whereInterpretativeConfidence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereNumberOfTimesHeard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereRecipientUserIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereRespondingToPhraseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereResponsePhraseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BasePhrase whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePhrase withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BasePhrase withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BasePhrase extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_INTERPRETATIVE_CONFIDENCE = 'interpretative_confidence';
	public const FIELD_NUMBER_OF_TIMES_HEARD = 'number_of_times_heard';
	public const FIELD_IS_PUBLIC = 'is_public';
	public const FIELD_RECIPIENT_USER_IDS = 'recipient_user_ids';
	public const FIELD_RESPONDING_TO_PHRASE_ID = 'responding_to_phrase_id';
	public const FIELD_RESPONSE_PHRASE_ID = 'response_phrase_id';
	public const FIELD_TEXT = 'text';
	public const FIELD_TITLE = 'title';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_URL = 'url';
	public const FIELD_USER_ID = 'user_id';
	public const TABLE = 'phrases';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_INTERPRETATIVE_CONFIDENCE => 'float',
		self::FIELD_NUMBER_OF_TIMES_HEARD => 'int',
		self::FIELD_RECIPIENT_USER_IDS => 'string',
		self::FIELD_RESPONDING_TO_PHRASE_ID => 'int',
		self::FIELD_RESPONSE_PHRASE_ID => 'int',
		self::FIELD_TEXT => 'string',
		self::FIELD_TITLE => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_URL => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_IMAGE => 'nullable|max:100',
		self::FIELD_INTERPRETATIVE_CONFIDENCE => 'nullable|numeric',
		self::FIELD_NUMBER_OF_TIMES_HEARD => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_RECIPIENT_USER_IDS => 'nullable|max:65535',
		self::FIELD_RESPONDING_TO_PHRASE_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_RESPONSE_PHRASE_ID => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_TEXT => 'required|max:65535',
		self::FIELD_TITLE => 'nullable|max:80',
		self::FIELD_TYPE => 'required|max:80',
		self::FIELD_URL => 'nullable|max:100',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_CLIENT_ID => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_ID => '',
		self::FIELD_IMAGE => '',
		self::FIELD_TEXT => '',
		self::FIELD_TITLE => '',
		self::FIELD_TYPE => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_URL => '',
		self::FIELD_USER_ID => '',
		self::FIELD_RESPONDING_TO_PHRASE_ID => '',
		self::FIELD_RESPONSE_PHRASE_ID => '',
		self::FIELD_RECIPIENT_USER_IDS => '',
		self::FIELD_NUMBER_OF_TIMES_HEARD => '',
		self::FIELD_INTERPRETATIVE_CONFIDENCE => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Phrase::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Phrase::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Phrase::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Phrase::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Phrase::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Phrase::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Phrase::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Phrase::FIELD_USER_ID);
	}
}
