<?php
namespace Tests\UnitTests\SpreadsheetImporters;
use App\DataSources\QMDataSource;
use App\DataSources\SpreadsheetImporters\MedHelperSpreadsheetImporter;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\Units\CountUnit;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;

class MedHelperSpreadsheetImportTest extends SpreadsheetImportTestCase
{
    public $importerClass = MedHelperSpreadsheetImporter::class;
    public string $expectedVariableCategoryName = TreatmentsVariableCategory::NAME;
    public array $possibleUnitAbbreviatedNames = [CountUnit::NAME, 'mg', 'tablets'];
    public string $fileName = 'medhelper-spreadsheet-fixture.xls';
    public function testMedHelperSpreadsheetImport(){
        //TestDB::resetVariablesTable();
        $c = QMDataSource::find(69);
        $this->assertEquals($this->getConnectorName(), $c->name);
        $this->importAndCheckMeasurements();
        $this->assertImportErrorMessageContains("50 out of 128 rows failed to import.  78 measurements saved to database..
Could not find a header matching any of the following: category, variable category name, variable category.
Could not find a header matching any of the following: unit, unit name, unit abbreviated name, abbreviated unit name.
Could not find a valid timestamp for row 4.
Could not find a valid timestamp for row 5.
Could not find a valid timestamp for row 6.
Could not find a valid timestamp for row 7.
Could not find a valid timestamp for row 8.
Could not find a valid timestamp for row 9.
Could not find a valid timestamp for row 10.
Could not find a valid timestamp for row 11.
Could not find a valid timestamp for row 30.
Could not find a valid timestamp for row 31.
Could not find a valid timestamp for row 32.
Could not find a valid timestamp for row 33.
Could not find a valid timestamp for row 41.
Could not find a valid timestamp for row 42.
Could not find a valid timestamp for row 43.
Could not find a variable name for row 45.
Could not find a valid timestamp for row 46.
Could not find a valid timestamp for row 47.
Could not find a valid timestamp for row 48.
Could not find a valid timestamp for row 49.
Could not find a valid timestamp for row 50.
Could not find a valid timestamp for row 51.
Could not find a valid timestamp for row 52.
Could not find a valid timestamp for row 53.
Could not find a valid timestamp for row 54.
Could not find a valid timestamp for row 55.
Could not find a valid timestamp for row 56.
Could not find a variable name for row 57.
Could not find a valid timestamp for row 58.
Could not find a valid timestamp for row 59.
Could not find a valid timestamp for row 63.
Could not find a valid timestamp for row 65.
Could not find a valid timestamp for row 66.
Could not find a valid timestamp for row 71.
Could not find a valid timestamp for row 75.
Could not find a valid timestamp for row 76.
Could not find a valid timestamp for row 77.
Could not find a valid timestamp for row 78.
Could not find a valid timestamp for row 86.
Could not find a valid timestamp for row 87.
Could not find a valid timestamp for row 96.
Could not find a valid timestamp for row 97.
Could not find a valid timestamp for row 98.
Could not find a valid timestamp for row 107.
Could not find a valid timestamp for row 121.
Could not find a valid timestamp for row 122.
Could not find a valid timestamp for row 123.
Could not find a valid timestamp for row 124.
Could not find a valid timestamp for row 125.
Could not find a valid timestamp for row 126");
    }
    /**
     * @param QMMeasurementExtended $m
     */
    protected function checkVariableCategoryName($m): void{
        $this->assertContains($m->variableCategoryName, [TreatmentsVariableCategory::NAME, FoodsVariableCategory::NAME]);
    }
}
