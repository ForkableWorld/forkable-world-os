<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\QMQueryException;
use App\Models\Base\BaseSentEmail;
use App\Properties\Base\BaseClientIdProperty;
use App\Traits\HasModel\HasUser;
use App\Types\QMStr;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Titasgailius\SearchRelations\SearchesRelations;
/**
 * App\Models\SentEmail
 * @property integer $user_id
 * @property string $type
 * @method static \Illuminate\Database\Query\Builder|SentEmail whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|SentEmail whereType($value)
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|SentEmail newModelQuery()
 * @method static Builder|SentEmail newQuery()
 * @method static Builder|SentEmail query()
 * @method static Builder|SentEmail whereClientId($value)
 * @method static Builder|SentEmail whereCreatedAt($value)
 * @method static Builder|SentEmail whereDeletedAt($value)
 * @method static Builder|SentEmail whereId($value)
 * @method static Builder|SentEmail whereUpdatedAt($value)
 * @mixin Eloquent
 * @property string|null $slug
 * @property string|null $response
 * @property string|null $content
 * @method static Builder|SentEmail whereContent($value)
 * @method static Builder|SentEmail whereResponse($value)
 * @method static Builder|SentEmail whereSlug($value)
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property int|null $wp_post_id
 * @method static Builder|SentEmail whereWpPostId($value)
 * @property string|null $email_address
 * @property-read WpPost $wp_post
 * @method static Builder|SentEmail whereEmailAddress($value)
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property string $subject A Subject Line is the introduction that identifies the emails intent.
 *                     This subject line, displayed to the email user or recipient when they look at their list of
 *     messages in their inbox, should tell the recipient what the message is about, what the sender wants to convey.
 * @method static Builder|SentEmail whereSubject($value)
 * @property-read OAClient|null $client
 */
class SentEmail extends BaseSentEmail {
    use HasFactory;

	use HasUser;
	use SearchesRelations;
	/**
	 * The single value that should be used to represent the resource when being displayed.
	 * @var string
	 */
	public static $title = 'id';
	/**
	 * The columns that should be searched.
	 * @var array
	 */
	public static $search = [//'id',
	];
	//public $with = ['user'];
	/**
	 * The relationship columns that should be searched.
	 * @var array
	 */
	public static $searchRelations = [
		'user' => [User::FIELD_DISPLAY_NAME],
	];
	public static $group = DeviceToken::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION = 'A sent email';
	public const FIELD_CONTENT = 'content';
	public const FIELD_RESPONSE = 'response';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SUBJECT = 'subject';
	public const FIELD_TYPE = 'type';
	public const FONT_AWESOME = FontAwesome::MAIL_BULK_SOLID;
	public const MAX_CONTENT_LENGTH = 65535;
	public const TABLE = 'sent_emails';
	protected array $rules = [
		self::FIELD_USER_ID => 'nullable|numeric|min:1',
		self::FIELD_TYPE => 'required|max:100',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_SLUG => 'nullable|max:100',
		self::FIELD_RESPONSE => 'nullable|max:140',
		self::FIELD_CONTENT => 'required|max:' . self::MAX_CONTENT_LENGTH,
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:1',
		self::FIELD_EMAIL_ADDRESS => 'required|max:255',
	];
	/**
	 * @param array $attributes
	 * @return SentEmail|Model
	 */
	public static function updateUserLastEmailedAtAndCreate(array $attributes = []){
		if($clientId = BaseClientIdProperty::fromRequest(false)){
			$attributes[SentEmail::FIELD_CLIENT_ID] = $clientId;
		}
		if(isset($attributes[self::FIELD_USER_ID])){
			User::getById($attributes[self::FIELD_USER_ID])->updateLastEmailAt();
		}
		$attributes[self::FIELD_CONTENT] =
			QMStr::truncate($attributes[self::FIELD_CONTENT], self::MAX_CONTENT_LENGTH - 1,
				" [TRUNCATED DUE TO DATABASE FIELD SIZE LIMITATION OF " . self::MAX_CONTENT_LENGTH . "]");
		try {
			return parent::create($attributes);
		} catch (QueryException $e) {
			throw new QMQueryException($e);
		}
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public function getFields(): array{
		$fields = [];
		$fields[] = $this->getNameDetailsLink();
		$fields = array_merge($fields, $this->getShowablePropertyFields());
		return $fields;
	}
	public function getNameAttribute(): string{
		return $this->subject;
	}
	public static function getClassNameTitlePlural(): string{
		return "Messages";
	}
	public function getInterestingRelationshipButtons(): array{
		return [$this->getButton()];
	}
}
