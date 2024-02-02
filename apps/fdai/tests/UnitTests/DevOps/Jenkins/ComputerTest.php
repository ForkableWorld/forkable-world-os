<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\DevOps\Jenkins;
use App\Computers\JenkinsSlave;
use App\DevOps\JenkinsAPI\Client\Api\RemoteAccessApi;
use App\DevOps\JenkinsAPI\Client\Configuration;
use GuzzleHttp\Client;
use Tests\UnitTestCase;
class ComputerTest extends UnitTestCase
{
    public function testGetComputers()
    {
		$this->skipTest("too slow");
        $all = JenkinsSlave::all();
        $this->assertNotEmpty($all);
        return;
        // Configure HTTP basic authorization: jenkins_auth
        $config = Configuration::getDefaultConfiguration()
            //->setApiKey(\App\Utils\Env::get('JENKINS_TOKEN'))
            ->setDebug(true)
            ->setUsername(\App\Utils\Env::get('JENKINS_USERNAME'))
            ->setPassword(\App\Utils\Env::get('JENKINS_TOKEN'))
            //->setPassword(\App\Utils\Env::get('JENKINS_PASSWORD'))
        ;
        $apiInstance = new RemoteAccessApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
            new Client(),
            $config
        );
        $result = $apiInstance->getComputer(10);
        $this->assertIsString($result);
    }
}
