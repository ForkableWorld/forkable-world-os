<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseUserTag;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
/**
 * App\Models\UserTag
 * @property int $id
 * @property int $tagged_variable_id This is the id of the variable being tagged with an ingredient or something.
 * @property int $tag_variable_id This is the id of the ingredient variable whose value is determined based on the
 *     value of the tagged variable.
 * @property float $conversion_factor Number by which we multiply the tagged variable's value to obtain the tag
 *     variable's value
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $client_id
 * @property Carbon|null $deleted_at
 * @method static Builder|UserTag newModelQuery()
 * @method static Builder|UserTag newQuery()
 * @method static Builder|UserTag query()
 * @method static Builder|UserTag whereClientId($value)
 * @method static Builder|UserTag whereConversionFactor($value)
 * @method static Builder|UserTag whereCreatedAt($value)
 * @method static Builder|UserTag whereDeletedAt($value)
 * @method static Builder|UserTag whereId($value)
 * @method static Builder|UserTag whereTagVariableId($value)
 * @method static Builder|UserTag whereTaggedVariableId($value)
 * @method static Builder|UserTag whereUpdatedAt($value)
 * @method static Builder|UserTag whereUserId($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @property-read User $user
 * @property-read Variable $variable
 * @property-read Variable $tag_variable
 * @property-read Variable $tagged_variable
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()

 * @property int|null $tagged_user_variable_id
 * @property int|null $tag_user_variable_id
 * @property mixed $raw
 * @method static Builder|UserTag whereTagUserVariableId($value)
 * @method static Builder|UserTag whereTaggedUserVariableId($value)
 * @property-read UserVariable|null $tag_user_variable
 * @property-read UserVariable|null $tagged_user_variable
 * @property-read OAClient|null $client
 */
class UserTag extends BaseUserTag {
    use HasFactory;

	use HasUser;
	public const CLASS_DESCRIPTION = "User-created variable tags are used to infer the user intake of the different ingredients by just entering the foods. The inferred intake levels will then be used to determine the effects of different nutrients on the user during analysis. ";
	public const FONT_AWESOME = FontAwesome::USER_TAG_SOLID;
	protected array $rules = [
		self::FIELD_TAGGED_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_TAG_VARIABLE_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_CONVERSION_FACTOR => 'required|numeric',
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];

	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	/**
	 * Determine if this resource is available for navigation.
	 * @param Request $request
	 * @return bool
	 */
	public static function availableForNavigation(Request $request): bool{ return true; }
	public function setTaggedUserVariable(UserVariable $uv){
		$this->user_id = $uv->getUserId();
		$this->tagged_user_variable_id = $uv->getId();
		$this->tagged_variable_id = $uv->getVariableIdAttribute();
		$this->setRelationAndAddToMemory('tagged_user_variable', $uv);
		$this->setRelationAndAddToMemory('tagged_variable', $uv->getVariable());
	}
	public function setTagUserVariable(UserVariable $uv){
		$this->user_id = $uv->getUserId();
		$this->tag_user_variable_id = $uv->getId();
		$this->tag_variable_id = $uv->getVariableIdAttribute();
		$this->setRelationAndAddToMemory('tag_user_variable', $uv);
		$this->setRelationAndAddToMemory('tag_variable', $uv->getVariable());
	}
}
