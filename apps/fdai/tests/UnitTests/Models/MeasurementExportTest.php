<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Models;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Slim\Model\Measurement\MeasurementExportRequest;
use Tests\UnitTestCase;
/**
 * @coversDefaultClass  \App\Models\MeasurementExport
 */
class MeasurementExportTest extends UnitTestCase
{
	/**
	 * @covers \App\Models\MeasurementExport
	 */
	public function testPostRequestCsv(){
        if(\App\Utils\Env::get('CIRCLE_ARTIFACTS')){$this->skipTest('Cannot use /tmp directory on CircleCI');}
        $this->actingAsUserOne();
        $body = $this->postWithClientId('/api/v2/measurements/request_csv');
        $export = MeasurementExportRequest::findMeasurementExportRequest($body['exportId']);
        $this->assertNotNull($export);
        $this->assertEquals(MeasurementExportRequest::STATUS_WAITING, $export->status);
    }
    public function testExportMeasurements(){
        MeasurementExportRequest::truncate();
        $exportId = MeasurementExportRequest::createExportRequestRecord($this->getOrSetAuthenticatedUser(1),
            MeasurementExportRequest::TYPE_USER, BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT);
        $this->assertNotNull($exportId);
        $u = User::find(1);
        $u->unsubscribed = false;
        $u->save();
        $requests = MeasurementExportRequest::sendAllWaitingOrStuckExportRequests();
        $this->assertCount(1,$requests);
        foreach($requests as $r){
            $this->assertEquals(MeasurementExportRequest::STATUS_FULFILLED, $r->status);
        }
        $this->compareLastEmail();
    }
}
