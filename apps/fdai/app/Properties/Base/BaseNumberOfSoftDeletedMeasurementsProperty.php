<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfSoftDeletedMeasurementsProperty extends BaseProperty{
    use IsInt;
    use IsCalculated;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Formula: update user_variables v
                inner join (
                    select measurements.user_variable_id, count(measurements.id) as number_of_soft_deleted_measurements
                    from measurements
                    where measurements.deleted_at is not null
                    group by measurements.user_variable_id
                    ) m on v.id = m.user_variable_id
                set v.number_of_soft_deleted_measurements = m.number_of_soft_deleted_measurements
            ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEASUREMENT;
	public $htmlType = 'text';
	public $image = ImageUrls::MEASUREMENT;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_soft_deleted_measurements';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Soft Deleted Measurements';
	public $type = self::TYPE_INTEGER;
    /**
     * @param Variable|UserVariable $model
     * @return int
     */
    public static function calculate($model): int {
        $val = $model
            ->l()
            ->measurements()
            ->whereNotNull(Measurement::FIELD_DELETED_AT)
            ->withTrashed()
            ->count();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
