<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\TypedProjectFile;
use App\Folders\AbstractFolder;
use App\Folders\DynamicFolder;
use App\Logging\ConsoleLog;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Repos\QMAPIRepo;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\DiffFile;
use App\Utils\EnvOverride;
use App\Utils\QMProfile;
use Tests\QMDebugBar;
trait IsTestArtifactFile {
	protected static function getSuffix(): string{
		$short = QMStr::toShortClassName(static::class);
		$str = str_replace("File", "", $short);
		return "$str." . static::getDefaultExtension();
	}
	/**
	 * @return array|string
	 */
	abstract public static function getData();
	public static function getFolderPaths(): array{
		return [
			DynamicFolder::STORAGE_LOGS_PHPUNIT,
		];
	}
	/**
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function assertSame(): void{
		$differ = new DiffFile(self::getData(), static::generatePath(), false);
		$differ->assertSame();
	}
	/**
	 * @return string
	 */
	public static function generatePathPrefix(): string{
        $f = AbstractFolder::getCurrentTestFolder();
        $sc = \App\Utils\AppMode::getCurrentTestShortClass();
        $n = \App\Utils\AppMode::getCurrentTestName();
		return $f . DIRECTORY_SEPARATOR . $sc . '-' . $n . '-';
	}
	/**
	 * @return string
	 */
	public static function generatePath(): string{
		return static::generatePathPrefix() . static::getSuffix();
	}
	public static function generateContents(): ?string{
		$data = self::getData();
		if(empty($data)){
			return null;
		}
		if(!is_string($data)){
			$data = QMStr::prettyJsonEncode($data);
		}
		return $data;
	}
	public static function saveIfLocal(){
		if(!EnvOverride::isLocal()){
			return;
		}
		QMLog::info(__METHOD__);
		$data = self::generateContents();
		if(empty($data)){
			self::logNoDataError();
			return;
		}
		FileHelper::saveIfLocal(static::generatePath(), $data);
	}
	/**
	 * @param string $artifactName
	 * @param string $new
	 * @param string|null $message
	 * @param bool $ignoreNumbers
	 * @return void
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function compareFile(string $artifactName, string $new, string $message = null,
		bool $ignoreNumbers = false): void{
		$artifactName = str_replace(" ", "-", $artifactName);
		$d = new DiffFile($new, static::generatePathPrefix() . $artifactName, $ignoreNumbers);
		$d->assertSame($message);
	}
	public static function saveAllIfLocal(){
		if(!QMAPIRepo::branchIsDevelop()){
			ConsoleLog::debug("not saving logs because we're not on the develop branch");
			return;
		}
		ConsoleLog::info(__METHOD__);
		TestLogsFile::saveIfLocal();
		//TestMemoryFile::saveIfLocal();
		//TestQueriesFile::saveIfLocal();
		if(QMClockwork::enabled()){
			ClockworkFile::saveIfLocal();
		}
		if(QMProfile::alreadyProfiling()){
			TestProfile::saveIfLocal();
		}
		if(QMDebugBar::enabled()){
			AbstractDebugbarFile::saveAllIfLocal();
		}
		TestQueryLogFile::saveIfLocal();
	}
	/**
	 * @param string $key
	 * @param $obj
	 * @param string|null $message
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function assertSameObject(string $key, $obj, string $message = null): void{
		$json = $obj;
		if(!is_string($json)){
			$json = json_encode($json);
		}
		$arr = json_decode($json, true);
		QMArr::alphabetizeKeysRecursive($arr);
		$new = QMStr::prettyJsonEncodeUnescapedSlashes($arr);
		static::compareFile("$key.json", $new, $message);
	}
	/**
	 * @param string $filename
	 * @param string $new
	 * @return void
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function assertSameStringInFile(string $filename, string $new): void{
		static::compareFile($filename, $new);
	}
	/**
	 * Over-ridden parent because 1 file is too big
	 */
	protected static function savePropertiesAsSeparateFiles(): void{
		$data = self::getData();
		if(empty($data)){
			self::logNoDataError();
			return;
		}
		if($data instanceof TypedProjectFile){
			$data = $data->getDecoded();
		}
		foreach($data as $key => $value){
			$json = QMStr::prettyJsonEncode($value);
			if(QMStr::getSizeOfStringInKB($json) > 10){
				static::create(self::generatePathPrefix() . "$key-" . static::getSuffix(), $json);
				if(is_array($data)){
					unset($data[$key]);
				} else{
					unset($data->$key);
				}
			}
		}
		$json = QMStr::prettyJsonEncode($data);
		static::create(self::generatePathPrefix() . static::getSuffix(), $json);
	}
	public static function getDefaultFolderRelative(): string{
		return AbstractFolder::getCurrentTestFolder();
	}
	public static function getDefaultName(): string{
		return \App\Utils\AppMode::getCurrentTestName() . "-" . static::getSuffix();
	}
	protected static function logNoDataError(): void{
		QMLog::debug("No getData in " . QMStr::toShortClassName(__CLASS__) . "::" . __FUNCTION__ . " for " .
			QMStr::toShortClassName(static::class));
	}
	public function getPath(): string{
		$this->setAbsPath(static::generatePath());
		return $this->absPath;
	}
	public function getContents(): string{
		return self::generateContents();
	}
}
