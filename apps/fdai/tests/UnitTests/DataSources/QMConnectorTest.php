<?php

namespace DataSources;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\Connectors\RescueTimeConnector;
use App\DataSources\Connectors\WeatherConnector;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\EnvironmentCommonVariables\CloudCoverCommonVariable;
use App\Variables\CommonVariables\PhysiqueCommonVariables\BodyFatCommonVariable;
use App\Variables\CommonVariables\TreatmentsCommonVariables\MelatoninCommonVariable;
use Tests\UnitTestCase;
class QMConnectorTest extends UnitTestCase
{
	/**
	 * @return void
	 * @covers \App\DataSources\QMConnector::getConnectUrlWithParams
	 */
	public function testGetConnectorUrl(){
		$c = RescueTimeConnector::instance();
		$url = $c->getConnectUrlWithParams();
		$this->assertEquals("https://testing.quantimo.do/api/v1/connectors/rescuetime/connect", $url);
	}
	/**
	 * @return void
	 * @covers \App\DataSources\QMConnector::addParamsToUrlAndRedirect
	 */
	public function testGetCallbackUrl(){
		$expected = 'http://localhost:8000/api/qm/callback';
		$r = $this->get('/auth/googleplus?final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fapp%2Fpublic%2F%3Flogout%3D0%23%2Fapp%2Flogin&clientId=quantimodo');
		$r->assertSee("Redirecting to https://accounts.google.com/o/oauth2/auth");
	}
	/**
	 * @return void
	 * @covers \App\DataSources\QMConnector::getConnectInstructions
	 */
	public function testInstructionsHtml(){
		$instructionsHtml = '';

		$ss = GeneralSpreadsheetImporter::instance();
		$instructionsHtml .= $ss->setInstructionsHtml(MelatoninCommonVariable::instance());
		
		$weather = WeatherConnector::instance();
		$instructionsHtml .= $weather->setInstructionsHtml(CloudCoverCommonVariable::instance());
		
		$mood = OverallMoodCommonVariable::instance();
		$instructionsHtml .= $mood->getTrackingInstructionsHtml();
		
		$fitbit = FitbitConnector::instance();
		$instructionsHtml .= $fitbit->setInstructionsHtml(BodyFatCommonVariable::instance());

		$this->compareHtmlFragment("instructions", $instructionsHtml);
		
	}
}
