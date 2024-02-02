<?php namespace Tests\APIs;

use App\DataSources\Connectors\FitbitConnector;
use App\Exceptions\UnauthorizedException;
use App\Http\Resources\ConnectorResource;
use App\Models\Connection;
use Database\Seeders\ConnectorsTableSeeder;
use Illuminate\Testing\TestResponse;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\Connector;

class ConnectorApiTest extends UnitTestCase
{
    use ApiTestTrait;


    public function test_get_connectors()
    {
        $this->assertArrayEquals(array (
            0 => 'qm_client',
            1 => 'wp_post_id',
        ), Connector::getDeprecatedAttributes());
        $r = $this->jsonAsUser18535(
            'GET',
            $this->getV6BasePathForClassTested()
        );
        $r->assertStatus(200);
        $r->assertJsonCount(24, 'data');
        $this->checkGetResponse(array (
	                                0 => 'worldweatheronline',
	                                1 => 'rescuetime',
	                                2 => 'fitbit',
	                                3 => 'facebook',
	                                4 => 'googlefit',
	                                5 => 'myfitnesspal',
	                                6 => 'quantimodo',
	                                7 => 'withings',
	                                8 => 'github',
	                                9 => 'whatpulse',
	                                10 => 'sleepcloud',
	                                11 => 'air-quality',
	                                12 => 'daylight',
	                                13 => 'pollen-count',
	                                14 => 'mynetdiary',
	                                15 => 'runkeeper',
	                                16 => 'mint-spreadsheet',
	                                17 => 'netatmo',
	                                18 => 'twitter',
	                                19 => 'general_spreadsheet',
	                                20 => 'medhelper',
	                                21 => 'googleplus',
	                                22 => 'moodimodochrome',
	                                23 => 'slack',
                                ));
    }

    public function test_create_connector()
    {
        $connector = Connector::find(FitbitConnector::ID);
        $arr = $connector->toArray();
        $arr['name'] = 'Test Connector';
        unset($arr['id']);
        unset($arr['slug']);
        $this->expectUnauthorizedException();
        $path = $this->getV6BasePathForClassTested();
        $r = $this->json(
            'POST',
            $path, $arr
        );
        $r->assertStatus(401);
    }

    public function test_read_connector()
    {
        $connector = Connector::find(FitbitConnector::ID);
        if(!$connector){
            (new ConnectorsTableSeeder())->run();
        }
        $this->assertTrue((bool)$connector->is_public);
        $this->assertTrue((bool)$connector->enabled);
        $this->assertNull($connector->deleted_at);
        $this->find($connector);
        $this->update();
        $this->delete_request();
        $this->list();
    }

    /**
     * @return TestResponse
     */
    private function update(): TestResponse
    {
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/connectors/' . FitbitConnector::ID,
            ['name' => 'you suck']
        );
        $r->assertStatus(401);
        return $r;
    }

    /**
     * @param $connector
     * @return TestResponse
     */
    private function find($connector): TestResponse
    {
        $this->assertEquals(FitbitConnector::ID, $connector->id);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/connectors/' . FitbitConnector::ID
        );
        $expected = $connector->toArray();
        $expected = ConnectorResource::anonymousConnectorFormat($expected);
        $expected = Connector::removeDeprecatedAttributesFromArray($expected);
        $expected = json_decode(json_encode($expected), true);

        $r->assertStatus(200)
            ->assertJson(['data' => $expected]);
        return $r;
    }

    /**
     * @return TestResponse
     */
    private function delete_request(): TestResponse
    {
        $this->expectUnauthorizedException();
        $r = $this->jsonAsUser18535(
            'DELETE',
            '/api/v6/connectors/' . FitbitConnector::ID
        );
        $r->assertStatus(401);
        return $r;
    }

    /**
     * @return void
     */
    private function list(): void{
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/connectors'
        );
        $r->assertStatus(200);
        $data = $this->getJsonResponseData();
        foreach ($data as $name => $connector){
            //$this->assertEquals($connector[Connector::FIELD_DISPLAY_NAME], $name);
            $this->assertHasRequiredAttributes($connector);
        }
    }

}
