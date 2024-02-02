<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Buttons\Admin\PHPStormButton;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Env;
use App\Utils\UrlHelper;
use Illuminate\Support\Collection;
use Tests\QMBaseTestCase;
use Throwable;
class SolutionButton extends QMButton {
	public const PHPUNIT_TESTS = 'PHPUNIT_TESTS';
	public static array $cache = [];
	public float $time;
	public ?string $test;
	public ?string $content;
	/**
	 * SolutionLink constructor.
	 * @param string      $name
	 * @param string      $url
     * @param string|null $content
     */
    public function __construct(string $name, string $url, string $content = null){
        $this->time = microtime(true);
        $this->test = AppMode::getCurrentTestName();
	    $this->content = $content;
	    parent::__construct($name, $url);
    }
    public static function addUrlNameArrays(array $links = [], \Throwable $e = null): array{
		$buttons = self::all();
        foreach($buttons as $test){$links[$test->getNameAttribute()] = $test->getUrl();}
        if($e){$links = array_merge(self::getLinksFromException($e), $links);}
        $req = Memory::getLast(Memory::REQUESTS);
        if($req && $req->uri){
            if(!is_string($req->uri)){le("req->uri should be a string but is: ",$req->uri);}
            $url = UrlHelper::getTestUrl($req->uri);
            $links[$url] = $url;
        }
        if(AppMode::isJenkins()){
            $console = new JenkinsConsoleButton();
            $links[$console->getTitleAttribute()] = $console->getUrl();
        }
		if($url = UrlHelper::getBuildUrl()){$links['BUILD'] = UrlHelper::getBuildUrl();}
        if(($test = AppMode::getCurrentTestName()) && $e){
	        $links = array_merge($links, [$test => SolutionButton::getPHPStormUrlForTest($e)]);
        }
        if(AppMode::isApiRequest()){
            if($url = QMRequest::getTestVersionOfCurrentUrl()){$links['Test Version Of Current Url'] = $url;}
        }
	    if($c = QMConnector::getCurrentConnection()) {$links['IMPORT_TEST'] = $c->getPHPUnitTestUrl();}
        return self::removeDuplicateUrls($links);
    }
    public static function reset(){
        self::$cache = [];
    }
	public static function addIfDebugMode(string $name, string $url, string $content = null){
		if(Env::APP_DEBUG()){
			self::add($name, $url, $content);
		}
	}
    /**
     * @param string $name
     * @param string $url
     * @param string|null $content
     */
    public static function add(string $name, string $url, string $content = null){
        if(strpos($url, "http") !== 0){
            $url = UrlHelper::getLocalUrl("api/v3/".$url);
        }
        QMLog::debug(__METHOD__.": $name solution button URL:\n\t$url");
        self::$cache[] = new static($name, $url, $content);
    }
    /**
     * @return static[]
     * @noinspection PhpDocSignatureInspection
     */
    public static function getWithAccessTokens(): Collection{
        $all = self::all();
        if($all->count()){
            $t = Memory::getQmAccessTokenObject();
            foreach($all as $item){
                if($t && stripos($item->url, 'token') === false){
                    $item->url = UrlHelper::addParam($item->url, 'accessToken', $t->getAccessTokenString());
                }
            }
        }
        return $all;
    }
	/**
	 * @return static[]|Collection
	 */
	public static function all(): Collection{
		if(AppMode::getCurrentTestName()){
			return collect(self::$cache)->filter(function($one){
				/** @var static $one */
				return $one->test === AppMode::getCurrentTestName();
			});
		}
		return collect(self::$cache);
	}
    /**
     * @param \Throwable|null $e
     * @return array
     */
    protected static function getLinksFromException(\Throwable $e): array{
	    $links = [];
	    $arr = GlobalLogMeta::get();
        foreach($arr as $key => $value){
            if(is_string($value) && stripos($value, "http") === 0){
                $links[$key] = $value;
            }
        }
	    $links[get_class($e)] = self::getPHPStormUrlForTest($e);
        return $links;
    }
    /**
     * @param Throwable $e
     * @return string
     */
    public static function getPHPStormUrlForTest(Throwable $e): string{
        $fileFromClass = null;
        $functionFromClass = null;
        $trace = $e->getTrace();
        foreach($trace as $frame){
            $file = $frame['file'] ?? null;
            if(!$file){
                continue;
            }
            $class = $frame["class"] ?? null;
            if(!$class){
                continue;
            }
            if(stripos($class, "Tests") === 0){
                $fileFromClass = QMStr::classToPath($class);
                $functionFromClass = $frame["function"];
            }
            if(stripos($file, '/vendor/') !== false){
                continue;
            }
            if(stripos($file, 'Test.php') === false){
                continue;
            }
            //if(stripos($frame['function'], 'test') !== 0){continue;}
            return PHPStormButton::redirectUrl($file, $frame['line']);
        }
        if(!$fileFromClass){
            $message = $e->getMessage()." ".get_class($e);
            return "No file from this $message trace to get getPHPStormUrlForTest: ";
        }
        try {
            $line = FileFinder::findLineNumberContainingString($fileFromClass, "function ".$functionFromClass);
        } catch (QMFileNotFoundException $e) {
            /** @var \LogicException $e */
            throw $e;
        }
        return PHPStormButton::redirectUrl($fileFromClass, $line);
    }
    /**
     * @param array $links
     * @return array
     */
    private static function removeDuplicateUrls(array $links): array{
        $keep = [];
        $urls = [];
        $links = QMArr::sortByLengthOfKeysDescending($links);
        foreach($links as $name => $url){
            if(empty($name)){le("Name for $url is $name", $links);throw new \LogicException();}
            if(!in_array($url, $urls)){
                $keep[$name] = $url;
            }else{
                $duplicates[$name] = $urls;
            }
            $urls[] = $url;
        }
        return $keep;
    }
}
