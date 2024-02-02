<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Http;
use App\Computers\ThisComputer;
use App\Exceptions\InvalidStringException;
use App\Http\Controllers\BaseDataLabController;
use App\Models\BaseModel;
use App\Models\OAClient;
use App\Models\TrackingReminder;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Utils\QMProfile;
use Illuminate\Auth\AuthenticationException;
use Tests\UnitTestCase;
/**
 * @coversDefaultClass \App\Http\Controllers\BaseDataLabController
 */
class DataLabTest extends UnitTestCase
{
	/**
	 * @var string
	 */
	protected $registerPath = 'auth/register';
	/**
	 * @throws InvalidStringException
	 */
	public function testDataLabEmails(){
        $this->skipTest("TODO");
		$this->skipTest("Keeps freezing remotely");
        $this->setAuthenticatedUser(UserIdProperty::USER_ID_MIKE);
        $routes = BaseDataLabController::getIndexRoutes();
		$factory = TrackingReminder::factory()->make();
        foreach($routes as $route){
	        ThisComputer::logMemoryUsage();
			QMProfile::profileIfEnvSet();
            $model = $this->getOrCreateModelForRoute($route);
            $this->checkDataTable($model);
            $response = $this->assertGet200($route->uri."?email=1");
            $body = $response->getContent();
            $type = $route->getTestUrl()." response for admin";
            $this->assertContainsDropDownButtons($body, $type);
			QMProfile::endProfile();
        }
    }
	public function testGetTableFromUrl(){
        $actual = QMRequest::getTable("datalab/oAuthClients");
        $this->assertEquals(OAClient::TABLE, $actual);
    }
	public function testDataLabRoutesWithoutAuth(){
        $this->skipTest("TODO");
        $this->setAuthenticatedUser(null);
		$this->assertGuest();
        $routes = BaseDataLabController::getIndexRoutes();
		self::setExpectedRequestException(AuthenticationException::class);
        foreach($routes as $route){
			ThisComputer::logMemoryUsage();
			$this->assertGetRedirect($route->uri, $this->registerPath);
		}
    }
	/**
	 * @throws InvalidStringException
	 */
	public function testDataLabRoutesAsAdmin(){
        $this->skipTest("TODO");
        WpPost::firstOrFakeNew();
        $routes = BaseDataLabController::getIndexRoutes();
        $this->setAdminUser();
        foreach($routes as $route){
            //if(strpos($route->uri, '/connectors') === false){continue;}
            $model = $this->getOrCreateModelForRoute($route);
            $this->checkDataTable($model);
            $response = $this->assertGet200($route->uri);
            $body = $response->getContent();
            $type = $route->getTestUrl()." response for admin";
            $this->assertContainsDropDownButtons($body, $type);
            if($model->hasUserIdAttribute()){
                $this->assertHtmlContains('user_id_link', $body, $type);
            } else {
                $this->assertHtmlDoesNotContain('User ID', $body, $type);
            }
            //$this->assertHtmlContains([$model->getDisplayNameAttribute()], $body, $type);
        }
    }
	/**
	 * @throws InvalidStringException
	 */
	public function testDataLabRoutesAsRegularUser(){
        $this->skipTest("TODO");
        $routes = BaseDataLabController::getIndexRoutes();
        $this->setAuthenticatedUser(UserIdProperty::USER_ID_TEST_USER);
        $this->assertFalse(QMAuth::isAdmin());
        foreach($routes as $route){
            $model = $this->getOrCreateModelForRoute($route);
            if($route->isAdmin()){
                $this->assertGetRedirect($route->uri, $this->registerPath);
            } else {
                $response = $this->assertGet200($route->uri);
                $body = $response->getContent();
                $type = $route->getTestUrl()." response for non-admin";
                $this->assertContainsDropDownButtons($body, $type);
                if($route->uri !== 'datalab/users'){
                    $this->assertHtmlDoesNotContain('user_id_link', $body, $type);
                }
                //$this->assertHtmlContains([$model->getDisplayNameAttribute()], $body, $type);
            }
        }
    }
	/**
	 * @param string $body
	 * @param string $type
	 * @throws InvalidStringException
	 */
    private function assertContainsDropDownButtons(string $body, string $type): void{
        $this->assertHtmlContains('drop_down_button',
            $body,
            $type,
            false,
            "$type should have column for drop_down_button");
        $this->assertHtmlContains('index-menu',
            $body,
            $type,
            false,
            "$type should have header index-menu button");
    }
    /**
     * @param \App\Models\BaseModel|null $model
     */
    private function checkDataTable(BaseModel $model): void{
        $datatable = $model->getEloquentDataTable();
        $def = $datatable->getColumnDef();
        $append = $def['append'];
        $this->assertGreaterThan(2, count($append));
        foreach($append as $arr){
            $this->assertIsCallable($arr['content']);
        }
        $this->assertEquals(count($datatable->getExtraColumns()), count($append));
    }
}
