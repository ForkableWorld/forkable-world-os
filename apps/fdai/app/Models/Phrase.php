<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BasePhrase;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Class Phrase
 * @property string $client_id
 * @property Carbon $created_at
 * @property string $deleted_at
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
 * @property bool $public
 * @property string $recipient_user_ids
 * @property int $number_of_times_heard
 * @property float $interpretative_confidence
 * @property User $user
 * @package App\Models
 * @method static bool|null forceDelete()
 * @method static Builder|Phrase newModelQuery()
 * @method static Builder|Phrase newQuery()
 * @method static \Illuminate\Database\Query\Builder|Phrase onlyTrashed()
 * @method static Builder|Phrase query()
 * @method static bool|null restore()
 * @method static Builder|Phrase whereClientId($value)
 * @method static Builder|Phrase whereCreatedAt($value)
 * @method static Builder|Phrase whereDeletedAt($value)
 * @method static Builder|Phrase whereId($value)
 * @method static Builder|Phrase whereImage($value)
 * @method static Builder|Phrase whereInterpretativeConfidence($value)
 * @method static Builder|Phrase whereNumberOfTimesHeard($value)
 * @method static Builder|Phrase wherePublic($value)
 * @method static Builder|Phrase whereRecipientUserIds($value)
 * @method static Builder|Phrase whereRespondingToPhraseId($value)
 * @method static Builder|Phrase whereResponsePhraseId($value)
 * @method static Builder|Phrase whereText($value)
 * @method static Builder|Phrase whereTitle($value)
 * @method static Builder|Phrase whereType($value)
 * @method static Builder|Phrase whereUpdatedAt($value)
 * @method static Builder|Phrase whereUrl($value)
 * @method static Builder|Phrase whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Phrase withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Phrase withoutTrashed()
 * @mixin Eloquent
 * @property-read OAClient $oa_client
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 */
class Phrase extends BasePhrase {
	use HasUser;
	public const CLASS_DESCRIPTION = 'A statement heard or said by Dr. Roboto. ';
	const CLASS_CATEGORY = "Miscellaneous";
	public const FONT_AWESOME = FontAwesome::COMMENT;
	use SoftDeletes;
	public static function getUniqueIndexColumns(): array{
		return [
			self::FIELD_USER_ID,
			self::FIELD_TYPE,
			self::FIELD_TEXT,
		];
	}
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_IMAGE => 'nullable|max:100',
		self::FIELD_TEXT => 'required|max:65535',
		self::FIELD_TITLE => 'nullable|max:80',
		self::FIELD_TYPE => 'required|max:80',
		self::FIELD_URL => 'nullable|max:100',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_RESPONDING_TO_PHRASE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_RESPONSE_PHRASE_ID => 'nullable|integer|min:1|max:2147483647',
		self::FIELD_IS_PUBLIC => 'nullable|boolean',
		self::FIELD_NUMBER_OF_TIMES_HEARD => 'nullable|integer|min:-2147483648|max:2147483647',
		self::FIELD_INTERPRETATIVE_CONFIDENCE => 'nullable|numeric',
	];
	protected $casts = [
		'user_id' => 'int',
		'responding_to_phrase_id' => 'int',
		'response_phrase_id' => 'int',
		'public' => 'bool',
		'number_of_times_heard' => 'int',
		'interpretative_confidence' => 'float',
		self::FIELD_RECIPIENT_USER_IDS => 'array',
	];

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
