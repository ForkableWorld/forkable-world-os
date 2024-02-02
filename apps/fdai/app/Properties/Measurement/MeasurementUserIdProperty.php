<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
use App\Slim\Middleware\QMAuth;
class MeasurementUserIdProperty extends BaseUserIdProperty
{
    use MeasurementProperty;
    use HasUserFilter;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return int
	 */
	public function pluckAndSetDBValue($data, bool $fallback = false): int {
        $val = static::pluckOrDefault($data);
        if(!$val){
            $variable = $this->getUserVariable();
            $val = $variable->user_id;
        }
        $this->setRawAttribute($val);
        return $val;
    }
    public function showOnIndex(): bool {return QMAuth::canSeeOtherUsers();}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}

}
