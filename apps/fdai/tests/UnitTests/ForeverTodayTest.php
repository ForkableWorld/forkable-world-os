<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests;
use App\Models\OAClient;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\UserVariableRelationship\CorrelationCauseVariableIdProperty;
use App\Utils\UrlHelper;
use Tests\UnitTestCase;

class ForeverTodayTest extends UnitTestCase {
    private $uuid = '20361fef-e7df-45af-9890-9bc70c8bd7e5';
    public function testForeverToday(){
        $json = '
        {"uuid":"' . $this->uuid . '","hrv":{"2022-05-7":34,"2022-05-8":34,"2022-05-9":32,"2022-05-10":39,"2022-05-11":41,"2022-05-12":25,"2022-05-13":30},"steps":{"2022-05-7":8472,"2022-05-8":3402,"2022-05-9":3930,"2022-05-10":9909,"2022-05-11":4943,"2022-05-12":9012,"2022-05-13":1122}}
        ';
        $original = json_decode($json, true);
        $measurements = [];
        foreach ($original['hrv'] as $key => $value){
            $measurements[] = [
                'start_at' => $key,
                'value' => $value,
                'variable_name' => 'hrv',
                'unit_name' => 'milliseconds',
                'variable_category_name' => 'Vital Signs',
            ];
        }
        foreach ($original['steps'] as $key => $value){
            $measurements[] = [
                'start_at' => $key,
                'value' => $value,
                'variable_name' => 'Daily Step Count',
                'unit_name' => 'count',
                'variable_category_name' => 'Physical Activity',
            ];
        }
        $client = OAClient::getTestClient();
        $body = [
            'measurements' => $measurements,
            'client_id' => $client->getClientId(),
            'client_secret' => $client->getClientSecret(),
            'provider_id' => $this->uuid ,
        ];
        $response = $this->postMeasurements($body);
        $user_variables = $response->user_variables;
        unset($body['measurements']);
        $this->assertCount(2, $user_variables);
	    $causeVariableNameOrId = CorrelationCauseVariableIdProperty::pluck(['cause' => 'Daily Step Count']);
        $study = $this->createUserStudy("Daily Step Count", "HRV", $body);
        $this->assertNotNull($study['html']);
        $this->assertNotNull($study['analysis']);
        //return;
        $this->actingAsUserId(1);
        $units = $this->getUnits();
	    $hrv = $this->getVariables('hrv');
	    $hrv = $hrv[0];
        $steps = $this->getVariables('daily step count')[0];

        $u = User::whereProviderId($this->uuid)->first();
        $u->forceDelete();
        $u = User::whereProviderId($this->uuid)->first();
        $this->assertNull($u);
        $this->createUser();
        $this->getUser();
        $v = $this->getVariables('hrv');
		$this->assertCount(1, $v);  
    }

    /**
     * @return void
     */
    private function createUser(): void
    {
        $response = $this->post('api/v6/users', [
            'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'client_secret' => BaseClientSecretProperty::TEST_CLIENT_SECRET,
            'provider_id' => $this->uuid
        ]);
        $this->assertEquals(201, $response->getStatusCode());
        $this->checkUserResponse($response);
    }

    /**
     * @return void
     */
    private function getUser(): void
    {
		$path = UrlHelper::addParam('api/v6/users', 'provider_id', $this->uuid);
        $response = $this->get($path, [
            'X-Client-ID' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
            'X-Client-Secret' => BaseClientSecretProperty::TEST_CLIENT_SECRET
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->checkUserResponse($response);
    }

    /**
     * @param $response
     * @return void
     */
    private function checkUserResponse($response): void
    {
        $got = json_decode($response->getContent());
        $got = $got->data;
		$user = $got[0];
        $this->assertEquals($this->uuid, $user->provider_id);
        $this->assertIsObject($user->access_token);
        $this->assertIsObject($user->refresh_token);
    }



}
