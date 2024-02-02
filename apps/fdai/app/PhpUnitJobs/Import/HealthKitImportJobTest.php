<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Import;
use App\DataSources\SpreadsheetImports\AppleHealthKitImporter;
use App\PhpUnitJobs\JobTestCase;
class HealthKitImportJobTest extends JobTestCase {
    public function testImport(){
        AppleHealthKitImporter::import();
    }
}
