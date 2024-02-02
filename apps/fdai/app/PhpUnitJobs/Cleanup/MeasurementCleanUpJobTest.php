<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\DataSources\Connectors\WeatherConnector;
use App\DataSources\QMConnector;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\OAAccessToken;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Variable\VariableMaximumAllowedDailyValueProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\RawQMMeasurement;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Storage\DB\Writable;
use App\Types\TimeHelper;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariableCategory;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
/** @package App\PhpUnitJobs
 */
class MeasurementCleanUpJobTest extends JobTestCase {
    public function testVariableMaximumAllowedDailyValueProperty(){
        VariableMaximumAllowedDailyValueProperty::fixInvalidRecords();
    }
    public function testMeasurementCleanup(){
        self::listVariablesWithWrongNumberOfRawMeasurements();
        self::deleteEnvMeasurementsOutsideTimeRange(false);
        Measurement::logVariableWithMostMeasurements();
        //QMCommonVariable::updateDatabaseTableFromHardCodedConstants();
        //\App\Variables\VariableCategory::updateDatabaseTableFromHardCodedConstants();
        //self::getVariableIdsWithMostMeasurements(50);
        //self::fixTooFrequentMeasurements();
    }
    public static function listVariablesWithWrongNumberOfRawMeasurements(int $limit = 100){
        $ids = Writable::selectStatic("
            select m.variable_id as variable,
                   count(*) as measurements,
                   v.name as variable_name,
                   v.number_of_measurements as from_variables
            from measurements m
            join variables v on v.id = m.variable_id
            group by m.variable_id
            where count(measurements.id) <> from_variables
            order by measurements desc
            limit $limit
        ;");
        \App\Logging\ConsoleLog::info(__FUNCTION__);
        QMLog::table($ids);
    }
    public static function DeleteWeatherMeasurementsBeforeUserEarliestTaggedMeasurementTime(){
        $users = User::all();
        foreach($users as $user){
            $total = $user->measurements()->count();
            $user->logInfo("has $total TOTAL measurements");
            if(!$total){continue;}
            $weather = $user->measurements()
                ->where(Measurement::FIELD_CONNECTOR_ID, WeatherConnector::ID)
                ->count();
            $user->logInfo("has $weather WEATHER measurements");
            if(!$weather){continue;}
            $min = $user->measurements()
                ->where(Measurement::FIELD_CONNECTOR_ID, "<>", WeatherConnector::ID)
                ->min(Measurement::FIELD_START_TIME);
            if(!$min){
                $user->logInfo("No non-weather measurements!");
                $count = $user->measurements()
                    ->where(Measurement::FIELD_CONNECTOR_ID, WeatherConnector::ID)
                    ->count();
                $user->logInfo("$count Weather measurements");
            } else {
                $qb = $user->measurements()
                    ->where(Measurement::FIELD_CONNECTOR_ID, WeatherConnector::ID)
                    ->where(Measurement::FIELD_START_TIME, "<", $min);
                $count = $qb->count();
                if($count){
                    $user->logInfo("$count weather measurements before ".Carbon::createFromTimestamp($min)->toString());
                } else {
                    $user->logInfo("No weather before ".Carbon::createFromTimestamp($min)->toString());
                }
            }
        }
    }
    public static function UpdateConnectorId(){
        $rowsLeft = true;
        while($rowsLeft){
            $rowsLeft = QMMeasurement::readonly()
                ->join(QMConnector::TABLE.'.'.QMConnector::FIELD_DISPLAY_NAME, '=', Measurement::TABLE.'.'.
	                Measurement::FIELD_SOURCE_NAME)
                ->whereNull(Measurement::FIELD_CONNECTOR_ID)
                ->count();
            QMLog::infoWithoutContext("$rowsLeft rows left");
	        Writable::exec("
                update measurements m
                    join connectors c on m.source_name = c.display_name
                    set connector_id = c.id
                    where connector_id is null
                    limit 1000
                ;
            ");
        }
    }
    public static function HardDeleteMeasurementsWithValuesGreaterThanMaxForVariable(){
        $qb = GetMeasurementRequest::getQBWithVariablesJoin();
        $qb->columns[] = 'measurements.value AS value';
        $qb->columns[] = 'measurements.created_at AS created_at';
        $qb->whereNotNull(Variable::TABLE.'.'. Variable::FIELD_MAXIMUM_ALLOWED_VALUE);
        $qb->whereRaw(Measurement::TABLE.'.'. Measurement::FIELD_VALUE. ' > '.
            Variable::TABLE.'.'. Variable::FIELD_MAXIMUM_ALLOWED_VALUE);
        //$qb->limit(100);
        $rows = $qb->getArray();
        $mostRecent = null;
        foreach($rows as $row){
            if(!$mostRecent || $row->created_at > $mostRecent->created_at){
                $mostRecent = $row;
            }
            $row->unitName = QMUnit::getByNameOrId($row->unitId)->name;
            $byVariable[$row->variableName][] = $row;
        }
        foreach ($rows as $row){
            QMMeasurement::writable()->where(Measurement::FIELD_ID, $row->id)->hardDelete();
        }
    }
    public static function HardDeleteSoftDeletedMeasurements(){
        $deleted = self::getGroupedSoftDeletedMeasurements();
        $numberOfUserVariables = $deleted->count();
        QMLog::infoWithoutContext("$numberOfUserVariables user variables with soft-deleted measurements");
        foreach ($deleted as $item){
            $measurementsDeleted = $item->total;
            $v = QMUserVariable::getOrCreateById($item->user_id, $item->variable_id);
            $notDeleted = $v->calculateNumberOfRawMeasurementsWithTagsJoinsChildren();
            $v->logInfo($item->total ." deleted and $notDeleted remain. Charts: ".$v->getIonicChartsUrl());
            $shouldDelete = $measurementsDeleted < 10;
            if($notDeleted){
                $percent = 100 * $measurementsDeleted/$notDeleted;
                if($percent < 10){$shouldDelete = true;}
            }
            if($shouldDelete){
                $v->hardDeleteSoftDeletedMeasurements();
            }
        }
    }
    public static function CreateUserVariablesForOrphanedMeasurements(){
        $rows = self::getOrphanedMeasurements();
        $total = $rows->count();
        QMLog::infoWithoutContext("$total orphaned measurements");
        $grouped = $rows->groupBy(["variable_id", "user_id"]);
        $groupedTotal = $grouped->count();
        QMLog::infoWithoutContext("$groupedTotal orphaned variables");
        $arr = $grouped->toArray();
        foreach ($arr as $variableId => $byUserIds){
            $v = QMCommonVariable::find($variableId);
            $v->logInfo(count($byUserIds)." missing user variables");
            foreach ($byUserIds as $userId => $group){
                $v = QMUserVariable::getOrCreateById($userId, $variableId);
                $v->logInfo($v->name);
            }
        }
    }
    /**
     * @return Collection
     */
    private static function getGroupedSoftDeletedMeasurements(): Collection{
        $deleted = QMMeasurement::writable()
            ->selectRaw(
                Measurement::FIELD_VARIABLE_ID . ', ' .
                Measurement::FIELD_USER_ID . ', ' .
                'count(*) as total'
            )
            ->whereNotNull(Measurement::FIELD_DELETED_AT)
            ->groupBy([
                Measurement::FIELD_USER_ID,
                Measurement::FIELD_VARIABLE_ID
            ])
            ->get();
        return $deleted;
    }
    /**
     * @return Collection
     */
    private static function getOrphanedMeasurements(): Collection {
        $qb = QMMeasurement::readonly()
            ->select([
                "measurements.id as id",
                "measurements.deleted_at as deleted_at",
                "measurements.user_id as user_id",
                "measurements.variable_id as variable_id",
                'user_variables.variable_id as user_variable_id',
            ])
            ->leftJoin(UserVariable::TABLE, static function ($join) {
                /** @var JoinClause $join */
                $join->on('measurements.variable_id', '=', 'user_variables.variable_id');
                $join->on('measurements.user_id', '=', 'user_variables.user_id');
            })
            ->whereNull("user_variables.variable_id")
//            ->groupBy([
//                "measurements.user_id as user_id",
//                "measurements.variable_id as variable_id",
//            ])
            //->limit(1000)
        ;
        $rows = $qb->get();
        return $rows;
    }
    /**
     * @param RawQMMeasurement[] $measurements
     * @param QMUserVariable $v
     * @return RawQMMeasurement[]
     */
    public static function removeAndDeleteInvalidMeasurements(array $measurements, QMUserVariable $v){
        $valid = $invalid = [];
        $total = count($measurements);
        foreach($measurements as $m){
            $message = $v->valueInvalidForCommonVariableOrUnit($m->value, "measurement");
            if($message){
                $v->logInfo($message);
                $v->logInfo(count($invalid)." invalid out of ".$total);
                $invalid[] = $m;
                $m->userId = $v->userId;
                $m->delete($message, false);
            }else{
                $valid[] = $m;
            }
        }
        if(isset($invalid) && count($invalid)){
            $v->logError(count($invalid)." invalid measurements soft deleted", ['invalid_measurements' => $invalid]);
        }
        return $valid;
    }
    /**
     * @param int $limit
     * @return array
     */
    public static function getVariableIdsWithMostMeasurements(int $limit): array{
        $categoriesToExclude = QMVariableCategory::where(
                VariableCategory::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS,
                "<",
                3600)->get();
        $idsToExclude = $categoriesToExclude->pluck('id')->all();
        $excluded = join(", ", $idsToExclude);
        $ids = Writable::selectStatic("
            select m.variable_id,
                   count(*) as measurements,
                   v.name as variable_name,
                   v.number_of_measurements as number_from_variables
            from measurements m
            join variables v on v.id = m.variable_id
            where v.variable_category_id not in ($excluded)
            group by m.variable_id
            order by measurements desc
            limit $limit
        ;");
        \App\Logging\ConsoleLog::info(__FUNCTION__);
        QMLog::table($ids);
        return $ids;
    }
    /**
     * @param bool $dryRun
     */
    public static function deleteEnvMeasurementsOutsideTimeRange($dryRun){
        $envCatId = EnvironmentVariableCategory::ID;
        $byUserId = [];
        $enviro = Writable::selectStatic("select
                MAX(start_time) as max_enviro,
                MIN(start_time) as min_enviro,
                count(id) as total_enviro,
                user_id
            from measurements where variable_category_id = $envCatId
            group by user_id;");
        foreach($enviro as $item){
            $item->max_enviro_date = TimeHelper::YYYYmmddd($item->max_enviro);
            $item->min_enviro_date = TimeHelper::YYYYmmddd($item->min_enviro);
            $byUserId[$item->user_id] = $item;
        }
        $nonEnviro = Writable::selectStatic("select
                MAX(start_time) as max_non_enviro,
                MIN(start_time) as min_non_enviro,
                count(id) as total_non_enviro,
                user_id
            from measurements where variable_category_id <> $envCatId
            group by user_id;");
        foreach($nonEnviro as $item){
            if(!isset($byUserId[$item->user_id])){
                QMLog::debug("No enviro measurements for $item->user_id");
                continue;
            }
            $item->max_non_enviro_date = TimeHelper::YYYYmmddd($item->max_non_enviro);
            $item->min_non_enviro_date = TimeHelper::YYYYmmddd($item->min_non_enviro);
            $byUserId[$item->user_id] = (object) array_merge((array)$byUserId[$item->user_id], (array)$item);
        }
        QMLog::table($byUserId);
        foreach($byUserId as $item){
            if(!isset($item->max_non_enviro)){
                //throw new \LogicException(\App\Logging\QMLog::print_r($item, true));
                \App\Logging\ConsoleLog::info("$item->user_id has no non-enviro measurements");
                $item->max_non_enviro = 0;
                $item->min_non_enviro = time();
            }
            $max = $item->max_non_enviro;
            $maxInLastMonth = $max > time() - 30 * 86400;
            if(!$maxInLastMonth){
                $qb = Measurement::where(Measurement::FIELD_START_TIME, ">", $max)
                    ->where(Measurement::FIELD_VARIABLE_CATEGORY_ID, $envCatId)
                    ->where(Measurement::FIELD_USER_ID, $item->user_id);
                $count = $qb->count();
                \App\Logging\ConsoleLog::info("Deleting $count environment measurements for user $item->user_id after ".
                    TimeHelper::YYYYmmddd($item->max_non_enviro));
                //if(!$dryRun){$qb->forceDelete();}
            }
            $min = $item->min_non_enviro - 14 * 86400;
            $minInLastMonth = $min > time() - 30 * 86400;
            if(!$minInLastMonth){
                $qb = Measurement::where(Measurement::FIELD_START_TIME, "<", $min)
                    ->where(Measurement::FIELD_VARIABLE_CATEGORY_ID, $envCatId)
                    ->where(Measurement::FIELD_USER_ID, $item->user_id);
                $count = $qb->count();
                if(!$count){continue;}
                $u = QMUser::find($item->user_id);
                $tString = $u->getOrSetAccessTokenString(BaseClientIdProperty::CLIENT_ID_SYSTEM);
                $token = OAAccessToken::getByAccessToken($tString);
                if($token->user_id !== $u->getId()){
                    throw new \LogicException("token->user_id $token->user_id doesn't match user $u->id !");
                }
                $u->logPatientHistoryUrl();
                $u->logRelationCounts();
                \App\Logging\ConsoleLog::info("Deleting $count environment measurements for user $item->user_id before ".
                    TimeHelper::YYYYmmddd($item->min_non_enviro));
                if(!$dryRun){$qb->forceDelete();}
            }
        }
    }
}
