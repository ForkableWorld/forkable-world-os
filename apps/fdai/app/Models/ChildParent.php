<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseChildParent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\ChildParent
 * @property int $id
 * @property int $child_user_id The child who has granted data access to the parent.
 * @property int $parent_user_id The parent who has been granted access to the child data.
 * @property string $scopes Whether the parent has read access and/or write access to the data.
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $child_user
 * @property mixed $raw

 * @property-read User $parent_user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|ChildParent newModelQuery()
 * @method static Builder|ChildParent newQuery()
 * @method static Builder|ChildParent query()
 * @method static Builder|ChildParent whereChildUserId($value)
 * @method static Builder|ChildParent whereCreatedAt($value)
 * @method static Builder|ChildParent whereDeletedAt($value)
 * @method static Builder|ChildParent whereId($value)
 * @method static Builder|ChildParent whereParentUserId($value)
 * @method static Builder|ChildParent whereScopes($value)
 * @method static Builder|ChildParent whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class ChildParent extends BaseChildParent {
	const CLASS_CATEGORY = "Data Sharing";
}
