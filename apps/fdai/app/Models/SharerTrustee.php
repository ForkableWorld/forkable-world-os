<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseSharerTrustee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\SharerTrustee
 * @property int $id
 * @property int $sharer_user_id The sharer who has granted data access to the trustee.
 * @property int $trustee_user_id The trustee who has been granted access to the sharer data.
 * @property string $scopes Whether the trustee has read access and/or write access to the data.
 * @property string $relationship_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @property-read User $sharer_user
 * @property-read User $trustee_user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|SharerTrustee newModelQuery()
 * @method static Builder|SharerTrustee newQuery()
 * @method static Builder|SharerTrustee query()
 * @method static Builder|SharerTrustee whereCreatedAt($value)
 * @method static Builder|SharerTrustee whereDeletedAt($value)
 * @method static Builder|SharerTrustee whereId($value)
 * @method static Builder|SharerTrustee whereRelationshipType($value)
 * @method static Builder|SharerTrustee whereScopes($value)
 * @method static Builder|SharerTrustee whereSharerUserId($value)
 * @method static Builder|SharerTrustee whereTrusteeUserId($value)
 * @method static Builder|SharerTrustee whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class SharerTrustee extends BaseSharerTrustee {
	const CLASS_CATEGORY = OAClient::CLASS_CATEGORY;

}
