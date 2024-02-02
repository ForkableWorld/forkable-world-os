<?php
namespace Tests\UnitTests\Utils;
use App\Files\TestArtifacts\TestLogsFile;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Utils\SecretHelper;
use Tests\UnitTestCase;

class SecretHelperTest extends UnitTestCase
{
    public function testTruncate(){
        /** @noinspection SpellCheckingInspection */
        $full = "sk_test_HuJNBhLDJueoS4yWpeLfrbdI";
        $truncatedSecret = $this->truncate($full);
        $this->assertEquals("sk_t", $truncatedSecret);
    }
    public function testSecretValuesCount(){
        $secretValues = SecretHelper::getSecretValues();
        $this->assertGreaterThanOrEqual(20, $secretValues);
    }
    public function testSecretNames(){
        $arr = SecretHelper::getSecretValues();
        $names = array_keys($arr);
        foreach($this->getSecretNames() as $name){
            if(!in_array($name, $names)){
                $val = \App\Utils\Env::get($name);
                continue;
            }
            $this->assertContains($name, $names);
        }
    }
    public function testSecretNotLogged(){
    	$this->skipTest("TODO");
        foreach($this->getSecretNames() as $key){
            $this->assertSecretNotLogged($key);
        }
    }
    /**
     * @param string $full
     * @return string
     */
    private function truncate(string $full): string{
        return QMStr::truncate($full, 4);
    }
    /**
     * @param string $key
     */
    private function assertSecretNotLogged(string $key): void{
        $value = \App\Utils\Env::get($key);
        if(!$value){
            QMLog::info("$key is EMPTY!");
            return;
        }
        QMLog::info("$key is $value");
        $logs = TestLogsFile::getData();
        $this->assertNotContains($value, $logs);
    }
    /**
     * @return string[]
     */
    private function getSecretNames(): array{
        $arr = [
            0  => 'CONNECTOR_ALPACA_CLIENT_SECRET',
            1  => 'CONNECTOR_AMAZON_CLIENT_SECRET',
            2  => 'CONNECTOR_EBAY_CLIENT_SECRET',
            3  => 'CONNECTOR_FACEBOOK_CLIENT_SECRET',
            4  => 'CONNECTOR_FITBIT_CLIENT_SECRET',
            5  => 'CONNECTOR_GITHUB_CLIENT_SECRET',
            6  => 'CONNECTOR_GOOGLE_CLIENT_SECRET',
            7  => 'CONNECTOR_LINKEDIN_CLIENT_SECRET',
            8  => 'CONNECTOR_NETATMO_CLIENT_SECRET',
            9  => 'CONNECTOR_OPEN_HUMANS_CLIENT_SECRET',
            10 => 'CONNECTOR_RESCUETIME_CLIENT_SECRET',
            11 => 'CONNECTOR_RUNKEEPER_CLIENT_SECRET',
            12 => 'CONNECTOR_SLACK_CLIENT_SECRET',
            13 => 'CONNECTOR_SLEEPCLOUD_CLIENT_SECRET',
            14 => 'CONNECTOR_SLICE_CLIENT_SECRET',
            15 => 'CONNECTOR_STRAVA_CLIENT_SECRET',
            16 => 'CONNECTOR_TWITTER_CLIENT_SECRET',
            17 => 'CONNECTOR_WITHINGS_CLIENT_SECRET',
            18 => 'DO_SPACES_SECRET',
            19 => 'STRIPE_API_SECRET',
            20 => 'GITHUB_ACCESS_TOKEN',
            21 => 'JENKINS_TOKEN',
            22 => 'QUANTIMODO_ACCESS_TOKEN',
            23 => 'JENKINS_PASSWORD',
            24 => 'MYSQL_PASSWORD',
            25 => 'SENDGRID_PASSWORD',
            26 => 'DB_URL',
            27 => 'DB_MIGRATION_PASS',
            28 => 'BUGSNAG_API_KEY',
            29 => 'GOOGLE_CLOUD_MESSAGING_API_KEY',
            30 => 'SENDGRID_API_KEY',
            31 => 'DB_PASSWORD',
        ];
        foreach ($_ENV as $key => $value) {
            if(SecretHelper::isSecretyName($key)){
                $arr[] = $key;
            }
        }
        $arr[] = 'STORAGE_SECRET_ACCESS_KEY';
        return $arr;
    }
}
