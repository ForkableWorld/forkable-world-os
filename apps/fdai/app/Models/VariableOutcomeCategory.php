<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseVariableOutcomeCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\VariableOutcomeCategory
 * @property int $id
 * @property int $variable_id
 * @property int $variable_category_id
 * @property int $number_of_outcome_variables
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property mixed $raw

 * @property-read Variable $variable
 * @property-read VariableCategory $variable_category
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|VariableOutcomeCategory newModelQuery()
 * @method static Builder|VariableOutcomeCategory newQuery()
 * @method static Builder|VariableOutcomeCategory query()
 * @method static Builder|VariableOutcomeCategory whereCreatedAt($value)
 * @method static Builder|VariableOutcomeCategory whereDeletedAt($value)
 * @method static Builder|VariableOutcomeCategory whereId($value)
 * @method static Builder|VariableOutcomeCategory whereNumberOfOutcomeVariables($value)
 * @method static Builder|VariableOutcomeCategory whereUpdatedAt($value)
 * @method static Builder|VariableOutcomeCategory whereVariableCategoryId($value)
 * @method static Builder|VariableOutcomeCategory whereVariableId($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class VariableOutcomeCategory extends BaseVariableOutcomeCategory {

}
