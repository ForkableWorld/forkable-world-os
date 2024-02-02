<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseButtonClick;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\ButtonClick
 * @property string $card_id
 * @property string $button_id
 * @property string $client_id
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property int $id
 * @property string|null $input_fields
 * @property string|null $intent_name
 * @property string|null $parameters
 * @property Carbon $updated_at
 * @property int $user_id
 * @method static Builder|ButtonClick newModelQuery()
 * @method static Builder|ButtonClick newQuery()
 * @method static Builder|ButtonClick query()
 * @method static Builder|ButtonClick whereButtonId($value)
 * @method static Builder|ButtonClick whereCardId($value)
 * @method static Builder|ButtonClick whereClientId($value)
 * @method static Builder|ButtonClick whereCreatedAt($value)
 * @method static Builder|ButtonClick whereDeletedAt($value)
 * @method static Builder|ButtonClick whereId($value)
 * @method static Builder|ButtonClick whereInputFields($value)
 * @method static Builder|ButtonClick whereIntentName($value)
 * @method static Builder|ButtonClick whereParameters($value)
 * @method static Builder|ButtonClick whereUpdatedAt($value)
 * @method static Builder|ButtonClick whereUserId($value)
 * @mixin Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()+
 * @property-read OAClient $client
 */
class ButtonClick extends BaseButtonClick {
	use HasUser;
	public const CLASS_DESCRIPTION = 'Buttons that a user has clicked. ';
	const CLASS_CATEGORY = "User Activity";
	public const FONT_AWESOME = FontAwesome::HOCKEY_PUCK_SOLID;
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
}
