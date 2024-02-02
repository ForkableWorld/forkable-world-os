<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\UnitTestCase;
use Tests\ApiTestTrait;
use App\Models\WpPost;

class WpPostApiTest extends UnitTestCase
{
    use ApiTestTrait;

    public function test_create_wp_post()
    {
        $this->skipTest('Not implemented.');
        $wpPost = WpPost::factory()->make()->toArray();

        $r = $this->jsonAsUser18535(
            'POST',
            '/api/v6/wp_posts', $wpPost
        );

        $this->assertApiResponse($wpPost);
    }

    public function test_read_wp_post()
    {
        $this->skipTest('Not implemented.');
        $wpPost = WpPost::fakeSaveFromPropertyModels();

        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/wp_posts/'.$wpPost->ID
        );

        $this->assertApiResponse($wpPost->toArray());
    }

    public function test_update_wp_post()
    {
        $this->skipTest('Not implemented.');
        $wpPost = WpPost::fakeSaveFromPropertyModels();
        $editedWpPost = WpPost::factory()->make()->toArray();

        $r = $this->jsonAsUser18535(
            'PUT',
            '/api/v6/wp_posts/'.$wpPost->ID,
            $editedWpPost
        );

        $this->assertApiResponse($editedWpPost);
    }

    public function test_delete_wp_post()
    {
        $this->skipTest('Not implemented.');
        $wpPost = WpPost::fakeSaveFromPropertyModels();

        $r = $this->jsonAsUser18535(
            'DELETE',
             '/api/v6/wp_posts/'.$wpPost->ID
         );

        $this->assertApiSuccess();
        $r = $this->jsonAsUser18535(
            'GET',
            '/api/v6/wp_posts/'.$wpPost->ID
        );

        $this->testResponse->assertStatus(404);
    }
}
