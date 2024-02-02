<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use Tests\QMBaseTestCase;
use Tests\QMDebugBar;
class ResponsesRepo extends GitRepo {
	public static $REPO_NAME = 'qm-responses';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'master';
	public const ENCODING_FORMAT = 'json'; // JSON allows collapsing sections when viewing in file editor and print_r does not
	/**
	 * @param string $testName
	 * @param string $property
	 * @return string|null
	 * @throws QMFileNotFoundException
	 */
	public static function getPreviousPropertyResponse(string $testName, string $property): ?string{
		$str = FileHelper::getContents(ResponsesRepo::pathForPropertyRelativeToQmApiRepo($testName, $property));
		if(self::ENCODING_FORMAT !== 'print_r'){
			$str = json_decode($str);
		}
		return $str;
	}
	/**
	 * @param string $url
	 * @param string $type json xml or html
	 * @param int $userId
	 * @return string
	 * @throws InvalidFilePathException
	 */
	public static function urlToPath(string $url, string $type, int $userId): string{
		$url = UrlHelper::stripAPIKeysFromURL($url);
		$path = self::truncatePath($url, $userId);
		$path = QMStr::sanitizeFilePath($path);
		if($type === "html"){
			$path .= ".html";
		}
		//$relativeFilePath = str_replace("=", "/", $relativeFilePath);
		if($type === "json"){
			$path .= "/data." . $type;
		}
		$path = self::truncatePath($path, $userId);
		return "users/$userId/" . $path;
	}
	/**
	 * @param string $relativeFilePath
	 * @param int $userId
	 * @return string
	 */
	protected static function truncatePath(string $relativeFilePath, int $userId): string{
		$chars =
			InvalidFilePathException::MAXIMUM_FILE_NAME_LENGTH - strlen(self::getAbsolutePath("users/$userId/")) - 1;
		return InvalidFilePathException::truncateFromBeginning($relativeFilePath, $chars);
	}
	/**
	 * @param string $testName
	 * @param string $property
	 * @param $value
	 * @return array
	 */
	private static function saveProperty(string $testName, string $property, $value): ?array{
		// This uses up space on the nodes and we wouldn't commit anyway
		// Doing it on development when running troublesome tests, we can commit and avoid lots of data transfer
		if($value === null){
			return null;
		}
		if($property === "errors"){
			return null;
		}
		return self::writeToPropertyValuesToSubFolders($testName, $property, $value);
	}
	/**
	 * @param string $testName
	 * @param string $property
	 * @return string
	 */
	protected static function pathForPropertyRelativeToQmApiRepo(string $testName, string $property): string{
		return self::getPathForTestRelativeToQmApiRepo($testName) . "/" . $property;
	}
	/**
	 * @param string $testName
	 * @return string
	 */
	protected static function getPathForTestRelativeToQmApiRepo(string $testName): string{
		return static::getAbsPath() . "/tests/" . $testName;
	}
	/**
	 * @param string $testName
	 * @param string $property
	 * @param mixed $value
	 * @return array
	 */
	protected static function writeToPropertyValuesToSubFolders(string $testName, string $property, $value): array{
		$urls = [];
		$mainPropertyPath = ResponsesRepo::pathForPropertyRelativeToQmApiRepo($testName, $property);
		FileHelper::deleteIfNotDirectory($mainPropertyPath);
		if(is_object($value)){
			foreach($value as $subKey => $subValue){
				if($subValue === null){
					continue;
				}
				if(stripos($subKey, " ") !== false){
					$urls[$property] = self::writePropertyValueToFile($value, $mainPropertyPath);
					return $urls;
				}
				if(is_string($subValue) && trim($subValue) === ""){
					continue;
				}
				$subPropertyPath = $mainPropertyPath . "/$subKey";
				$urls[$property][$subKey] = self::writePropertyValueToFile($subValue, $subPropertyPath);
			}
		} else{
			$urls[$property] = self::writePropertyValueToFile($value, $mainPropertyPath);
		}
		return $urls;
	}
	/**
	 * @param $value
	 * @param string $path
	 * @return string
	 */
	protected static function writePropertyValueToFile($value, string $path): ?string{
		if($value === null){
			le("No value provided!");
		}
		if(is_string($value) && HtmlHelper::isHtml($value)){
			$path .= '.html';
			$str = $value;
		} elseif(self::ENCODING_FORMAT === 'print_r'){
			$str = \App\Logging\QMLog::print_r($value, true);
		} else{
			$str = QMStr::prettyJsonEncodeUnescapedSlashes($value);
			$path .= '.json';
		}
		$str = QMStr::removeDatesAndTimes($str);
		if(trim($str) === ""){
			return null;
		}
		try {
			FileHelper::validateFilePath($path);
		} catch (InvalidFilePathException $e) {
			le($e);
		}
		try {
			$absPath = FileHelper::writeByFilePath($path, $str);
		} catch (InvalidFilePathException $e) {
			le($e);
		}
		return FileHelper::getStaticUrlForFile($absPath);
	}
	public static function saveFile(string $name, string $content): string{
		$absPath = FileHelper::writeByFilePath(self::getAbsolutePath($name), $content);
		return $absPath;
	}
	public static function saveIfLocal(string $s3Path, $contents): ?string{
		if(!EnvOverride::isLocal()){
			return null;
		}
		static::cloneIfNecessary();
		return FileHelper::writeByFilePath(static::getAbsPath() . DIRECTORY_SEPARATOR . $s3Path, $contents);
	}
	/**
	 * @param string $url
	 * @param $body
	 * @param string $type
	 * @param int $userId
	 * @return string
	 * @throws QMFileNotFoundException
	 * @throws InvalidFilePathException
	 */
	public static function saveResponse(string $url, $body, string $type, int $userId): string{
		$relativeFilePath = self::urlToPath($url, $type, $userId);
		$isHtml = QMStr::isHtml($body);
		//if($isHtml){$body = HtmlHelper::addAbsolutePathsToTags($body, $url);}
		if(!$isHtml && QMStr::isJson($body)){
			$body = QMStr::prettyPrintJson($body);
		}
		if(is_array($body) || is_object($body)){
			$body = QMStr::prettyJsonEncode($body);
		}
		$absPath = static::getAbsolutePath($relativeFilePath);
		try {
			//if(FileHelper::getContents($absPath)){return $absPath;}
		} catch (QMFileNotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
		}
		\App\Logging\ConsoleLog::info(__FUNCTION__ . " for " . \App\Utils\AppMode::getCurrentTestName());
		self::writeToFile($absPath, $body);
		if(!FileHelper::getContents($absPath)){
			le('!FileHelper::getContents($absPath)');
		}
		return $absPath;
	}
	/**
	 * @param string $testName
	 */
	private static function deleteTestDirectory(string $testName): void{
		FileHelper::deleteDir(self::getPathForTestRelativeToQmApiRepo($testName));
	}
	public static function saveCollectorData(string $type, array $data){
		self::writeJsonFile(self::getTestFolder() . "/" . $type, $data);
	}
	private static function getTestFolder(): string{
		$path = QMDebugBar::getCollectorFolderForCurrentTest();
		$path = QMStr::after('fixtures', $path, $path);
		return $path;
	}
	/**
	 * @param string $new
	 * @param string $folder
	 * @param string $fileName
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public static function upload(string $new, string $folder, string $fileName): void{
		\App\Logging\ConsoleLog::info("No old result to compare");
		/** @noinspection PhpUnhandledExceptionInspection */
		self::updateOrCreateByAPI($folder, $fileName, $new, $fileName);
		//self::fetchForceCheckoutAndPull();
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @param int $userId
	 * @return string
	 */
	public static function getResponse(string $url, string $type, int $userId): ?string{
		try {
			$relativeFilePath = self::urlToPath($url, $type, $userId);
		} catch (InvalidFilePathException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return null;
		}
		try {
			$str = parent::getContents($relativeFilePath);
		} catch (QMFileNotFoundException $e) {
			return null;
		}
		\App\Logging\ConsoleLog::info("Got local response at $relativeFilePath...");
		return $str;
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @param int $userId
	 * @return int|null
	 * @throws InvalidFilePathException
	 */
	public static function getResponseAge(string $url, string $type, int $userId): ?int{
		$relativeFilePath = self::urlToPath($url, $type, $userId);
		return FileHelper::getAgeOfFileInSeconds($relativeFilePath);
	}
	/**
	 * @param int $seconds
	 * @param string $url
	 * @param string $type
	 * @param int $userId
	 * @return bool
	 * @throws InvalidFilePathException
	 */
	public static function responseIsOlderThan(int $seconds, string $url, string $type, int $userId): bool{
		$age = static::getResponseAge($url, $type, $userId);
		return $age === null || $age > $seconds;
	}
	/**
	 * @param string $url
	 * @param string $type
	 * @param int $userId
	 * @return bool
	 */
	public static function shouldRequest(string $url, string $type, int $userId): bool{
		try {
			if(EnvOverride::isLocal() && static::responseIsOlderThan(86400, $url, $type, $userId)){
				return true;
			}
		} catch (InvalidFilePathException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return true;
		}
		return false;
	}
}
