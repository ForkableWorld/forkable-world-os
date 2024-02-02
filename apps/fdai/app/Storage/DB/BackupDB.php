<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseClientIdProperty;
use App\VariableCategories\LocationsVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use Illuminate\Database\QueryException;
class BackupDB extends AbstractMySQLDB
{
    public const CONNECTION_NAME = 'backup';
    public const DB_USER = 'homestead';
    public const DB_PASSWORD = 'secret';
    public const DB_NAME = 'backup';
    public const DB_HOST_PUBLIC = 'localhost';
    public const DB_HOST_PRIVATE = null;
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
	public static function getDefaultDBName(): string{return static::DB_NAME;}
	public static function copyBackupMeasurementsTable(){
        QMDB::copyTable(BackupDB::class, Writable::class, 'measurements_bak');
    }
	/**
	 * @return array
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 * @noinspection UnknownColumnInspection
	 */
    public static function restoreMissingMeasurements(): array{
        $idsFromProd = Writable::getBuilderByTable(Measurement::TABLE)
            ->groupBy([Measurement::FIELD_VARIABLE_ID])
            ->pluck(Measurement::FIELD_VARIABLE_ID);
        $idsFromBackup = BackupDB::getBuilderByTable('measurements_bak')
            ->groupBy([Measurement::FIELD_VARIABLE_ID])
            ->pluck(Measurement::FIELD_VARIABLE_ID);
        $idsNotOnProd = $idsFromBackup->diff($idsFromProd->all());
        $counts = BackupDB::getBuilderByTable('measurements_bak')
            ->selectRaw("count(measurements_bak.id) as num,
                variables.name as name,
                variables.variable_category_id as variable_category_id,
                measurements_bak.variable_id as variable_id")
            ->join(Variable::TABLE, Variable::TABLE.'.'.Variable::FIELD_ID, '=',
                'measurements_bak'.'.'.Measurement::FIELD_VARIABLE_ID)
            ->whereNotIn(Variable::TABLE.'.'.Variable::FIELD_VARIABLE_CATEGORY_ID, [
                LocationsVariableCategory::ID,
                SoftwareVariableCategory::ID,
            ])
            ->whereNotIn('measurements_bak.'.Measurement::FIELD_VARIABLE_ID, [
                1907, // Elevation
                5969791, // Likes
                1883, // Likes
                5947692,
                6054094,
            ])
            ->whereIn('measurements_bak.variable_id', $idsNotOnProd->all())
            ->groupBy(['measurements_bak.'.Measurement::FIELD_VARIABLE_ID])
            ->get();
        $moreThan2 = $counts->filter(function($one){
            return $one->num > 2;
        });
        QMLog::table($moreThan2->all(), "More than 2");
        $userVariables = $new = [];
        foreach($moreThan2 as $one){
            $measurements = BackupDB::getBuilderByTable('measurements_bak')
                ->where(Measurement::FIELD_VARIABLE_ID, $one->variable_id)
                ->get();
            foreach($measurements as $measurement){
                self::saveMeasurement($measurement);
            }
        }
        foreach($userVariables as $uv){
            $uv->getDBModel()->analyzeFully(__FUNCTION__);
        }
        return $new;
    }
	/**
	 * @param Measurement $measurement
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws NoChangesException
	 */
    protected static function saveMeasurement(Measurement $measurement){
        $existing = Measurement::find($measurement->id);
        if($existing){
            $existing->logInfo("Already exists");
            return;
        }
        $uv = UserVariable::fromForeignData($measurement);
        try {
            $m = $uv->newMeasurement($measurement);
        } catch (IncompatibleUnitException | InvalidVariableValueException $e) {
            le($e);
        }
        $client = $m->getClientId();
        if(!$client){$m->client_id = BaseClientIdProperty::CLIENT_ID_SYSTEM;}
        $m->id = $measurement->id;
        $m->logInfo("Saving $m->value ".$m->getUnitAbbreviatedName()." ".$m->getVariableName()." for user $m->user_id");
        try {
            $m->save();
            $uv->save();
            $new[$uv->getVariableName()][$m->getStartAtAttribute()] = $m;
            $userVariables[$uv->id] = $uv;
        } catch (QueryException $e) {
            if(stripos($e->getMessage(), "Duplicate entry") !== false){
                $m->logError(__METHOD__.": ".$e->getMessage());
            } else {
                le($e);
            }
        } catch (ModelValidationException $e) {
            le($e);
        }
    }
	/**
	 * @throws IncompatibleUnitException
	 * @throws InvalidVariableValueException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 * @throws NoChangesException
	 */
    public static function analyzeRestoredUserVariables(){
        $variableIds = Measurement::query()
            ->where(Measurement::UPDATED_AT, ">", db_date(time() -3*3600))
            ->groupBy([Measurement::FIELD_USER_VARIABLE_ID])
            ->pluck(Measurement::FIELD_USER_VARIABLE_ID);
        foreach($variableIds as $id){
            /** @var UserVariable $v */
            $uv = UserVariable::findInMemoryOrDB($id);
            $measurementsFromBackup = $counts = BackupDB::getBuilderByTable('measurements_bak')
                ->where(Measurement::FIELD_VARIABLE_ID, $uv->variable_id)
                ->where(Measurement::FIELD_USER_ID, $uv->user_id)
                ->get();
            $fromProd = $uv->measurements()->get();
            if(count($measurementsFromBackup) > $fromProd->count()){
                foreach($measurementsFromBackup as $m){
                    self::saveMeasurement($m);
                }
            }elseif(strtotime($uv->analysis_started_at) > time() - 3*3600){
                continue;
            }
            $uv->analyze(__FUNCTION__);
        }
    }
}
