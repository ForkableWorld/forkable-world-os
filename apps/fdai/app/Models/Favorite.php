<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseFavorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Favorite
 * @property int $id
 * @property int $user_id user_id
 * @property string $favoriteable_type
 * @property int $favoriteable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property mixed $raw

 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|Favorite newModelQuery()
 * @method static Builder|Favorite newQuery()
 * @method static Builder|Favorite query()
 * @method static Builder|Favorite whereCreatedAt($value)
 * @method static Builder|Favorite whereFavoriteableId($value)
 * @method static Builder|Favorite whereFavoriteableType($value)
 * @method static Builder|Favorite whereId($value)
 * @method static Builder|Favorite whereUpdatedAt($value)
 * @method static Builder|Favorite whereUserId($value)
 * @mixin \Eloquent
 * @property bool|null $is_public
 * @method static Builder|Favorite whereIsPublic($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class Favorite extends BaseFavorite {
    const TABLE = false;
    public $table = self::TABLE;
	const CLASS_CATEGORY = "Miscellaneous";

}
