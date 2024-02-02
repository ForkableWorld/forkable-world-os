<?php

namespace Tests\Feature\API\GithubRepositories;

class UpdateGithubRepositoriesTest extends BaseGithubRepositoriesTest
{
    public function testUpdate()
    {
        $user = $this->login();
        $item = $this->makeSave(['user_id' => $user->id]);
        $response = $this->patchJson($this->getURI($item->id), $item->toArray());
        $response->assertSuccessful();
    }
}
