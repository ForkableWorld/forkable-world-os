<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use Tests\DBUnitTestCase;
use Tests\SlimTests\SlimTestCase;
class MaxLimitTest extends \Tests\SlimTests\SlimTestCase
{
    protected $fixtureFiles = [];
    public function testMaxLimit() {
        $this->expectQMException();
        $this->setAuthenticatedUser(1);
        $limit = 2001;
        $parameters = ['user' => 1, 'limit' => $limit];
        $response = $this->slimGet('/api/v3/measurements', $parameters, 400);
        $this->assertEquals(400, $response->getStatus(), DBUnitTestCase::getErrorMessageFromResponse($response));
        $body = json_decode($response->getBody(), true);
        $this->assertContains('Maximum limit is 1000', $body['error']['message']);
        $this->assertQueryCountLessThan(5);
    }
}
