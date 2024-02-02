<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\UserVariable;

class UserVariableApiTest extends UnitTestCase
{
    use ApiTestTrait, InteractsWithDatabase;
    //use LazilyRefreshDatabase;

    public function test_create_user_variable()
    {
        //$this->refreshDatabase();
        UserVariable::deleteAll();
        $factory = UserVariable::factory();
        $make = $factory->make();
        $arr = $make->getAttributes();
        $arr[UserVariable::FIELD_DESCRIPTION] = "before update";
        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/user_variables', $arr
        );
        $id = $this->getIdFromTestResponse();
        $this->assertApiResponse($arr);
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/user_variables/'.$id
        );
        $this->assertApiResponse($arr);
        $newData = [UserVariable::FIELD_DESCRIPTION => 'after update'];
        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/user_variables/'.$id,
            $newData
        );
        $r->assertStatus(201);
        $data = $this->getJsonResponseData();
        $this->assertContains($newData, $data);
        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/user_variables/'.$id
         );

        $r->assertStatus(204);
        $this->expectModelNotFoundException();
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/user_variables/'.$id
        );

        $r->assertStatus(404);
    }
}
