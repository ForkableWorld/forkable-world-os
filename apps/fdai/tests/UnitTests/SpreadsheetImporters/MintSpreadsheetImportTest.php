<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\SpreadsheetImporters;
use App\Models\Measurement;
use App\DataSources\SpreadsheetImporters\MintSpreadsheetImporter;
use App\DataSources\SpreadsheetImportRequest;
use App\Units\CountUnit;
use App\Units\DollarsUnit;
class MintSpreadsheetImportTest extends SpreadsheetImportTestCase
{
    public $importerClass = MintSpreadsheetImporter::class;
    public string $fileName = 'mint-spreadsheet-fixture.csv';
    public array $possibleUnitAbbreviatedNames = [
        CountUnit::ABBREVIATED_NAME,
        DollarsUnit::ABBREVIATED_NAME
    ];
    public function testMintSpreadsheetImport(){
        $this->importAndCheckMeasurements();
        $this->verifyThatConnectorIsReturnedFromConnectorsRequest(SpreadsheetImportRequest::STATUS_UPDATED);
        $rawMeasurementsWithExtendedProperties = Measurement::all();
        foreach ($rawMeasurementsWithExtendedProperties as $l){
            $m = $l->getDBModel();
            $this->assertNotNull($m->additionalMetaData);
            $this->assertNotNull($m->note);
        }
    }
}
