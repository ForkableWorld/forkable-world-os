<?php namespace Tests\APIs;

use App\Models\TrackingReminder;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Properties\User\UserIdProperty;
use Database\Factories\MeasurementFactory;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\Measurement;

class MeasurementApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_measurement()
    {
        UserVariableClient::deleteAll();
        Measurement::deleteAll();
        TrackingReminder::deleteAll();
        UserVariable::deleteAll();
        $input = MeasurementFactory::getData();
        $input['value'] = 1;
        $r = $this->jsonAsUser18535('POST', '/api/v6/measurements', $input);
        $r->assertStatus(201);
        $responseData = $this->getJsonResponseData();
        $this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $responseData["measurements"][0]["user_id"]);
	    $id = $responseData["measurements"][0]["id"];
	    $r = $this->jsonAsUser18535('GET', '/api/v6/measurements/'.$id);
        $r->assertStatus(200);
	    $r = $this->getTestResponse();
	    $measurementResponse = $r->json();
        $expected['note'] = ['message' => $input['note']];
        $this->assertContains($expected, $measurementResponse['data']);
        $newData = ['value' => 5];
        $r = $this->jsonAsUser18535('PUT', '/api/v6/measurements/'.$id, $newData);
        $r->assertStatus(201);
	    $r = $this->getTestResponse();
	    $responseData = $r->json();
        $this->assertEquals($newData['value'], $responseData["data"]['value']);
        $r = $this->jsonAsUser18535('DELETE', '/api/v6/measurements/'.$id);
        $r->assertStatus(204);
        $this->expectModelNotFoundException();
        $r = $this->jsonAsUser18535('GET', '/api/v6/measurements/'.$id);
        $r->assertStatus(404);
    }
}
