<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseUserVariablePredictorCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\UserVariablePredictorCategory
 * @property int $id
 * @property int $user_variable_id
 * @property int $variable_id
 * @property int $variable_category_id
 * @property int $number_of_predictor_user_variables
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @property-read UserVariable $user_variable
 * @property-read Variable $variable
 * @property-read VariableCategory $variable_category
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|UserVariablePredictorCategory newModelQuery()
 * @method static Builder|UserVariablePredictorCategory newQuery()
 * @method static Builder|UserVariablePredictorCategory query()
 * @method static Builder|UserVariablePredictorCategory whereCreatedAt($value)
 * @method static Builder|UserVariablePredictorCategory whereDeletedAt($value)
 * @method static Builder|UserVariablePredictorCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVariablePredictorCategory
 *     whereNumberOfPredictorUserVariables($value)
 * @method static Builder|UserVariablePredictorCategory whereUpdatedAt($value)
 * @method static Builder|UserVariablePredictorCategory whereUserVariableId($value)
 * @method static Builder|UserVariablePredictorCategory whereVariableCategoryId($value)
 * @method static Builder|UserVariablePredictorCategory whereVariableId($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class UserVariablePredictorCategory extends BaseUserVariablePredictorCategory {

}
