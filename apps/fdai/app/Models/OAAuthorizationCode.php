<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseOAAuthorizationCode;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\OAAuthorizationCode
 * @property string $authorization_code
 * @property string $client_id
 * @property int $user_id
 * @property string|null $redirect_uri
 * @property Carbon|null $expires
 * @property string|null $scope
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @method static Builder|OAAuthorizationCode newModelQuery()
 * @method static Builder|OAAuthorizationCode newQuery()
 * @method static Builder|OAAuthorizationCode query()
 * @method static Builder|OAAuthorizationCode whereAuthorizationCode($value)
 * @method static Builder|OAAuthorizationCode whereClientId($value)
 * @method static Builder|OAAuthorizationCode whereCreatedAt($value)
 * @method static Builder|OAAuthorizationCode whereDeletedAt($value)
 * @method static Builder|OAAuthorizationCode whereExpires($value)
 * @method static Builder|OAAuthorizationCode whereRedirectUri($value)
 * @method static Builder|OAAuthorizationCode whereScope($value)
 * @method static Builder|OAAuthorizationCode whereUpdatedAt($value)
 * @method static Builder|OAAuthorizationCode whereUserId($value)
 * @mixin \Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 */
class OAAuthorizationCode extends BaseOAAuthorizationCode {
	use HasUser;
	public const CLASS_DESCRIPTION = "The authorization code is a temporary code that the client will exchange for an access token. ";
	const CLASS_CATEGORY = OAClient::CLASS_CATEGORY;
	public const FIELD_ID = self::FIELD_AUTHORIZATION_CODE;
	public const FONT_AWESOME = FontAwesome::KEY_SOLID;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_KEY;
    public $hidden = [];
	public $fillable = [
		self::FIELD_AUTHORIZATION_CODE,
		self::FIELD_CLIENT_ID,
		self::FIELD_USER_ID,
		self::FIELD_REDIRECT_URI,
		self::FIELD_EXPIRES,
		self::FIELD_SCOPE,
	];
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
	public static function getUniqueIndexColumns(): array{
		return [static::FIELD_ID];
	}
}
