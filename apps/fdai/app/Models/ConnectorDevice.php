<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseConnectorDevice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\ConnectorDevice
 * @property int|null $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $image
 * @property string|null $get_it_url
 * @property string|null $short_description
 * @property string|null $long_description
 * @property int|null $enabled
 * @property int|null $oauth
 * @property int|null $qm_client
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $client_id
 * @property Carbon|null $deleted_at
 * @property int|null $is_parent
 * @method static Builder|ConnectorDevice newModelQuery()
 * @method static Builder|ConnectorDevice newQuery()
 * @method static Builder|ConnectorDevice query()
 * @method static Builder|ConnectorDevice whereClientId($value)
 * @method static Builder|ConnectorDevice whereCreatedAt($value)
 * @method static Builder|ConnectorDevice whereDeletedAt($value)
 * @method static Builder|ConnectorDevice whereDisplayName($value)
 * @method static Builder|ConnectorDevice whereEnabled($value)
 * @method static Builder|ConnectorDevice whereGetItUrl($value)
 * @method static Builder|ConnectorDevice whereId($value)
 * @method static Builder|ConnectorDevice whereImage($value)
 * @method static Builder|ConnectorDevice whereIsParent($value)
 * @method static Builder|ConnectorDevice whereLongDescription($value)
 * @method static Builder|ConnectorDevice whereName($value)
 * @method static Builder|ConnectorDevice whereOauth($value)
 * @method static Builder|ConnectorDevice whereQmClient($value)
 * @method static Builder|ConnectorDevice whereShortDescription($value)
 * @method static Builder|ConnectorDevice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 * @property-read OAClient|null $oa_client
 */
class ConnectorDevice extends BaseConnectorDevice {
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;
	public const CLASS_DESCRIPTION = 'Various devices whose data may be obtained from a given connector API';
}
