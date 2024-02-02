<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PropertyCanBeStaticInspection */
/** @noinspection ArgumentEqualsDefaultValueInspection */
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpDeprecationInspection */
namespace Tests;
use App\Storage\DB\TestDB;
use App\Utils\AppMode;
use Illuminate\Foundation\Application;

/**
 * @backupGlobals disabled
 */
class DBUnitTestCase extends QMBaseTestCase {
    use ApiTestTrait;
    protected const DISABLED_UNTIL = null;
    protected const REASON_FOR_SKIPPING = null;
    public const DAY = 86400;
    public const BASE_TIME = 1348072640;
    public const NUMBER_OF_GENERATED_MEASUREMENTS = 50;
    /**
     * @var Application
     */
    protected $app;
	protected function getAllowedDBNames(): array{return [TestDB::DB_NAME];}
	protected function validateDB(): void {
		parent::validateDB();
		if(stripos(\App\Utils\Env::get('DB_DATABASE'), 'production') !== false){
			le("Cannot load test fixtures in production tests!");
		}
		if(AppMode::isStagingUnitTesting()){
			le("Cannot load test fixtures in production tests!");
		}
	}
}
