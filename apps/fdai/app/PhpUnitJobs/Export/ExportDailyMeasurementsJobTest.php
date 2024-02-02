<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Export;
use App\Mail\DailyMeasurementExportQMEmail;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\User\QMUser;
use App\PhpUnitJobs\JobTestCase;
/** @package App\PhpUnitJobs
 */
class ExportDailyMeasurementsJobTest extends JobTestCase {
    public function testExportDailyMeasurements(){
        //$filePath = \App\Slim\Model\Measurement\Measurement::exportAllDailyMeasurementsMatrixToCsv(230);
        $email = new DailyMeasurementExportQMEmail(UserIdProperty::USER_ID_SYSTEM);
        $email->send();
        $this->assertTrue(true);
    }
}
