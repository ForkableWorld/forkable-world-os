<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Export;
use App\DataSources\Connectors\GithubConnector;
use App\Models\Connection;
use App\Models\Measurement;
use App\Slim\Model\Measurement\MeasurementExportRequest;
use App\PhpUnitJobs\JobTestCase;
/** Class ImportMeasurementSpreadsheetsTest
 * @package App\PhpUnitJobs
 */
class ExportMeasurementsJob extends JobTestCase {
    public function testExportGithubMeasurements(){
        $c = Connection::where(Connection::FIELD_CONNECTOR_ID, GithubConnector::ID)
            ->where(Connection::FIELD_USER_ID, 230)
            ->first();
        $qb = $c->measurements();
        $measurements = $qb
            //->where(Measurement::FIELD_START_AT, ">", db_date("2022-06-01"))
            //->where(Measurement::FIELD_START_AT, ">", "2022-06-29")
            ->orderByDesc(Measurement::FIELD_START_AT)
            ->limit(1000)
            ->get();
        MeasurementExportRequest::exportMeasurementsToCsv($c->getSlugWithNames(), $measurements);
        $csv = $c->getCsv();
        $requests = MeasurementExportRequest::sendAllWaitingOrStuckExportRequests();
        foreach($requests as $r){
            $this->assertEquals(MeasurementExportRequest::STATUS_FULFILLED, $r->status);
        }
        $this->assertTrue(true);
    }
    public function testExportMeasurementsJob(){
        $requests = MeasurementExportRequest::sendAllWaitingOrStuckExportRequests();
        foreach($requests as $r){
            $this->assertEquals(MeasurementExportRequest::STATUS_FULFILLED, $r->status);
        }
        $this->assertTrue(true);
    }
}
