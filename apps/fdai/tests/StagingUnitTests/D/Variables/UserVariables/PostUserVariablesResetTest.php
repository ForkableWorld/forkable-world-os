<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Computers\ThisComputer;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\QMUser;
use App\Utils\AppMode;
use App\Variables\CommonVariables\FoodsCommonVariables\WaterCommonVariable;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class PostUserVariablesResetTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostUserVariablesReset(): void{
        $u = QMUser::getAnyOldTestUser();
        $cv = WaterCommonVariable::instance();
        $qUV = $cv->getOrCreateUserVariable($u->getId());
        AppMode::setIsApiRequest(true);
        $qUV->minimum_allowed_value = 2;
        $qUV->save();
        $uv = UserVariable::find($qUV->getId());
        $this->assertEquals(2, $uv->minimum_allowed_value);
        $this->slimEnvironmentSettings = array(
            'REQUEST_METHOD'          => 'POST',
            'REMOTE_ADDR'             => '192.168.10.1',
            'SCRIPT_NAME'             => '',
            'PATH_INFO'               => '/api/v3/userVariables/reset',
            'SERVER_NAME'             => ThisComputer::LOCAL_HOST_NAME,
            'SERVER_PORT'             => '443',
            'HTTP_ACCEPT_LANGUAGE'    => 'en-US,en;q=0.9',
            'HTTP_ACCEPT_ENCODING'    => 'gzip',
            'HTTP_REFERER'            => 'https://dev-web.quantimo.do/',
            'HTTP_SEC_FETCH_SITE'     => 'same-site',
            'HTTP_CONTENT_TYPE'       => 'application/json',
            'HTTP_SEC_FETCH_MODE'     => 'cors',
            'HTTP_USER_AGENT'         => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
            'HTTP_AUTHORIZATION'      => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
            'HTTP_ORIGIN'             => 'https://dev-web.quantimo.do',
            'HTTP_ACCEPT'             => 'application/json',
            'HTTP_CONNECTION'         => 'keep-alive',
            'CONTENT_LENGTH'          => '22',
            'CONTENT_TYPE'            => 'application/json',
            'slim.url_scheme'         => 'https',
            'slim.input'              => json_encode(["variableId"=>$uv->getVariableIdAttribute()]),
            'slim.request.form_hash'  => array(),
            'slim.request.query_hash' => array(
                'appName'    => 'QuantiModo',
                'clientId'   => 'quantimodo',
                'appVersion' => '2.9.1022',
            ),
            'responseStatusCode'      => 201,
            'unixtime'                => 1572824949,
            'requestDuration'         => 7.989214897155762,
        );
        $expectedString = '';
        /** @var QMResponseBody $r */
        $r = $this->callAndCheckResponse($expectedString);
        /** @var QMUserVariable $uvStd */
        $uvStd = $r->data->userVariable;
        $cv = Variable::whereId($uvStd->variableId)->first();
        $this->assertEquals($cv->minimum_allowed_value, $uvStd->minimumAllowedValue);
        $uv = UserVariable::find($qUV->getId());
        $this->assertEquals($cv->minimum_allowed_value, $uv->minimum_allowed_value);
        $this->assertNull($uv->getRawAttribute(UserVariable::FIELD_MINIMUM_ALLOWED_VALUE));
        $this->checkTestDuration(20);
        $this->checkQueryCount(16);
    }
    public $expectedResponseSizes = array(
        'success' => 0.004,
        'data'    => 26.294,
    );
}
