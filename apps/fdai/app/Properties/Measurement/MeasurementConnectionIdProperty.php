<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Exceptions\UserVariableNotFoundException;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseConnectionIdProperty;
use Illuminate\Support\Facades\DB;
use App\DataSources\QMConnector;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Variables\QMUserVariable;
class MeasurementConnectionIdProperty extends BaseConnectionIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
	/**
	 * @return void
	 */
	public static function fixNulls(){
        db_statement("
            update measurements m 
                join connections c on m.connector_id = c.connector_id and m.user_id = c.user_id 
                set m.connection_id = c.id 
                where m.connection_id is null;
        ");
    }
    /**
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function fixInvalidRecords(): array{
        $qb = QMMeasurement::readonly()
            ->select([
                //"measurements.id as id",
                Measurement::TABLE . "." . Measurement::FIELD_CONNECTOR_ID,
                Measurement::TABLE . "." . Measurement::FIELD_SOURCE_NAME,
                Measurement::TABLE . "." . Measurement::FIELD_USER_ID,
                Measurement::TABLE . "." . Measurement::FIELD_VARIABLE_ID,
                Measurement::TABLE . ".id" . " as id_from_connectors",
                DB::raw("count(*) as number")
            ])
            ->whereNotNull("measurements." . Measurement::FIELD_CONNECTOR_ID)
            ->leftJoin(QMConnector::TABLE, Measurement::TABLE . '.' . Measurement::FIELD_CONNECTOR_ID, '=', QMConnector::TABLE . '.id')
            ->whereNull(QMConnector::TABLE . '.id');
        $qb->groupBy([
                Measurement::FIELD_CONNECTOR_ID,
                Measurement::FIELD_SOURCE_NAME,
                Measurement::FIELD_USER_ID,
                Measurement::FIELD_VARIABLE_ID
        ]
        );
        $rows = $qb->getArray();
        foreach ($rows as $row) {
            $v = QMUserVariable::getByNameOrId($row->user_id, $row->variable_id);
            $v->logInfo($v->name);
        }
        return $rows;
    }
}
