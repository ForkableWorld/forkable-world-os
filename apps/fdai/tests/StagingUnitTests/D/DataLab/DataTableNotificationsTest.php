<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace Tests\StagingUnitTests\D\DataLab;
use App\Override\GeneratedTestRequest;
use App\Override\QMHeaderBag;
use App\Override\QMParameterBag;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
use Tests\LaravelStagingTestCase;
class DataTableNotificationsTest extends LaravelStagingTestCase
{
    protected $REQUEST_URI = "/datalab/notifications?draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=body&columns%5B4%5D%5Bdata%5D=read_at&columns%5B4%5D%5Bsearchable%5D=false&columns%5B5%5D%5Bdata%5D=id_link&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=created_at&columns%5B6%5D%5Bsearchable%5D=false&columns%5B7%5D%5Bdata%5D=updated_at&columns%5B7%5D%5Bsearchable%5D=false&columns%5B8%5D%5Bdata%5D=deleted_at&columns%5B8%5D%5Bsearchable%5D=false&columns%5B9%5D%5Bdata%5D=action&columns%5B9%5D%5Bsearchable%5D=false&columns%5B9%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=7&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1639072257839";
    public function testDataTableNotificationsAsRegularUser(): void{
        $this->actAsTestUser();
	    $this->stagingRequest(200, "");
	    $response = $this->getTestResponse();
	    $this->checkTestDuration(5);
	    $this->checkQueryCount(6);
	    $this->assertDataTableQueriesEqual([]);
    }
    public function testDataTableNotificationsAsAdmin(): void{
        $this->actAsAdmin();
        $this->stagingRequest(200, "");
        $response = $this->getTestResponse();
        $this->checkTestDuration(5);
        $this->checkQueryCount(9);
        $this->assertDataTableQueriesEqual([]);
    }
    /**
     * @param string|null $expectedString
     * @param int|null $expectedCode
     * @return TestResponse
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {
		$this->serializedRequest = GeneratedTestRequest::__set_state(array(
   'request' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
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
          'data' => 'body',
        ),
        4 => 
        array (
          'data' => 'read_at',
          'searchable' => 'false',
        ),
        5 => 
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        6 => 
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        7 => 
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        8 => 
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        9 => 
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
          'column' => '7',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' => 
      array (
        'value' => '',
      ),
      '_' => '1639072257839',
    ),
  )),
   'query' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
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
          'data' => 'body',
        ),
        4 => 
        array (
          'data' => 'read_at',
          'searchable' => 'false',
        ),
        5 => 
        array (
          'data' => 'id_link',
          'searchable' => 'false',
          'orderable' => 'false',
        ),
        6 => 
        array (
          'data' => 'created_at',
          'searchable' => 'false',
        ),
        7 => 
        array (
          'data' => 'updated_at',
          'searchable' => 'false',
        ),
        8 => 
        array (
          'data' => 'deleted_at',
          'searchable' => 'false',
        ),
        9 => 
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
          'column' => '7',
          'dir' => 'desc',
        ),
      ),
      'start' => '0',
      'length' => '10',
      'search' => 
      array (
        'value' => '',
      ),
      '_' => '1639072257839',
    ),
  )),
   'server' => 
  QMServerBag::__set_state(array(
     'parameters' => 
    array (
      'USER' => 'vagrant',
      'HOME' => '/home/vagrant',
      'HTTP_COOKIE' => 'drift_aid=c624de60-7082-433f-ae2e-89f422964c63; driftt_aid=c624de60-7082-433f-ae2e-89f422964c63; _ga=GA1.1.559032929.1637774096; moesif_anonymous_id=17d52f17dbe4ee-0fa74d4e3f1e7c8-4c3e207f-1fa400; moesif_campaign_data=%7B%7D; XSRF-TOKEN=eyJpdiI6ImtwMGpIallrZEtiaGp4ZE5xZE5raXc9PSIsInZhbHVlIjoiMTBGUk51OTBsMHg3Vjd3dGtPc1A5VzB2WFdBbnJZc1FuWlBnYm1OMkRuWkVoMFliTU9YWGlkY0JtNjNhdzArYSIsIm1hYyI6IjJjM2M3NDI4MWIyOTE3NTIwMmM2NWIyYmNjMmNjNTVhZDg4ZmZjNTMwMWIxM2I5ZTQwZjcwZWM3OTg0YzI4ZmUifQ%3D%3D; laravel_session=eyJpdiI6IkVWZFJrdjV6NlNnTjlqMWNMd2E0VEE9PSIsInZhbHVlIjoidXNyZjYxTGRcLzV1b1wvR0IyVmhCQkZRSUtEbGtjdFFKTHJOUWZvWkI0UnVkZVJ3MEtnMmlJT1JNUVdDbGVwZTNKIiwibWFjIjoiODQxYjM5ZmE0NTYyNWU2Mzg1ODQxN2NjZjVjMGFmMDNlNjUyYjQyNjlhMTAwOTBiMmQ4OTRmMmE2ZmQxYWVlZSJ9; intended_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Fimport; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IklcL3lrWWcxUXl3MG9hQXg1TnYxempBPT0iLCJ2YWx1ZSI6IlI1V1ZndlRKMHM0NHNLRUFja2ppQUE9PSIsIm1hYyI6ImYxZjU4ZmU0YWVkMjgyMDE0M2YyYmMwNDFlNjlkMzIxMGI3NGQzZjQ3ZGI3YTIyZGY2ZmNkNmVlNWNmYzJjNmUifQ%3D%3D; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IktybDNwa1wvTitTRFduU1VONkY2UHV3PT0iLCJ2YWx1ZSI6Im1Jc1NVTzIrR1wvdmhTc0g2UjVWcWo5ZDhzbmNpVXpYRWdLRUluOFVOSHhDYkVEY2xndU5Ld05rWEtpSm9TVDVvZnJsRlF6VDNoaGhzRlFIYjhXWnp2RG1cL3pmeGtGQU5VXC9KK3p3ZlNPZGltVkU5ZThyVlQrZGZzK3FKWjBLWUR2VjJRNjFMNGdhZE4wSnJ4NWFKaExaUm45aENvaXdFUVN4MUhQOUd3MjJzRT0iLCJtYWMiOiJjMzQ4NTY5OGYwOWM2N2FhYmNiYWFkOTY4YzYzMTlkYmViM2NiMWRmNzFhZWFmZGY1YzUyMGU2MDc0N2UyZDE1In0%3D; Tdmx4VfcWmeiwT22yqQ8EeoX9LucQgQ8zU23SPuf=eyJpdiI6IjhFRlhlMDZPWWhGWndoSTZaWmFrUUE9PSIsInZhbHVlIjoiemNLdFwvNUVHcHd1MVwvNWZ1U2NyUVdpazZRQVczY2Y1WkJnSHRReWJWcmtzRkJcLzU3R1FvemkyellTYUxiNm5QMEdkemJaOFdmTUpwQWsySjQ0NVh0WkZJeGN2bGhGSGp5ZXpDWVZ5T0FiK2hTMnJCMHJaODJqZE1QUE93R2Jmek1hdUVYNG1tdkFsaEd0Q3dBUWdtd2h4YVhPWHVTVG5GdFM2R3I1eitYdEoxS1BCM0EweWtVWDdcL2liQ0JuRmU2dlIxMlJmaUJ0SjhobGljSU4rU3lscjc4QjFrcHRxNk5pRTFqR1VrSlhKQnIzMm01TzArUkRWWEZ2cXJhVVZ0czhPXC95V250TGR5SFhRcHg0UVlFWUxjMkc4MmJaRnZYZWU0WnhlMm14M3EwcnZqYmE2NE5lZ2thcE5DdzlwbWlYeVlld1NPMUxKY0tER1o3eFpGYUpUNlowWlhMT2VaQ1Y5QzVGU0xvcUU2Y29xNXBCamY0T1YrXC9obFwvV3c1TFRcL2lSbnNvb1BIQjd4dHIzYlBaYmJxd2VWWE1rQ2R5Y1ZXejNDZ0pMMjNIekliamtSdk93aWd5VzYzb1lEN3AxZ2Rnb3p1NnhReWhwTWlyNUREaGxCN3dxNElrdE41YmREZko4aUZyOWpYTXJSc2JMZEJXOCtWczZ1V244dUpOdjlrcm45bFlsV3Q1R2psR2dtaElzNHBLdW5Mbkw4KzQ2UGVudmt6NUNaenc4b0sxTlRcL2xvVG96UW9idDhGV1Radk9PK0NRSkZzcG9mMUQ2dFVGZHZLMVNUcWVPMko5Q2o2N2FCMlwvOFU0aERSeWR6WXJPcCt2XC9MZkMyaFo2Nldhc2hNVXBIdWNXMlFRdGlibGxnb2dUbmdvOGt3a0dGekNBXC8wd3VCZ1V4bUdkRnNMYzdaaE5FR3FoWm8zS1lXXC9BOFozNnZxT0l3ZXhtQW1DVWhsNUtkTnNtRWZkaVdwV1JGNVBRVVNqYmxKK0crVU9uRDhrU3N5WlB4U1BiR1NWTGFDOW00blNxRmhuZjJoU3hXUjlpTWFZdUNvYnpnWjJJbnFza1VNNHhPNGVnN3JZQk9nN3YxTTIzcndMZ0FhQzRLM3NjT3pLTkIwTTRoU1dkV0VxdGtcL05GZXFIODRMVmpVOXZSaVNyUUpHOU1RNmpVQ0lcL0daT3lEaWZmaUlFR01JMVwveUJsQlwvSzZJMnhVaktmZjV5dVVhVDZKcWU2RUk2NVBlZmpydTZLUzhyU1pJbHVmbmFhTVBxZlpvRDFPeWlleGNIMFhUVUlmOVVXZzJ6MUVNczFEUUEyYVAxYlM1aVJDcGtCUTV5TFdTTkQ5aytOUUlBcTh1UFpWNFJuUFwva0Q1dWo0d0lCUklyZUpqQUMwWDZqNFh6R1F0K08raTIyYUpWdFduSjBJREZ4XC9lWWxkNFVtdnNqMHE3QXFJOXJvYTJlMDF3ZFpWb2I1RVNXakZJV01wU0g3cWQ1T3AzZ0F6UlN2VEtrbW9qdk1pYys0NVlaVnl3TmYyK1NGUUZnVElzYU9kQkN0NzRYQ05ZK3NVM0xVelQra1U3NjhxektRZkpIQVRISHZxelwvV3BIZE8waFZoUnVmYXNQNmFjQ0ZaQWxrWDlQaHJ5OWgrWHR6S0dMbHE1U0kwcEpYbXp1ZVRzTFZsS2pXY3ZwZTNMQW5SU2lnM3RNUjlDamQ5ZWY4UndWVTRCVFlGYndJSVp4V05odkhMNUVHUllnTlJIcUg5QkxJaUFITm9Jb2VFOVBxMEJlOTJlXC9pc1gycXpKY1FqZjJCbVBwXC9VQXJKVG9zU1dQMVJHOGpRSDFBZlk5WkZlWEoyOFU0bUg2UjZKSDlOd2h4eExraXoyZzdcL1wvTExBYXVsUHBcL2hnTlhrdnduSGNoY3U5ZHNWM0RaNHRPaXlcL1ZGWEZIcWMrMDBOZHloeFFsb0xYNldKc00yZnEycVlqZEhkUXZJK1JRSmFtVHoyT2RLaDg4cUlmREpFUHZxZmdXazZad1N6UlM4OHhCanFwTDh5ZDVoeW9CZFZtMGpYUE1NK2NhcXIweklDcmttc1wvNUVINzJFREhaSDEwdnNcL0NCK3BWdVlabVhnSjVNeFpRd05aOFF2dHdSUUJtZDBoWCt2QlRSbkxuc1gyYVlVb24rbkhFUEdOajVZN09cL1pqYVA0OTVGUjZYeDZjNEtacUozN1wvYk9TcTJpTkNqVWxJdDFMcVdvS24zOEpCWnpcL2hwa1BTUTQ4ZnRza0NYWkdoalo4MGkyejgrcDNFRjdsc0dROXVoc2hhVDQ3NVNzTVFMdlVGMmQ4MUdna3RYTjNcL0swUTJtXC9FNUlSZ2ZUbHlibERuNlVLYjRUczd0UTByWjdqQkVuTHNpUmszcnVSWGR5ZjNKS1JDMnV1OHYzNVFicjFiTnhJYUdzWGR6djQxT3M4RW90Y0pFV0thbDZRZkhLbUg4RVp1V2p3WkViUGlaQWdUc0pjWW1TM2Jsb1wvWG94VzhxSEpHc0pIREQrdEhMVXZXZUVRa1JkWW9aTG5ENHpCRHR4dXVjelwvUW5CQVwvamRrUkg0allRSDNIXC9Ic1wvXC8zRStvOXhiTXRlTmRJNFwvVHNmQmoxK21ZSHZhc1FyRzZMU1J5TFNKbVhkUHhOOUUxckJxMFhSVUc2N205MkwzMlpDUjZFQ3g4QnlcLzN3aHUxZEVTXC9UYmdLbzROQlNtVlNRUzFtcXp0cWxEODhoV3VxK0NNclNlOUJHZlpKVzM0ZDc5M3FsRitWSEJPOWdWMnVSSTl0bW5VMElheElQTmRcL3FIZk9qeWMwUVwvVFoyTGFmZlwvTXdUaVR6cFU4SXpZY01cL0wrQ2ZqTENTM05uTkFUVzZBMGdpQXR6SHFPUlhBQXRBcmlkTU51N0hwcGR3ZE4zTkpUYjJMTHVuTEFnSFVZdk11MUVvMXNHZXJ5cnZpVDNPNHlQUDh1aTBOVmxjeUNHZUdzMm9tWEQxUU5KQUc3MCtrWVwvaVwvXC9vYVVHa3lzY2pOeVg0enVBWHg5Zz09IiwibWFjIjoiNWQyOWFiNmJmMjUwYWYzODliMmZkMzQyNzY4ZTliY2UwNzJmMDVkYTQ3N2U4MDQ5YTE1M2U2MmUyOGM3ZWRjYyJ9; final_callback_url=https%3A%2F%2Fapp.quantimo.do%2Fapi%2Fv2%2Fauth%2Fregister%3FafterLoginGoToUrl%3Dhttps%253A%252F%252Fapp.quantimo.do%252Fnova%26client_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1639414327%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; _ga_YWVCSGVYJ7=GS1.1.1638619878.1.1.1638622927.0; DokuWiki=ae40qk9q3tkuev9jlv69mj1dmb',
      'HTTP_TE' => 'trailers',
      'HTTP_CACHE_CONTROL' => 'max-age=0',
      'HTTP_SEC_FETCH_USER' => '?1',
      'HTTP_SEC_FETCH_SITE' => 'none',
      'HTTP_SEC_FETCH_MODE' => 'navigate',
      'HTTP_SEC_FETCH_DEST' => 'document',
      'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
      'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0',
      'HTTP_HOST' => 'testing.quantimo.do',
      'REDIRECT_STATUS' => '200',
      'SERVER_NAME' => 'local.quantimo.do',
      'SERVER_PORT' => '443',
      'SERVER_ADDR' => '127.0.0.1',
      'REMOTE_PORT' => '34642',
      'REMOTE_ADDR' => '127.0.0.1',
      'SERVER_SOFTWARE' => 'nginx/1.19.7',
      'GATEWAY_INTERFACE' => 'CGI/1.1',
      'HTTPS' => 'on',
      'REQUEST_SCHEME' => 'https',
      'SERVER_PROTOCOL' => 'HTTP/2.0',
      'DOCUMENT_ROOT' => '/www/wwwroot/qm-api/public',
      'DOCUMENT_URI' => '/index.php',
      'REQUEST_URI' => $this->REQUEST_URI.'',
      'SCRIPT_NAME' => '/index.php',
      'REQUEST_METHOD' => 'GET',
      'QUERY_STRING' => 'draw=1&columns%5B0%5D%5Bdata%5D=open_button&columns%5B0%5D%5Bsearchable%5D=false&columns%5B0%5D%5Borderable%5D=false&columns%5B1%5D%5Bdata%5D=drop_down_button&columns%5B1%5D%5Bsearchable%5D=false&columns%5B1%5D%5Borderable%5D=false&columns%5B2%5D%5Bdata%5D=related_data&columns%5B2%5D%5Bsearchable%5D=false&columns%5B2%5D%5Borderable%5D=false&columns%5B3%5D%5Bdata%5D=body&columns%5B4%5D%5Bdata%5D=read_at&columns%5B4%5D%5Bsearchable%5D=false&columns%5B5%5D%5Bdata%5D=id_link&columns%5B5%5D%5Bsearchable%5D=false&columns%5B5%5D%5Borderable%5D=false&columns%5B6%5D%5Bdata%5D=created_at&columns%5B6%5D%5Bsearchable%5D=false&columns%5B7%5D%5Bdata%5D=updated_at&columns%5B7%5D%5Bsearchable%5D=false&columns%5B8%5D%5Bdata%5D=deleted_at&columns%5B8%5D%5Bsearchable%5D=false&columns%5B9%5D%5Bdata%5D=action&columns%5B9%5D%5Bsearchable%5D=false&columns%5B9%5D%5Borderable%5D=false&order%5B0%5D%5Bcolumn%5D=7&order%5B0%5D%5Bdir%5D=desc&start=0&length=10&search%5Bvalue%5D=&_=1639072257839',
      'SCRIPT_FILENAME' => '/www/wwwroot/qm-api/public/index.php',
      'FCGI_ROLE' => 'RESPONDER',
      'PHP_SELF' => '/index.php',
      'REQUEST_TIME_FLOAT' => 1639072293.989339,
      'REQUEST_TIME' => 1639072293,
    ),
  )),
   'cookies' => 
  QMParameterBag::__set_state(array(
     'parameters' => 
    array (
      'drift_aid' => 'c624de60-7082-433f-ae2e-89f422964c63',
      'driftt_aid' => 'c624de60-7082-433f-ae2e-89f422964c63',
      '_ga' => 'GA1.1.559032929.1637774096',
      'moesif_anonymous_id' => '17d52f17dbe4ee-0fa74d4e3f1e7c8-4c3e207f-1fa400',
      'moesif_campaign_data' => '{}',
      'XSRF-TOKEN' => 'eyJpdiI6ImtwMGpIallrZEtiaGp4ZE5xZE5raXc9PSIsInZhbHVlIjoiMTBGUk51OTBsMHg3Vjd3dGtPc1A5VzB2WFdBbnJZc1FuWlBnYm1OMkRuWkVoMFliTU9YWGlkY0JtNjNhdzArYSIsIm1hYyI6IjJjM2M3NDI4MWIyOTE3NTIwMmM2NWIyYmNjMmNjNTVhZDg4ZmZjNTMwMWIxM2I5ZTQwZjcwZWM3OTg0YzI4ZmUifQ==',
      'laravel_session' => 'eyJpdiI6IkVWZFJrdjV6NlNnTjlqMWNMd2E0VEE9PSIsInZhbHVlIjoidXNyZjYxTGRcLzV1b1wvR0IyVmhCQkZRSUtEbGtjdFFKTHJOUWZvWkI0UnVkZVJ3MEtnMmlJT1JNUVdDbGVwZTNKIiwibWFjIjoiODQxYjM5ZmE0NTYyNWU2Mzg1ODQxN2NjZjVjMGFmMDNlNjUyYjQyNjlhMTAwOTBiMmQ4OTRmMmE2ZmQxYWVlZSJ9',
      'intended_url' => 'https://web.quantimo.do/#/app/import',
      'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IklcL3lrWWcxUXl3MG9hQXg1TnYxempBPT0iLCJ2YWx1ZSI6IlI1V1ZndlRKMHM0NHNLRUFja2ppQUE9PSIsIm1hYyI6ImYxZjU4ZmU0YWVkMjgyMDE0M2YyYmMwNDFlNjlkMzIxMGI3NGQzZjQ3ZGI3YTIyZGY2ZmNkNmVlNWNmYzJjNmUifQ==',
      'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d' => 'eyJpdiI6IktybDNwa1wvTitTRFduU1VONkY2UHV3PT0iLCJ2YWx1ZSI6Im1Jc1NVTzIrR1wvdmhTc0g2UjVWcWo5ZDhzbmNpVXpYRWdLRUluOFVOSHhDYkVEY2xndU5Ld05rWEtpSm9TVDVvZnJsRlF6VDNoaGhzRlFIYjhXWnp2RG1cL3pmeGtGQU5VXC9KK3p3ZlNPZGltVkU5ZThyVlQrZGZzK3FKWjBLWUR2VjJRNjFMNGdhZE4wSnJ4NWFKaExaUm45aENvaXdFUVN4MUhQOUd3MjJzRT0iLCJtYWMiOiJjMzQ4NTY5OGYwOWM2N2FhYmNiYWFkOTY4YzYzMTlkYmViM2NiMWRmNzFhZWFmZGY1YzUyMGU2MDc0N2UyZDE1In0=',
      'Tdmx4VfcWmeiwT22yqQ8EeoX9LucQgQ8zU23SPuf' => 'eyJpdiI6IjhFRlhlMDZPWWhGWndoSTZaWmFrUUE9PSIsInZhbHVlIjoiemNLdFwvNUVHcHd1MVwvNWZ1U2NyUVdpazZRQVczY2Y1WkJnSHRReWJWcmtzRkJcLzU3R1FvemkyellTYUxiNm5QMEdkemJaOFdmTUpwQWsySjQ0NVh0WkZJeGN2bGhGSGp5ZXpDWVZ5T0FiK2hTMnJCMHJaODJqZE1QUE93R2Jmek1hdUVYNG1tdkFsaEd0Q3dBUWdtd2h4YVhPWHVTVG5GdFM2R3I1eitYdEoxS1BCM0EweWtVWDdcL2liQ0JuRmU2dlIxMlJmaUJ0SjhobGljSU4rU3lscjc4QjFrcHRxNk5pRTFqR1VrSlhKQnIzMm01TzArUkRWWEZ2cXJhVVZ0czhPXC95V250TGR5SFhRcHg0UVlFWUxjMkc4MmJaRnZYZWU0WnhlMm14M3EwcnZqYmE2NE5lZ2thcE5DdzlwbWlYeVlld1NPMUxKY0tER1o3eFpGYUpUNlowWlhMT2VaQ1Y5QzVGU0xvcUU2Y29xNXBCamY0T1YrXC9obFwvV3c1TFRcL2lSbnNvb1BIQjd4dHIzYlBaYmJxd2VWWE1rQ2R5Y1ZXejNDZ0pMMjNIekliamtSdk93aWd5VzYzb1lEN3AxZ2Rnb3p1NnhReWhwTWlyNUREaGxCN3dxNElrdE41YmREZko4aUZyOWpYTXJSc2JMZEJXOCtWczZ1V244dUpOdjlrcm45bFlsV3Q1R2psR2dtaElzNHBLdW5Mbkw4KzQ2UGVudmt6NUNaenc4b0sxTlRcL2xvVG96UW9idDhGV1Radk9PK0NRSkZzcG9mMUQ2dFVGZHZLMVNUcWVPMko5Q2o2N2FCMlwvOFU0aERSeWR6WXJPcCt2XC9MZkMyaFo2Nldhc2hNVXBIdWNXMlFRdGlibGxnb2dUbmdvOGt3a0dGekNBXC8wd3VCZ1V4bUdkRnNMYzdaaE5FR3FoWm8zS1lXXC9BOFozNnZxT0l3ZXhtQW1DVWhsNUtkTnNtRWZkaVdwV1JGNVBRVVNqYmxKK0crVU9uRDhrU3N5WlB4U1BiR1NWTGFDOW00blNxRmhuZjJoU3hXUjlpTWFZdUNvYnpnWjJJbnFza1VNNHhPNGVnN3JZQk9nN3YxTTIzcndMZ0FhQzRLM3NjT3pLTkIwTTRoU1dkV0VxdGtcL05GZXFIODRMVmpVOXZSaVNyUUpHOU1RNmpVQ0lcL0daT3lEaWZmaUlFR01JMVwveUJsQlwvSzZJMnhVaktmZjV5dVVhVDZKcWU2RUk2NVBlZmpydTZLUzhyU1pJbHVmbmFhTVBxZlpvRDFPeWlleGNIMFhUVUlmOVVXZzJ6MUVNczFEUUEyYVAxYlM1aVJDcGtCUTV5TFdTTkQ5aytOUUlBcTh1UFpWNFJuUFwva0Q1dWo0d0lCUklyZUpqQUMwWDZqNFh6R1F0K08raTIyYUpWdFduSjBJREZ4XC9lWWxkNFVtdnNqMHE3QXFJOXJvYTJlMDF3ZFpWb2I1RVNXakZJV01wU0g3cWQ1T3AzZ0F6UlN2VEtrbW9qdk1pYys0NVlaVnl3TmYyK1NGUUZnVElzYU9kQkN0NzRYQ05ZK3NVM0xVelQra1U3NjhxektRZkpIQVRISHZxelwvV3BIZE8waFZoUnVmYXNQNmFjQ0ZaQWxrWDlQaHJ5OWgrWHR6S0dMbHE1U0kwcEpYbXp1ZVRzTFZsS2pXY3ZwZTNMQW5SU2lnM3RNUjlDamQ5ZWY4UndWVTRCVFlGYndJSVp4V05odkhMNUVHUllnTlJIcUg5QkxJaUFITm9Jb2VFOVBxMEJlOTJlXC9pc1gycXpKY1FqZjJCbVBwXC9VQXJKVG9zU1dQMVJHOGpRSDFBZlk5WkZlWEoyOFU0bUg2UjZKSDlOd2h4eExraXoyZzdcL1wvTExBYXVsUHBcL2hnTlhrdnduSGNoY3U5ZHNWM0RaNHRPaXlcL1ZGWEZIcWMrMDBOZHloeFFsb0xYNldKc00yZnEycVlqZEhkUXZJK1JRSmFtVHoyT2RLaDg4cUlmREpFUHZxZmdXazZad1N6UlM4OHhCanFwTDh5ZDVoeW9CZFZtMGpYUE1NK2NhcXIweklDcmttc1wvNUVINzJFREhaSDEwdnNcL0NCK3BWdVlabVhnSjVNeFpRd05aOFF2dHdSUUJtZDBoWCt2QlRSbkxuc1gyYVlVb24rbkhFUEdOajVZN09cL1pqYVA0OTVGUjZYeDZjNEtacUozN1wvYk9TcTJpTkNqVWxJdDFMcVdvS24zOEpCWnpcL2hwa1BTUTQ4ZnRza0NYWkdoalo4MGkyejgrcDNFRjdsc0dROXVoc2hhVDQ3NVNzTVFMdlVGMmQ4MUdna3RYTjNcL0swUTJtXC9FNUlSZ2ZUbHlibERuNlVLYjRUczd0UTByWjdqQkVuTHNpUmszcnVSWGR5ZjNKS1JDMnV1OHYzNVFicjFiTnhJYUdzWGR6djQxT3M4RW90Y0pFV0thbDZRZkhLbUg4RVp1V2p3WkViUGlaQWdUc0pjWW1TM2Jsb1wvWG94VzhxSEpHc0pIREQrdEhMVXZXZUVRa1JkWW9aTG5ENHpCRHR4dXVjelwvUW5CQVwvamRrUkg0allRSDNIXC9Ic1wvXC8zRStvOXhiTXRlTmRJNFwvVHNmQmoxK21ZSHZhc1FyRzZMU1J5TFNKbVhkUHhOOUUxckJxMFhSVUc2N205MkwzMlpDUjZFQ3g4QnlcLzN3aHUxZEVTXC9UYmdLbzROQlNtVlNRUzFtcXp0cWxEODhoV3VxK0NNclNlOUJHZlpKVzM0ZDc5M3FsRitWSEJPOWdWMnVSSTl0bW5VMElheElQTmRcL3FIZk9qeWMwUVwvVFoyTGFmZlwvTXdUaVR6cFU4SXpZY01cL0wrQ2ZqTENTM05uTkFUVzZBMGdpQXR6SHFPUlhBQXRBcmlkTU51N0hwcGR3ZE4zTkpUYjJMTHVuTEFnSFVZdk11MUVvMXNHZXJ5cnZpVDNPNHlQUDh1aTBOVmxjeUNHZUdzMm9tWEQxUU5KQUc3MCtrWVwvaVwvXC9vYVVHa3lzY2pOeVg0enVBWHg5Zz09IiwibWFjIjoiNWQyOWFiNmJmMjUwYWYzODliMmZkMzQyNzY4ZTliY2UwNzJmMDVkYTQ3N2U4MDQ5YTE1M2U2MmUyOGM3ZWRjYyJ9',
      'final_callback_url' => 'https://app.quantimo.do/auth/register?afterLoginGoToUrl=https%3A%2F%2Fapp.quantimo.do%2Fnova&client_id=quantimodo',
      'quantimodo_logged_in_af6160480df78a3a6d520187243f05c9' => 'mike|1639414327|dd460739350ce71c52c696ceb4cc9350|quantimodo',
      '_ga_YWVCSGVYJ7' => 'GS1.1.1638619878.1.1.1638622927.0',
      'DokuWiki' => 'ae40qk9q3tkuev9jlv69mj1dmb',
    ),
  )),
   'headers' => 
  QMHeaderBag::__set_state(array(
     'headers' => 
    array (
      'cookie' => 
      array (
        0 => 'drift_aid=c624de60-7082-433f-ae2e-89f422964c63; driftt_aid=c624de60-7082-433f-ae2e-89f422964c63; _ga=GA1.1.559032929.1637774096; moesif_anonymous_id=17d52f17dbe4ee-0fa74d4e3f1e7c8-4c3e207f-1fa400; moesif_campaign_data=%7B%7D; XSRF-TOKEN=eyJpdiI6ImtwMGpIallrZEtiaGp4ZE5xZE5raXc9PSIsInZhbHVlIjoiMTBGUk51OTBsMHg3Vjd3dGtPc1A5VzB2WFdBbnJZc1FuWlBnYm1OMkRuWkVoMFliTU9YWGlkY0JtNjNhdzArYSIsIm1hYyI6IjJjM2M3NDI4MWIyOTE3NTIwMmM2NWIyYmNjMmNjNTVhZDg4ZmZjNTMwMWIxM2I5ZTQwZjcwZWM3OTg0YzI4ZmUifQ%3D%3D; laravel_session=eyJpdiI6IkVWZFJrdjV6NlNnTjlqMWNMd2E0VEE9PSIsInZhbHVlIjoidXNyZjYxTGRcLzV1b1wvR0IyVmhCQkZRSUtEbGtjdFFKTHJOUWZvWkI0UnVkZVJ3MEtnMmlJT1JNUVdDbGVwZTNKIiwibWFjIjoiODQxYjM5ZmE0NTYyNWU2Mzg1ODQxN2NjZjVjMGFmMDNlNjUyYjQyNjlhMTAwOTBiMmQ4OTRmMmE2ZmQxYWVlZSJ9; intended_url=https%3A%2F%2Fweb.quantimo.do%2F%23%2Fapp%2Fimport; login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IklcL3lrWWcxUXl3MG9hQXg1TnYxempBPT0iLCJ2YWx1ZSI6IlI1V1ZndlRKMHM0NHNLRUFja2ppQUE9PSIsIm1hYyI6ImYxZjU4ZmU0YWVkMjgyMDE0M2YyYmMwNDFlNjlkMzIxMGI3NGQzZjQ3ZGI3YTIyZGY2ZmNkNmVlNWNmYzJjNmUifQ%3D%3D; remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d=eyJpdiI6IktybDNwa1wvTitTRFduU1VONkY2UHV3PT0iLCJ2YWx1ZSI6Im1Jc1NVTzIrR1wvdmhTc0g2UjVWcWo5ZDhzbmNpVXpYRWdLRUluOFVOSHhDYkVEY2xndU5Ld05rWEtpSm9TVDVvZnJsRlF6VDNoaGhzRlFIYjhXWnp2RG1cL3pmeGtGQU5VXC9KK3p3ZlNPZGltVkU5ZThyVlQrZGZzK3FKWjBLWUR2VjJRNjFMNGdhZE4wSnJ4NWFKaExaUm45aENvaXdFUVN4MUhQOUd3MjJzRT0iLCJtYWMiOiJjMzQ4NTY5OGYwOWM2N2FhYmNiYWFkOTY4YzYzMTlkYmViM2NiMWRmNzFhZWFmZGY1YzUyMGU2MDc0N2UyZDE1In0%3D; Tdmx4VfcWmeiwT22yqQ8EeoX9LucQgQ8zU23SPuf=eyJpdiI6IjhFRlhlMDZPWWhGWndoSTZaWmFrUUE9PSIsInZhbHVlIjoiemNLdFwvNUVHcHd1MVwvNWZ1U2NyUVdpazZRQVczY2Y1WkJnSHRReWJWcmtzRkJcLzU3R1FvemkyellTYUxiNm5QMEdkemJaOFdmTUpwQWsySjQ0NVh0WkZJeGN2bGhGSGp5ZXpDWVZ5T0FiK2hTMnJCMHJaODJqZE1QUE93R2Jmek1hdUVYNG1tdkFsaEd0Q3dBUWdtd2h4YVhPWHVTVG5GdFM2R3I1eitYdEoxS1BCM0EweWtVWDdcL2liQ0JuRmU2dlIxMlJmaUJ0SjhobGljSU4rU3lscjc4QjFrcHRxNk5pRTFqR1VrSlhKQnIzMm01TzArUkRWWEZ2cXJhVVZ0czhPXC95V250TGR5SFhRcHg0UVlFWUxjMkc4MmJaRnZYZWU0WnhlMm14M3EwcnZqYmE2NE5lZ2thcE5DdzlwbWlYeVlld1NPMUxKY0tER1o3eFpGYUpUNlowWlhMT2VaQ1Y5QzVGU0xvcUU2Y29xNXBCamY0T1YrXC9obFwvV3c1TFRcL2lSbnNvb1BIQjd4dHIzYlBaYmJxd2VWWE1rQ2R5Y1ZXejNDZ0pMMjNIekliamtSdk93aWd5VzYzb1lEN3AxZ2Rnb3p1NnhReWhwTWlyNUREaGxCN3dxNElrdE41YmREZko4aUZyOWpYTXJSc2JMZEJXOCtWczZ1V244dUpOdjlrcm45bFlsV3Q1R2psR2dtaElzNHBLdW5Mbkw4KzQ2UGVudmt6NUNaenc4b0sxTlRcL2xvVG96UW9idDhGV1Radk9PK0NRSkZzcG9mMUQ2dFVGZHZLMVNUcWVPMko5Q2o2N2FCMlwvOFU0aERSeWR6WXJPcCt2XC9MZkMyaFo2Nldhc2hNVXBIdWNXMlFRdGlibGxnb2dUbmdvOGt3a0dGekNBXC8wd3VCZ1V4bUdkRnNMYzdaaE5FR3FoWm8zS1lXXC9BOFozNnZxT0l3ZXhtQW1DVWhsNUtkTnNtRWZkaVdwV1JGNVBRVVNqYmxKK0crVU9uRDhrU3N5WlB4U1BiR1NWTGFDOW00blNxRmhuZjJoU3hXUjlpTWFZdUNvYnpnWjJJbnFza1VNNHhPNGVnN3JZQk9nN3YxTTIzcndMZ0FhQzRLM3NjT3pLTkIwTTRoU1dkV0VxdGtcL05GZXFIODRMVmpVOXZSaVNyUUpHOU1RNmpVQ0lcL0daT3lEaWZmaUlFR01JMVwveUJsQlwvSzZJMnhVaktmZjV5dVVhVDZKcWU2RUk2NVBlZmpydTZLUzhyU1pJbHVmbmFhTVBxZlpvRDFPeWlleGNIMFhUVUlmOVVXZzJ6MUVNczFEUUEyYVAxYlM1aVJDcGtCUTV5TFdTTkQ5aytOUUlBcTh1UFpWNFJuUFwva0Q1dWo0d0lCUklyZUpqQUMwWDZqNFh6R1F0K08raTIyYUpWdFduSjBJREZ4XC9lWWxkNFVtdnNqMHE3QXFJOXJvYTJlMDF3ZFpWb2I1RVNXakZJV01wU0g3cWQ1T3AzZ0F6UlN2VEtrbW9qdk1pYys0NVlaVnl3TmYyK1NGUUZnVElzYU9kQkN0NzRYQ05ZK3NVM0xVelQra1U3NjhxektRZkpIQVRISHZxelwvV3BIZE8waFZoUnVmYXNQNmFjQ0ZaQWxrWDlQaHJ5OWgrWHR6S0dMbHE1U0kwcEpYbXp1ZVRzTFZsS2pXY3ZwZTNMQW5SU2lnM3RNUjlDamQ5ZWY4UndWVTRCVFlGYndJSVp4V05odkhMNUVHUllnTlJIcUg5QkxJaUFITm9Jb2VFOVBxMEJlOTJlXC9pc1gycXpKY1FqZjJCbVBwXC9VQXJKVG9zU1dQMVJHOGpRSDFBZlk5WkZlWEoyOFU0bUg2UjZKSDlOd2h4eExraXoyZzdcL1wvTExBYXVsUHBcL2hnTlhrdnduSGNoY3U5ZHNWM0RaNHRPaXlcL1ZGWEZIcWMrMDBOZHloeFFsb0xYNldKc00yZnEycVlqZEhkUXZJK1JRSmFtVHoyT2RLaDg4cUlmREpFUHZxZmdXazZad1N6UlM4OHhCanFwTDh5ZDVoeW9CZFZtMGpYUE1NK2NhcXIweklDcmttc1wvNUVINzJFREhaSDEwdnNcL0NCK3BWdVlabVhnSjVNeFpRd05aOFF2dHdSUUJtZDBoWCt2QlRSbkxuc1gyYVlVb24rbkhFUEdOajVZN09cL1pqYVA0OTVGUjZYeDZjNEtacUozN1wvYk9TcTJpTkNqVWxJdDFMcVdvS24zOEpCWnpcL2hwa1BTUTQ4ZnRza0NYWkdoalo4MGkyejgrcDNFRjdsc0dROXVoc2hhVDQ3NVNzTVFMdlVGMmQ4MUdna3RYTjNcL0swUTJtXC9FNUlSZ2ZUbHlibERuNlVLYjRUczd0UTByWjdqQkVuTHNpUmszcnVSWGR5ZjNKS1JDMnV1OHYzNVFicjFiTnhJYUdzWGR6djQxT3M4RW90Y0pFV0thbDZRZkhLbUg4RVp1V2p3WkViUGlaQWdUc0pjWW1TM2Jsb1wvWG94VzhxSEpHc0pIREQrdEhMVXZXZUVRa1JkWW9aTG5ENHpCRHR4dXVjelwvUW5CQVwvamRrUkg0allRSDNIXC9Ic1wvXC8zRStvOXhiTXRlTmRJNFwvVHNmQmoxK21ZSHZhc1FyRzZMU1J5TFNKbVhkUHhOOUUxckJxMFhSVUc2N205MkwzMlpDUjZFQ3g4QnlcLzN3aHUxZEVTXC9UYmdLbzROQlNtVlNRUzFtcXp0cWxEODhoV3VxK0NNclNlOUJHZlpKVzM0ZDc5M3FsRitWSEJPOWdWMnVSSTl0bW5VMElheElQTmRcL3FIZk9qeWMwUVwvVFoyTGFmZlwvTXdUaVR6cFU4SXpZY01cL0wrQ2ZqTENTM05uTkFUVzZBMGdpQXR6SHFPUlhBQXRBcmlkTU51N0hwcGR3ZE4zTkpUYjJMTHVuTEFnSFVZdk11MUVvMXNHZXJ5cnZpVDNPNHlQUDh1aTBOVmxjeUNHZUdzMm9tWEQxUU5KQUc3MCtrWVwvaVwvXC9vYVVHa3lzY2pOeVg0enVBWHg5Zz09IiwibWFjIjoiNWQyOWFiNmJmMjUwYWYzODliMmZkMzQyNzY4ZTliY2UwNzJmMDVkYTQ3N2U4MDQ5YTE1M2U2MmUyOGM3ZWRjYyJ9; final_callback_url=https%3A%2F%2Fapp.quantimo.do%2Fapi%2Fv2%2Fauth%2Fregister%3FafterLoginGoToUrl%3Dhttps%253A%252F%252Fapp.quantimo.do%252Fnova%26client_id%3Dquantimodo; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1639414327%7Cdd460739350ce71c52c696ceb4cc9350%7Cquantimodo; _ga_YWVCSGVYJ7=GS1.1.1638619878.1.1.1638622927.0; DokuWiki=ae40qk9q3tkuev9jlv69mj1dmb',
      ),
      'te' => 
      array (
        0 => 'trailers',
      ),
      'cache-control' => 
      array (
        0 => 'max-age=0',
      ),
      'sec-fetch-user' => 
      array (
        0 => '?1',
      ),
      'sec-fetch-site' => 
      array (
        0 => 'none',
      ),
      'sec-fetch-mode' => 
      array (
        0 => 'navigate',
      ),
      'sec-fetch-dest' => 
      array (
        0 => 'document',
      ),
      'upgrade-insecure-requests' => 
      array (
        0 => '1',
      ),
      'accept-encoding' => 
      array (
        0 => 'gzip, deflate, br',
      ),
      'accept-language' => 
      array (
        0 => 'en-US,en;q=0.5',
      ),
      'accept' => 
      array (
        0 => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
      ),
      'user-agent' => 
      array (
        0 => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:96.0) Gecko/20100101 Firefox/96.0',
      ),
      'host' => 
      array (
        0 => 'testing.quantimo.do',
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
      'max-age' => '0',
    ),
  )),
   'defaultLocale' => 'en',
   'isHostValid' => true,
   'isForwardedValid' => true,
));
		$responseBody = $this->callAndCheckResponse($expectedCode, $expectedString);
		return $responseBody;
	}
}
