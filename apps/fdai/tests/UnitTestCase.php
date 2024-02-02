<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PropertyCanBeStaticInspection */
/** @noinspection ArgumentEqualsDefaultValueInspection */
/** @noinspection ForgottenDebugOutputInspection */
/** @noinspection PhpDeprecationInspection */
namespace Tests;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Storage\DB\TestDB;
use App\Utils\Env;
use hanneskod\classtools\Exception\LogicException;
/**
 * @backupGlobals disabled
 */
class UnitTestCase extends QMBaseTestCase {
    public const DAY = 86400;
    public const BASE_TIME = 1348072640;
    public const NUMBER_OF_GENERATED_MEASUREMENTS = 50;
	protected function getAllowedDBNames(): array{return [TestDB::DB_NAME];}
	protected function setUp(): void{
		parent::setUp();
		$appUrl = Env::getAppUrl();
		if($appUrl !== 'https://testing.quantimo.do'){
			$str = 'Env::getAppUrl() should be "https://testing.quantimo.do" but is "'.$appUrl;
			QMLog::error($str);
			QMAPIRepo::setStatusFailed(new LogicException($str));
			die(1);
		}
	}
}
