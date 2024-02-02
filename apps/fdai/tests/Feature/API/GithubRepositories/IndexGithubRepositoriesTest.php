<?php

namespace Tests\Feature\API\GithubRepositories;

class IndexGithubRepositoriesTest extends BaseGithubRepositoriesTest
{
    public function testIndex()
    {
        $response = $this->getJson($this->getURI());
        $response->assertSuccessful();
    }
}
