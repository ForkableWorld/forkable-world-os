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
use App\Models\SentEmail;
use App\Models\WpPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseSentEmail
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property string $client_id
 * @property string $slug
 * @property string $response
 * @property string $content
 * @property int $wp_post_id
 * @property string $email_address
 * @property string $subject
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @property WpPost $wp_post
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSentEmail onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereEmailAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseSentEmail whereWpPostId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSentEmail withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseSentEmail withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseSentEmail extends BaseModel {
	use SoftDeletes;
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONTENT = 'content';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_EMAIL_ADDRESS = 'email_address';
	public const FIELD_ID = 'id';
	public const FIELD_RESPONSE = 'response';
	public const FIELD_SLUG = 'slug';
	public const FIELD_SUBJECT = 'subject';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_WP_POST_ID = 'wp_post_id';
	public const TABLE = 'sent_emails';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_CONTENT => 'string',
		self::FIELD_EMAIL_ADDRESS => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_RESPONSE => 'string',
		self::FIELD_SLUG => 'string',
		self::FIELD_SUBJECT => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_USER_ID => 'int',
		self::FIELD_WP_POST_ID => 'int',	];
	protected array $rules = [
		self::FIELD_CLIENT_ID => 'nullable|max:255',
		self::FIELD_CONTENT => 'nullable|max:65535',
		self::FIELD_EMAIL_ADDRESS => 'nullable|max:255',
		self::FIELD_RESPONSE => 'nullable|max:140',
		self::FIELD_SLUG => 'nullable|max:100',
		self::FIELD_SUBJECT => 'required|max:78',
		self::FIELD_TYPE => 'required|max:100',
		self::FIELD_USER_ID => 'nullable|numeric|min:0',
		self::FIELD_WP_POST_ID => 'nullable|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ID => '',
		self::FIELD_USER_ID => '',
		self::FIELD_TYPE => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_SLUG => '',
		self::FIELD_RESPONSE => '',
		self::FIELD_CONTENT => '',
		self::FIELD_WP_POST_ID => '',
		self::FIELD_EMAIL_ADDRESS => '',
		self::FIELD_SUBJECT => 'A Subject Line is the introduction that identifies the emails intent.
                    This subject line, displayed to the email user or recipient when they look at their list of messages in their inbox,
                    should tell the recipient what the message is about, what the sender wants to convey.',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => SentEmail::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => SentEmail::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => SentEmail::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => SentEmail::FIELD_USER_ID,
			'methodName' => 'user',
		],
		'wp_post' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => WpPost::class,
			'foreignKeyColumnName' => 'wp_post_id',
			'foreignKey' => SentEmail::FIELD_WP_POST_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => WpPost::FIELD_ID,
			'ownerKeyColumnName' => 'wp_post_id',
			'ownerKey' => SentEmail::FIELD_WP_POST_ID,
			'methodName' => 'wp_post',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, SentEmail::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			SentEmail::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, SentEmail::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			SentEmail::FIELD_USER_ID);
	}
	public function wp_post(): BelongsTo{
		return $this->belongsTo(WpPost::class, SentEmail::FIELD_WP_POST_ID, WpPost::FIELD_ID,
			SentEmail::FIELD_WP_POST_ID);
	}
}
