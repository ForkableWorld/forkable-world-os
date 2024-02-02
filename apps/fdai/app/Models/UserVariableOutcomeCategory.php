<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseUserVariableOutcomeCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\UserVariableOutcomeCategory
 * @property int $id
 * @property int $user_variable_id
 * @property int $variable_id
 * @property int $variable_category_id
 * @property int $number_of_outcome_user_variables
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
 * @method static Builder|UserVariableOutcomeCategory newModelQuery()
 * @method static Builder|UserVariableOutcomeCategory newQuery()
 * @method static Builder|UserVariableOutcomeCategory query()
 * @method static Builder|UserVariableOutcomeCategory whereCreatedAt($value)
 * @method static Builder|UserVariableOutcomeCategory whereDeletedAt($value)
 * @method static Builder|UserVariableOutcomeCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserVariableOutcomeCategory
 *     whereNumberOfOutcomeUserVariables($value)
 * @method static Builder|UserVariableOutcomeCategory whereUpdatedAt($value)
 * @method static Builder|UserVariableOutcomeCategory whereUserVariableId($value)
 * @method static Builder|UserVariableOutcomeCategory whereVariableCategoryId($value)
 * @method static Builder|UserVariableOutcomeCategory whereVariableId($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class UserVariableOutcomeCategory extends BaseUserVariableOutcomeCategory {

}
