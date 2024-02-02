<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class MeasurementIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'measurement_id',
        'id',
    ];
	public static function pluckOrDefault($data){
		if(is_object($data) && property_exists($data, 'id')){
			return $data->id;
		}
		return parent::pluckOrDefault($data);
	}
}
