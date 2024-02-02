<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\DataSources\SpreadsheetImportRequest;
use App\PhpUnitJobs\JobTestCase;
/** Class ImportMeasurementSpreadsheetsTest
 * @package App\PhpUnitJobs
 */
class ImportMeasurementSpreadsheetsJob extends JobTestCase {
    public function testImportMeasurementSpreadsheetsJob(){
        SpreadsheetImportRequest::importWaitingStaleStuck();
        $this->assertTrue(true);
    }
}
