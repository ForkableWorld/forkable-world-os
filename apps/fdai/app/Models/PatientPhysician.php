<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BasePatientPhysician;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\PatientPhysician
 * @property int $id
 * @property int $patient_user_id The patient who has granted data access to the physician.
 * @property int $physician_user_id The physician who has been granted access to the patients data.
 * @property string $scopes Whether the physician has read access and/or write access to the data.
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @property-read User $patient_user
 * @property-read User $physician_user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|PatientPhysician newModelQuery()
 * @method static Builder|PatientPhysician newQuery()
 * @method static Builder|PatientPhysician query()
 * @method static Builder|PatientPhysician whereCreatedAt($value)
 * @method static Builder|PatientPhysician whereDeletedAt($value)
 * @method static Builder|PatientPhysician whereId($value)
 * @method static Builder|PatientPhysician wherePatientUserId($value)
 * @method static Builder|PatientPhysician wherePhysicianUserId($value)
 * @method static Builder|PatientPhysician whereScopes($value)
 * @method static Builder|PatientPhysician whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class PatientPhysician extends BasePatientPhysician {
	const CLASS_CATEGORY = OAClient::CLASS_CATEGORY;

}
