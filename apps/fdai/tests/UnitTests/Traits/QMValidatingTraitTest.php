<?php
namespace Tests\UnitTests\Traits;
use App\Properties\Base\BaseClientIdProperty;
use App\Types\QMStr;
use App\Utils\UrlHelper;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Traits\QMValidatingTrait;
 */
class QMValidatingTraitTest extends UnitTestCase {
	/**
	 * @covers QMStr::assertIsUrl
	 * @noinspection PhpUnitMissingTargetForTestInspection (from a trait)
	 */
	public function testUrlInvalid(){
		$this->assertFalse(UrlHelper::urlInvalid("http://localhost"));
		$this->assertFalse(UrlHelper::urlInvalid("http://localhost/api/v1/variables/causes?limit=200&appName=MoodiModo&appVersion=2.1.1.0&client_id=" . BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT));
		$this->assertFalse(UrlHelper::urlInvalid("http://localhost/api/v1/variables/BodyMassIndexOrBMI/causes?limit=200&appName=MoodiModo&appVersion=2.1.1.0&client_id=" . BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT));
		$this->assertFalse(UrlHelper::urlInvalid($phpUnitUrl =
            \App\Utils\Env::getAppUrl() . "/dev/test?test=%3C%3Fphp+%2F%2A%2A+%40noinspection+PhpUnhandledExceptionInspection+%2A%2F%0A%2F%2A%2A+%40noinspection+PhpUnusedLocalVariableInspection+%2A%2F%0Anamespace+App%5CPhpUnitJobs%5CImport%3B%0Ause+Tests%5CSlimStagingTestCase%3B%0Ause+App%5CDataSources%5CConnection%3B%0Aclass+ConnectionUser1SourceAirQualityTest+extends+SlimStagingTestCase%0A%7B%0A++++public+function+testConnectionUser1SourceAirQuality%28%29%3A+void%7B%0A%09%09Connection%3A%3Afind%281%29-%3Etest%28%29%3B%0A%09%09%24this-%3EcheckTestDuration%2810%29%3B%0A%09%09%24this-%3EcheckQueryCount%285%29%3B%0A%09%7D%0A%7D%0A&filename=ConnectionUser1SourceAirQualityTest.php"));
		QMStr::assertIsUrl($phpUnitUrl, "PHP Unit Test");
	}
}
