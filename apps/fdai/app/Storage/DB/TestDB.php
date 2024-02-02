<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpDeprecationInspection */
namespace App\Storage\DB;
use App\Computers\ThisComputer;
use App\Console\Kernel;
use App\DataSources\QMConnector;
use App\DataSources\QMSpreadsheetImporter;
use App\DataSources\SpreadsheetImportRequest;
use App\Exceptions\GitAlreadyUpToDateException;
use App\Exceptions\GitBranchAlreadyExistsException;
use App\Exceptions\GitBranchNotFoundException;
use App\Exceptions\GitConflictException;
use App\Exceptions\GitLockException;
use App\Exceptions\GitNoStashException;
use App\Exceptions\GitRepoAlreadyExistsException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\ProtectedDatabaseException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\TestArtifacts\TestQueryLogFile;
use App\Folders\AbstractFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\BaseModel;
use App\Models\Collaborator;
use App\Models\Connection;
use App\Models\Connector;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\OAAccessToken;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Models\Unit;
use App\Models\UnitCategory;
use App\Models\User;
use App\Models\UserTag;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Models\WpPost;
use App\Models\WpPostmetum;
use App\Models\WpTerm;
use App\Models\WpTermmetum;
use App\Models\WpTermRelationship;
use App\Models\WpTermTaxonomy;
use App\Models\WpUsermetum;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseWpPostIdProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserUserLoginProperty;
use App\Properties\Variable\VariableStatusProperty;
use App\Repos\QMAPIRepo;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use App\Solutions\UpdateTestDBSolution;
use App\Storage\LocalFileCache;
use App\Storage\Memory;
use App\Storage\QMQueryExecuted;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use Database\Seeders\AnalyticsDatabaseSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\VariablesTableSeeder;
use Illuminate\Database\QueryException;
use Iseed;
use Throwable;
class TestDB extends QMDB {
	public const USER = "homestead";
	public const PW = "secret";
	public const CONNECTION_NAME = 'pgsql_testing';
	public const DB_NAME = "quantimodo_test";
	const PATH = "tests/fixtures/quantimodo.sql";
	const LAST_IMPORT_PATH = self::PATH . '-last-imported.json';
	const LAST_DUMP_PATH = 'tests/fixtures/last-dumped.txt';
	public const FIXTURES_COMPLETED_ANALYSES = 'completed-analyses';
	public const TESTS_FIXTURES_QM_TEST_SQLITE = 'tests/fixtures/qm_test.sqlite';
	public const STORAGE_QM_TEST_SQLITE = 'storage/qm_test.sqlite';
	public static function getConnectionName(): string{
		if(getenv('DB_CONNECTION') === "sqlite"){
			return "sqlite";
		}
        //$connectionName = \App\Utils\Env::get('DB_CONNECTION');
        return static::CONNECTION_NAME;
    }
	public static function getDefaultDBName(): string{return static::DB_NAME;}
	/**
	 * @var array
	 */
	private static $blackListedTables = [];
	/**
     * @var array
     */
    private static $whiteListedTables = [];
    /**
     * @var bool
     */
    private static $justMigrated;
    /**
     * @return string
     */
    public static function getTestFixturesPath(): string{
        return FileHelper::absPath("tests/fixtures");
    }
    public static function seed(string $class = 'DatabaseSeeder'): \Illuminate\Console\Events\CommandFinished
    {
        $params = [
            '--force' => true,
            '--class' => $class,
        ];
        QMLog::logStartOfProcess(__METHOD__);
        $output = Kernel::artisan("db:seed", $params);
        QMLog::logEndOfProcess(__METHOD__);
        return $output;
    }
    public static function importTestDatabase(): void{
	    self::info("Importing base database...");
	    $testDbName = self::getDbName();
	    if(stripos($testDbName, 'production') !== false){
		    le("Cannot load test DB on $testDbName!");
	    }
        Kernel::artisan("db:seed", [
            '--force' => true,
            '--class' => 'DatabaseSeeder',
        ]);
		if(EnvOverride::isLocal()){
			static::setLastImported();
		}
        static::getTableStatic('sessions')->update(['last_activity' => time()]);
        return;
	    $testDbCredentials = self::credentialsCommand();
        try {
            //TestDB::statementStatic($cmd = "DROP DATABASE IF EXISTS $testDbName;");
        } catch (QueryException $e) {
            //ConsoleLog::error($cmd." => ".$e->getMessage());
        }
        try {
            //TestDB::statementStatic($cmd = "CREATE DATABASE $testDbName;");
        } catch (QueryException $e) {
            //ConsoleLog::error($cmd." => ".$e->getMessage());
        }
	    $mysqlGeneralLog = 'OFF';
	    //static::pdoStatement("SET @@global.sql_mode= '';");
	    //static::pdoStatement("SET GLOBAL general_log = 'OFF';");
	    //static::pdoStatement("SET GLOBAL time_zone = '+00:00';");
	    //self::logAndExecute("mysql $testDbCredentials $testDbName < $testDbFile");
//	    $files = FileFinder::listFilesRecursively('tests/fixtures/tables');
//	    if(EnvOverride::isLocal()){self::setLastImported();}
//	    foreach($files as $file){
//		    $skip = [
//			    'tables/o_',
//			    '.txt',
//		    ];
//		    $path = $file->getRealPath();
//		    foreach($skip as $str){
//                if(!$path || stripos($path, $str) !== false){
//                    continue 2;
//                }
//            }
//            self::loadTestTable($path);
//        }
//		$conf = config('database.connections.mysql');
//        Application::where(AppSettings::FIELD_USER_ID, '>', 2)
//            ->update([AppSettings::FIELD_USER_ID => 1]);
////        QMAccessToken::writable()
////            ->where(\App\Models\OAAccessToken::FIELD_USER_ID, '>', 2)
////            ->update([\App\Models\OAAccessToken::FIELD_USER_ID => 1]);
////        QMClient::writable()->where(QMClient::FIELD_USER_ID, '>', 2)
////            ->update([QMClient::FIELD_USER_ID => 1]);
////        QMClient::writable()->where(QMClient::FIELD_USER_ID, '<', 1)
////            ->update([QMClient::FIELD_USER_ID => 1]);
////        QMRefreshToken::writable()->where(QMRefreshToken::FIELD_USER_ID, '>', 2)->update([QMRefreshToken::FIELD_USER_ID => 1]);
////        QMRefreshToken::writable()->where(QMClient::FIELD_USER_ID, '<', 1)->update([QMClient::FIELD_USER_ID => 1]);
        self::updateUserMetaAdmin();
    }
    /**
     * @return array
     */
    public static function getSecureTestTables(): array{
        $secureTables = [
            'credentials',
            'device_tokens',
            'collaborators',
            'applications',
            //'wp_users'
        ];
        return $secureTables;
    }
    public static function dumpDBStructure(){
	    $output = FileHelper::absPath('tests/fixtures/structure');
	    $testDbCredentials = self::credentialsCommand();
	    $testDbName = self::getDbName();
	    self::exec("mysqldump $testDbCredentials --xml --no-data --single-transaction=true $testDbName > $output");
    }
    public static function generateSeeds(string $testName = null)
    {
        $tables = static::getDBTables();
        foreach ($tables as $table) {
            if($table->getName() === "migrations"){continue;}
            if(!$table->count()){
                QMLog::info("Skipping empty table $table");
                continue;
            }
            Iseed::generateSeed($table->getName(), $testName, null, static::getConnectionName());
        }
    }

    public static function getSecondsSinceLastImported(): int
    {
        $lastImported = self::getLastImportedTime();
        if(!$lastImported){return 0;}
        $secondsSinceLastImported = time() - $lastImported;
        return $secondsSinceLastImported;
    }
	public static function resetTestDB(){
		$path = self::STORAGE_QM_TEST_SQLITE;
//		if(EnvOverride::isLocal()){
//			$path = 'storage/test-dbs/'.AppMode::getCurrentTest()->getName().'.sqlite';
//			Env::set('DB_DATABASE', $path);
//		}
		QMLog::info("Resetting test DB to $path...");
		FileHelper::copy(
			abs_path(self::TESTS_FIXTURES_QM_TEST_SQLITE),
			abs_path($path),
		);
	}
	public static function copyStorageToFixtures(){
		FileHelper::copy(
			abs_path(self::STORAGE_QM_TEST_SQLITE),
			abs_path(self::TESTS_FIXTURES_QM_TEST_SQLITE),
		);
	}
	/**
	 * @return void
	 */
	public static function setVariablesAnalyzedOneDayAgo(): void{
		Variable::query()->whereNotNull(Variable::FIELD_ID)
		                 ->update([
			                                                            Variable::FIELD_ANALYSIS_ENDED_AT => db_date(time() -
			                                                                                                         86400),
			                                                            Variable::FIELD_STATUS => VariableStatusProperty::STATUS_UPDATED,
			                                                            Variable::FIELD_ANALYSIS_STARTED_AT => db_date(time() -
			                                                                                                           86400 -
			                                                                                                           60),
			                                                            Variable::FIELD_NEWEST_DATA_AT => null,
			                                                            Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID => null
		                                                            ]);
	}
	public static function dumpTestDB(){
        $tables = static::getDBTables();
        foreach($tables as $table){
            Iseed::generateSeed($table);
            continue;
			if(!$table->count()){
				static::disableForeignKeyConstraints();
                try {
                    $table->truncate();
                } catch (Throwable $e) {
                    ConsoleLog::exception($e);
                }
                static::enableForeignKeyConstraints();
				continue;
			}
            $timestamps = $table->getTimestampColumns();
            foreach($timestamps as $timestamp){
                \App\Logging\ConsoleLog::info("Setting $timestamp to 2020-01-01 00:00:00");
				$table->qb()
					->whereNotNull($timestamp->getName())
					->update([$timestamp->getName() => "2020-01-01 00:00:00"]);
            }
        }
        self::createMikeUser();
        foreach($tables as $t){
            self::dumpTestTable($t);
        }
        FileHelper::writeByFilePath(self::LAST_DUMP_PATH, time());
    }

    /**
     * @param string $table
     * @param string|null $fileName
     * @throws InvalidAttributeException
     * @throws ModelValidationException
     */
    public static function dumpTestFixture(string $table, string $fileName = null){
        $model = BaseModel::getClassByTable($table);
        $model->dumpTestFixture($fileName);
    }
    /**
     * @param string $command
     * @param bool $obfuscate
     * @noinspection PhpDocMissingThrowsInspection
     * @return string
     */
	public static function exec(string $command, bool $obfuscate = true): string {
		/** @noinspection PhpUnhandledExceptionInspection */
		return parent::exec($command, false);
	}
	public static function importAndMigrateTestDB(){
		QMLog::logStartOfProcess(__FUNCTION__);
		self::resetTestDB();
		static::validateTestDBUrl();
        static::migrate(); // Must come before constants if we're
		//static::importTestDatabase();
//		(new DatabaseSeeder())->run();
//		(new AnalyticsDatabaseSeeder)->run();
		TestDB::copyStorageToFixtures();
		self::setJustMigrated(true);

		//self::loadTestTable(Variable::TABLE);
		//static::updateDBConstantsIfNecessary();
//		if(EnvOverride::getFormatted('DUMP_TEST_DB_IF_NECESSARY')){
//			self::dumpTestDBIfNecessary();
//		}
		QMLog::logEndOfProcess(__FUNCTION__);
    }
    public static function validateTestDBUrl(): void{
        if(stripos(self::getHost(), 'localhost') === false &&
            strpos(self::getHost(), ThisComputer::getHostAddress()) === false &&
            stripos(self::getHost(), 'mysql') === false &&
            stripos(self::getDbName(), 'test') === false){
            $url = self::getDbName()." @".self::getHost();
            le("$url is not a test database!");
        }
    }
    /**
     * @return int
     */
    public static function lastModified():int {
        $last = FileHelper::getSecondsSinceLastModified(self::PATH);
        return $last;
    }
    /**
     * @return int
     */
    public static function getLastImportedTime(): ?int {
        $time = LocalFileCache::get(self::LAST_IMPORT_PATH);
        QMLog::debug("Last imported test DB ".TimeHelper::timeSinceHumanString($time));
        return $time;
    }
    /**
     * @return int
     */
    public static function getLastDump():int {
        try {
            $time = FileHelper::getContents(self::LAST_DUMP_PATH);
        } catch (QMFileNotFoundException $e) {
            le("Why can't we find ".self::LAST_DUMP_PATH);
        }
        $time = QMStr::trimWhitespaceAndLineBreaks($time);
        \App\Logging\ConsoleLog::info("Last dumped test DB ".TimeHelper::timeSinceHumanString($time));
        return $time;
    }
    public static function setLastImported(): void {
        $time = time();
        LocalFileCache::set(self::LAST_IMPORT_PATH, $time);
        $gotten = self::getLastImportedTime();
		if($gotten !== $time){le( "FileCache not working! We set it to $time but got $gotten");}
    }
    /**
     * @return bool
     */
    public static function shouldImport(): bool {
		if(AppMode::isStaging()){
			ConsoleLog::info("Not importing test DB because AppMode::isStaging...");
			return false;
		}
	    if(AppMode::isProduction()){
		    ConsoleLog::info("Not importing test DB because AppMode::isProduction...");
		    return false;
	    }
	    $DBLastModified = static::getLastModifiedAt();
        if(!$DBLastModified) {
            ConsoleLog::info("Importing test DB because DBLastModified is null...");
            return true;
        }
        $stagingUnit = AppMode::isStagingUnitTesting();
        if($stagingUnit){
            ConsoleLog::info("Not importing test DB because this is a staging unit test..");
            return false;
        }
        if(!EnvOverride::isLocal()){
            QMLog::info('Importing test DB because env is not local...');
            return true;
        } else {
	        QMLog::info('Root\App\Utils\EnvOverride::isLocal() so doing more checks to see if we need to import...');
        }
        $newMigrations = self::newMigrations();
        if($newMigrations){return true;} // We always have to import if there are new migrations
        $lastImportTime = self::getLastImportedTime();
        if(!$lastImportTime){
            QMLog::info("No last test DB import time from file cache!");
            return true;
        }
        $secondsSinceLastImport = static::getSecondsSinceLastImported();
        $secondsSinceConstantsLastModified = QMVariableCategory::secondsSinceConstantsLastModified();
        $sinceImport = TimeHelper::timeSinceHumanString($lastImportTime);
        if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
            QMLog::info("Need to import because VariableCategory file modified ".
                TimeHelper::timeSinceHumanString($secondsSinceConstantsLastModified).
                " after last import $sinceImport");
            return true;
        }
        $secondsSinceConstantsLastModified = QMCommonVariable::secondsSinceConstantsLastModified();
        if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
            QMLog::info("Need to import because QMCommonVariable file modified ".
                TimeHelper::timeSinceHumanString($secondsSinceConstantsLastModified).
                " after last import $sinceImport");
            return true;
        }
        $secondsSinceConstantsLastModified = QMUnit::secondsSinceConstantsLastModified();
        if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
            QMLog::info("Need to import because Unit file modified ".
                TimeHelper::timeSinceHumanString($secondsSinceConstantsLastModified)." after last import $sinceImport");
            return true;
        }
        $secondsSinceConstantsLastModified = QMSpreadsheetImporter::secondsSinceConstantsLastModified();
        if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
            QMLog::info("Need to import because SpreadsheetImporter file modified ".
                TimeHelper::timeSinceHumanString($secondsSinceConstantsLastModified)." after last import $sinceImport");
            return true;
        }
        $secondsSinceConstantsLastModified = QMConnector::secondsSinceConstantsLastModified();
        if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
            QMLog::info("Need to import because QMConnector file modified ".
                TimeHelper::timeSinceHumanString($secondsSinceConstantsLastModified)." after last import $sinceImport");
            return true;
        }
        QMLog::debug("Skipping DB import because no relevant files changed since last import $sinceImport");
        return false;
    }
    public static function newMigrations(): bool {
        $lastDump = self::getLastDump();
        $lastImport = self::getLastImportedTime();
        $lastImportAt = db_date($lastImport);
        if(!$lastImport){
            QMLog::info("Need to import because: No last test DB import time from file cache!");
            return true;
        }
        $migrationsModified = Migrations::getLastModifiedTime();
        if($migrationsModified > $lastImport){
            QMLog::info("Need to import because:".
                "\n\tMigrations file modified: ".TimeHelper::timeSinceHumanString($migrationsModified).
                "\n\tLast Import: ".TimeHelper::timeSinceHumanString($lastImport));
            return true;
        }
        return false;
    }
    public static function createMikeUser(): void{
        QMUser::getOrCreateByEmail("m@thinkbynumbers.org", BaseClientIdProperty::CLIENT_ID_QUANTIMODO, [
	                                                         User::FIELD_USER_LOGIN => UserUserLoginProperty::USERNAME_MIKE,
	                                                         User::FIELD_ID => UserIdProperty::USER_ID_MIKE
                                                         ]);
    }
    public static function resetUserTables(){
	    TestDB::resetTestDB();
//	    TrackingReminderNotification::deleteAll();
//	    TrackingReminder::deleteAll();
//		try {
//			(new WpUsersTableSeeder())->run();
//		} catch (\Throwable $e) {
//		    QMLog::error("Error seeding WP users: " . $e->getMessage());
//		}
    }
    public static function deleteUserAndAggregateData(): void{
        ConsoleLog::info(__FUNCTION__."...");
        self::deleteUserData();
	    self::setVariablesAnalyzedOneDayAgo();
	    //TestDB::loadTestTable(GlobalVariableRelationship::TABLE);
    }
    public static function updateUserMetaAdmin(): void{
        WpUsermetum::query()
            ->whereLike(WpUsermetum::FIELD_META_VALUE,'%administrator%')
            ->where(WpUsermetum::FIELD_USER_ID, "<>", 230)
            ->forceDelete();
    }
    public static function updateAndCommitTestDB(){
        QMAPIRepo::createFeatureBranch("updated-test-db");
        TestDB::importAndMigrateTestDB();
        QMAPIRepo::addFilesInFolder(TestDB::getTestFixturesPath());
        try {
            QMAPIRepo::commitAndPush("Updated test DB");
        } /** @noinspection PhpDeprecationInspection */ catch (GitAlreadyUpToDateException | GitBranchAlreadyExistsException | GitBranchNotFoundException |
        GitRepoAlreadyExistsException | GitNoStashException | GitLockException |
        GitConflictException $e) {
            le($e);
        }
    }
    public static function getWhiteListedTables(): array{
        return self::$whiteListedTables;
    }
    public static function setBlackListedTables(array $array){
        self::$blackListedTables = $array;
    }
    public static function getBlackListedTables(): array{
        return self::$blackListedTables;
    }
    /**
     * @param array $whiteListedTables
     */
    public static function setWhiteListedTables(array $whiteListedTables): void{
        self::$whiteListedTables = $whiteListedTables;
    }
    /**
     * @return void
     */
    public static function logDuplicateQueries(): void {
        if($duplicates = TestQueryLogFile::getDuplicateQueryTables()){
            ConsoleLog::info($duplicates);
        }
    }
    /**
     * @param bool $excludeTelescope
     * @return int
     */
    public static function getTotalQueryCount(bool $excludeTelescope = true): int{
        return count(TestQueryLogFile::getQueriesExecuted($excludeTelescope, false));
    }
    /**
     * @param bool $sortByDuration
     * @return QMQueryExecuted[]
     */
    public static function getDuplicateQueriesExecutedArray(bool $sortByDuration = false): array{
        $data = [];
        foreach(TestQueryLogFile::getQueriesExecuted(true, $sortByDuration) as $q){
            $data[] = $q->toArray();
        }
        return $data;
    }
    public static function seedsFolder(string $folder = null): string{
        if(!$folder){
            $folder = AbstractFolder::getCurrentTestFolder();
        } else {
            $folder = "tests/fixtures/$folder";
        }
        return $folder;
    }
    public static function loadFixtures($folder = null){
        $files = self::getSeedFiles($folder);
        foreach($files as $file){
            self::importTableFromJson($file);
        }
    }
    public static function loadCompletedAnalysesFixtures(){
        TestDB::loadFixtures(self::FIXTURES_COMPLETED_ANALYSES);
    }
    public static function loadUserTables(){
        self::resetUserTables();
    }
    public static function deleteUserData(): void{
        ConsoleLog::info(__FUNCTION__."...");
        Memory::resetClearOrDeleteAll();
		static::disableForeignKeyConstraints();
	    TestDB::resetUserTables();
	    try {SpreadsheetImportRequest::truncate();} catch (ProtectedDatabaseException $e) {le($e);}
	    UserTag::truncate();
        UserVariableRelationship::truncate();
		if(UserVariableRelationship::count() > 0){le("UserVariableRelationship::count() > 0");}
        self::deleteMeasurementsAndReminders();
	    UserVariableClient::truncate();
        UserVariable::truncate();
		ConnectorRequest::truncate();
		ConnectorImport::truncate();
		Connection::truncate();
		static::enableForeignKeyConstraints();
        Variable::query()->update([
            Variable::FIELD_ANALYSIS_ENDED_AT                        => null,
            Variable::FIELD_ANALYSIS_STARTED_AT                      => null,
            Variable::FIELD_EARLIEST_NON_TAGGED_MEASUREMENT_START_AT => null,
            Variable::FIELD_LATEST_TAGGED_MEASUREMENT_START_AT       => null,
            Variable::FIELD_NEWEST_DATA_AT                           => null,
            Variable::FIELD_NUMBER_OF_USER_VARIABLES                 => 0,
            Variable::FIELD_MOST_COMMON_CONNECTOR_ID                 => null,
        ]);
    }
    public static function deleteMeasurementsAndReminders(){
        Measurement::deleteAll();
        TrackingReminder::deleteAll();
        TrackingReminderNotification::deleteAll();
    }
    public static function resetVariablesTable(){
        (new VariablesTableSeeder)->run();
        //TestDB::loadTestTable(Variable::TABLE);
    }
    public static function deleteWpData(): void{
        User::query()->update([User::FIELD_WP_POST_ID => null]);
        WpPostmetum::query()->forceDelete();
        WpTermRelationship::query()->forceDelete();
        BaseWpPostIdProperty::setNullInAllTables();
        WpPost::query()->forceDelete();
        WpTermmetum::query()->forceDelete();
        WpTermTaxonomy::query()->forceDelete();
        WpTerm::query()->forceDelete();
    }
    public static function getMeasurementDependentTables(): array{
        return [
            Measurement::TABLE,
            UserVariable::TABLE,
            UserVariableClient::TABLE,
        ];
    }
    /**
     * @return bool
     */
    public static function isJustMigrated(): bool{
        return self::$justMigrated ?? false;
    }
    public static function shouldRegenerateFixtures(): bool{
        //return \App\Utils\EnvOverride::isLocal();
        return self::isJustMigrated() && EnvOverride::isLocal();
    }
    /**
     * @param bool $justMigrated
     */
    public static function setJustMigrated(bool $justMigrated): void{
        self::$justMigrated = $justMigrated;
    }
    /**
     * @param string $filePath
     */
    public static function normalizeFixtureDump(string $filePath): void{
	    try {
		    FileHelper::removeLinesContaining($filePath, " version	5.7."); // Don't clutter diff
		    FileHelper::removeLinesContaining($filePath, "  Distrib 5.7."); // Don't clutter diff
		    FileHelper::removeLinesContaining($filePath, "Server version	5.7.");
	    } catch (QMFileNotFoundException $e) {
			le($e);
	    } // Don't clutter diff
    }
	/**
	 * @return void
	 */
	private static function dumpTestDBIfNecessary(): void{
		$lastMigration = Migrations::getLastModifiedTime();
		$lastDump = static::getLastDump();
		$local = EnvOverride::isLocal();
		if($lastMigration > $lastDump){
			if($local){
				TestDB::dumpTestDB();
			} else{
				$dumpSince = TimeHelper::timeSinceHumanString($lastDump);
				$migrationSince = TimeHelper::timeSinceHumanString($lastMigration);
				QMLog::warning("Should we update test DB fixtures?
                Last dump: $dumpSince
                Last migration: $migrationSince
                ".new UpdateTestDBSolution());
			}
		}
	}

    /**
     * @param DBTable|string $t
     * @return void
     */
    public static function dumpTestTable($t): void{
        $testDbCredentials = self::credentialsCommand();
        $testDbName = self::getDbName();
        $opts = "--max-allowed-packet=16M --skip-dump-date";
        $output = FileHelper::absPath('tests/fixtures/tables');
        $out = "$output/$t.sql";
        self::exec("mysqldump $opts $testDbCredentials $testDbName $t > $out");
        self::normalizeFixtureDump($out);
    }

    public static function getStaticTables(): array
    {
        return [
            Connector::TABLE,
            Unit::TABLE,
            UnitCategory::TABLE,
            VariableCategory::TABLE,
        ];
    }

    /**
     * @param $folder
     * @return \SplFileInfo[]
     */
    public static function getSeedFiles($folder): array
    {
        $folder = self::seedsFolder($folder);
        $files = FileFinder::listFiles($folder, false, ".json");
        return $files;
    }
	protected static function getDBDriverName(): string{
		return getenv('DB_DRIVER') ?? 'sqlite';
	}
}
