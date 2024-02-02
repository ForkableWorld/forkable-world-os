<?php

namespace Tests\UnitTests\Http\Controllers;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BaseClientSecretProperty;
use App\Properties\User\UserProviderIdProperty;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Http\Controllers\API\UserStudyAPIController
 */
class UserStudyControllerTest extends UnitTestCase
{
    public function testAnalyze()
    {
        $predictorVariableName = 'Daily Step Count';
        $outcomeVariableName = 'Heart Rate Variability (HRV)';
	    $providerId = '20361fef-e7df-45af-9890-9bc70c8bd7e5';
	    $postData = [
		    'predictor' => [
			    'measurements' => [
				    0 => [
					    'start_at' => '2022-05-7',
					    'value' => 8472,
				    ],
				    1 => [
					    'start_at' => '2022-05-8',
					    'value' => 3402,
				    ],
				    2 => [
					    'start_at' => '2022-05-9',
					    'value' => 3930,
				    ],
				    3 => [
					    'start_at' => '2022-05-10',
					    'value' => 9909,
				    ],
				    4 => [
					    'start_at' => '2022-05-11',
					    'value' => 4943,
				    ],
				    5 => [
					    'start_at' => '2022-05-12',
					    'value' => 9012,
				    ],
				    6 => [
					    'start_at' => '2022-05-13',
					    'value' => 1122,
				    ],
			    ],
			    'variable_name' => $predictorVariableName,
			    'unit_name' => 'count',
			    'variable_category_name' => 'Physical Activity',
		    ],
		    'outcome' => [
			    'measurements' => [
				    0 => [
					    'start_at' => '2022-05-7',
					    'value' => 34,
				    ],
				    1 => [
					    'start_at' => '2022-05-8',
					    'value' => 34,
				    ],
				    2 => [
					    'start_at' => '2022-05-9',
					    'value' => 32,
				    ],
				    3 => [
					    'start_at' => '2022-05-10',
					    'value' => 39,
				    ],
				    4 => [
					    'start_at' => '2022-05-11',
					    'value' => 41,
				    ],
				    5 => [
					    'start_at' => '2022-05-12',
					    'value' => 25,
				    ],
				    6 => [
					    'start_at' => '2022-05-13',
					    'value' => 30,
				    ],
			    ],
			    'variable_name' => $outcomeVariableName,
			    'unit_name' => 'milliseconds',
			    'variable_category_name' => 'Vital Signs',
		    ],
		    'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
		    'client_secret' => BaseClientSecretProperty::TEST_CLIENT_SECRET,
		    'your_user_id' => $providerId,
	    ];
		$this->assertEquals($providerId, UserProviderIdProperty::pluck($postData));
	    $data = $this->postApiV6("studies", $postData);
		$study = $data[0];
        $this->assertNotNull($study['statistics']);
        $this->assertHtmlContains([$predictorVariableName, $outcomeVariableName],
                                  $study["studyHtml"]["fullStudyHtml"], 'study-html');

    }
}
