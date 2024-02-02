<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Users;
use Tests\SlimStagingTestCase;

class PostUserRegistrationTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostUserRegistration(): void {
        $str = json_encode($this->slimEnvironmentSettings);
        $str = str_replace('bucket_box', 'testuser-' . time(), $str);
        $this->slimEnvironmentSettings = json_decode($str, true);
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(20);
		$this->checkQueryCount(19);
	}
	public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD'          => 'POST',
        'REMOTE_ADDR'             => '10.190.186.194',
        'SCRIPT_NAME'             => '',
        'PATH_INFO'               => '/api/v3/userSettings',
        'SERVER_NAME'             => '_',
        'SERVER_PORT'             => '443',
        'HTTP_CDN_LOOP'           => 'cloudflare',
        'HTTP_CF_CONNECTING_IP'   => '17.222.113.220',
        'HTTP_ACCEPT_LANGUAGE'    => 'ja-jp',
        'HTTP_USER_AGENT'         => 'Mozilla/5.0 (iPad; CPU OS 12_1_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/16D57',
        'HTTP_ACCEPT'             => 'application/json',
        'HTTP_COOKIE'             => '__cfduid=d2516a57088caba3619e1f798a095c6ac1553204570',
        'HTTP_ORIGIN'             => 'file://',
        'HTTP_CONTENT_TYPE'       => 'application/json',
        'HTTP_CF_VISITOR'         => '{"scheme":"https"}',
        'HTTP_X_FORWARDED_PROTO'  => 'https',
        'HTTP_CF_RAY'             => '4bb31a5518086cbe-SJC',
        'HTTP_X_FORWARDED_FOR'    => '172.68.132.209',
        'HTTP_CF_IPCOUNTRY'       => 'US',
        'HTTP_ACCEPT_ENCODING'    => 'gzip',
        'CONTENT_LENGTH'          => '89',
        'CONTENT_TYPE'            => 'application/json',
        'slim.url_scheme'         => 'https',
        //'slim.input' => '{"pwd":"Apple123","log":"flyflyerson2@gmail.com","register":true,"pwdConfirm":"Apple123"}',
        'slim.input'              => '{"pwd":"theawesomepassword","log":"bucket_box","register":true,"pwdConfirm":"theawesomepassword"}',
        'slim.request.form_hash'  => [],
        'slim.request.query_hash' => [
    'appName' => 'MediModo',
    'appVersion' => '2.9.314',
    'clientId' => 'medimodo',
        ],
        'responseStatusCode'      => NULL,
        'unixtime'                => 1553204622,
        'requestDuration'         => 0.4445509910583496,
    ];
}
