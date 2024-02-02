<?php

namespace Tests\Feature\API\GithubRepositories;

class ShowGithubRepositoriesTest extends BaseGithubRepositoriesTest
{
    public function testShow()
    {
        $user = $this->login();
        $item = $this->makeSave(['user_id' => $user->id]);
        $response = $this->getJson($this->getURI($item->id));
        $response->assertSuccessful();
    }
}
