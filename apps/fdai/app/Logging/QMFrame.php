<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Buttons\Admin\PHPStormButton;
use App\Files\FileHelper;
use App\Files\PHP\UnitTestFile;
use App\Types\QMStr;
use App\Utils\EnvOverride;
use Facade\FlareClient\Stacktrace\Codesnippet;
use Nette\PhpGenerator\Method;
use NunoMaduro\Collision\ArgumentFormatter;
use Tests\QMBaseTestCase;
use Tests\UnitTests\AAAErrorLoggingTest;
use Whoops\Exception\Frame;
class QMFrame extends Frame {
	protected $argsString;
	protected $linkText;
	protected $phpunitUrl;
	protected $url;
	/**
	 * @var \Whoops\Exception\Frame
	 */
	private Frame $whoopsFrame;
	/**
	 * QMFrame constructor.
	 * @param Frame $whoopsFrame
	 */
	public function __construct(Frame $whoopsFrame){
		$this->setWhoopsFrame($whoopsFrame);
		parent::__construct($whoopsFrame->frame);
		foreach($whoopsFrame as $key => $value){
			$this->$key = $value;
		}
		if($this->application === null){
			$isApp = self::isApplicationFrame($whoopsFrame);
			$this->setApplication($isApp);
		}
	}
	/**
	 * @param \Whoops\Exception\Frame $whoopsFrame
	 * @return bool
	 */
	public static function isApplicationFrame(Frame $whoopsFrame): bool{
		$class = $whoopsFrame->getClass();
		$isApp = strpos($class, 'App\\') === 0 || strpos($class, 'Tests\\') === 0 ||
			strpos($whoopsFrame->getFile(), '/vendor/') === false;
		return $isApp;
	}
	public function getPhpStormButton(): PHPStormButton{
		return new PHPStormButton($this->getClassFunctionFileLine(), $this->getPHPStormUrl());
	}
	public function getClassFunctionFileLine(): string{
		return $this->getShortClassName() . "::" . $this->getFunction() . " " . $this->getFileLineNumber();
	}
	private function getShortClassName(): ?string{
		$class = $this->getClass();
		if(!$class){
			return null;
		}
		return QMStr::toShortClassName($class);
	}
	public function getFileLineNumber(): string{
		return $this->getFile() . ":" . $this->getLine();
	}
	public function getPHPStormUrl(): string{
		$file = $this->getFile();
		$root = FileHelper::projectRoot();
		if(stripos($file, $root) === false){
			return "Could not determine URL for file $file";
		}
		return $this->url = PHPStormButton::redirectUrl($file, $this->getLine());
	}
	public function getLink(): array{
		return [$this->getLinkText() => $this->getPHPStormUrl()];
	}
	public function getLinkText(): string{
		if($this->linkText){
			return $this->linkText;
		}
		$function = $this->getFunction();
		if(strpos($function, "test") === 0){
			return "RUN $function TO SEE THE PATH TO FAILURE";
		}
		if($function === "fail"){
			return "BREAK @ $function";
		}
		return $this->getFunctionCallWithArgs();
	}
	public function setLinkText(string $string){
		$this->linkText = $string;
	}
	public function getFunctionCallWithArgs(): string{
		$function = $this->getFunction();
		if(strpos($function, "test") === 0){
			return "RUN => $function";
		}
		if (!extension_loaded('xdebug') || !function_exists('xdebug_is_enabled') || !xdebug_is_enabled()) {
			QMLog::once("Cannot return args with stack because xdebug is not enabled");
		}
		$args = $this->getArgsString();
		$args = QMStr::truncate($args, 140, "[TRUNCATED']");
		$val = $this->getShortClassName() . "::" . $this->getFunction() . "($args)";
		return $val;
	}
	public function getArgsString(): string{
		$args = $this->getArgs();
		return $this->argsString = (new ArgumentFormatter())->format($args);
	}
	/**
	 * @return string|null
	 */
	public function getFunctionOrFile(): string{
		$func = $this->getFunction();
		if($func){
			return $func;
		}
		if(EnvOverride::isLocal() && !QMBaseTestCase::currentTestClassIs(AAAErrorLoggingTest::class)){return $this->getFileLine();}
		return $this->getFileLineWithoutBasePath();
	}
	public function getFileLine(): string{
		return $this->getFile() . ":" . $this->getLine();
	}
	public function getFileLineWithoutBasePath(): string{
		$base = FileHelper::projectRoot();
		return str_replace($base."/", "", $this->getFileLine());
	}
	public function toArray(): array{
		$codeSnippet = $this->getCodeSnippet();
		return [
			'line_number' => $this->getLine(),
			'method' => $this->getFunction(),
			'class' => $this->getClass(),
			'code_snippet' => $codeSnippet,
			'file' => $this->getFile(),
			'is_application_frame' => $this->application,
		];
	}
	public function getCodeSnippet(): array{
		$codeSnippet = (new Codesnippet())
			->snippetLineCount(31)
	          ->surroundingLine($this->getLine())
	          ->get($this->getFile());
		$args = $this->getArgs();
		if($args){
			$before = $codeSnippet[$this->getLine()];
			$argStr = $this->getArgsString();
			$codeSnippet[$this->getLine()] = "$before // ARG VALUES: $argStr";
		}
		return $codeSnippet;
	}
	public function getPhpUnitUrl(): string{
		if($url = $this->phpunitUrl){
			return $url;
		}
		$file = $this->getPhpUnitFile();
		if(!$file->exists()){
			$file->generate();
		}
		$file->setMethods([$this->getTestMethod()]);
		$file->save();
		return $file->getPhpStormUrl();
	}
	private function getPhpUnitFile(): UnitTestFile{
		return UnitTestFile::findOrNew($this->getClass());
	}
	/**
	 * @return Method
	 */
	public function getTestMethod(): Method{
		$method = new Method("test" . ucfirst($this->getFunction()));
		$method->setBody($this->getCodeSnippetString());
		return $method;
	}
	public function getCodeSnippetString(): string{
		return implode("\n", $this->getCodeSnippet());
	}
	/**
	 * @return Method
	 */
	public function getMethod(): Method{
		$method = new Method($this->getFunction());
		$method->setBody($this->getCodeSnippetString());
		return $method;
	}
	public function getLocation(): string{
		$str = $this->getClassFunctionFileLine();
		if(!EnvOverride::isLocal()){
			$str = "\n\t" . $this->getPHPStormUrl();
		}
		return $str;
	}
	/**
	 * @return \Whoops\Exception\Frame
	 */
	public function getWhoopsFrame(): Frame{
		return $this->whoopsFrame;
	}
	/**
	 * @param \Whoops\Exception\Frame $whoopsFrame
	 */
	public function setWhoopsFrame(Frame $whoopsFrame): void{
		$this->whoopsFrame = $whoopsFrame;
	}
}
