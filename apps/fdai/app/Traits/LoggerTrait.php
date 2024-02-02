<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\QMException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\Compare;
use App\Utils\Env;
use App\Utils\EnvOverride;
trait LoggerTrait {
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logError(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		// Prefixing getLogMetaData to the message spams bugsnag with prefixed stuff like:
		// "Relationship Between Graphic Design Activities and Minutes After Wakeup Still In Bed: "
		// Making the actual error invisible: Excluding a pair of averages for https://app.quantimo.do/user-variables/162957
		// The meta stuff should be in the meta-object
		//$this->addSolutionLinks();
		QMLog::error($name, $this->getLogMetaData($meta), $obfuscate, $message);
	}
	public function logErrorIfNotTesting(string $name, $meta = [], string $message = null){
		if(AppMode::isTestingOrStaging()){
			QMLog::info($name, $this->getLogMetaData($meta));
		} else {
			$this->logError($name, $meta, true, $message);
		}
	}
    public function logName(){
        ConsoleLog::info($this->getTitleAttribute());
    }
    public function logId(){
        ConsoleLog::info($this->getId());
    }
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logWarning(string $message, $meta = []){
		// Prefixing getLogMetaData to the message spams bugsnag with prefixed stuff like:
		// "Relationship Between Graphic Design Activities and Minutes After Wakeup Still In Bed: "
		// Making the actual error invisible: Excluding a pair of averages for https://app.quantimo.do/user-variables/162957
		// The meta stuff should be in the meta-object
		QMLog::warning($message, $this->getLogMetaData($meta));
	}
	/**
	 * @param array|null $meta
	 * @return array
	 */
	public function getLogMetaData(?array $meta = []): array{
		if(!$meta){$meta = [];}
		//$meta["NAME"] = $this->getNameOrTitle(); // Causes infinite loop
//		if(method_exists($this, 'getUrl')){
//			try {$meta["DEBUG_URL"] = $this->getUrl();} catch (\Throwable $e){ConsoleLog::info(static::class.": ".$e->getMessage());}
//		}
//		if(method_exists($this, 'hasId')){
//			if($this->hasId()){
//				try {$meta["ID"] = $this->getId();} catch (\Throwable $e){ConsoleLog::info(static::class.": " .$e->getMessage());}
//			}
//		}
		//$meta["CLASS"] = get_class($this);
		return $meta;
	}
	/**
	 * @return string
	 */
	public function getNameOrTitle(): string {
		if (method_exists($this, 'getTitleAttribute')){
			try {return $this->getTitleAttribute();
			} catch (\Throwable $e){ConsoleLog::info(static::class.": ".$e->getMessage());}
		}
		if(method_exists($this, 'getNameAttribute')){
			try {return $this->getNameAttribute();} catch (\Throwable $e){ConsoleLog::info(static::class.": "
				.$e->getMessage());}
		}
		if (method_exists($this, 'getName')){
			try {return $this->getName();
			} catch (\Throwable $e){ConsoleLog::info(static::class.": ".$e->getMessage());}
		}
		if (method_exists($this, 'getDisplayNameAttribute')){
			try {return $this->getDisplayNameAttribute();
			} catch (\Throwable $e){ConsoleLog::info(static::class.": ".$e->getMessage());}
		}
		return QMStr::classToTitle(static::class);
		//le("Please add getTitleAttribute and/or getNameAttribute to ".get_class($this));throw new \LogicException();
	}
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logErrorOrDebugIfTesting(string $message, $meta = []){
		QMLog::errorOrDebugIfTestingOrTestUser($message, $this->getLogMetaData($meta), null, null);
	}
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logInfo(string $message, $meta = []){
		// Don't add getLogMetaDataString all the time. Just make sure __toString is defined and put $this in the message string when needed
		try {
			$meta = $this->getLogMetaData($meta);
		} catch (\Throwable $e) {
			ConsoleLog::info("Could not getLogMetaData because " . $e->getMessage());
		}
		$class = (new \ReflectionClass(static::class))->getShortName();
		ConsoleLog::info($class.": ".$message, $meta);
	}
	/**
	 * @param $old
	 * @param $new
	 * @param string $what
	 */
	public function logChange($old, $new, string $what){
		$this->logInfo(Compare::toString($old, $new, $what));
	}
	/**
	 * @param string|null $url
	 * @param string|null $title
	 */
	public function logLink(string $url = null, string $title = null){
		if(!$title){$title = $this->getNameOrTitle();}
		QMLog::logLink($url ?? $this->getUrl(), $title);
	}
	/**
	 * @param string $message
	 */
	public function logInfoWithoutContext(string $message){
		ConsoleLog::info($message);
	}
	/**
	 * @param string $name
	 * @param mixed $meta
	 * @param string|null $message
	 */
	public function logInfoWithoutObfuscation(string $name, $meta = [], string $message = null){
		QMLog::info($name, $meta, false, $message);
	}
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logInfoIfDevelopment(string $message, $meta = []){
		if(!EnvOverride::isLocal()){
			return;
		}
		$this->logInfo($message, $this->getLogMetaData($meta));
	}
	/**
	 * @param string $message
	 * @param mixed $meta
	 */
	public function logDebug(string $message, $meta = []){
		if(!Env::APP_DEBUG()){
			return;
		}
		QMLog::debug($message, $this->getLogMetaData($meta));
	}
	/**
	 * @param string $message
	 * @param array $meta
	 */
	public function logErrorOrInfoIfTesting(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		QMLog::errorOrInfoIfTesting($name, $this->getLogMetaData($meta), $obfuscate, $message);
	}
	/**
	 * @param string $message
	 * @param null $meta
	 */
	public function exceptionIfNotProductionAPI(string $name, $meta = [], bool $obfuscate = true, string $message = null){
		QMLog::logicExceptionIfNotProductionApiRequest($name, $this->getLogMetaData($meta), $obfuscate, $message);
	}
	/**
	 * @param string $message
	 * @param int $code
	 */
	public function throwQMException(string $message, int $code = QMException::CODE_INTERNAL_SERVER_ERROR){
		throw new QMException($code, $message);
	}
	/**
	 * @param string $message
	 * @param null $meta
	 */
	public function throwLogicException(string $message, $meta = null){
		le($message, $this->getLogMetaData($meta));
	}
	/**
	 * @return string
	 */
	abstract public function __toString();
	/**
	 * @param string $message
	 */
	public function errorOrLogicExceptionIfTesting(string $message){
		if(AppMode::isTestingOrStaging()){
			le($message);
		}
		$this->logError($message);
	}
	protected function addSolutionLinks(): void{
		if(method_exists($this, 'getShowUrl')){
			try {
				SolutionButton::add("View " . $this->getTitleAttribute(), $this->getUrl());
			} catch (\Throwable $e) {
				QMLog::info("Could not add getShowUrl because: \n" . $e->getMessage());
			}
		}
		if(method_exists($this, 'getPHPUnitTestUrl') && $this->hasId()){
			try {
				SolutionButton::add("PHPUnit Test for " . $this->getTitleAttribute(), $this->getPHPUnitTestUrl());
			} catch (\Throwable $e) {
				QMLog::info("Could not add getPHPUnitTestUrl because: \n" . $e->getMessage());
			}
		}
		if(method_exists($this, 'getPHPUnitJobTest') && $this->hasId()){
			if(!AppMode::isAnyKindOfUnitTest()){
				try {
					SolutionButton::add("PHPUnit Job Test for " . $this->getTitleAttribute(), $this->getPHPUnitJobTest());
				} catch (\Throwable $e) {
					QMLog::info("Could not add getPHPUnitTestUrl because: \n" . $e->getMessage());
				}
			}
		}
	}
	/**
	 * @param string $message
	 * @param array $metaData
	 */
	public function exceptionIfTesting(string $message, array $metaData = []){
		QMLog::exceptionIfTesting($this->__toString() . $message, $this->getLogMetaData($metaData));
	}
	/**
	 * @return array
	 */
	public function __debugInfo(): array {
        return [];
	}
}
