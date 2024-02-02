<?php

namespace Tests\Feature\API\GithubRepositories;

class StoreGithubRepositoriesTest extends BaseGithubRepositoriesTest
{
    public function testStore()
    {
        $user = $this->login();
        $item = $this->make();
        $response = $this->postJson($this->getURI(), $item->toArray());
        $response->assertSuccessful();
    }
}
