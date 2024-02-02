<?php
namespace App\Files\PHP;
use App\Buttons\Admin\PHPStormButton;
use App\DevOps\Jenkins\Jenkins;
use App\Exceptions\TooBigForCacheException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Folders\DynamicFolder;
use App\Logging\ConsoleLog;
use App\Logging\LinksLogMetaData;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Models\User;
use App\Notifications\TestGeneratedNotification;
use App\Slim\View\Request\QMRequest;
use App\Storage\Firebase\FirebaseGlobalTemp;
use App\Storage\Memory;
use App\Types\QMStr;
use App\UI\Alerter;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\N8N;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
abstract class BasePhpUnitTestFile extends PhpClassFile {
	public const GENERATED_TESTS_PATH = 'tests/Generated';
	private static $alreadyGenerated = [];
	abstract public static function generateNameSpace(): string;
	public static function generateFileName(string $testNameSuffix): string{
		return self::generateClassName($testNameSuffix).'.php';
	}
	protected static function generateClassName(string $testNameSuffix): string{
		return $testNameSuffix.'Test';
	}
	protected static function generateUrl(string $testName, string $content): string{
		$urlEncoded = urlencode($content);
        $filename = self::generateFileName($testName);
		$folder = self::getFolderFromContent($content);
        $url = "https://local.quantimo.do/dev/test?test=".$urlEncoded.'&filename='.$folder."/".$filename;
        return $url;
    }
    /**
     * @param string|null $content
     * @param string|null $namePrefix
     * @return string
     */
    public static function generateAndGetUrl(string $content = null, string $namePrefix = null): string{
	    if(!$namePrefix){$namePrefix = ApiTestFile::generateNamePrefix();}
	    $existing = self::getExistingTest($namePrefix);
	    if($existing){return $existing;}
        if(!$content){$content = ApiTestFile::getFileContent();}
        $url = self::generateUrl($namePrefix, $content);
        $length = strlen($url);
        if($length > 2048){
            if(AppMode::isUnitOrStagingUnitTest()){le("Why are we generating a test in a test?");}
	        $key = SolutionButton::PHPUNIT_TESTS . '/' . time();
            try {
	            FirebaseGlobalTemp::set($key, $content);
            } catch (TooBigForCacheException $e) {
	            QMLog::error(__METHOD__.": ".$e->getMessage());
	            return $e->getMessage();
            }
	        $url = "https://local.quantimo.do/dev/test?key=" . urlencode($key) . '&filename=' .
	               urlencode($namePrefix . 'Test.php');
        }
		LinksLogMetaData::add($namePrefix, $url);
	    return $url;
    }
	private static function getExistingTest(string $testName): ?string {
		return LinksLogMetaData::find($testName);
	}
	/**
	 * @return string
	 */
	protected static function getFileContent(): string{
        if(AppMode::isApiRequest()){
	        return ApiTestFile::getFileContent();
        } else{
            return UnitTestFile::getFileContent();
        }
    }
    public static function getLastTest(): ?array {
	    return Memory::getLast(SolutionButton::PHPUNIT_TESTS);
    }
    public static function saveLocallyAndNotify(string $namePrefix = null, string $content = null): string {
	    if(!$namePrefix){$namePrefix = static::generateNamePrefix();}
		if(isset(self::$alreadyGenerated[$namePrefix])){return self::$alreadyGenerated[$namePrefix];}
        if(!$content){$content = static::getFileContent();}
	    $path = self::saveAndGetPath($content);
	    $url = PHPStormButton::redirectUrl($path);
	    self::notify($url, $namePrefix);
	    return self::$alreadyGenerated[$namePrefix] = $url;
    }
    /**
     * @param string $content
     * @return mixed|string|null
     */
    protected static function getFileNameFromContent(string $content){
        $filename = QMStr::between($content, "class ", " extends").'.php';
        if(QMRequest::getParam('filename')){
            $filename = QMRequest::getParam('filename');
        }
        return $filename;
    }
    /**
     * @param string $content
     * @return string
     */
    protected static function getFolderFromContent(string $content): string{
        $ns = QMStr::between($content, "namespace ", ";");
        $folder = FileHelper::namespaceToFolder($ns);
        return $folder;
    }
    public static function saveAndRedirect(): RedirectResponse {
        $url = static::saveLocallyAndNotify();
        return UrlHelper::redirect($url);
    }
	/**
	 * @param $e
	 */
	public static function saveAndNotify($e): void {
		$url = static::saveLocallyAndNotify();
	}
    public static function generate(){
        static::saveLocallyAndNotify();
    }
	/**
	 * @return array
	 */
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::FOLDER_TESTS,
		];
	}
	public static function clean(){
		$envs = Env::all();
		$envs = array_merge($envs, [
			'REQUEST_TIME_FLOAT' => 1592118699.982282,
			'REQUEST_TIME' => 1592118699,
			'AV_KEY' => 'Q0UL1SDYQBNOVEIN',
			'QM_CONNECTOR_HOST' => 'staging.quantimo.do',
			'DEBUGBAR_ENABLED' => '0',
			'MAILGUN_SMTP_SERVER' => 'smtp.mailgun.org',
			'RELEASE_STAGE' => 'production',
			'REQUEST_LOGGING_ENABLED' => '0',
			'XHGUI_PROFILES_PER_100' => '1',
			'API_LAST_MODIFIED' => '2021-07-07T15:40:40T-0500',
			'GIT_COMMIT' => 'ab6443386e15e2383c614308a690328ece0faaff',
			'BUILD_URL' => 'http://quantimodo2.asuscomm.com:8082/job/DEPLOY-production-api/754/',
			'JOB_NAME' => 'DEPLOY-production-api',
			'BUILD_ID' => '754',
			'NODE_NAME' => 'production-web',
			'GIT_BRANCH' => 'origin/master',
			'JENKINS_HOME' => Jenkins::JENKINS_HOME_FOLDER,
			'JENKINS_URL' => 'http://quantimodo2.asuscomm.com:8082/',]);
		$files = static::all();
		$needles = [];
		foreach($envs as $name => $val){
			$needles[] = "'$name' => ";
		}
		foreach($files as $file){
			if($file->contains("HTTP_SEC_FETCH_DEST")){
				$file->removeLinesContaining($needles);
			}
		}
	}
	/**
	 * @param array|null $folders
	 * @return static[]|Collection
	 */
	public static function get(array $folders = [], string $pathNotLike = null): Collection{
		if(!$folders){$folders = static::getFolderPaths();}
		$ext = static::getDefaultExtension();
		$fileInfos = FileFinder::listProjectFiles("Test.php", $folders, $pathNotLike, $ext);
		$files = self::instantiateArray($fileInfos);
		return collect($files);
	}
	public static function generateName():string{
		return static::generateNamePrefix();
	}
	/**
	 * @param string $url
	 * @param string|null $namePrefix
	 */
	protected static function notify(string $url, ?string $namePrefix): void{
		if(!getenv('NOTIFY_TESTS')){return;}
		$n = new TestGeneratedNotification($url, $namePrefix . "Test");;
		$mike = User::mike();
		try {
			$mike->notify($n);
		} catch (\Throwable $e){
		    ConsoleLog::info($e->getMessage(), QMLog::var_export($n));
		}
		try {
			Alerter::toastWithButton($namePrefix . " saved", $url, "Open Test");
		} catch (\Throwable $e) {
			ConsoleLog::exception($e);
		}
		try {
			N8N::openUrl($url);
		} catch (\Throwable $e) {
			ConsoleLog::exception($e);
		}
	}
	/**
	 * @param string|null $content
	 * @return string
	 */
	protected static function saveAndGetPath(?string $content): string{
		$folder = self::getFolderFromContent($content);
		$filename = self::getFileNameFromContent($content);
        //$folder = 'storage';
		$path = FileHelper::writeByDirectoryAndFilename($folder, $filename, $content);
		return $path;
	}
}
