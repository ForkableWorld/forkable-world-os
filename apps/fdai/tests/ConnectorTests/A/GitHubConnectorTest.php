<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\Models\Measurement;
use App\DataSources\Connectors\GithubConnector;
use Tests\ConnectorTests\ConnectorTestCase;
/**
 * Class GitHubTest
 * @package Tests\Api\Connectors1
 * @covers \App\DataSources\Connectors\GithubConnector
 */
class GitHubConnectorTest extends ConnectorTestCase {
    public const DISABLED_UNTIL = "2021-09-19"; // Probably need to update auth method
    protected $variablesToCheck = ["Github Code Commits to mikepsinn/curedao-api"];
    protected $connectorName = GithubConnector::NAME;
    public $requireNote = true;
	/**
	 * @covers \App\DataSources\Connectors\GithubConnector::importData
	 */
	public function testGithub(){
        if(!class_exists(\App\DataSources\Connectors\Responses\Github\Repo::class)){
            le("No \App\DataSources\Connectors\Responses\Github\Repo");
        }
        if(!class_exists(\App\DataSources\Connectors\Responses\Github\Repo::class)){
            le("No App\DataSources\Connectors\Responses\Github\Repo");
        }
        if($this->weShouldSkip()){return;}
        //$this->fromTime = ConnectionImportedDataFromAtProperty::generateEarliestUnixTime();
        $this->fromTime = time() - 60 * 86400; // Need to go far back because it only gets main branch
        $this->connectImportCheckDisconnect([]);
        $this->checkGithubMeasurements();
        $this->checkConnectorLogin();
    }
    protected function checkGithubMeasurements(): void{
        $measurements = Measurement::all();
        foreach($measurements as $l){
            $m = $l->getDBModel();
            $containsMessage = strpos($m->note, '"message"');
            if($containsMessage){
                $this->assertFalse($containsMessage, "Note is: $m->note");
            }
            $meta = $m->getAdditionalMetaData();
            $this->assertFalse(strpos($meta->message, '"message"'));
            $this->assertNotNull($m->additionalMetaData);
            $this->assertNotNull($meta->message);
            $this->assertNotNull($meta->url);
            $message = $m->getAdditionalMetaData()->getMessage();
            $this->assertNotEmpty($message);
            $this->assertIsString($message, "should contain commit message");
        }
    }
}
