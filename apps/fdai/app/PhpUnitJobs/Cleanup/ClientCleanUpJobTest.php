<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Cleanup;
use App\Models\Application;
use App\Models\OAClient;
use App\Models\CommonTag;
use App\Models\Connector;
use App\Models\Measurement;
use App\Properties\User\UserIdProperty;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Collaborator;
use App\DataSources\Connectors\WeatherConnector;
use App\DataSources\QMClient;
use App\DataSources\QMDataSource;
use App\Storage\DB\Writable;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs
 */
class ClientCleanUpJobTest extends JobTestCase {
    public function testFixClientIds(){
        self::fixRecordsWithoutMatchingClients();
    }
    public static function createSystemClientsAndUser(){
        Writable::disableForeignKeyConstraints();
        UserCleanUpJobTest::createSystemUser();
        QMClient::createSystemClients();
        Writable::enableForeignKeyConstraints();
    }
    public function testCreateAndFixClientIds(){
        //User::system();
        //self::createClientsForTagsAndConnectors();
        self::fixWeatherMeasurementsWithBadClientId();
        //ClientCleanUpJobTest::createClientsForTagsAndConnectors();
        //ClientCleanUpJobTest::fixRecordsWithoutMatchingClients();
    }
    /**
     * @param string $table
     */
    public static function replaceConnectorDisplayNameClientIds(string $table){
        $skip = [
            Measurement::TABLE,
            OAClient::TABLE
        ];
        if(in_array($table, $skip)){
            \App\Logging\ConsoleLog::info("Skipping $table...");
            return;
        }
        \App\Logging\ConsoleLog::info("$table: ".__FUNCTION__);
        $connectors = QMDataSource::get();
        foreach($connectors as $connector){
            \App\Logging\ConsoleLog::info("$table: $connector->displayName => $connector->name ...");
            if(strtolower($connector->name) === strtolower($connector->displayName)){
                continue;
            }
            Writable::getBuilderByTable($table)->where(QMClient::FIELD_CLIENT_ID, $connector->displayName)->update([
                QMClient::FIELD_CLIENT_ID => $connector->name
            ]);
        }
    }
    public static function fixWeatherMeasurementsWithBadClientId(){
        $clientIds = Measurement::where(Measurement::FIELD_CLIENT_ID, \App\Storage\DB\ReadonlyDB::like(), '% at %')
            ->groupBy(Measurement::FIELD_CLIENT_ID)
            //->limit(1)
            ->pluck(Measurement::FIELD_CLIENT_ID);
        $total = $clientIds->count();
        \App\Logging\ConsoleLog::info($clientIds->count()." ids like at");
        $i = 0;
        foreach($clientIds as $clientId){
            $i++;
            \App\Logging\ConsoleLog::info("Updating $clientId measurements ($i of $total)...");
            $location = QMStr::after(" at ", $clientId);
            $variableNameID = QMStr::before(" at ", $clientId);
            try {
                Measurement::where(Measurement::FIELD_CLIENT_ID, $clientId)->update([
                    Measurement::FIELD_NOTE => json_encode(['previous_variable' => $variableNameID]),
                    Measurement::FIELD_LOCATION => $location,
                    Measurement::FIELD_CLIENT_ID => WeatherConnector::NAME
                ]);
            } catch (\Throwable $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
            }
        }
    }
    public static function createMissingClientsForTagsCollaboratorsApplications(){
        self::createMissingClientsForTable(Application::TABLE);
        self::createMissingClientsForTable(CommonTag::TABLE);
        self::createMissingClientsForTable(Collaborator::TABLE);
    }
    public static function replaceUnderscoresInClientIds(){
        /** @var OAClient[] $clients */
        $qb = OAClient::whereLike(QMClient::FIELD_CLIENT_ID, '%\_%');
        $clients = $qb->get();
        foreach($clients as $client){
            $client->logInfoWithoutContext($client->client_id);
            //$client->updateClientId(str_replace("_", "-", $client->getClientId()));
        }
    }
    public static function fixRecordsWithoutMatchingClients(): void{
        self::createSystemClientsAndUser();
        self::createConnectorClients();
        self::createMissingClientsForTagsCollaboratorsApplications();
        $tables = Writable::getTableNamesWithColumn(QMClient::FIELD_CLIENT_ID);
        $tables = Arr::where($tables,
            function($table){
                return stripos($table, 'deleted_') === false;
            });
        foreach($tables as $table){
            self::replaceConnectorDisplayNameClientIds($table);
        }
        foreach($tables as $table){
            Writable::statementStatic("update $table
                left join oa_clients boc on $table.client_id = boc.client_id
                set $table.client_id = 'unknown'
                where boc.client_id is null;");
        }
    }
    /**
     * @param string $tableName
     */
    private static function createMissingClientsForTable(string $tableName): void{
        $rows = DB::select("select $tableName.client_id from $tableName
            left join oa_clients boc on $tableName.client_id = boc.client_id
            where boc.client_id is null and $tableName.client_id is not null
            group by $tableName.client_id
        ;");
        /** @var Application $application */
        foreach($rows as $row){
            $userId = UserIdProperty::USER_ID_SYSTEM;
            if(empty($row->client_id)){
                \App\Logging\ConsoleLog::info("Skipping $tableName record with client_id: $row->client_id");
                continue;
            }
            OAClient::getOrCreate($row->client_id, [OAClient::FIELD_USER_ID => $userId]);
        }
    }
    private static function createConnectorClients(){
        $connectors = Connector::all();
        foreach($connectors as $connector){
            $connector->getOrCreateClient();
        }
        $sources = Source::all();
        foreach($sources as $source){
            $slug = QMStr::slugify($source->name);
            if(empty($slug)){
                \App\Logging\ConsoleLog::info("Skipping source $slug...");
                continue;
            }
            \App\Logging\ConsoleLog::info("getOrCreate client for source $slug...");
            OAClient::getOrCreate($slug,
                [OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM]);
            $source->client_id = $slug;
            $source->save();
        }
        //\App\Storage\DB\Writable::statementStatic("update connectors set client_id = name where client_id <> name;");
    }
}
