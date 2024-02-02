<?php namespace Tests\APIs;
use App\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\ApiTestCase;
use Tests\ApiTestTrait;
use App\Models\DeviceToken;

class DeviceTokenApiTest extends ApiTestCase
{
    use ApiTestTrait
        //WithoutMiddleware,
        //DatabaseTransactions
        ;
    public function test_create_device_token(){
        DeviceToken::query()->forceDelete();
		$this->assertArrayEquals(array (
			0 => 'device_token',
			1 => 'client_id',
			2 => 'created_at',
			3 => 'deleted_at',
			5 => 'error_message',
			6 => 'last_checked_at',
			7 => 'last_notified_at',
			8 => 'number_of_new_tracking_reminder_notifications',
			9 => 'number_of_notifications_last_sent',
			10 => 'number_of_waiting_tracking_reminder_notifications',
			11 => 'platform',
			12 => 'received_at',
			13 => 'server_hostname',
			14 => 'server_ip',
			15 => 'updated_at',
			16 => 'user_id',
		), DeviceToken::newModelInstance()->getFillable());
        /** @var DeviceToken $deviceToken */
        $deviceToken = DeviceToken::factory()->make();
        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/device_tokens', $deviceToken->toArray()
        );
        $this->assertApiResponse($deviceToken->toArray());
        $t = DeviceToken::find($deviceToken->device_token);
        $this->assertNotNull($t);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/device_tokens/'.$deviceToken->device_token
        );
        $this->assertApiResponse($deviceToken->toArray());
        self::expectUnauthorizedException(); // We're providing user_id 2, but the user is 18535
        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/device_tokens/'.$deviceToken->device_token,
            ['user_id' => 2]
        );
        $r->assertStatus(401);
        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/device_tokens/'.$deviceToken->device_token
         );

        $r->assertStatus(204);
        self::setExpectedRequestException(ModelNotFoundException::class);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/device_tokens/'.$deviceToken->device_token
        );

        $this->testResponse->assertStatus(404);
    }
}
