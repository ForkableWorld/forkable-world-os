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
use App\Models\Button;
use App\Models\OAClient;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/** Class BaseButton
 * @property string $accessibility_text
 * @property string $action
 * @property string $additional_information
 * @property string $client_id
 * @property string $color
 * @property string $confirmation_text
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 * @property string $function_name
 * @property string $function_parameters
 * @property string $html
 * @property string $element_id
 * @property string $image
 * @property string $input_fields
 * @property string $ion_icon
 * @property string $link
 * @property string $state_name
 * @property string $state_params
 * @property string $success_alert_body
 * @property string $success_alert_title
 * @property string $success_toast_text
 * @property string $text
 * @property string $title
 * @property string $tooltip
 * @property string $type
 * @property Carbon $updated_at
 * @property int $user_id
 * @property int $id
 * @property OAClient $oa_client
 * @property \App\Models\User $user
 * @package App\Models\Base

 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel applyRequestParams($request)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel exclude($columns)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel excludeLargeColumns()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\BaseModel nPerGroup($group, $n = 10)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButton onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereAccessibilityText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereAdditionalInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereConfirmationText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereElementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereFunctionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereFunctionParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereInputFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereIonIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereStateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereStateParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereSuccessAlertBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereSuccessAlertTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereSuccessToastText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereTooltip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Base\BaseButton whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButton withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Base\BaseButton withoutTrashed()
 * @mixin \Eloquent
 * @property mixed $raw
 */
abstract class BaseButton extends BaseModel {
	use SoftDeletes;
	public const FIELD_ACCESSIBILITY_TEXT = 'accessibility_text';
	public const FIELD_ACTION = 'action';
	public const FIELD_ADDITIONAL_INFORMATION = 'additional_information';
	public const FIELD_CLIENT_ID = 'client_id';
	public const FIELD_COLOR = 'color';
	public const FIELD_CONFIRMATION_TEXT = 'confirmation_text';
	public const FIELD_CREATED_AT = 'created_at';
	public const FIELD_DELETED_AT = 'deleted_at';
	public const FIELD_ELEMENT_ID = 'element_id';
	public const FIELD_FUNCTION_NAME = 'function_name';
	public const FIELD_FUNCTION_PARAMETERS = 'function_parameters';
	public const FIELD_HTML = 'html';
	public const FIELD_ID = 'id';
	public const FIELD_IMAGE = 'image';
	public const FIELD_INPUT_FIELDS = 'input_fields';
	public const FIELD_ION_ICON = 'ion_icon';
	public const FIELD_LINK = 'link';
	public const FIELD_STATE_NAME = 'state_name';
	public const FIELD_STATE_PARAMS = 'state_params';
	public const FIELD_SUCCESS_ALERT_BODY = 'success_alert_body';
	public const FIELD_SUCCESS_ALERT_TITLE = 'success_alert_title';
	public const FIELD_SUCCESS_TOAST_TEXT = 'success_toast_text';
	public const FIELD_TEXT = 'text';
	public const FIELD_TITLE = 'title';
	public const FIELD_TOOLTIP = 'tooltip';
	public const FIELD_TYPE = 'type';
	public const FIELD_UPDATED_AT = 'updated_at';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_SLUG = 'slug';
	public const TABLE = 'buttons';
	protected $table = self::TABLE;
	public const CLASS_DESCRIPTION = '';
	public $incrementing = false;
	protected $casts = [
        self::FIELD_CREATED_AT => 'datetime',
        self::FIELD_DELETED_AT => 'datetime',
        self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_ACCESSIBILITY_TEXT => 'string',
		self::FIELD_ACTION => 'string',
		self::FIELD_ADDITIONAL_INFORMATION => 'string',
		self::FIELD_CLIENT_ID => 'string',
		self::FIELD_COLOR => 'string',
		self::FIELD_CONFIRMATION_TEXT => 'string',
		self::FIELD_ELEMENT_ID => 'string',
		self::FIELD_FUNCTION_NAME => 'string',
		self::FIELD_FUNCTION_PARAMETERS => 'string',
		self::FIELD_HTML => 'string',
		self::FIELD_ID => 'int',
		self::FIELD_IMAGE => 'string',
		self::FIELD_INPUT_FIELDS => 'string',
		self::FIELD_ION_ICON => 'string',
		self::FIELD_LINK => 'string',
		self::FIELD_STATE_NAME => 'string',
		self::FIELD_STATE_PARAMS => 'string',
		self::FIELD_SUCCESS_ALERT_BODY => 'string',
		self::FIELD_SUCCESS_ALERT_TITLE => 'string',
		self::FIELD_SUCCESS_TOAST_TEXT => 'string',
		self::FIELD_TEXT => 'string',
		self::FIELD_TITLE => 'string',
		self::FIELD_TOOLTIP => 'string',
		self::FIELD_TYPE => 'string',
		self::FIELD_USER_ID => 'int',	];
	protected array $rules = [
		self::FIELD_ACCESSIBILITY_TEXT => 'nullable|max:100',
		self::FIELD_ACTION => 'nullable|max:20',
		self::FIELD_ADDITIONAL_INFORMATION => 'nullable|max:20',
		self::FIELD_CLIENT_ID => 'required|max:80',
		self::FIELD_COLOR => 'nullable|max:20',
		self::FIELD_CONFIRMATION_TEXT => 'nullable|max:100',
		self::FIELD_ELEMENT_ID => 'required|max:80',
		self::FIELD_FUNCTION_NAME => 'nullable|max:20',
		self::FIELD_FUNCTION_PARAMETERS => 'nullable|max:65535',
		self::FIELD_HTML => 'nullable|max:200',
		self::FIELD_IMAGE => 'nullable|max:100',
		self::FIELD_INPUT_FIELDS => 'nullable|max:65535',
		self::FIELD_ION_ICON => 'nullable|max:20',
		self::FIELD_LINK => 'nullable|max:100',
		self::FIELD_STATE_NAME => 'nullable|max:20',
		self::FIELD_STATE_PARAMS => 'nullable|max:65535',
		self::FIELD_SUCCESS_ALERT_BODY => 'nullable|max:200',
		self::FIELD_SUCCESS_ALERT_TITLE => 'nullable|max:80',
		self::FIELD_SUCCESS_TOAST_TEXT => 'nullable|max:80',
		self::FIELD_TEXT => 'nullable|max:80',
		self::FIELD_TITLE => 'nullable|max:80',
		self::FIELD_TOOLTIP => 'nullable|max:80',
		self::FIELD_TYPE => 'required|max:80',
		self::FIELD_USER_ID => 'required|numeric|min:0',
	];
	protected $hints = [
		self::FIELD_ACCESSIBILITY_TEXT => '',
		self::FIELD_ACTION => '',
		self::FIELD_ADDITIONAL_INFORMATION => '',
		self::FIELD_CLIENT_ID => '',
		self::FIELD_COLOR => '',
		self::FIELD_CONFIRMATION_TEXT => '',
		self::FIELD_CREATED_AT => 'datetime',
		self::FIELD_DELETED_AT => 'datetime',
		self::FIELD_FUNCTION_NAME => '',
		self::FIELD_FUNCTION_PARAMETERS => '',
		self::FIELD_HTML => '',
		self::FIELD_ELEMENT_ID => '',
		self::FIELD_IMAGE => '',
		self::FIELD_INPUT_FIELDS => '',
		self::FIELD_ION_ICON => '',
		self::FIELD_LINK => '',
		self::FIELD_STATE_NAME => '',
		self::FIELD_STATE_PARAMS => '',
		self::FIELD_SUCCESS_ALERT_BODY => '',
		self::FIELD_SUCCESS_ALERT_TITLE => '',
		self::FIELD_SUCCESS_TOAST_TEXT => '',
		self::FIELD_TEXT => '',
		self::FIELD_TITLE => '',
		self::FIELD_TOOLTIP => '',
		self::FIELD_TYPE => '',
		self::FIELD_UPDATED_AT => 'datetime',
		self::FIELD_USER_ID => '',
		self::FIELD_ID => '',
	];
	protected array $relationshipInfo = [
		'oa_client' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => OAClient::class,
			'foreignKeyColumnName' => 'client_id',
			'foreignKey' => Button::FIELD_CLIENT_ID,
			'otherKeyColumnName' => 'client_id',
			'otherKey' => OAClient::FIELD_CLIENT_ID,
			'ownerKeyColumnName' => 'client_id',
			'ownerKey' => Button::FIELD_CLIENT_ID,
			'methodName' => 'oa_client',
		],
		'user' => [
			'relationshipType' => 'BelongsTo',
			'qualifiedUserClassName' => \App\Models\User::class,
			'foreignKeyColumnName' => 'user_id',
			'foreignKey' => Button::FIELD_USER_ID,
			'otherKeyColumnName' => 'ID',
			'otherKey' => \App\Models\User::FIELD_ID,
			'ownerKeyColumnName' => 'user_id',
			'ownerKey' => Button::FIELD_USER_ID,
			'methodName' => 'user',
		],
	];
	public function oa_client(): BelongsTo{
		return $this->belongsTo(OAClient::class, Button::FIELD_CLIENT_ID, OAClient::FIELD_CLIENT_ID,
			Button::FIELD_CLIENT_ID);
	}
	public function user(): BelongsTo{
		return $this->belongsTo(\App\Models\User::class, Button::FIELD_USER_ID, \App\Models\User::FIELD_ID,
			Button::FIELD_USER_ID);
	}
}
