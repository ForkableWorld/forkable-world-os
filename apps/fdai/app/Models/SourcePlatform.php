<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseSourcePlatform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\SourcePlatform
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @method static Builder|SourcePlatform newModelQuery()
 * @method static Builder|SourcePlatform newQuery()
 * @method static Builder|SourcePlatform query()
 * @method static Builder|SourcePlatform whereClientId($value)
 * @method static Builder|SourcePlatform whereCreatedAt($value)
 * @method static Builder|SourcePlatform whereDeletedAt($value)
 * @method static Builder|SourcePlatform whereId($value)
 * @method static Builder|SourcePlatform whereName($value)
 * @method static Builder|SourcePlatform whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OAClient|null $oa_client
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class SourcePlatform extends BaseSourcePlatform {
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;

}
