<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\SpreadsheetImporters;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
class GeneralSpreadsheetWithoutCategoriesTest extends SpreadsheetImportTestCase
{
    public $importerClass = GeneralSpreadsheetImporter::class;
    public string $fileName = 'general-spreadsheet-without-categories.xlsx';
    public function testGeneralSpreadsheetWithoutCategories(){
        $this->importWaiting();
        $this->assertImportErrorMessageContains("<p><b>Missing Variable Category:</b></p>
 for row 1: Array
(
    [0] => 5/22/2017 7:15:25
    [1] => datapoint
    [2] => 0
    [3] => ms
    [4] => 
    [5] => 
)
");
    }
}
