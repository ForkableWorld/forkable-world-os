<?php
namespace Tests\TestGenerators;
use App\Files\Env\EnvFile;
use App\Files\PHP\BasePhpUnitTestFile;
use App\Logging\QMLog;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Throwable;
class ApiTestFile extends BasePhpUnitTestFile {
    public const GENERATED_TESTS_PATH = 'tests/StagingUnitTests/D';
	/**
	 * @param string|null $pathWithQuery
	 * @return string
	 */
	public static function generateNamePrefix(string $pathWithQuery = null): string{
		$path = self::getTestedPath($pathWithQuery);
		$params = self::getQueryParams($pathWithQuery);
		$parts = self::formatPathParts($path);
		$prefix = implode("", $parts);
		if(AppMode::isApiRequest()){
			$method = ucfirst(strtolower(qm_request()->getMethod()));
			if($method !== "Get"){$prefix = $method.$prefix;}
		}
		$prefix = substr($prefix, 0, 150);
		return $prefix;
	}
    public static function generateNameSpace():string{
        return QMStr::pathToNameSpace(static::GENERATED_TESTS_PATH);
    }
    /**
     * @return string|string[]
     */
    protected static function getFileContent(): string{
        try {
            $captured = clone Request::capture();
	        $server = $captured->server;
			foreach($captured as $key => $value){
				if(empty($value)){
					unset($captured->$key);
				} elseif ($value instanceof ParameterBag){
					if(!$value->all()){
						unset($captured->$key);
					}
				}
			}
			foreach($server->all() as $key => $value){
				if(empty($value)){$server->remove($key);}
				elseif(EnvFile::rootEnvHasValue($key)){$server->remove($key);}
//				if(strpos($key, "CONTENT") !== 0 &&
//				   strpos($key, "RE") !== 0){
//					$server->remove($key);
//				}
			}
            $requestString = QMLog::var_export($captured, true);
        } catch (Throwable $exception) {
            try {
                $captured = Request::capture();
                $requestString = "'".serialize($captured)."'";
            } catch (Throwable $exception) {
                return $exception->getMessage();
            }
        }
		$requestString = QMStr::removeLinesContaining("=> NULL,", $requestString);
        $requestString = str_replace("'createTest' => '1',", "", $requestString);
        $requestString = str_replace("'phpunit' => '1',", "", $requestString);
        $testName = ApiTestFile::generateNamePrefix();
        $assertions = "
        \$this->checkTestDuration(5);
        \$this->checkQueryCount(2);";
        if(QMRequest::getParam('draw')){
            $testName = str_replace("Datalab", "DataTable", $testName);
            $testName = str_replace("DataLab", "DataTable", $testName);
            $assertions .= "
        \$this->assertCount(1, \$response->data);
        foreach(\$response->data as \$datum){\$this->assertEquals(UserIdProperty::USER_ID_TEST_USER, \$datum->user_id);}
        \$this->assertDataTableQueriesEqual([]);";
        }
        $namespace = self::generateNameSpace();
        if(\request()->expectsJson()){
            $noAuthFunction = "assertUnauthenticatedResponse";
        } else {
	        $noAuthFunction = "assertGuestRedirectToLogin";
        }
        $testCase = 'LaravelStagingTestCase';
		//if(AppMode::isAstral()){$testCase = "AstralTestCase";}
        $content = '<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection SpellCheckingInspection */
namespace '.$namespace.';
use Tests\\'.$testCase.';
use App\Override\QMHeaderBag;
use App\Properties\User\UserIdProperty;
use App\Override\QMParameterBag;
use App\Override\GeneratedTestRequest;
use App\Override\QMServerBag;
use Illuminate\Testing\TestResponse;
class '.$testName.'Test extends '.$testCase.'
{
    protected $REQUEST_URI = "'.$_SERVER['REQUEST_URI'].'";
    public function test'.ucfirst($testName).'AsRegularUser(): void{
        $this->actAsTestUser();
        $response = $this->get($this->REQUEST_URI, $this->getHeaders());
        $response->assertStatus(200);
		$response->assertSee("Measurements");
        $content = $response->getContent();
        $body = json_decode($content, true);
        $this->assertNotNull($body);
        $this->assertTrue(isset($body[\'data\']), "No data found in response");
        $this->checkTestDuration(5);
        $this->checkQueryCount(2);
        '.$assertions.'
    }
    public function test'.ucfirst($testName).'AsAdmin(): void{
        $this->actAsAdmin();
        $response = $this->get($this->REQUEST_URI, $this->getHeaders());
        $response->assertStatus(200);
		$response->assertSee("Measurements");
        $content = $response->getContent();
        $body = json_decode($content, true);
        $this->assertNotNull($body);
        $this->assertTrue(isset($body[\'data\']), "No data found in response");
        $this->checkTestDuration(5);
        $this->checkQueryCount(2);
        '.$assertions.'
    }
    public function test'.ucfirst($testName).'WithoutAuth(): void{
        $this->'.$noAuthFunction.'();
    }
    protected function getHeaders(): array{
        return '.QMLog::var_export(getallheaders(), true).';
    }
    protected function getCookies(): array{
        return '.QMLog::var_export($_COOKIE || [], true).';
    }
    /**
     * @param string|null $expectedString
     * @param int|null $expectedCode
     * @return TestResponse
     */
    protected function stagingRequest(int $expectedCode = 200, string $expectedString = null): TestResponse {'
	        .PHP_EOL;
        $tab = "\t";
        $content .= $tab.$tab.'return $responseBody;'.PHP_EOL;
        $content .= $tab.'}'.PHP_EOL;
        $content .= '}'.PHP_EOL;
        $content = str_replace("Symfony\Component\HttpFoundation\ParameterBag", "QMParameterBag", $content);
        $content = str_replace("Illuminate\Http\Request", "GeneratedTestRequest", $content);
        $content = str_replace("Symfony\Component\HttpFoundation\ServerBag", "QMServerBag", $content);
        $content = str_replace("Symfony\Component\HttpFoundation\FileBag", "QMFileBag", $content);
        $content = str_replace("Symfony\Component\HttpFoundation\HeaderBag", "QMHeaderBag", $content);
        $content = str_replace("'".\App\Utils\Env::getAppUrl(), "\App\Utils\Env::getAppUrl().'", $content);
        $content = str_replace("'".$_SERVER['REQUEST_URI'], "\$this->REQUEST_URI.'", $content);
        return $content;
    }
	/**
	 * @param string|null $path
	 * @return string[]
	 */
	private static function formatPathParts(?string $path): array
    {
		$parts = explode("/", $path);
		foreach($parts as $i => $part){
			if(is_numeric($part) ||
				$part === "api" ||
				empty($part) ||
				strlen($part) == 2) // v2, v1, etc
			{
				unset($parts[$i]);
			} else{
				$part = QMStr::sanitizePHPVariableName($part);
				$parts[$i] = QMStr::toClassName($part);
			}
		}
		return $parts;
	}
	/**
	 * @param string|null $pathWithQuery
	 * @return array
	 */
	private static function getQueryParams(?string $pathWithQuery): array {
		if($pathWithQuery){
			if(strpos($pathWithQuery, "?") !== false){
				$params = UrlHelper::parseQuery($pathWithQuery);
			} else{
				$params = [];
			}
		} else{
			$params = qm_request()->query();
		}
		return $params;
	}
	/**
	 * @param string|null $pathWithQuery
	 * @return null|string
	 */
	private static function getTestedPath(?string $pathWithQuery): string{
		if(!$pathWithQuery){
			$pathWithQuery = qm_request()->getRequestUri();
		}
		$path = QMStr::before('?', $pathWithQuery, $pathWithQuery);
		//$path = str_replace("/api", "tests/StagingUnitTests", $path);
		return $path;
	}
}
