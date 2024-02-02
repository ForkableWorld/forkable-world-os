<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection MultiAssignmentUsageInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\Imports\SPPriceImport;
use App\Scrapers\FederalReserve\FederalReserveScraper;
use App\Files\FileHelper;
use Excel;
use App\PhpUnitJobs\JobTestCase;
class EconomicDataImportJob extends JobTestCase {
    public function testFedScrape(): void{
        $f = new FederalReserveScraper();
        $f->scrape();
    }
    public function testSchillerImport(): void{
        $import = new SPPriceImport();
        //$import->onlySheets('Data');
        $models = Excel::import($import, FileHelper::absPath('ie_data.xls'));
    }
}
