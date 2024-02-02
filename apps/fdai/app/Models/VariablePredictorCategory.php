<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseVariablePredictorCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\VariablePredictorCategory
 * @property int $id
 * @property int $variable_id
 * @property int $variable_category_id
 * @property int $number_of_predictor_variables
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
 * @method static Builder|VariablePredictorCategory newModelQuery()
 * @method static Builder|VariablePredictorCategory newQuery()
 * @method static Builder|VariablePredictorCategory query()
 * @method static Builder|VariablePredictorCategory whereCreatedAt($value)
 * @method static Builder|VariablePredictorCategory whereDeletedAt($value)
 * @method static Builder|VariablePredictorCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VariablePredictorCategory
 *     whereNumberOfPredictorVariables($value)
 * @method static Builder|VariablePredictorCategory whereUpdatedAt($value)
 * @method static Builder|VariablePredictorCategory whereVariableCategoryId($value)
 * @method static Builder|VariablePredictorCategory whereVariableId($value)
 * @mixin \Eloquent
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class VariablePredictorCategory extends BaseVariablePredictorCategory {

}
