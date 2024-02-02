<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMFileBag;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use App\Properties\User\UserIdProperty;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
use Tests\QMBaseTestCase;
class DatalabTrackingRemindersTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/trackingReminders?userId=230&draw=6&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=variable_image_name_link&columns%5B3%5D%5Bname%5D=variable.name&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=frequency&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&columns%5B5%5D%5Bdata%5D=start_tracking_date&columns%5B5%5D%5Bsearchable%5D=false&columns%5B6%5D%5Bdata%5D=stop_tracking_date&columns%5B6%5D%5Bsearchable%5D=false&columns%5B7%5D%5Bdata%5D=user_variable_link&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=variable_category_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&columns%5B9%5D%5Bdata%5D=reminder_start_time&columns%5B9%5D%5Bsearchable%5D=false&columns%5B10%5D%5Bdata%5D=reminder_frequency&columns%5B10%5D%5Bsearchable%5D=false&columns%5B11%5D%5Bdata%5D=last_tracked&columns%5B11%5D%5Bsearchable%5D=false&columns%5B12%5D%5Bdata%5D=instructions&columns%5B13%5D%5Bdata%5D=image_url&columns%5B14%5D%5Bdata%5D=user_variable_id&columns%5B14%5D%5Bsearchable%5D=false&columns%5B15%5D%5Bdata%5D=latest_tracking_reminder_notification_notify_at&columns%5B15%5D%5Bsearchable%5D=false&columns%5B16%5D%5Bdata%5D=id_link&columns%5B16%5D%5Bsearchable%5D=false&columns%5B16%5D%5Borderable%5D=false&columns%5B17%5D%5Bdata%5D=created_at&columns%5B17%5D%5Bsearchable%5D=false&columns%5B18%5D%5Bdata%5D=updated_at&columns%5B18%5D%5Bsearchable%5D=false&columns%5B19%5D%5Bdata%5D=deleted_at&columns%5B19%5D%5Bsearchable%5D=false&columns%5B20%5D%5Bdata%5D=action&columns%5B20%5D%5Bsearchable%5D=false&columns%5B20%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=18&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=adderall&_=1602994075689";
    public function testSearchRemindersByVariableNameAsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->stagingRequest(200, "");
        foreach($this->lastResponseData('data') as $datum){
            $this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);
            $this->assertContains("Adderall", $datum->variable->name);
        }
        $this->checkTestDuration(10);
        $this->checkQueryCount(7);
        $this->assertCount(0, $this->lastResponseData('data'));
        foreach($this->lastResponseData('data') as $datum){$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, $datum->user_id);}
        $this->assertDataTableQueriesEqual(array (
            0 => 'select * from `oa_access_tokens` where `oa_access_tokens`.`access_token` = ? and `oa_access_tokens`.`deleted_at` is null limit 1',
            1 => 'select * from `wp_users` where `wp_users`.`ID` = ? and `wp_users`.`deleted_at` is null limit 1',
            2 => 'select count(*) as aggregate from (select * from `tracking_reminders` where `tracking_reminders`.`user_id` in (?) and `tracking_reminders`.`deleted_at` is null order by `tracking_reminders`.`updated_at` desc) count_row_table',
            3 => 'select count(*) as aggregate from (select * from `tracking_reminders` where `tracking_reminders`.`user_id` in (?) and (exists (select * from `variables` where `tracking_reminders`.`variable_id` = `variables`.`id` and LOWER(`variables`.`name`) LIKE ? and `variables`.`deleted_at` is null) or LOWER(`tracking_reminders`.`instructions`) LIKE ? or LOWER(`tracking_reminders`.`image_url`) LIKE ?) and `tracking_reminders`.`deleted_at` is null order by `tracking_reminders`.`updated_at` desc) count_row_table',
            4 => 'select * from `tracking_reminders` where `tracking_reminders`.`user_id` in (?) and (exists (select * from `variables` where `tracking_reminders`.`variable_id` = `variables`.`id` and LOWER(`variables`.`name`) LIKE ? and `variables`.`deleted_at` is null) or LOWER(`tracking_reminders`.`instructions`) LIKE ? or LOWER(`tracking_reminders`.`image_url`) LIKE ?) and `tracking_reminders`.`deleted_at` is null order by `tracking_reminders`.`updated_at` desc limit 10 offset 0',
        ));
    }
    public function testSearchRemindersByVariableNameAsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->stagingRequest(200, "");
        $this->checkTestDuration(10);
        $this->checkQueryCount(13);
        $this->assertCount(10, $this->lastResponseData('data'));
        foreach($this->lastResponseData('data') as $datum){
            $this->assertContains("Adderall", $datum->variable->name);
        }
        $this->assertDataTableQueryCount(11);
    }
    public function testSearchRemindersByVariableNameWithoutAuth(): void{
	    QMBaseTestCase::setExpectedRequestException(AuthenticationException::class);
	    $this->stagingRequest(401, "Unauthenticated");
	    $response = $this->getTestResponse();
	    $this->checkTestDuration(5);
	    $this->checkQueryCount(3);
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
      'draw' => '6',
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
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        2 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'variable_image_name_link',
          'name' => 'variable.name',
          'orderable' => 'false',
        ),
        4 =>
        array (
          'data' => 'frequency',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        5 =>
        array (
          'data' => 'start_tracking_date',
          'searchable' => 'false',
        ),
        6 =>
        array (
          'data' => 'stop_tracking_date',
          'searchable' => 'false',
        ),
        7 =>
        array (
          'data' => 'user_variable_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        8 =>
        array (
          'data' => 'variable_category_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        9 =>
        array (
          'data' => 'reminder_start_time',
          'searchable' => 'false',
        ),
        10 =>
        array (
          'data' => 'reminder_frequency',
          'searchable' => 'false',
        ),
        11 =>
        array (
          'data' => 'last_tracked',
          'searchable' => 'false',
        ),
        12 =>
        array (
          'data' => 'instructions',
        ),
        13 =>
        array (
          'data' => 'image_url',
        ),
        14 =>
        array (
          'data' => 'user_variable_id',
          'searchable' => 'false',
        ),
        15 =>
        array (
          'data' => 'latest_tracking_reminder_notification_notify_at',
          'searchable' => 'false',
        ),
        16 =>
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        17 =>
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        18 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        19 =>
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        20 =>
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
          'column' => '18',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => 'adderall',
      ),
      '_' => '1602994075689',
    ),
  )),
   'query' =>
  QMParameterBag::__set_state(array(
     'parameters' =>
    array (
      'draw' => '6',
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
          'data' => 'drop_down_button',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        2 =>
        array (
          'data' => 'related_data',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        3 =>
        array (
          'data' => 'variable_image_name_link',
          'name' => 'variable.name',
          'orderable' => 'false',
        ),
        4 =>
        array (
          'data' => 'frequency',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        5 =>
        array (
          'data' => 'start_tracking_date',
          'searchable' => 'false',
        ),
        6 =>
        array (
          'data' => 'stop_tracking_date',
          'searchable' => 'false',
        ),
        7 =>
        array (
          'data' => 'user_variable_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        8 =>
        array (
          'data' => 'variable_category_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        9 =>
        array (
          'data' => 'reminder_start_time',
          'searchable' => 'false',
        ),
        10 =>
        array (
          'data' => 'reminder_frequency',
          'searchable' => 'false',
        ),
        11 =>
        array (
          'data' => 'last_tracked',
          'searchable' => 'false',
        ),
        12 =>
        array (
          'data' => 'instructions',
        ),
        13 =>
        array (
          'data' => 'image_url',
        ),
        14 =>
        array (
          'data' => 'user_variable_id',
          'searchable' => 'false',
        ),
        15 =>
        array (
          'data' => 'latest_tracking_reminder_notification_notify_at',
          'searchable' => 'false',
        ),
        16 =>
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        17 =>
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        18 =>
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        19 =>
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        20 =>
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
          'column' => '18',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' =>
      array (
        'value' => 'adderall',
      ),
      '_' => '1602994075689',
    ),
  )),
   'server' =>
  QMServerBag::__set_state(array(
     'parameters' =>
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => '__cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; XDEBUG_SESSION=PHPSTORM; _gid=GA1.2.1636261980.1602777261; _gid=GA1.1.1636261980.1602777261; driftt_eid=230; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604195899%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo; driftt_sid=6b627692-d7aa-4fad-8ba7-3d440e59d453; XSRF-TOKEN=eyJpdiI6IkF4V2xQc1o2UEJGOXFGYTlyTmFsNlE9PSIsInZhbHVlIjoiTjZOcXNVN2xva05BZ0VNd3RsbXJtYmtjT0VwQWVadEk0MFpubGxFSXU4XC90UTU2WldjN1AranVZYWFNdDdQaVYiLCJtYWMiOiIwODJjOWJkOWM0OTUwZjAyY2Q0NjM3MWI0YTcxMWU4YThhYmNkYzhiMjg2NzBkNTM3N2I2ZGJlNzdiOTI4ZTYzIn0%3D; laravel_session=eyJpdiI6Ik9rdE8rdVEyM3JHaXRMczA5VkJXeUE9PSIsInZhbHVlIjoic2swOTkxejEzakp0MG9LKzBaRjRjbnoxU2JOZmFTZXJyajhTMWVJdEZOb2hidURmK3dnd0JTOERpYVYrZXVUOSIsIm1hYyI6ImE2MzVkNGNkYWRmMTU1MzliNmUxOWEwOTFkYjNiM2FkNjgwMDYwZTZiZDM4YTQxMjI1NTdjYTZjN2EyNTY2YjIifQ%3D%3D',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => getenv('APP_URL').'/datalab/trackingReminders',
      'HTTP_SEC_FETCH_DEST' => 'empty',
      'HTTP_SEC_FETCH_MODE' => 'cors',
      'HTTP_SEC_FETCH_SITE' => 'same-origin',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
      'HTTP_ACCEPT' => 'application/json, text/javascript, */*; q=0.01',
      'HTTP_X_SOCKET_ID' => '8846.4556518',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'REDIRECT_STATUS' => '200',
      'HTTPS' => 'on',
      'SERVER_NAME' => 'testing.quantimo.do',
      'SERVER_PORT' => '443',
      'REMOTE_PORT' => '14070',
      'SERVER_SOFTWARE' => 'nginx/1.15.8',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => '',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'draw=6&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=variable_image_name_link&columns%5B3%5D%5Bname%5D=variable.name&columns%5B3%5D%5Borderable%5D=false&columns%5B4%5D%5Bdata%5D=frequency&columns%5B4%5D%5Bsearchable%5D=false&columns%5B4%5D%5Borderable%5D=false&columns%5B5%5D%5Bdata%5D=start_tracking_date&columns%5B5%5D%5Bsearchable%5D=false&columns%5B6%5D%5Bdata%5D=stop_tracking_date&columns%5B6%5D%5Bsearchable%5D=false&columns%5B7%5D%5Bdata%5D=user_variable_link&columns%5B7%5D%5Bsearchable%5D=false&columns%5B7%5D%5Borderable%5D=false&columns%5B8%5D%5Bdata%5D=variable_category_link&columns%5B8%5D%5Bsearchable%5D=false&columns%5B8%5D%5Borderable%5D=false&columns%5B9%5D%5Bdata%5D=reminder_start_time&columns%5B9%5D%5Bsearchable%5D=false&columns%5B10%5D%5Bdata%5D=reminder_frequency&columns%5B10%5D%5Bsearchable%5D=false&columns%5B11%5D%5Bdata%5D=last_tracked&columns%5B11%5D%5Bsearchable%5D=false&columns%5B12%5D%5Bdata%5D=instructions&columns%5B13%5D%5Bdata%5D=image_url&columns%5B14%5D%5Bdata%5D=user_variable_id&columns%5B14%5D%5Bsearchable%5D=false&columns%5B15%5D%5Bdata%5D=latest_tracking_reminder_notification_notify_at&columns%5B15%5D%5Bsearchable%5D=false&columns%5B16%5D%5Bdata%5D=id_link&columns%5B16%5D%5Bsearchable%5D=false&columns%5B16%5D%5Borderable%5D=false&columns%5B17%5D%5Bdata%5D=created_at&columns%5B17%5D%5Bsearchable%5D=false&columns%5B18%5D%5Bdata%5D=updated_at&columns%5B18%5D%5Bsearchable%5D=false&columns%5B19%5D%5Bdata%5D=deleted_at&columns%5B19%5D%5Bsearchable%5D=false&columns%5B20%5D%5Bdata%5D=action&columns%5B20%5D%5Bsearchable%5D=false&columns%5B20%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=18&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=adderall&_=1602994075689',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',

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
      '__cfduid' => 'd0b262ec60d9dad143da74009904d74921601569701',
      '_ga' => 'GA1.2.644404966.1601578880',
      'driftt_aid' => 'df29ce65-369a-440c-9d5e-a1888f0cd13d',
      'DFTT_END_USER_PREV_BOOTSTRAPPED' => 'true',
      'u' => '084521ae39828198127bd5b3d1d7fe9ccf4eca35',
      '__gads' => 'ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw',
      'XDEBUG_SESSION' => 'PHPSTORM',
      '_gid' => 'GA1.2.1636261980.1602777261',
      'driftt_eid' => '230',
      'final_callback_url' => 'https://web.quantimo.do/index.html#/app/login?client_id=quantimodo',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1604195899|7bbd02cce0932081841094cc76a908c8|quantimodo',
      'driftt_sid' => '6b627692-d7aa-4fad-8ba7-3d440e59d453',
      'XSRF-TOKEN' => 'eyJpdiI6IkF4V2xQc1o2UEJGOXFGYTlyTmFsNlE9PSIsInZhbHVlIjoiTjZOcXNVN2xva05BZ0VNd3RsbXJtYmtjT0VwQWVadEk0MFpubGxFSXU4XC90UTU2WldjN1AranVZYWFNdDdQaVYiLCJtYWMiOiIwODJjOWJkOWM0OTUwZjAyY2Q0NjM3MWI0YTcxMWU4YThhYmNkYzhiMjg2NzBkNTM3N2I2ZGJlNzdiOTI4ZTYzIn0=',
      'laravel_session' => 'eyJpdiI6Ik9rdE8rdVEyM3JHaXRMczA5VkJXeUE9PSIsInZhbHVlIjoic2swOTkxejEzakp0MG9LKzBaRjRjbnoxU2JOZmFTZXJyajhTMWVJdEZOb2hidURmK3dnd0JTOERpYVYrZXVUOSIsIm1hYyI6ImE2MzVkNGNkYWRmMTU1MzliNmUxOWEwOTFkYjNiM2FkNjgwMDYwZTZiZDM4YTQxMjI1NTdjYTZjN2EyNTY2YjIifQ==',
    ),
  )),
   'headers' =>
  QMHeaderBag::__set_state(array(
     'headers' =>
    array (
      'cookie' =>
      array (
        0 => '__cfduid=d0b262ec60d9dad143da74009904d74921601569701; _ga=GA1.2.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; DFTT_END_USER_PREV_BOOTSTRAPPED=true; u=084521ae39828198127bd5b3d1d7fe9ccf4eca35; _ga=GA1.1.644404966.1601578880; driftt_aid=df29ce65-369a-440c-9d5e-a1888f0cd13d; __gads=ID=baf1b7342d7fe58c:T=1601862028:S=ALNI_MZ1pa8rvTAuTSgPwbsR16DZSDT1bw; XDEBUG_SESSION=PHPSTORM; _gid=GA1.2.1636261980.1602777261; _gid=GA1.1.1636261980.1602777261; driftt_eid=230; final_callback_url=https%3A%2F%2Fweb.quantimo.do%2Findex.html%23%2Fapp%2Flogin%3Fclient_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1604195899%7C7bbd02cce0932081841094cc76a908c8%7Cquantimodo; driftt_sid=6b627692-d7aa-4fad-8ba7-3d440e59d453; XSRF-TOKEN=eyJpdiI6IkF4V2xQc1o2UEJGOXFGYTlyTmFsNlE9PSIsInZhbHVlIjoiTjZOcXNVN2xva05BZ0VNd3RsbXJtYmtjT0VwQWVadEk0MFpubGxFSXU4XC90UTU2WldjN1AranVZYWFNdDdQaVYiLCJtYWMiOiIwODJjOWJkOWM0OTUwZjAyY2Q0NjM3MWI0YTcxMWU4YThhYmNkYzhiMjg2NzBkNTM3N2I2ZGJlNzdiOTI4ZTYzIn0%3D; laravel_session=eyJpdiI6Ik9rdE8rdVEyM3JHaXRMczA5VkJXeUE9PSIsInZhbHVlIjoic2swOTkxejEzakp0MG9LKzBaRjRjbnoxU2JOZmFTZXJyajhTMWVJdEZOb2hidURmK3dnd0JTOERpYVYrZXVUOSIsIm1hYyI6ImE2MzVkNGNkYWRmMTU1MzliNmUxOWEwOTFkYjNiM2FkNjgwMDYwZTZiZDM4YTQxMjI1NTdjYTZjN2EyNTY2YjIifQ%3D%3D',
      ),
      'accept-language' =>
      array (
        0 => 'en-US,en;q=0.9',
      ),
      'accept-encoding' =>
      array (
        0 => 'gzip, deflate, br',
      ),
      'referer' =>
      array (
        0 => getenv('APP_URL').'/datalab/trackingReminders',
      ),
      'sec-fetch-dest' =>
      array (
        0 => 'empty',
      ),
      'sec-fetch-mode' =>
      array (
        0 => 'cors',
      ),
      'sec-fetch-site' =>
      array (
        0 => 'same-origin',
      ),
      'user-agent' =>
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
      ),
      'x-requested-with' =>
      array (
        0 => 'XMLHttpRequest',
      ),
      'accept' =>
      array (
        0 => 'application/json, text/javascript, */*; q=0.01',
      ),
      'x-socket-id' =>
      array (
        0 => '8846.4556518',
      ),
      'cache-control' =>
      array (
        0 => 'no-cache',
      ),
      'pragma' =>
      array (
        0 => 'no-cache',
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
      'no-cache' => true,
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
