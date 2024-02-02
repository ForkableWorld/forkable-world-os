<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\AccessTokenExpiredException;
use App\Models\Base\BaseUnitConversion;
use App\Slim\Middleware\QMAuth;
use App\Traits\HasModel\HasUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\UnitConversion
 * @property int $unit_id
 * @property int $step_number step in the conversion process
 * @property int $operation 0 is add and 1 is multiply
 * @property float $value number used in the operation
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|UnitConversion newModelQuery()
 * @method static Builder|UnitConversion newQuery()
 * @method static Builder|UnitConversion query()
 * @method static Builder|UnitConversion whereClientId($value)
 * @method static Builder|UnitConversion whereCreatedAt($value)
 * @method static Builder|UnitConversion whereDeletedAt($value)
 * @method static Builder|UnitConversion whereOperation($value)
 * @method static Builder|UnitConversion whereStepNumber($value)
 * @method static Builder|UnitConversion whereUnitId($value)
 * @method static Builder|UnitConversion whereUpdatedAt($value)
 * @method static Builder|UnitConversion whereValue($value)
 * @mixin \Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class UnitConversion extends BaseUnitConversion {
    public const TABLE = false;
    public $table = self::TABLE;
	use HasUnit;
	protected array $rules = [
		self::FIELD_UNIT_ID => 'required|integer|min:1|max:2147483647',
		self::FIELD_STEP_NUMBER => 'required|boolean',
		self::FIELD_OPERATION => 'required|boolean',
		self::FIELD_VALUE => 'required|numeric',
	];

	public function getUnitIdAttribute(): ?int{
		return $this->attributes[self::FIELD_UNIT_ID] ?? null;
	}
    /**
     * @param null $writer
     * @return bool
     * @throws AccessTokenExpiredException
     */
    public function canCreateMe($writer = null): bool{
        return QMAuth::isAdmin();
    }
}
