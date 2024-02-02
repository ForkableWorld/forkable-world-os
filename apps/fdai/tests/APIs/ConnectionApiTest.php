<?php namespace Tests\APIs;

use App\Properties\Connection\ConnectionConnectStatusProperty;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\Connection;

class ConnectionApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_connection()
    {
        Connection::deleteAll();
        $input = Connection::factory()->getData();
        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/connections', $input
        );
        $r->assertStatus(201);
        $data = $this->getJsonResponseData();
		$connection = $data[0];
        $this->assertContains($input, $connection);
        $this->getApiConnection($id = $connection['id']);
        $editedConnection = [Connection::FIELD_CONNECT_STATUS => ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED];

        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/connections/'.$id,
            $editedConnection
        );
        $r->assertStatus(201);
        $data = $this->getJsonResponseData();
        $this->assertContains($editedConnection, $data);
        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/connections/'.$id
         );
        $r->assertStatus(204);
        $this->assertApiSuccess();
        $this->expectModelNotFoundException();
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/connections/'.$id
        );

        $r->assertStatus(404);
    }

    /**
     * @param int $id
     * @return array
     */
    private function getApiConnection(int $id): array
    {

        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/connections/' . $id
        );
        $r->assertStatus(200);
        return $this->getJsonResponseData();
    }
}
