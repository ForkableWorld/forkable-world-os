<?php namespace Tests\APIs;
use App\Models\Application;
use App\Models\User;
use App\Properties\Base\BaseClientIdProperty;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Tests\ApiTestTrait;
use Tests\UnitTestCase;
class ApplicationApiTest extends UnitTestCase
{
    use ApiTestTrait, InteractsWithDatabase;
    public function setUp(): void
    {
        parent::setUp();
        $this->assertDatabaseCount(User::TABLE, 11);
    }

    public function test_create_application()
    {
        $this->markTestSkipped('Not implemented yet.');
        Application::whereClientId(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT)
            ->forceDelete();
        $factory = Application::factory();
        /** @var Application $toCreate */
        $toCreate = $factory->make();
        if(is_array($toCreate)){
            dd($toCreate);
        }
        $this->assertNull($toCreate->id);
        $whereName = Application::whereAppDisplayName($toCreate->app_display_name);
        $this->assertNull($whereName->first());
        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/applications', $toCreate->toArray()
        );
        $r->assertStatus(201);
        $this->assertNotNull($created = $whereName->first());
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/applications/'.$created->id
        );
        $r->assertStatus(200);
        $this->assertEquals($toCreate->app_display_name, $created->app_display_name, "app_display_name");
        $this->assertNotNull($created->id, "Should have an ID");
        $this->assertEquals(BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT, $created->client_id, "client_id");
        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/applications/'.$created->id,
            $editedData = ['app_display_name' => 'test']
        );
        $r->assertStatus(201);
        $responseData = $this->getJsonResponseData();
        $this->assertEquals($editedData['app_display_name'], $responseData['app_display_name']);
        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/applications/'.$created->id
         );
        $r->assertStatus(204);
        $this->expectModelNotFoundException();
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/applications/'.$created->id
        );
        $r->assertStatus(404);
        $this->assertNull($whereName->first());
    }
}
