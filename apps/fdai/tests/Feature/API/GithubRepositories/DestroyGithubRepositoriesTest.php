<?php

namespace Tests\Feature\API\GithubRepositories;

class DestroyGithubRepositoriesTest extends BaseGithubRepositoriesTest
{

    public function testDelete()
    {
        $user = $this->login();
        $item = $this->makeSave(['user_id' => $user->id]);
        $response = $this->deleteJson($this->getURI($item->id));
        $response->assertSuccessful();
    }

}
