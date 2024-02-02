<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\SpreadsheetImporters;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
use App\DataSources\SpreadsheetImportRequest;
use App\Logging\QMLogLevel;
class AAAGeneralSpreadsheetImportTest extends SpreadsheetImportTestCase
{
    public $importerClass = GeneralSpreadsheetImporter::class;
    public string $fileName = 'general-spreadsheet-fixture.csv';
    public function testGeneralSpreadSheetImport(){
        $this->importAndCheckMeasurements();
        $this->verifyThatConnectorIsReturnedFromConnectorsRequest(SpreadsheetImportRequest::STATUS_ERROR);
		QMLogLevel::revertToPreviousLogLevel();
    }
}
