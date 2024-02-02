<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Buttons\QMButton;
use App\Exceptions\ModelValidationException;
use App\Models\Base\BaseButton;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\User\UserIdProperty;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Button
 * @property string|null $accessibility_text
 * @property string|null $action
 * @property string|null $additional_information
 * @property string $client_id
 * @property string|null $color
 * @property string|null $confirmation_text
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property string|null $function_name
 * @property string|null $function_parameters
 * @property string|null $html
 * @property string $id
 * @property string|null $image
 * @property string|null $input_fields
 * @property string|null $ion_icon
 * @property string|null $link
 * @property string|null $state_name
 * @property string|null $state_params
 * @property string|null $success_alert_body
 * @property string|null $success_alert_title
 * @property string|null $success_toast_text
 * @property string|null $text
 * @property string|null $title
 * @property string|null $tooltip
 * @property string $type
 * @property Carbon $updated_at
 * @property int $user_id
 * @method static Builder|Button newModelQuery()
 * @method static Builder|Button newQuery()
 * @method static Builder|Button query()
 * @method static Builder|Button whereAccessibilityText($value)
 * @method static Builder|Button whereAction($value)
 * @method static Builder|Button whereAdditionalInformation($value)
 * @method static Builder|Button whereClientId($value)
 * @method static Builder|Button whereColor($value)
 * @method static Builder|Button whereConfirmationText($value)
 * @method static Builder|Button whereCreatedAt($value)
 * @method static Builder|Button whereDeletedAt($value)
 * @method static Builder|Button whereFunctionName($value)
 * @method static Builder|Button whereFunctionParameters($value)
 * @method static Builder|Button whereHtml($value)
 * @method static Builder|Button whereId($value)
 * @method static Builder|Button whereImage($value)
 * @method static Builder|Button whereInputFields($value)
 * @method static Builder|Button whereIonIcon($value)
 * @method static Builder|Button whereLink($value)
 * @method static Builder|Button whereStateName($value)
 * @method static Builder|Button whereStateParams($value)
 * @method static Builder|Button whereSuccessAlertBody($value)
 * @method static Builder|Button whereSuccessAlertTitle($value)
 * @method static Builder|Button whereSuccessToastText($value)
 * @method static Builder|Button whereText($value)
 * @method static Builder|Button whereTitle($value)
 * @method static Builder|Button whereTooltip($value)
 * @method static Builder|Button whereType($value)
 * @method static Builder|Button whereUpdatedAt($value)
 * @method static Builder|Button whereUserId($value)
 * @mixin \Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @property string $element_id
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|Button whereElementId($value)
 * @property string|null $slug The slug is the part of a URL that identifies a page in human-readable keywords.
 * @property-read OAClient $client
 * @method static Builder|Button whereSlug($value)
 */
class Button extends BaseButton {
	use HasUser;
	const CLASS_CATEGORY = "UI";
	public $primaryKey = self::FIELD_SLUG;
	public const FONT_AWESOME = FontAwesome::HOCKEY_PUCK_SOLID;
	public const CLASS_DESCRIPTION = "Buttons that can trigger an action. ";
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public static function getUniqueIndexColumns(): array{
		return QMButton::UNIQUE_INDEX_COLUMNS;
	}
	/**
	 * @throws ModelValidationException
	 */
	public static function import(){
		$buttons = QMButton::all();
		foreach($buttons as $button){
			$button->getSlug();
			$l = $button->l();
			$l->user_id = UserIdProperty::USER_ID_SYSTEM;
			$l->client_id = BaseClientIdProperty::CLIENT_ID_SYSTEM;
			$button->save();
		}
	}
	public function save(array $options = []): bool{
		return parent::save($options);
	}
}
