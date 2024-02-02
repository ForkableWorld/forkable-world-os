<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseOARefreshToken;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\OARefreshToken
 * @property string $refresh_token
 * @property string $client_id
 * @property int $user_id
 * @property Carbon|null $expires
 * @property string|null $scope
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @method static Builder|OARefreshToken newModelQuery()
 * @method static Builder|OARefreshToken newQuery()
 * @method static Builder|OARefreshToken query()
 * @method static Builder|OARefreshToken whereClientId($value)
 * @method static Builder|OARefreshToken whereCreatedAt($value)
 * @method static Builder|OARefreshToken whereDeletedAt($value)
 * @method static Builder|OARefreshToken whereExpires($value)
 * @method static Builder|OARefreshToken whereRefreshToken($value)
 * @method static Builder|OARefreshToken whereScope($value)
 * @method static Builder|OARefreshToken whereUpdatedAt($value)
 * @method static Builder|OARefreshToken whereUserId($value)
 * @mixin \Eloquent
 * @property-read OAClient $oa_client
 * @property-read User $user
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 */
class OARefreshToken extends BaseOARefreshToken {
	use HasUser;
	public const CLASS_DESCRIPTION = "The Refresh Token grant type is used by clients to exchange a refresh token for an access token when the access token has expired.  ";
	const CLASS_CATEGORY = OAClient::CLASS_CATEGORY;
	public const FIELD_ID = self::FIELD_CLIENT_ID;
	public const FONT_AWESOME = FontAwesome::KEY_SOLID;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_KEY;
	public static function getUniqueIndexColumns(): array{
		return [self::FIELD_REFRESH_TOKEN];
	}
	protected $hidden = [];
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
    /**
     * @param array $attributes
     * @return OARefreshToken
     * @throws \App\Exceptions\ModelValidationException
     */
    public static function findInMemoryDBOrCreate(array $attributes = []): OARefreshToken{
        if($t = static::findInMemoryWhere($attributes)){return $t;}
        $t = static::where($attributes)
            ->where(self::FIELD_EXPIRES, '>', db_date(time()))
            ->orderByDesc(self::FIELD_EXPIRES)
            ->first();
        if($t){return $t;}
        $t = new static();
        $t->forceFill($attributes);
        $t->refresh_token = Str::random(40);
        $t->expires = now()->addDays(60)->unix();
        $t->save();
        return $t;
    }
}
