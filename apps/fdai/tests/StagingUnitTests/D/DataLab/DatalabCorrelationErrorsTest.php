<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Models\UserVariableRelationship;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Properties\User\UserIdProperty;
use Illuminate\Testing\TestResponse;
use Tests\DatalabTestCase;
class DatalabCorrelationErrorsTest extends DatalabTestCase
{
    protected $REQUEST_URI = "/datalab/user_variable_relationships?internal_error_message=not%20null&sort=-analysis_started_at&draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=internal_error_message&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=gauge_link&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=cause_link&columns%5B3%5D%5Bname%5D=cause_variable.name&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=effect_follow_up_percent_change_from_baseline&columns%5B4%5D%5Bsearchable%5D=false&columns%5B5%5D%5Bdata%5D=effect_link&columns%5B5%5D%5Bname%5D=effect_variable.name&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=drop_down_button&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&columns%5B7%5D%5Bdata%5D=related_data&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=user_id_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&columns%5B9%5D%5Bdata%5D=analysis_requested_at&columns%5B9%5D%5Bsearchable%5D=false&columns%5B10%5D%5Bdata%5D=number_of_pairs&columns%5B10%5D%5Bsearchable%5D=false&columns%5B11%5D%5Bdata%5D=qm_score&columns%5B11%5D%5Bsearchable%5D=false&columns%5B12%5D%5Bdata%5D=z_score&columns%5B12%5D%5Bsearchable%5D=false&columns%5B13%5D%5Bdata%5D=analysis_ended_at&columns%5B13%5D%5Bsearchable%5D=false&columns%5B14%5D%5Bdata%5D=post_link&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=false&columns%5B15%5D%5Bdata%5D=created_at&columns%5B15%5D%5Bsearchable%5D=false&columns%5B16%5D%5Bdata%5D=deleted_at&columns%5B16%5D%5Bsearchable%5D=false&columns%5B17%5D%5Bdata%5D=experiment_end_at&columns%5B17%5D%5Bsearchable%5D=false&columns%5B18%5D%5Bdata%5D=experiment_start_at&columns%5B18%5D%5Bsearchable%5D=false&columns%5B19%5D%5Bdata%5D=updated_at&columns%5B19%5D%5Bsearchable%5D=false&columns%5B20%5D%5Bdata%5D=published_at&columns%5B20%5D%5Bsearchable%5D=false&columns%5B21%5D%5Bdata%5D=newest_data_at&columns%5B21%5D%5Bsearchable%5D=false&columns%5B22%5D%5Bdata%5D=analysis_started_at&columns%5B22%5D%5Bsearchable%5D=false&columns%5B23%5D%5Bdata%5D=latest_measurement_start_at&columns%5B23%5D%5Bsearchable%5D=false&columns%5B24%5D%5Bdata%5D=earliest_measurement_start_at&columns%5B24%5D%5Bsearchable%5D=false&columns%5B25%5D%5Bdata%5D=aggregated_at&columns%5B25%5D%5Bsearchable%5D=false&columns%5B26%5D%5Bdata%5D=action&columns%5B26%5D%5Bsearchable%5D=false&columns%5B26%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=12&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1595038987226";
    public function testDataTableCorrelationsAsRegularUser(): void{
        $this->skipTest("Not sure why this keeps changing");
        return;
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        $this->checkTestDuration(7);
        $this->checkQueryCount(9);
        $this->assertCount(0, $this->lastResponseData('data'));
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        $this->assertDataTableQueriesEqual(array (
            0 => 'select * from `wp_users` where `wp_users`.`ID` = ? and `wp_users`.`deleted_at` is null limit 1',
            1 => 'select count(*) as aggregate from (select * from `user_variable_relationships` where `user_variable_relationships`.`user_id` in (?) and `user_variable_relationships`.`internal_error_message` is not null and `user_variable_relationships`.`deleted_at` is null order by `user_variable_relationships`.`z_score` desc) count_row_table',
            2 => 'select * from `user_variable_relationships` where `user_variable_relationships`.`user_id` in (?) and `user_variable_relationships`.`internal_error_message` is not null and `user_variable_relationships`.`deleted_at` is null order by `user_variable_relationships`.`z_score` desc limit 10 offset 0',
        ));
    }
    public function testDataTableCorrelationsAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(28);
        $this->checkQueryCount(14);
        $this->assertCount(10, $this->lastResponseData('data'), UserVariableRelationship::generateErrorsIndexUrl());
        $this->assertDataTableQueryCount(12);
    }
    public function testDataTableCorrelationsWithoutAuth(): void{
        $this->assertUnauthenticatedResponse();
    }
    /**
     * @param int $expectedCode
     * @param string|null $expectedString
     * @return string|object
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state(array(
   'json' => NULL,
   'convertedFiles' => NULL,
   'userResolver' => NULL,
   'routeResolver' => NULL,
   'attributes' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'request' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'internal_error_message' => 'not null',
      'sort' => '-analysis_started_at',
      'draw' => '1',
      'columns' =>
      array (
        0 =>
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        1 =>
        array (
          'data' => 'internal_error_message',
          'orderable' => 'false',
        ),
        2 =>
        array (
          'data' => 'gauge_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'cause_link',
          'name' => 'cause_variable.name',
          'orderable' => 'false',
        ),
        4 =>
        array (
          'data' => 'effect_follow_up_percent_change_from_baseline',
          'searchable' => 'false',
        ),
        5 =>
        array (
          'data' => 'effect_link',
          'name' => 'effect_variable.name',
          'orderable' => 'false',
        ),
        6 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        7 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        8 =>
        array (
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        9 =>
        array (
          'data' => 'analysis_requested_at',
          'searchable' => 'false',
        ),
        10 =>
        array (
          'data' => 'number_of_pairs',
          'searchable' => 'false',
        ),
        11 =>
        array (
          'data' => 'qm_score',
          'searchable' => 'false',
        ),
        12 =>
        array (
          'data' => 'z_score',
          'searchable' => 'false',
        ),
        13 =>
        array (
          'data' => 'analysis_ended_at',
          'searchable' => 'false',
        ),
        14 =>
        array (
          'data' => 'post_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        15 =>
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        16 =>
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        17 =>
        array (
          'data' => 'experiment_end_at',
          'searchable' => 'false',
        ),
        18 =>
        array (
          'data' => 'experiment_start_at',
          'searchable' => 'false',
        ),
        19 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        20 =>
        array (
          'data' => 'published_at',
          'searchable' => 'false',
        ),
        21 =>
        array (
          'data' => 'newest_data_at',
          'searchable' => 'false',
        ),
        22 =>
        array (
          'data' => 'analysis_started_at',
          'searchable' => 'false',
        ),
        23 =>
        array (
          'data' => 'latest_measurement_start_at',
          'searchable' => 'false',
        ),
        24 =>
        array (
          'data' => 'earliest_measurement_start_at',
          'searchable' => 'false',
        ),
        25 =>
        array (
          'data' => 'aggregated_at',
          'searchable' => 'false',
        ),
        26 =>
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'order' =>
      array (
        0 =>
        array (
          'column' => '12',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1595038987226',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'internal_error_message' => 'not null',
      'sort' => '-analysis_started_at',
      'draw' => '1',
      'columns' =>
      array (
        0 =>
        array (
          'data' => 'open_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        1 =>
        array (
          'data' => 'internal_error_message',
          'orderable' => 'false',
        ),
        2 =>
        array (
          'data' => 'gauge_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'cause_link',
          'name' => 'cause_variable.name',
          'orderable' => 'false',
        ),
        4 =>
        array (
          'data' => 'effect_follow_up_percent_change_from_baseline',
          'searchable' => 'false',
        ),
        5 =>
        array (
          'data' => 'effect_link',
          'name' => 'effect_variable.name',
          'orderable' => 'false',
        ),
        6 =>
        array (
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        7 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        8 =>
        array (
          'data' => 'user_id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        9 =>
        array (
          'data' => 'analysis_requested_at',
          'searchable' => 'false',
        ),
        10 =>
        array (
          'data' => 'number_of_pairs',
          'searchable' => 'false',
        ),
        11 =>
        array (
          'data' => 'qm_score',
          'searchable' => 'false',
        ),
        12 =>
        array (
          'data' => 'z_score',
          'searchable' => 'false',
        ),
        13 =>
        array (
          'data' => 'analysis_ended_at',
          'searchable' => 'false',
        ),
        14 =>
        array (
          'data' => 'post_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        15 =>
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        16 =>
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        17 =>
        array (
          'data' => 'experiment_end_at',
          'searchable' => 'false',
        ),
        18 =>
        array (
          'data' => 'experiment_start_at',
          'searchable' => 'false',
        ),
        19 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        20 =>
        array (
          'data' => 'published_at',
          'searchable' => 'false',
        ),
        21 =>
        array (
          'data' => 'newest_data_at',
          'searchable' => 'false',
        ),
        22 =>
        array (
          'data' => 'analysis_started_at',
          'searchable' => 'false',
        ),
        23 =>
        array (
          'data' => 'latest_measurement_start_at',
          'searchable' => 'false',
        ),
        24 =>
        array (
          'data' => 'earliest_measurement_start_at',
          'searchable' => 'false',
        ),
        25 =>
        array (
          'data' => 'aggregated_at',
          'searchable' => 'false',
        ),
        26 =>
        array (
          'data' => 'action',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
      ),
      'order' =>
      array (
        0 =>
        array (
          'column' => '12',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => '',
      ),
      '_' => '1595038987226',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '_ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; XDEBUG_SESSION=PHPSTORM; php-console-server=5; php-console-client=eyJwaHAtY29uc29sZS1jbGllbnQiOjV9; __gads=ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA; __utmz=109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __utmc=109117957; __utma=109117957.2014257657.1592502511.1593836440.1593836440.1; _gid=GA1.2.1245501381.1594832893; _ga=GA1.2.1415477443.1592425993; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1596214177%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; driftt_eid=230; __cfduid=d78c81ea746227e1437c0203e9b6018d71595018221; XSRF-TOKEN=eyJpdiI6ImZJZWl6MTVNM1BiSEEyWHFaYW5vXC9nPT0iLCJ2YWx1ZSI6IllTa01jVWxPV21iZXRlRFp0RlQ1QWQrcjVJWUJSWlNYanVBd2NJNmp5ZkhJRTBlRkVlcDdTeVlQVEJEdUJZZ2ciLCJtYWMiOiI2MzFiMDk1ODhjMzBjOWEyMDRhNzhkMGVmNzBiM2NhZTMxODQ4OWZmMzk4YTkyNjAxNDcxZjI1ZmZhODQ3ZGVjIn0%3D; laravel_session=eyJpdiI6IkpiUlBjZldnN05TaWpPSnJSSHlQVHc9PSIsInZhbHVlIjoicTFBeVVhelQ2SUhjV2Zid0RNS091WWJGUk1wMnJHaWdSYnhpR0ZzcWdqV2w5a0NMMkhpYjFPVThYRFY0TlNJWSIsIm1hYyI6IjJiMWY5ZjU4NGExMGU2MWZjNWY0OGM3NmM4OTdlNzc4ZGRjMTE3NjkzNTUxZWZlYzllNDlhNWFjMTEzM2I3NzEifQ%3D%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '62686',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'internal_error_message=not%20null&sort=-analysis_started_at&draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=internal_error_message&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=gauge_link&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=cause_link&columns%5B3%5D%5Bname%5D=cause_variable.name&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=effect_follow_up_percent_change_from_baseline&columns%5B4%5D%5Bsearchable%5D=false&columns%5B5%5D%5Bdata%5D=effect_link&columns%5B5%5D%5Bname%5D=effect_variable.name&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=drop_down_button&columns%5B6%5D%5Bsearchable%5D=false&columns%5B6%5D%5Borderable%5D=false&columns%5B7%5D%5Bdata%5D=related_data&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=user_id_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&columns%5B9%5D%5Bdata%5D=analysis_requested_at&columns%5B9%5D%5Bsearchable%5D=false&columns%5B10%5D%5Bdata%5D=number_of_pairs&columns%5B10%5D%5Bsearchable%5D=false&columns%5B11%5D%5Bdata%5D=qm_score&columns%5B11%5D%5Bsearchable%5D=false&columns%5B12%5D%5Bdata%5D=z_score&columns%5B12%5D%5Bsearchable%5D=false&columns%5B13%5D%5Bdata%5D=analysis_ended_at&columns%5B13%5D%5Bsearchable%5D=false&columns%5B14%5D%5Bdata%5D=post_link&columns%5B14%5D%5Bsearchable%5D=false&columns%5B14%5D%5Borderable%5D=false&columns%5B15%5D%5Bdata%5D=created_at&columns%5B15%5D%5Bsearchable%5D=false&columns%5B16%5D%5Bdata%5D=deleted_at&columns%5B16%5D%5Bsearchable%5D=false&columns%5B17%5D%5Bdata%5D=experiment_end_at&columns%5B17%5D%5Bsearchable%5D=false&columns%5B18%5D%5Bdata%5D=experiment_start_at&columns%5B18%5D%5Bsearchable%5D=false&columns%5B19%5D%5Bdata%5D=updated_at&columns%5B19%5D%5Bsearchable%5D=false&columns%5B20%5D%5Bdata%5D=published_at&columns%5B20%5D%5Bsearchable%5D=false&columns%5B21%5D%5Bdata%5D=newest_data_at&columns%5B21%5D%5Bsearchable%5D=false&columns%5B22%5D%5Bdata%5D=analysis_started_at&columns%5B22%5D%5Bsearchable%5D=false&columns%5B23%5D%5Bdata%5D=latest_measurement_start_at&columns%5B23%5D%5Bsearchable%5D=false&columns%5B24%5D%5Bdata%5D=earliest_measurement_start_at&columns%5B24%5D%5Bsearchable%5D=false&columns%5B25%5D%5Bdata%5D=aggregated_at&columns%5B25%5D%5Bsearchable%5D=false&columns%5B26%5D%5Bdata%5D=action&columns%5B26%5D%5Bsearchable%5D=false&columns%5B26%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=12&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1595038987226',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',

      'VERSION_GIT_REMOTE_REPOSITORY' => 'https://github.com/mikepsinn/qm-api.git',
      'HTTP_CONTENT_LENGTH' => '',
      'HTTP_CONTENT_TYPE' => '',
    ),
  )),
   'files' =>
  QMFileBag::__set_state(array(
     'parameters' =>
    array (
    ),
  )),
   'cookies' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      '_ga' => 'GA1.1.1415477443.1592425993',
      'driftt_aid' => 'c8735c86-68c0-4de2-94ce-73a43605c5c5',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '6ec3e62cc0069a10c6759edd5423df738a0fec0b',
      'XDEBUG_SESSION' => 'PHPSTORM',
      'php-console-server' => '5',
      'php-console-client' => 'eyJwaHAtY29uc29sZS1jbGllbnQiOjV9',
      '__gads' => 'ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA',
      '__utmz' => '109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/',
      '__utmc' => '109117957',
      '__utma' => '109117957.2014257657.1592502511.1593836440.1593836440.1',
      '_gid' => 'GA1.2.1245501381.1594832893',
      'final_callback_url' => 'https://web.quantimo.do/index.html#/app/login?client_id=quantimodo',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1596214177|de069cf4b3bbf933721060a76259dad7|quantimodo',
      'driftt_eid' => '230',
      '__cfduid' => 'd78c81ea746227e1437c0203e9b6018d71595018221',
      'XSRF-TOKEN' => 'eyJpdiI6ImZJZWl6MTVNM1BiSEEyWHFaYW5vXC9nPT0iLCJ2YWx1ZSI6IllTa01jVWxPV21iZXRlRFp0RlQ1QWQrcjVJWUJSWlNYanVBd2NJNmp5ZkhJRTBlRkVlcDdTeVlQVEJEdUJZZ2ciLCJtYWMiOiI2MzFiMDk1ODhjMzBjOWEyMDRhNzhkMGVmNzBiM2NhZTMxODQ4OWZmMzk4YTkyNjAxNDcxZjI1ZmZhODQ3ZGVjIn0=',
      'laravel_session' => 'eyJpdiI6IkpiUlBjZldnN05TaWpPSnJSSHlQVHc9PSIsInZhbHVlIjoicTFBeVVhelQ2SUhjV2Zid0RNS091WWJGUk1wMnJHaWdSYnhpR0ZzcWdqV2w5a0NMMkhpYjFPVThYRFY0TlNJWSIsIm1hYyI6IjJiMWY5ZjU4NGExMGU2MWZjNWY0OGM3NmM4OTdlNzc4ZGRjMTE3NjkzNTUxZWZlYzllNDlhNWFjMTEzM2I3NzEifQ==',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '_ga=GA1.1.1415477443.1592425993; driftt_aid=c8735c86-68c0-4de2-94ce-73a43605c5c5; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=6ec3e62cc0069a10c6759edd5423df738a0fec0b; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; XDEBUG_SESSION=PHPSTORM; php-console-server=5; php-console-client=eyJwaHAtY29uc29sZS1jbGllbnQiOjV9; __gads=ID=747672481a4ab161:T=1593795481:S=ALNI_MZhRWXjPuKtS5vGg1WXOPNKdVmSVA; __utmz=109117957.1593836440.1.1.utmcsr=local.quantimo.do|utmccn=(referral)|utmcmd=referral|utmcct=/; __utmc=109117957; __utma=109117957.2014257657.1592502511.1593836440.1593836440.1; _gid=GA1.2.1245501381.1594832893; _ga=GA1.2.1415477443.1592425993; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1596214177%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; driftt_eid=230; __cfduid=d78c81ea746227e1437c0203e9b6018d71595018221; XSRF-TOKEN=eyJpdiI6ImZJZWl6MTVNM1BiSEEyWHFaYW5vXC9nPT0iLCJ2YWx1ZSI6IllTa01jVWxPV21iZXRlRFp0RlQ1QWQrcjVJWUJSWlNYanVBd2NJNmp5ZkhJRTBlRkVlcDdTeVlQVEJEdUJZZ2ciLCJtYWMiOiI2MzFiMDk1ODhjMzBjOWEyMDRhNzhkMGVmNzBiM2NhZTMxODQ4OWZmMzk4YTkyNjAxNDcxZjI1ZmZhODQ3ZGVjIn0%3D; laravel_session=eyJpdiI6IkpiUlBjZldnN05TaWpPSnJSSHlQVHc9PSIsInZhbHVlIjoicTFBeVVhelQ2SUhjV2Zid0RNS091WWJGUk1wMnJHaWdSYnhpR0ZzcWdqV2w5a0NMMkhpYjFPVThYRFY0TlNJWSIsIm1hYyI6IjJiMWY5ZjU4NGExMGU2MWZjNWY0OGM3NmM4OTdlNzc4ZGRjMTE3NjkzNTUxZWZlYzllNDlhNWFjMTEzM2I3NzEifQ%3D%3D',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'accept-encoding' =>
      array (
        0 => 'gzip, deflate, br',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'document',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'navigate',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'none',
      ),
      'accept' =>
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
      ),
      'upgrade-insecure-requests' =>
      array (
        0 => '1',
      ),
      'connection' =>
      array (
        0 => 'keep-alive',
      ),
      'host' =>
      array (
        0 => 'local.quantimo.do',
      ),
      'content-length' =>
      array (
        0 => '',
      ),
      'content-type' =>
      array (
        0 => '',
      ),
    ),
     'cacheControl' =>
    array (
    ),
  )),
   'content' => NULL,
   'languages' => NULL,
   'charsets' => NULL,
   'encodings' => NULL,
   'acceptableContentTypes' => NULL,
   'pathInfo' => NULL,
   'requestUri' => NULL,
   'baseUrl' => NULL,
   'basePath' => NULL,
   'method' => NULL,
   'format' => NULL,
   'session' => NULL,
   'locale' => NULL,
   'defaultLocale' => 'en',
   'preferredFormat' => NULL,
   'isHostValid' => true,
   'isForwardedValid' => true,
));
		return  $this->callAndCheckResponse($expectedCode, $expectedString);
	}
}
