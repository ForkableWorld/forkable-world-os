<?php /** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection ForgottenDebugOutputInspection */
namespace Tests\UnitTests\SpreadsheetImporters;
use App\Models\Measurement;
use App\Properties\Connector\ConnectorNameProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\TestDB;
use App\Files\FileHelper;
use Illuminate\Http\UploadedFile;
use LogicException;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\DataSources\QMSpreadsheetImporter;
use App\Utils\SecretHelper;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Slim\Model\User\QMUser;
use App\DataSources\SpreadsheetImportRequest;
use App\PhpUnitJobs\JobTestCase;

use Tests\UnitTestCase;
use Tests\SlimTests\SlimTestCase;

/**
 * Class ImportSpreadsheetTaskTest
 * @package App\Slim\Tasks
 */
class SpreadsheetImportTestCase extends \Tests\SlimTests\SlimTestCase {

    public $importerClass;
    public array $possibleUnitAbbreviatedNames = [];
    public string $expectedVariableCategoryName;
    public string $fileName;
    public int $userId = UserIdProperty::USER_ID_DEMO;
    protected function setUp(): void{
        parent::setUp();
        //TestDB::deleteUserData();
        $this->assertWaitingCount(0);
        $this->uploadSpreadsheetFixture();
        $this->assertWaitingCount(1);
        $this->verifyThatConnectorIsReturnedFromConnectorsRequest(SpreadsheetImportRequest::STATUS_WAITING);
        JobTestCase::resetStartTime();
    }
    /**
     * @return QMSpreadsheetImporter|string
     */
    public function getImporterClass(): string {
        return $this->importerClass;
    }
	/**
	 * @param bool $stripExt
	 * @return string
	 */
    public function getFileName(bool $stripExt = false): string {
        return $this->fileName;
    }
	/**
	 * @return mixed
	 */
	protected function getConnectorsFromApi(){
		$connectorsArray = $this->getAndDecodeBody('/api/v3/connectors/list', [])->connectors;
		return $connectorsArray;
	}
	protected function importAndCheckMeasurements(){
        Measurement::deleteAll();
        $this->importWaiting();
        $measurements = Measurement::whereUserId($this->userId)->get();
        $measurements = QMMeasurement::toDBModels($measurements);
        $this->assertGreaterThan(0, count($measurements), "No measurements!");
        foreach($measurements as $m){
            $m->validateStartTimeAndFallbackToCurrentTimeOnProductionApiRequest();
            $this->checkMeasurementSourceName($m);
            $this->checkVariableCategoryName($m);
        }
    }
    /**
     * @param string $expectedStatus
     * @return QMConnector
     */
    protected function verifyThatConnectorIsReturnedFromConnectorsRequest(string $expectedStatus){
        $connectorName = $this->getConnectorName();
        $this->setAuthenticatedUser($this->userId);
	    $connectorsArray = $this->getConnectorsFromApi();
	    foreach ($connectorsArray as $connector){if($connector->name === $connectorName){$expectedConnector = $connector;}}
        if(!isset($expectedConnector)){
			le("$connectorName not returned!", ConnectorNameProperty::pluckNames($connectorsArray));
		}
        $this->assertTrue($expectedConnector->spreadsheetUpload,
            SecretHelper::obfuscateAndJsonEncodeObject($expectedConnector));
        $this->assertEquals($expectedStatus, $expectedConnector->updateStatus,
            SecretHelper::obfuscateAndJsonEncodeObject($expectedConnector));
        return $expectedConnector;
    }
    /**
     * @return string
     */
    protected function uploadSpreadsheetFixture(): string{
        $filename = $this->getFileName();
        $folder = FileHelper::getFolderForClass(self::class);
        $filepath = $folder."/".$filename;
		$filepath = abs_path($filepath);
        $uploadedFile = new UploadedFile($filepath, $filename, null, null, true);
        QMSpreadsheetImporter::encryptAndUploadSpreadsheetToS3($this->userId, $uploadedFile,
            $this->getConnectorName());
        return $filepath;
    }
    /**
     * @param string $expectedErrorMessage
     */
    protected function assertImportErrorMessageContains(string $expectedErrorMessage): void{
        $c = $this->verifyThatConnectorIsReturnedFromConnectorsRequest(SpreadsheetImportRequest::STATUS_ERROR);
        $message = $c->message;
        if(!is_string($message)){le('!is_string($message)');}
        $this->assertContains($expectedErrorMessage, $message);
    }
    public function getConnectorName():string{
        $class = $this->getImporterClass();
        return $class::NAME;
    }
    protected function importWaiting(): void{
        $this->assertWaitingCount(1);
        SpreadsheetImportRequest::importWaitingStaleStuck();
        $this->assertWaitingCount(0);
    }
    /**
     * @param $m
     */
    protected function checkMeasurementSourceName($m): void{
        $connector = $this->getSpreadsheetImporter();
        if($connector->displayName !== $m->sourceName){
            \App\Logging\QMLog::print_r($m);
        }
        $this->assertEquals($connector->displayName, $m->sourceName);
    }
    private function getSpreadsheetImporter(): QMSpreadsheetImporter {
        $connectorName = $this->getConnectorName();
        $importer = QMDataSource::getDataSourceByNameOrIdOrSynonym($connectorName);
        $this->assertEquals($connectorName, $importer->name);
        return $importer;
    }
    /**
     * @param QMMeasurementExtended $m
     */
    protected function checkVariableCategoryName($m): void{
        if(!isseT($this->expectedVariableCategoryName)){return;}
        if($expectedVariableCategoryName = $this->expectedVariableCategoryName){
            if($expectedVariableCategoryName !== $m->variableCategoryName){
                $actual = var_export($m, true);
                $this->assertEquals($expectedVariableCategoryName, $m->variableCategoryName, "we got $actual");
            }
            $this->assertEquals($expectedVariableCategoryName, $m->variableCategoryName);
        }
    }
    private function assertWaitingCount(int $expected){
        $all = SpreadsheetImportRequest::whereWaiting()->getDBModels();
        $this->assertCount($expected, $all);
    }
}
