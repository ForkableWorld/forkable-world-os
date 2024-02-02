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
use App\Models\Card;
use App\Models\OAClient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseCard
 * @property string $action_sheet_buttons
 * @property string $avatar
 * @property string $avatar_circular
 * @property string $background_color
 * @property string $buttons
 * @property string $client_id
 * @property string $content
 * @property Carbon $created_at
 * @property string $deleted_at
 * @property string $header_title
 * @property string $html
 * @property string $html_content
 * @property string $id
 * @property string $image
 * @property string $input_fields
 * @property string $intent_name
 * @property string $ion_icon
 * @property string $link
 * @property string $parameters
 * @property string $sharing_body
 * @property string $sharing_buttons
 * @property string $sharing_title
 * @property string $sub_header
 * @property string $sub_title
 * @property string $title
 * @property string $type
 * @property Carbon $updated_at
 * @property int $user_id
 * @property string $url
 * @property OAClient $oa_client
 * @property User $user
 * @package App\Models\Base
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCard onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereActionSheetButtons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereAvatarCircular($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereBackgroundColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereButtons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereHeaderTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereHtmlContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereInputFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereIntentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereIonIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereSharingBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereSharingButtons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereSharingTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereSubHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCard withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseCard withoutTrashed()
 * @mixin \Eloquent
 * @property string $element_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseCard whereElementId($value)
 */
abstract class BaseCard extends BaseModel {
	use SoftDeletes;
	public const FIELD_ACTION_SHEET_BUTTONS = 'action_sheet_buttons';
	public const FIELD_AVATAR = 'avatar';
	public const FIELD_AVATAR_CIRCULAR = 'avatar_circular';
	public const FIELD_BACKGROUND_COLOR = 'background_color';
	public const FIELD_BUTTONS = 'buttons';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_CONTENT = 'content';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_HEADER_TITLE = 'header_title';
	public const FIELD_HTML = 'html';
	public const FIELD_HTML_CONTENT = 'html_content';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_INPUT_FIELDS = 'input_fields';
	public const FIELD_INTENT_NAME = 'intent_name';
	public const FIELD_ION_ICON = 'ion_icon';
	public const FIELD_LINK = 'link';
	public const FIELD_PARAMETERS = 'parameters';
	public const FIELD_SHARING_BODY = 'sharing_body';
	public const FIELD_SHARING_BUTTONS = 'sharing_buttons';
	public const FIELD_SHARING_TITLE = 'sharing_title';
	public const FIELD_SUB_HEADER = 'sub_header';
	public const FIELD_SUB_TITLE = 'sub_title';
	public const FIELD_TITLE = 'title';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_URL = 'url';
	public const FIELD_USER_ID = 'user_id';
	protected $table = 'cards';
	public const TABLE = 'cards';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ACTION_SHEET_BUTTONS => 'nullable|max:65535',
		self::FIELD_AVATAR => 'nullable|max:100',
		self::FIELD_AVATAR_CIRCULAR => 'nullable|max:100',
		self::FIELD_BACKGROUND_COLOR => 'nullable|max:20',
		self::FIELD_BUTTONS => 'nullable|max:65535',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_CONTENT => 'nullable|max:65535',
		self::FIELD_HEADER_TITLE => 'nullable|max:100',
		self::FIELD_HTML => 'nullable|max:65535',
		self::FIELD_HTML_CONTENT => 'nullable|max:65535',
		self::FIELD_IMAGE => 'nullable|max:100',
		self::FIELD_INPUT_FIELDS => 'nullable|max:65535',
		self::FIELD_INTENT_NAME => 'nullable|max:80',
		self::FIELD_ION_ICON => 'nullable|max:20',
		self::FIELD_LINK => 'nullable|max:2083',
		self::FIELD_PARAMETERS => 'nullable|max:65535',
		self::FIELD_SHARING_BODY => 'nullable|max:65535',
		self::FIELD_SHARING_BUTTONS => 'nullable|max:65535',
		self::FIELD_SHARING_TITLE => 'nullable|max:80',
		self::FIELD_SUB_HEADER => 'nullable|max:80',
		self::FIELD_SUB_TITLE => 'nullable|max:80',
		self::FIELD_TITLE => 'nullable|max:80',
		self::FIELD_TYPE => 'required|max:80',
		self::FIELD_USER_ID => 'required|numeric|min:0',
		self::FIELD_URL => 'nullable|max:2083',
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Card::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Card::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(User::class, Card::FIELD_USER_ID, User::FIELD_ID, Card::FIELD_USER_ID);
	}
}
