<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\CodeGenerators\TVarDumper;
use App\Computers\ThisComputer;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\SecretException;
use App\Files\PHP\IndexDotPhp;
use App\Files\PHP\PhpClassFile;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\IonicHelper;
use App\Utils\SecretHelper;
use App\Utils\UrlHelper;
use Facade\Ignition\Support\ComposerClassMap;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use ReflectionException;
use RuntimeException;
use SimpleXMLElement;
use SplFileInfo;
use Storage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Throwable;
class FileHelper {
	const DEFAULT_FOLDER_PERMISSIONS = 755;
	const DEFAULT_FILE_PERMISSIONS = 664;
	const TYPE_PDF = 'pdf';
	public static function assertAbsPath(string $path): void{
		if(!str_starts_with($path, "/") || !str_contains($path, "\\:")){
			le("Please provide absolute path for $path");
		}
	}
	/**
	 * @param $path
	 * @param false $replace
	 */
	public static function createDirectoryIfNotExist($path, bool $replace = false){
		if(file_exists($path) && $replace){
			rmdir($path);
		}
		if(!file_exists($path)){
			mkdir($path, self::DEFAULT_FOLDER_PERMISSIONS, true);
		}
	}
	/**
	 * @param string $path
	 * @param string $fileName
	 * @param string $contents
	 * @return int
	 */
	public static function createFileByPathAndName(string $path, string $fileName, string $contents): int{
		if(!file_exists($path)){
			mkdir($path, self::DEFAULT_FOLDER_PERMISSIONS, true);
		}
		$path = $path.$fileName;
		$result = file_put_contents($path, $contents);
		if(!$result){
			throw new \LogicException("Failed to write to $path $fileName");
		}
		return $result;
	}
	/**
	 * @param string $path
	 * @param string $fileName
	 * @return bool
	 */
	public static function deleteFileByPathAndName(string $path, string $fileName): bool{
		if(file_exists($path.$fileName)){
			return unlink($path.$fileName);
		}
		return false;
	}
	public static function getOwnerName(string $path): string{
		return UntypedFile::find($path)->getOwnerName();
	}
	public static function getGroupId(string $path): string{
		return UntypedFile::find($path)->getGroup();
	}
	/**
	 * @throws FilePermissionsException
	 */
	public static function getGroupName(string $path): string{
		return UntypedFile::find($path)->getGroupName();
	}
	public static function getPermissions(string $path): int {
		$path = self::absPath($path);
		$permissions = fileperms($path);
        $permissions = decoct($permissions & 0777);
		//$permissions = substr($permissions, 3);
        return $permissions;
	}
	/**
	 * @param string $pathOrClass
	 * @return bool
	 */
	public static function isFilePath(string $pathOrClass): bool{
		return self::fileExists($pathOrClass);
	}
	/**
	 * @param string $relativePath
	 * @return bool
	 */
	public static function fileExists(string $relativePath): bool{
		$path = FileHelper::absPath($relativePath);
		$exists = file_exists($path);
		return $exists;
	}
	/**
	 * @param string|null $relativePath
	 * @return string
	 */
	public static function absPath(string $relativePath = null): string{
        //$path = \Illuminate\Support\Facades\Storage::disk('local')->path($relativePath);
        //return $path;
        //$parent = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        //$path = new SplFileInfo($parent.$relativePath);
        //return $path->getRealPath();
		if($relativePath && str_starts_with($relativePath, "~")){
			$home = ThisComputer::home();
			$path = str_replace("~", $home, $relativePath);
			if(DIRECTORY_SEPARATOR === "\\"){
				$path = str_replace("/", "\\", $path);
			}
			return $path;
		}
        if($relativePath && str_contains($relativePath, ":\\")){
            return $relativePath;
        }
		$project = self::projectRoot();
		if(!$relativePath || $relativePath === "."){
			return $project;
		}
		if(str_starts_with($relativePath, '/') || str_contains($relativePath, ':\\')){
			return $relativePath;
		}
		if(str_starts_with($relativePath, $project)){
			return $relativePath;
		}
		if(DIRECTORY_SEPARATOR === "\\"){
			$relativePath = str_replace("/", "\\", $relativePath);
		}
        $absolutePath = $project.DIRECTORY_SEPARATOR.$relativePath;
        return $absolutePath;
	}
	/**
	 * @return string
	 */
	public static function projectRoot(): string{
		$path = dirname(__DIR__, 2);
		if(empty($path)){
			le("empty(\$path)");
		}
		//$path = self::normalize_windows_path($path);
		return $path;
	}
	/**
	 * @param string $folderOrFilePath
	 * @param $data
	 * @param string|null $fileName
	 * @return string Absolute file path
	 */
	public static function writeJsonFile(string $folderOrFilePath, $data, string $fileName = null): string{
		$folderOrFilePath = self::absPath($folderOrFilePath);
		if(!$fileName){
			$fileName = FileHelper::getFileNameFromPath($folderOrFilePath);
			$folderPath = FileHelper::getFolderFromPath($folderOrFilePath);
		} else{
			$folderPath = $folderOrFilePath;
		}
		self::createDirectoryIfNecessary($folderPath);
		$json = QMStr::jsonEncodeIfNecessary($data);
		//$json_indented_by_2 = JsonHelper::alphabetizeJson($json_indented_by_2);
		if(!str_contains($fileName, '.json')){
			$fileName .= '.json';
		}
		return self::writeByDirectoryAndFilename($folderPath, $fileName, $json);
	}
	/**
	 * @param string $path
	 * @param bool $stripExtension
	 * @return string
	 */
	public static function getFileNameFromPath(string $path, bool $stripExtension = false): string{
        $name = QMStr::afterLast($path, DIRECTORY_SEPARATOR);
		if($stripExtension){
			$name = QMStr::before(".", $name);
		}
		return $name;
	}
	public static function getFolderFromPath(string $filePath): string{
        $filePath = str_replace("/", DIRECTORY_SEPARATOR, $filePath);
        $folderPath = QMStr::beforeLast(DIRECTORY_SEPARATOR, $filePath);
		return $folderPath;
	}
	/**
	 * @param string $directory
	 * @param string|null $owner
	 */
	public static function createDirectoryIfNecessary(string $directory, string $owner = null): void{
		if(self::isDir($directory)){
			if($owner){
				ThisComputer::ownDirectory($directory, $owner);
			}
			return;
		}
		if($owner && $owner !== $_SERVER["USER"]){
			ThisComputer::sudoStatic("mkdir -p $directory"); // The -p switch creates parents directories.
			ThisComputer::ownDirectory($directory, $owner);
		} else{
			try {
				if(!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)){
					throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
				}
			} catch (\Throwable $e){
				throw new RuntimeException("Could not create $directory because:\n\t".$e->getMessage());
			}
		}
	}
	public static function isDir(string $path): bool{
		return is_dir(self::absPath($path));
	}
	/**
	 * @param string $directory
	 * @param string $fileName
	 * @param string $content
	 * @return string
	 */
	public static function writeByDirectoryAndFilename(string $directory, string $fileName, string $content): string{
		$directory = self::absPath($directory);
		$filePath = $directory.DIRECTORY_SEPARATOR.$fileName;
		//$filePath = str_replace('//', DIRECTORY_SEPARATOR, $filePath);
		$absPath = self::writeByFilePath($filePath, $content);
		ConsoleLog::info("Writing $filePath...");
		return $absPath;
	}
	/**
	 * @param string $filePath
	 * @param mixed $content
	 * @param array $blackListedStrings
	 * @param string|null $owner
	 * @return string
	 */
	public static function writeByFilePath(string $filePath, $content, array $blackListedStrings = [],
	                                       string $owner = null): string{
		if(!is_string($content)){
			$content = json_encode($content);
		}
		QMStr::assertNotEmptyOrNull(trim($content), $filePath);
		try {
			QMStr::assertDoesNotContain($content, $blackListedStrings, $filePath);
		} catch (InvalidStringException $e) {
			le($e);
		}
		$absPath = self::absPath($filePath);
		try {
			self::validateFilePath($absPath);
		} catch (InvalidFilePathException $e) {
			le($e);
		}
		$directory = self::getDirectoryFromFilePath($absPath);
		self::createDirectoryIfNecessary($directory, $owner);
		if(self::existsAndHasNotChanged($absPath, $content)){
			return $absPath;
		}
		self::outputViewPathIfLocal($absPath);
		//self::makeSureWeCanWriteToPath($absPath);
		if(QMStr::getSizeOfStringInKB($content) > 1000){
			QMStr::logSizeOfString($absPath, $content);
		}
		if($owner && $owner !== $_SERVER["USER"]){
			ThisComputer::ownDirectory($directory, $_SERVER["USER"], static::getDefaultGroupName());
		}
		try {
			$fp = fopen($absPath, 'wb');
		} catch (\Throwable $e) {
			//chmod($filename, $permissions)
			$fp = fopen($absPath, 'wb');
		}
		fwrite($fp, $content);
		fclose($fp);
		if($owner && $owner !== ThisComputer::user()){
			FileHelper::setOwner($absPath, $owner);
		}
		if(AppMode::isWindows()){
			return $absPath;
		}
		try {
			FileHelper::setGroup($absPath, static::getDefaultGroupName());
			FileHelper::setPermissions($absPath, self::DEFAULT_FILE_PERMISSIONS);
		} catch (\Throwable $e){
		    ConsoleLog::info(__METHOD__.": ".$e->getMessage());
		    //le($e);
		}
		return $absPath;
	}
	/**
	 * @param string $path
	 * @throws InvalidFilePathException
	 */
	public static function validateFilePath(string $path){
		$max = InvalidFilePathException::MAXIMUM_FILE_NAME_LENGTH;
		if(strlen($path) > $max){
			throw new InvalidFilePathException("Path too long!  Greater than $max characters: $path");
		}
		if(stripos($path, "..") !== false){
			le("Should path contain 2 dots? $path");
		}
		QMStr::exceptionIfStringContainsLineBreaks($path);
		QMStr::exceptionIfStringContainsUrl($path);
	}
	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function getDirectoryFromFilePath(string $filePath): string{
        if(AppMode::isWindows()){
            $filePath = str_replace("/", DIRECTORY_SEPARATOR, $filePath);
        }
        //$filePath = self::normalize_windows_path($filePath);
		if(strpos($filePath, ".") !== false){
			$val = QMStr::beforeLast(DIRECTORY_SEPARATOR, $filePath);
		} else{ // Is folder
			$arr = explode(DIRECTORY_SEPARATOR, $filePath);
			$val = end($arr);
		}
		if(empty($val)){
			le("no val from $filePath");
		}
		return $val;
	}
	/**
	 * @param string $filePath
	 * @param $content
	 * @return bool
	 */
	protected static function existsAndHasNotChanged(string $filePath, $content): bool{
		$existsAndHasNotChanged = false;
		try {
			$existing = self::getContents($filePath);
			if($existing === $content){
				ConsoleLog::debug("$filePath has not changed! ");
				$existsAndHasNotChanged = true;
			}
		} catch (QMFileNotFoundException $e) {
			ConsoleLog::debug(__METHOD__.": ".$e->getMessage());
		}
		return $existsAndHasNotChanged;
	}
	/**
	 * @param string $filePath
	 * @return string|null
	 * @throws QMFileNotFoundException
	 */
	public static function getContents(string $filePath): ?string{
		$filePath = self::absPath($filePath);
		if(!file_exists($filePath)){
			throw new QMFileNotFoundException($filePath);
		}
		try {
			$existing = file_get_contents($filePath);
		} catch (Throwable $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			$existing = null;
		}
		return $existing;
	}
	/**
	 * @param string $filePath
	 */
	public static function outputViewPathIfLocal(string $filePath): void{
		if(EnvOverride::isLocal()){
			$name = self::getFileNameFromPath($filePath);
			if(stripos($filePath, ".json") === false){
				$links = "\n".self::absPath($filePath);
				if(stripos($filePath, '.html') !== false){
					$links .= "\n or ".FileHelper::getStaticUrlForFile($filePath);
				}
				// Spams console when saving json test responses
				ConsoleLog::info("View $name at:\n => $links");
			}
		}
	}
	public static function getStaticUrlForFile(string $path): string{
		$url = UrlHelper::getLocalUrl("static", ['path' => $path]);
		$url = str_replace('static//', 'static/', $url);
		return $url;
	}
	public static function setFilePermissions(string $filePath, string $owner, string $perms,
	                                          string $group = null){
		ThisComputer::ownFile($filePath, $owner, $group);
		ThisComputer::chmod($filePath, $perms);
	}
	/**
	 * @param array $objects
	 * @param string|null $objectType
	 */
	public static function downloadCsv(array $objects, string $objectType = null){
		$header = [];
		foreach($objects[0] as $key => $value){
			$header[] = QMStr::camelToTitle($key);
		}
		if(!$objectType){
			$objectType = get_class($objects[0]);
		}
		$filename = QMStr::camelToTitle($objectType)." CSV Export.csv";
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'";');
		$output = fopen('php://output', 'wb');
		fputcsv($output, $header);
		foreach($objects as $object){
			$row = [];
			foreach($object as $value){
				$row[] = QMStr::convertToStringIfNecessary($value);
			}
			fputcsv($output, $row);
		}
	}
	public static function deleteFileOrFolder(string $path, string $reason = null){
		try {
			self::deleteFile($path, $reason);
		} catch (Throwable $e) {
			ConsoleLog::debug(__METHOD__.": ".$e->getMessage());
		}
		try {
			self::deleteDir($path, $reason);
		} catch (Throwable $e) {
			ConsoleLog::debug(__METHOD__.": ".$e->getMessage());
		}
	}
	/**
	 * @param string $absoluteOrRelativePath
	 * @param string|null $reason Explicitly pass null if you don't want an error logged
	 * @return bool
	 */
	public static function deleteFile(string $absoluteOrRelativePath, ?string $reason): bool{
		$absolutePath = self::absPath($absoluteOrRelativePath);
		if(!file_exists($absolutePath)){
			ConsoleLog::info("$absolutePath does not exist");
			return false;
		}
		if($reason){
			QMLog::error("Deleting $absolutePath because:\n\t$reason");
		} else{
			ConsoleLog::info("Deleting $absolutePath...");
		}
		try {
			return unlink($absolutePath);
		} catch (Throwable $e) {
			if(stripos($e->getMessage(), "No such file or directory") === false){
				le($e);
			}
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return false;
		}
	}
	/**
	 * @param string $dirPath
	 * @param string $reason
	 */
	public static function deleteDir(string $dirPath, string $reason = "REASON NOT PROVIDED!"){
		QMLog::error("Deleting $dirPath because: $reason");
		$dirPath = self::absPath($dirPath);
		if(!file_exists($dirPath)){
			return;
		}
		if(!is_dir($dirPath)){
			throw new InvalidArgumentException("$dirPath must be a directory");
		}
		if($dirPath[strlen($dirPath) - 1] !== DIRECTORY_SEPARATOR){
			$dirPath .= DIRECTORY_SEPARATOR;
		}
		$files = glob($dirPath.'*', GLOB_MARK);
		foreach($files as $file){
			if(is_dir($file)){
				self::deleteDir($file);
			} else{
				unlink($file);
			}
		}
		rmdir($dirPath);
	}
	/**
	 * @param string $dir
	 * @param string|null $excludeLike
	 * @return int
	 */
	public static function getLastModifiedTimeInFolder(string $dir, string $excludeLike = null): int{
		$top = self::getLastModifiedTimeAndPathInFolder($dir, $excludeLike);
		$time = key($top);
		$file = reset($top);
		ConsoleLog::debug($file." modified ".TimeHelper::timeSinceHumanString($time));
		if(!$time){
			ConsoleLog::warning("No last modified time in $dir");
            return 1;
		}
		return $time;
	}
	/**
	 * @param string $dir
	 * @param string|null $excludeLike
	 * @return array
	 */
	public static function getLastModifiedTimeAndPathInFolder(string $dir, string $excludeLike = null): array{
		$dir = FileHelper::absPath($dir);
		$mods = [];
		foreach(glob($dir.'/*') as $f){
			if($excludeLike && strpos($f, $excludeLike) !== false){
				continue;
			}
			$mods[filemtime($f)] = $f;
		}
		krsort($mods);
		$top = array_slice($mods, 0, 1, true);
		return $top;
	}
	/**
	 * @param string $dir
	 * @return int
	 */
	public static function getLastModifiedTimeInFolderFromGit(string $dir): int{
		$newest = FileFinder::getNewestFileInFolder($dir);
		$git = ThisComputer::exec("git log -- $newest");
		$dateFromGit = QMStr::between($git, "Date:   ", "\n");
		$timeFromGit = strtotime($dateFromGit);
		return $timeFromGit;
	}
	/**
	 * @param string $filePath
	 * @param bool $associative
	 * @return mixed
	 * @throws QMFileNotFoundException
	 */
	public static function readJsonFile(string $filePath, bool $associative = false){
		$res = self::getDecodedJsonFile($filePath, false, $associative);
		return $res;
	}
	/**
	 * @param string $filePath
	 * @param bool $outputLastModified
	 * @param bool $associative
	 * @return mixed
	 * @throws QMFileNotFoundException
	 */
	public static function getDecodedJsonFile(string $filePath, bool $outputLastModified = false,
	                                          bool   $associative = false){
		$filePath = self::absPath($filePath);
		if(strpos($filePath, '.json') === false){
			$filePath .= ".json";
		}
		ConsoleLog::debug("Reading $filePath");
		$exists = file_exists($filePath);
		if(!$exists){
			throw new QMFileNotFoundException("$filePath not found!");
		}
		try {
			$jsonString = file_get_contents($filePath);
			if(!$jsonString){
				return null;
			}
		} catch (Throwable $e) {
			if(stripos($e->getMessage(), 'No such file') !== false){
				throw new QMFileNotFoundException("$filePath not found!");
			}
			/** @var LogicException $e */
			throw $e;
		}
		if($outputLastModified){
			$time = filemtime($filePath);
			QMLog::info($filePath." was modified ".TimeHelper::timeSinceHumanString($time));
		}
		$decoded = json_decode($jsonString, $associative);
		if(!$decoded){
			le("could not decode $filePath: because ".json_last_error_msg()."\n\t Here's the string $jsonString");
		}
		return $decoded;
	}
	/**
	 * @param string $filePath
	 * @param bool $outputLastModified
	 * @return array
	 * @throws Throwable
	 */
	public static function readYamlFile(string $filePath, bool $outputLastModified): array{
		$filePath = self::absPath($filePath);
		ConsoleLog::debug("Reading $filePath");
		try {
			$str = file_get_contents($filePath);
		} catch (Throwable $e) {
			if(stripos($e->getMessage(), 'No such file') !== false){
				throw new QMFileNotFoundException("$filePath not found!");
			}
			throw $e;
		}
		if($outputLastModified){
			$time = filemtime($filePath);
			QMLog::info($filePath." was modified ".TimeHelper::timeSinceHumanString($time));
		}
		$decoded = Yaml::parse($str);
		return $decoded;
	}
	/**
	 * @param string $cmd
	 */
	public static function executeCommand(string $cmd){
		ThisComputer::exec($cmd);
	}
	/**
	 * @param string $path
	 */
	public static function deleteDirectoryOrFileIfNecessary(string $path){
		try {
			self::deleteDir($path);
		} /** @noinspection BadExceptionsProcessingInspection */ catch (Throwable $exception) {
		}
		try {
			self::deleteFile($path, __METHOD__);
		} /** @noinspection BadExceptionsProcessingInspection */ catch (Throwable $exception) {
		}
	}
	/**
	 * @param string $html
	 * @param string $filename
	 * @param string $dir
	 * @return string
	 * @throws MpdfException
	 */
	public static function HTMLtoPDF(string $html, string $filename, string $dir): string{
		$start = microtime(true);
		$dir = self::absPath($dir);
		self::createDirectoryIfNecessary($dir);
		$path = $dir.DIRECTORY_SEPARATOR.$filename.'.pdf';
		//$html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en');
		//$html2pdf->writeHTML($html);
		//$html2pdf->output($path, 'F');
		$mpdf = new Mpdf();
		$mpdf->WriteHTML($html);
		$mpdf->Output($path, Destination::FILE);
		$duration = microtime(true) - $start;
		QMLog::info("Generated $path in $duration seconds");
		return $path;
	}
	/**
	 * @param \Mpdf\Mpdf $mPDF
	 * @param string $filename
	 * @param string $dir
	 * @return string
	 */
	public static function MPDFtoFILE(\Mpdf\Mpdf $mPDF, string $filename, string $dir): string{
		if(!str_contains($filename, '.pdf')){
			le("Filename $filename should contain .pdf extension!");
		}
		$start = microtime(true);
		$dir = self::absPath($dir);
		self::createDirectoryIfNecessary($dir);
		$localAbsolutePath = $dir.DIRECTORY_SEPARATOR.$filename;
		try {
			$mPDF->Output($localAbsolutePath, Destination::FILE);
		} catch (MpdfException $e) {
			le($e);
		}
		$duration = microtime(true) - $start;
		QMLog::info("Generated $localAbsolutePath in $duration seconds");
		self::outputViewPathIfLocal($localAbsolutePath);
		return $localAbsolutePath;
	}
	/**
	 * @param string $html
	 * @param string $filename
	 * @param string $dir
	 */
	public static function toWord(string $html, string $filename, string $dir){
		$start = microtime(true);
		$dir = self::absPath($dir);
		self::createDirectoryIfNecessary($dir);
		$path = $dir.DIRECTORY_SEPARATOR.$filename.'.docx';
		$phpWord = new PhpWord();
		$phpWord->addParagraphStyle('Heading2', ['alignment' => 'center']);
		/* Note: any element you append to a document must reside inside a Section. */
		// Adding an empty Section to the document...
		$section = $phpWord->addSection();
		Html::addHtml($section, $html, false, false);
		// Saving the document as OOXML file...
		try {
			$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
		} catch (Exception $e) {
			le($e);
		}
		$objWriter->save($path);
		$duration = microtime(true) - $start;
		QMLog::info("Generated $path in $duration seconds");
	}
	public static function getAgeOfFileInSeconds(string $path): ?int{
		return self::getSecondsSinceLastModified($path);
	}
	/**
	 * @param string $path
	 * @return int|null
	 */
	public static function getSecondsSinceLastModified(string $path): ?int{
		if(!file_exists($path)){
			return null;
		}
		$lastModified = filemtime($path);
		ConsoleLog::debug("$path last modified ".TimeHelper::timeSinceHumanString($lastModified).". ");
		return time() - $lastModified;
	}
	/**
	 * @param string $path
	 * @param int $maxAgeInSeconds
	 * @return bool
	 */
	public static function existsAndNotExpired(string $path, int $maxAgeInSeconds = 0): bool{
		$age = self::getSecondsSinceLastModified($path);
		if($age === null){
			ConsoleLog::info("Local copy of $path not found");
			return false;
		}
		ConsoleLog::info("Local $path last modified ".TimeHelper::convertSecondsToHumanString($age));
		if(!$maxAgeInSeconds){
			return true;
		}
		$notExpired = $age < $maxAgeInSeconds;
		if($notExpired){
			ConsoleLog::info("$path not expired");
		} else{
			ConsoleLog::info("$path is expired");
		}
		return $notExpired;
	}
	/**
	 * @return FilesystemAdapter
	 */
	public static function tmp(): FilesystemAdapter{
		return Storage::disk('tmp');
	}
	/**
	 * @param string $relativePath
	 * @return int
	 */
	public static function getFileSizeInKb(string $relativePath): int{
		return round(self::repo()->size($relativePath) / 1000);
	}
	// Function to remove folders and files
	/**
	 * @return FilesystemAdapter
	 */
	public static function repo(): FilesystemAdapter{
		return Storage::disk('repo');
	}
	/**
	 * @param string $zipPath
	 * @param string $outputPath
	 * @return string
	 */
	public static function unzipFile(string $zipPath, string $outputPath): string{
		return ZipHelper::unzip($zipPath, $outputPath);
	}
	/**
	 * @param string $folderPath
	 * @param string $fileName
	 * @param string $url
	 * @return bool|int
	 */
	public static function download(string $folderPath, string $fileName, string $url){
		$filePath = self::absPath($folderPath.DIRECTORY_SEPARATOR.$fileName);
		$data = fopen($url, 'r');
		self::createDirectoryIfNecessary($folderPath);
		return file_put_contents($filePath, $data);
	}
	/**
	 * @param string $source
	 * @param string $destination
	 */
	public static function moveFiles(string $source, string $destination){
		$source = self::absPath($source);
		$destination = self::absPath($destination);
		// Get array of all source files
		$files = scandir($source);
		// Cycle through all source files
		$delete = [];
		foreach($files as $file){
			if(in_array($file, [
				".",
				"..",
			])) continue;
			// If we copied this successfully, mark it for deletion
			if(copy($source.DIRECTORY_SEPARATOR.$file, $destination.DIRECTORY_SEPARATOR.$file)){
				$delete[] = $source.DIRECTORY_SEPARATOR.$file;
			}
		}
		// Delete all successfully-copied files
		foreach($delete as $file){
			unlink($file);
		}
	}
	/**
	 * @param string $dir
	 */
	public static function deleteDirectorRecursively(string $dir){
		if(is_dir($dir)){
			$files = scandir($dir);
			foreach($files as $file){
				if($file !== "." && $file !== ".."){
					self::deleteDirectorRecursively("$dir/$file");
				}
			}
			rmdir($dir);
		} elseif(file_exists($dir)){
			unlink($dir);
		}
	}
	/**
	 * @param string $srcFolder
	 * @param string $dstFolder
	 * @param string|null $includeLike
	 * @param array $excludeLike
	 */
	public static function copyFiles(string $srcFolder, string $dstFolder, string $includeLike = null,
	                                 array  $excludeLike = []){
		$src = self::absPath($srcFolder);
		self::createDirectoryIfNecessary($dstFolder);
		$files = scandir($src);
		foreach($files as $file){
			if($file !== "." && $file !== ".."){
				if($includeLike && strpos($file, $includeLike) === false){
					continue;
				}
				if($excludeLike && QMStr::containsAny($file, $excludeLike)){
					continue;
				}
				$srcPath = $srcFolder.DIRECTORY_SEPARATOR.$file;
				$destPath = $dstFolder.DIRECTORY_SEPARATOR.$file;
				copy($srcPath, $destPath);
			}
		}
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function guessMimeContentTypeBasedOnFileContents(string $path): string{
		$mimeContentType = mime_content_type($path);
		return $mimeContentType;
	}
	/**
	 * @param string $s3Path
	 */
	public static function validateFileExtension(string $s3Path): void{
		$pathInfo = pathinfo($s3Path);
		$ext = $pathInfo['extension'] ?? null;
		if(!$ext){
			$whiteList = ['gitkeep'];
			foreach($whiteList as $item){
				if(stripos($s3Path, $item) !== false){
					return;
				}
			}
			le("Please add file extension to $s3Path");
		}
		$double = ".$ext.$ext";
		if(strpos($s3Path, $double)){
			le("Invalid double file extension $double in path $s3Path");
		}
	}
	/**
	 * @param string $dir
	 * @param string $search
	 * @param string $replace
	 * @param string|null $extension
	 */
	public static function replaceStringInAllFilesInFolder(string $dir, string $search, string $replace,
	                                                       string $extension = null){
		self::replaceTextInAllFilesRecursively($dir, $search, $replace, $extension);
	}
	/**
	 * @param string $dir
	 * @param string $search
	 * @param string $replace
	 * @param string|null $extension
	 */
	public static function replaceTextInAllFilesRecursively(string $dir, string $search, string $replace,
	                                                        string $extension = null, string $filenameLike = null,
                                                            string $pathNotLike = null){
		$objects = FileFinder::listFilesRecursively($dir, $filenameLike, $pathNotLike, $extension);
		foreach($objects as $object){
			$fileName = $object->getFileName(); // Get the file name
			$folderPath = $object->getPath();
			$filePath = $folderPath.DIRECTORY_SEPARATOR.$fileName;
			if($fileName === "." || $fileName === ".."){
				continue; // It's a directory
			}
			//            if (stripos($filePath, '.') === false) {
			//                continue;  // It's a directory
			//            }
			if($extension && stripos($fileName, '.'.$extension) === false){
				continue;
			}
			self::replaceStringInFile($filePath, $search, $replace);
		}
		//        $cmd = "cd $dir && grep -rl $find . |xargs sed -i -e 's/$find/$replace/'";
		//        \App\Logging\ConsoleLog::info($cmd);
		//        $output_including_status = shell_exec("$cmd 2>&1; echo $?");
		//        \App\Logging\ConsoleLog::info($output_including_status);
	}
	/**
	 * @param string $filePath
	 * @param string $search
	 * @param string $replace
	 * @return bool Returns false if no changes were necessary
	 */
	public static function replaceStringInFile(string $filePath, string $search, string $replace): bool{
		$filePath = self::absPath($filePath);
		$before = file_get_contents($filePath);
		$after = str_replace($search, $replace, $before);
		if($before === $after){
			ConsoleLog::debug("No $search's in $filePath");
			return false;
		}
		\App\Logging\ConsoleLog::info("Replaced $search with $replace in $filePath");
		file_put_contents($filePath, $after);
		return true;
	}
	/**
	 * @param string $ext
	 * @param array|Collection $filenames
	 * @return array
	 */
	public static function filterByExtension(string $ext, $filenames): array{
		if(!Str::contains($ext, ".")){
			$ext = ".$ext";
		}
		$filenames = Arr::where($filenames, static function($filename) use ($ext){
			return Str::endsWith($filename, $ext);
		});
		return $filenames;
	}
	public static function replaceClassInFiles(string $oldClass, string $newClass){
		FileHelper::replaceTextInAllFilesRecursively('app', $oldClass, $newClass, 'php');
		FileHelper::replaceTextInAllFilesRecursively('Api', $oldClass, $newClass, 'php');
		FileHelper::replaceTextInAllFilesRecursively('tests', $oldClass, $newClass, 'php');
	}
	/**
	 * @param string $path_to_file
	 * @return mixed|string
	 */
	public static function get_class_from_file(string $path_to_file): string{
		$namespace = self::getNameSpace($path_to_file);
		$contents = file_get_contents($path_to_file);
		$short = QMStr::between($contents, "class ", " ");
		return "\\$namespace\\$short";  // 3X faster
	}
	/**
	 * @param string $path_to_file
	 * @return string
	 */
	public static function getNameSpace(string $path_to_file): string{
		$path_to_file = self::absPath($path_to_file);
		$contents = file_get_contents($path_to_file);
		$namespace = QMStr::between($contents, "namespace ", ";");
		return $namespace;
	}
	/**
	 * @param string $filePath
	 * @return string
	 */
	public static function getExtension(string $filePath): ?string{
		$path_info = pathinfo($filePath);
		return $path_info['extension'] ?? null;
	}
	/**
	 * Checks if a folder exist and return canonicalized absolute pathname (sort version)
	 * @param string $folder the path being checked.
	 * @return string returns the canonicalized absolute pathname on success otherwise FALSE is returned
	 */
	public static function folder_exist(string $folder): ?string{
		if(isset(FileFinder::$folderExists[$folder])){
			return FileFinder::$folderExists[$folder];
		}
		$folder = self::absPath($folder);
		$isDir = is_dir($folder);
		if(!$isDir){
			\App\Logging\ConsoleLog::info("$folder is not a directory!");
			return null;
		}
		return FileFinder::$folderExists[$folder] = ($folder != false) ? $folder : null;
	}
	public static function deleteIfNotDirectory(string $path){
		if(!self::isDir($path) && self::fileExists($path)){
			self::deleteFile($path, __METHOD__);
		}
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @param string $toAdd
	 */
	public static function insertLineAfter(string $file, string $find, string $toAdd){
		self::removeNewLineAfterClassNameLine($file);
		$lines = self::getFileLineStrings($file);
		$toWrite = [];
		$added = false;
		foreach($lines as $line){
			$toWrite[] = $line;
			if(!$added && strpos($line, $find) !== false){
				$toWrite[] = $toAdd;
				$added = true;
			}
		}
		self::writeByFilePath($file, implode("\n", $toWrite));
	}
	/**
	 * @param string $file
	 * @return void
	 */
	protected static function removeNewLineAfterClassNameLine(string $file): void{
		try {
			$contents = self::getContents($file);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		$contents = str_replace("
{
	", "{
	", $contents); // Fixes trait adding
		self::writeByFilePath($file, $contents);
	}
	/**
	 * @param string $file
	 * @return string[]
	 */
	public static function getFileLineStrings(string $file): array{
		try {
			$contents = self::getContents($file);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return explode("\n", $contents);
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @param string $newLine
	 * @return int|null
	 */
	public static function insertLineIntoFileBelow(string $file, string $find, string $newLine): ?int{
		try {
			$file_content = self::getContents($file);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		$lines = explode("\n", $file_content);
		foreach($lines as $num => $line){
			$pos = strpos($line, $find);
			$before[] = $line;
			unset($lines[$num]);
			if($pos !== false){
				$all = array_merge($before, [$newLine], $lines);
				$str = implode("\n", $all);
				FileHelper::writeByFilePath($file, $str);
			}
		}
		return null;
	}
	/**
	 * @param string $path
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @noinspection PhpUnused
	 */
	public static function deleteFilesContaining(string $path, string $needle, bool $recursive,
	                                             string $filenameLike = null){
		$files = FileFinder::listFilesContaining($path, $needle, $recursive, $filenameLike);
		foreach($files as $file){
			self::deleteFile($file, __METHOD__);
		}
	}
	public static function saveToStatic(string $path, string $content){
		if(!EnvOverride::isLocal()){
			le("Can only save $path locally");
		}
		$path = self::addStaticToPathIfNecessary($path);
		self::validateStaticFiles($content, $path);
		FileHelper::writeByFilePath($path, $content);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function addStaticToPathIfNecessary(string $path): string{
		if(strpos($path, "public/") === 0){
			$path = Str::replaceFirst("public/", "static/", $path);
		}
		if(strpos($path, "static/") !== 0){
			$path = "static/$path";
		}
		return $path;
	}
	/**
	 * @param string $content
	 * @param string $path
	 */
	protected static function validateStaticFiles(string $content, string $path): void{
		try {
			QMStr::assertDoesNotContain($content, [
				"local.quantimo.do",
			],                          $content);
		} catch (InvalidStringException $e) {
			le($e);
		}
		try {
			SecretHelper::assertDoesNotContainSecrets($content, $path);
		} catch (SecretException $e) {
			le($e);
		}
	}
	public static function writeToPublic(string $path, string $content){
		self::saveToPublic($path, $content);
	}
	public static function saveToPublic(string $path, string $content){
		if(!EnvOverride::isLocal()){
			le("Can only save $path locally");
		}
		$path = self::addPublicToPathIfNecessary($path);
		$content = HtmlHelper::relativizePaths($content);
		$content = str_replace(IonicHelper::IONIC_BASE_URL, IonicHelper::CC_BASE_URL, $content);
		self::validateStaticFiles($content, $path);
		FileHelper::writeByFilePath($path, $content);
	}
	/**
	 * @param string $path
	 * @return string
	 */
	public static function addPublicToPathIfNecessary(string $path): string{
		if(strpos($path, "public/") !== 0){
			$path = "public/$path";
		}
		return $path;
	}
	/**
	 * @param $path
	 */
	public static function fixNamespaces($path){
		$files = FileFinder::listFilesRecursively($path);
		foreach($files as $file){
			try {
				$currentLine = self::getLineStartingWith($file->getRealPath(), "namespace ");
			} catch (Throwable $e) {
				ConsoleLog::info(__METHOD__.": ".$e->getMessage());
				continue;
			}
			try {
				$contents = self::getContents($file->getRealPath());
			} catch (QMFileNotFoundException $e) {
				le($e);
			}
			$filePath = $file->getRealPath();
			$namespace = self::filePathToNamespace($filePath);
			$newLine = "namespace $namespace;";
			if($currentLine === $newLine){
				continue;
			}
			$contents = str_replace($currentLine, $newLine, $contents);
			self::writeByFilePath($file->getRealPath(), $contents);
		}
	}
	/**
	 * @param $file
	 * @param string $needle
	 * @return string
	 * @throws QMFileNotFoundException
	 * @noinspection PhpSameParameterValueInspection
	 */
	private static function getLineStartingWith($file, string $needle): string{
		$contents = self::getContents($file);
		$exploded = explode("\n", $contents);
		foreach($exploded as $line){
			if(str_starts_with($line, $needle)){
				return $line;
			}
		}
		le("$needle not found in $file");
	}
	public static function filePathToNamespace(string $filePath): string{
		$rel = self::getRelativePath($filePath);
		$folder = self::getDirectoryFromFilePath($rel);
		return self::folderToNamespace($folder);
	}
	public static function getRelativePath(string $abs): string{
		$project = self::projectRoot();
		//$project = self::fromWindowsPath($project);
		if($abs === $project){
			return ".";
		}
		if(stripos($abs, $project) === false){
			$rel = $abs;
			ConsoleLog::debug("Cannot get relative path for absolute path: $abs.  Maybe it's already relative");
			if(DIRECTORY_SEPARATOR === "\\"){
				$rel = str_replace("\\", "/", $rel);
			}
			return $rel;
		}
		$abs = str_replace('/', DIRECTORY_SEPARATOR, $abs);
		$rel = str_replace($project.DIRECTORY_SEPARATOR, "", $abs);
		if(DIRECTORY_SEPARATOR === "\\"){
			$rel = str_replace("\\", "/", $rel);
		}
		return $rel;
	}
	public static function fromWindowsPath(string $path): string{
		if(!AppMode::isWindows()){
			return $path;
		}
		$path = str_replace(base_path()."\\", "", $path);
		return QMStr::replaceBackslashesWithForwardSlashes($path);
	}
	public static function folderToNamespace(string $relativeOrAbsPathToFolder): string{
		$relativePath = self::getRelativePath($relativeOrAbsPathToFolder);
		if(str_starts_with($relativePath, 'tests/')){
			$namespace = str_replace("tests/", "Tests\\", $relativePath);
		} elseif(stripos($relativePath, 'app/') === 0){
			$namespace = str_replace('app/', "App\\", $relativePath);
		} elseif($relativePath === 'tests'){
			return "Tests";
		} else{
			le("Could not determine namespace for folder: $relativePath");
		}
		return str_replace('/', '\\', $namespace);
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function getFolderForClass(string $class): string{
		return dirname(self::getFilePathToClass($class));
	}
	/**
	 * @param string $class
	 * @return string
	 */
	public static function getFilePathToClass(string $class): string{
		try {
			$a = new \ReflectionClass($class);
			return $a->getFileName();
		} catch (ReflectionException $e) {
			$newShortName = QMStr::toShortClassName($class);
			$newFolder = FileHelper::classToFolderPath($class);
			return $newFolder."/$newShortName.php";
		}
	}
	public static function classToFolderPath(string $class, bool $abs = false): string{
		$namespace = self::classToNamespace($class);
		$path = self::namespaceToFolder($namespace);
		if($abs){
			return self::absPath($path);
		}
		return $path;
	}
	public static function classToNamespace(string $class): string{
		$parts = explode("\\", $class);
		array_pop($parts);
		$namespace = implode("\\", $parts);
		return $namespace;
	}
	public static function namespaceToFolder(string $namespace): string{
		if(str_starts_with($namespace, 'Tests\\') || str_starts_with($namespace, '\\Tests\\')){
			$path = 'tests/'.QMStr::after('Tests\\', $namespace);
		} elseif(str_starts_with($namespace, 'App\\') || str_starts_with($namespace, '\\App\\')){
			$path = 'app/'.QMStr::after('App\\', $namespace);
		} elseif($namespace === "Tests"){
			return 'tests';
		} else{
			le("Could not determine folder for namespace: $namespace");
		}
		$rel = str_replace('\\', DIRECTORY_SEPARATOR, $path);
		return $rel;
	}
	/**
	 * @param string $dirPath
	 * @param bool $recursive
	 * @return array
	 * @throws QMFileNotFoundException
	 */
	public static function getFileContentsInFolder(string $dirPath, bool $recursive = false): array{
		$files = FileFinder::listFiles($dirPath, $recursive);
		$contents = [];
		foreach($files as $file){
			$contents[$file->getRealPath()] = self::getContents($file);
		}
		return $contents;
	}
	/**
	 * @param string $namespace
	 * @param bool $recursive
	 * @return string[]
	 */
	public static function getClassesInNamespace(string $namespace, bool $recursive = true): array{
		$path = self::namespaceToFolder($namespace);
		return self::getClassesInFolder($path, $recursive);
	}
	/**
	 * @param string $folder
	 * @param bool $recursive
	 * @return string[]
	 */
	public static function getClassesInFolder(string $folder, bool $recursive = true): array{
		$folder = self::absPath($folder);
		$finder = new Finder();
		try {
			$finder->in($folder)->files();
		} catch (DirectoryNotFoundException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return [];
		}
		if(!$recursive){
			$finder->depth('== 0');
		}
		$classes = [];
		foreach($finder as $file){
			if(str_contains($file->getRealPath(), ".bak")){
				continue;
			}
			$classes[] = self::pathToClass($file->getRealPath());
		}
		sort($classes); // Maintain consistency in tests
		return $classes;
	}
	public static function pathToClass(string $relativePath): string{
		$relativePath = self::fromWindowsPath($relativePath);
		$namespace = self::filePathToNamespace($relativePath);
		return $namespace.'\\'.self::getFileNameWithoutExtension($relativePath, '/');
	}
	public static function getFileNameWithoutExtension(string $filePath, string $separator): string{
		$arr = explode($separator ?? DIRECTORY_SEPARATOR, $filePath);
		$file = end($arr);
		return explode('.', $file)[0];
	}
	public static function pathToShortClass(string $filePath): string{
		return QMStr::toShortClassName(static::pathToClass($filePath));
	}
	public static function filePathToClassName(string $filePath): string{
		return static::pathToClass($filePath);
	}
	public static function getLineOfCode(string $file, int $line): string{
		try {
			$contents = self::getContents($file);
			$exploded = explode("\n", $contents);
			return $exploded[$line - 1];
		} catch (QMFileNotFoundException $e) {
			/** @var LogicException $e */
			throw $e;
		}
	}
	public static function saveIfLocal(string $filepath, string $content){
		if(EnvOverride::isLocal()){
			self::writeByFilePath($filepath, $content);
		}
	}
	public static function toRelativePath(string $path): string{
		$root = self::projectRoot();
		$withSlash = $root.DIRECTORY_SEPARATOR;
		$str = str_replace($withSlash, '', $path);
		if(DIRECTORY_SEPARATOR === '\\'){$str = str_replace('\\', '/', $str);}
		return $str;
	}
	public static function getHtmlFilesContaining(string $dir, string $needle, bool $recursive): array{
		$files = FileFinder::listFilesContaining($dir, $needle, $recursive, ".html");
		return $files;
	}
	/**
	 * @param string $traitClass
	 * @param $fileModelOrClass
	 * @throws QMFileNotFoundException
	 */
	public static function addTrait(string $traitClass, $fileModelOrClass): void{
		$file = PhpClassFile::find($fileModelOrClass);
		$file->cleanup();
		$shortTrait = QMStr::toShortClassName($traitClass);
		if($file->contains($shortTrait)){
			return;
		} // Already added
		$file->addTrait($traitClass);
		$file->save();
	}
	/**
	 * @param string $name
	 * @param $fileModelOrClass
	 * @param null $value
	 */
	public static function addConstant(string $name, $fileModelOrClass, $value = null): void{
		$file = PhpClassFile::find($fileModelOrClass);
		$file->addConstant($name, $value);
		$file->save();
	}
	/**
	 * @param string $usedClass
	 * @param $fileModelOrClass
	 */
	public static function addUseImportStatement(string $usedClass, $fileModelOrClass){
		$usedClass = QMStr::removeIfFirstCharacter("\\", $usedClass);
		$file = PhpClassFile::find($fileModelOrClass);
		$file->addUse($usedClass);
	}
	/**
	 * @param string $accessLevel
	 * @param string $name
	 * @param $defaultValue
	 * @param $fileModelOrClass
	 * @throws QMFileNotFoundException
	 */
	public static function addProperty(string $accessLevel, string $name, $defaultValue, $fileModelOrClass){
		$file = self::getPathToModelOrClass($fileModelOrClass);
		$contents = self::getContents($file);
		$line = "$accessLevel $$name;";
		if($defaultValue !== null){
			$line = "$accessLevel $$name = ".TVarDumper::dump($defaultValue).";";
		}
		if(strpos($contents, $line) === false){
			FileHelper::insertLineBefore($file, "public $", "\t$line");
		}
	}
	/**
	 * @param string|object $fileModelOrClass
	 * @return string Absolute path
	 */
	public static function getPathToModelOrClass($fileModelOrClass): string{
		if($fileModelOrClass instanceof SplFileInfo){
			$path = $fileModelOrClass->getRealPath();
		} elseif(is_string($fileModelOrClass)){
			if(class_exists($fileModelOrClass)){
				return self::getFilePathToClass($fileModelOrClass);
			}
			$path = self::absPath($fileModelOrClass);
		} else{
			$path = self::getFilePathToClass(get_class($fileModelOrClass));
		}
		if(empty($path)){
			le($fileModelOrClass);
		}
		return $path;
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @param string $toAdd
	 */
	public static function insertLineBefore(string $file, string $find, string $toAdd){
		try {
			$lines = explode("\n", self::getContents($file));
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		$toWrite = [];
		$added = false;
		foreach($lines as $line){
			if(!$added && strpos($line, $find) !== false){
				$toWrite[] = $toAdd;
				$added = true;
			}
			$toWrite[] = $line;
		}
		self::writeByFilePath($file, implode("\n", $toWrite));
	}
	public static function listClassesInNamespace(string $namespace): array{
		$namespaces = array_keys((new ComposerClassMap)->listClasses());
		return array_filter($namespaces, function($item) use ($namespace){
			return Str::startsWith($item, $namespace);
		});
	}
	public static function assertExists(string $path){
		if(!self::fileExists($path)){
			le("$path does not exist");
		}
	}
	/**
	 * @param string $input
	 * @return string
	 * @throws InvalidFilePathException
	 */
	public static function sanitizeFilePath(string $input): string{
		return QMStr::sanitizeFilePath($input);
	}
	public static function deleteFilesInFolder(string $folder){
		ThisComputer::exec("rm -rf $folder/*");
	}
	public static function deleteFiles(array $files){
		foreach($files as $file){
			self::delete($file);
		}
	}
	public static function delete(string $filePath){
		$filePath = self::absPath($filePath);
		if(file_exists($filePath)){
			unlink($filePath);
		}
	}
	/**
	 * @param string $dir
	 * @param string $needle
	 * @param bool $recursive
	 * @param string|null $filenameLike
	 * @return SplFileInfo[]
	 */
	public static function searchFiles(string $dir, string $needle, bool $recursive,
	                                   string $filenameLike = null): array{
		return FileFinder::findFilesContaining($dir, $needle, $recursive, $filenameLike);
	}
	public static function saveHtmlFragment(string $path, string $html){
		$html = HtmlHelper::renderReportWithTailwind($html);
		FileHelper::writeHtmlFile($path, $html);
	}
	/**
	 * @param string $path
	 * @param string $body
	 */
	public static function writeHtmlFile(string $path, string $body){
		$path = QMStr::before("?", $path, $path);
		if(stripos($path, '.html') === false){
			$path = $path.'.html';
		}
		self::writeByFilePath($path, $body);
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public static function deleteFoldersLike(string $path, string $needle){
		$folders = FileFinder::listFolders($path, true);
		foreach($folders as $folder){
			if(strpos($folder, $needle) !== false){
				self::deleteDir($folder);
			}
		}
	}
	public static function classToPath(string $class, bool $abs = false): string{
		$path = self::getFilePathToClass($class);
		if($abs){
			return self::absPath($path);
		}
		return $path;
	}
	public static function classToFileName(string $class): string{
		$parts = explode("\\", $class);
		$short = array_pop($parts);
		return $short.".php";
	}
	public static function classToFolderName(string $class): string{
		$namespace = self::classToNamespace($class);
		$parts = explode("\\", $namespace);
		return array_pop($parts);
	}
	public static function getFolderName(string $path): string{
		return self::getFolderFromFilePath($path);
	}
	public static function getFolderFromFilePath(string $filePath): string{
		return self::getDirectoryFromFilePath($filePath);
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public static function renameFoldersLike(string $search, string $replace){
		$folders = self::getFoldersLike($search);
		foreach($folders as $old){
			$new = str_replace($search, $replace, $old);
			self::rename($old, $new);
		}
	}
	/**
	 * @throws QMFileNotFoundException
	 */
	public static function getFoldersLike(string $search): array{
		$folders = FileFinder::listFolders(".");
		$matches = [];
		foreach($folders as $folder){
			if(strpos($folder, $search) !== false){
				$matches[] = $folder;
			}
		}
		return $matches;
	}
	public static function rename(string $old, string $new){
		$old = self::absPath($old);
		$new = self::absPath($new);
		QMLog::info("Renaming $old to $new...");
		self::createDirectoryIfNecessary(self::getFolderFromFilePath($new));
		rename($old, $new);
	}
	public static function renameProjectFilesStartingWith(string $needle, string $replace){
		$files = FileFinder::listProjectFiles();
		foreach($files as $file){
			$name = self::getFileNameFromPath($file);
			if(str_starts_with($name, $needle)){
				self::rename($file, str_replace($needle, $replace, $file));
			}
		}
	}
    public static function replaceInFileNames(string $needle, string $replace){
        $files = FileFinder::listFilesContaining(self::absPath(), $needle, true);
        foreach($files as $file){
            $name = self::getFileNameFromPath($file);
            if(str_contains($name, $needle)){
                self::rename($file, str_replace($needle, $replace, $file));
            }
        }
    }
	public static function replaceInProjectFiles(string $search, string $replace, string $like = null,
	                                             array  $folders = null){
		$files = FileFinder::listProjectFiles($like, $folders);
		foreach($files as $file){
			self::replaceStringInFile($file, $search, $replace);
		}
	}
	public static function toWslPath(string $windows): string{
		return self::toLinuxPath($windows);
	}
	public static function toLinuxPath(string $str): string{
		$str = str_replace("C:\\", "/mnt/c/", $str);
		return QMStr::replaceBackslashesWithForwardSlashes($str);
	}
	/**
	 * @param string $srcFolder
	 */
	public static function backupEnvs(string $srcFolder): void{
		$outBase = "/mnt/e/OneDrive/envs/";
		$like = '.env';
		QMSync::backupFilesLike($srcFolder, $like, $outBase, '.example');
	}
	/**
	 * @param string $dir
	 * @param string|null $filenameLike
	 * @param bool $recursive
	 * @param string|null $notLike
	 * @return SplFileInfo[]
	 */
	public static function findFilesWithNameLike(string $dir, string $filenameLike, bool $recursive = true,
	                                             string $notLike = null): array{
		return FileFinder::listFiles($dir, $recursive, $filenameLike, $notLike);
	}
	/**
	 * @param string $sourceFilePath
	 * @param string $destinationFilePath
	 * @return bool
	 */
	public static function copy(string $sourceFilePath, string $destinationFilePath): bool{
		$sourceFilePath = self::absPath($sourceFilePath);
		$destinationFilePath = self::absPath($destinationFilePath);
		$dir = self::getDirectoryFromFilePath($destinationFilePath);
		self::createDirectoryIfNecessary($dir);
		ConsoleLog::info("Copying $sourceFilePath to $destinationFilePath...");
		return copy($sourceFilePath, $destinationFilePath);
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @throws QMFileNotFoundException
	 */
	public static function removeLinesContaining(string $file, string $find){
		$lines = self::getLines($file);
		$keep = [];
		foreach($lines as $num => $line){
			$pos = strpos($line, $find);
			if($pos !== false){
				QMLog::info("Removing line $num containing $line");
				continue;
			}
			$keep[] = $line;
		}
		FileHelper::write($file, implode("\n", $keep));
	}
	/**
	 * @param string $file
	 * @return false|string[]
	 * @throws QMFileNotFoundException
	 */
	public static function getLines(string $file): array{
		$file_content = self::getContents($file);
		$lines = explode("\n", $file_content);
		return $lines;
	}
	/**
	 * @param string $filePath Absolute or relative to repo
	 * @param mixed $content
	 * @param array $blackListedStrings
	 * @param string|null $owner
	 * @return string Returns absolute path
	 */
	public static function write(string $filePath, $content, array $blackListedStrings = [],
	                             string $owner = null): string{
		return self::writeByFilePath($filePath, $content, $blackListedStrings, $owner);
	}
	/**
	 * @param string $path
	 * @return int|null
	 */
	public static function getLastModifiedTime(string $path): ?int{
		$path = self::absPath($path);
		if(!file_exists($path)){
			return null;
		}
		$lastModified = filemtime($path);
		ConsoleLog::info("$path last modified ".TimeHelper::timeSinceHumanString($lastModified).". ");
		return $lastModified;
	}
	/**
	 * @param string $filePath
	 * @param $data
	 * @param array $blackListedStrings
	 * @return string
	 */
	public static function writePrettyJson(string $filePath, $data, array $blackListedStrings = []): string{
		// Make sure we always use same encoding for diffs
		$content = QMStr::prettyJsonEncodeUnescapedSlashes($data);
		return self::write($filePath, $content, $blackListedStrings);
	}
	/**
	 * @param string $path
	 * @param string $string
	 */
	public static function createFileUnlessExists(string $path, string $string){
		if(self::fileExists($path)){
			QMLog::info("skipping $path because it already exists");
			return;
		}
		self::createFile($path, $string);
	}
	/**
	 * @param string $path
	 * @param string $content
	 */
	public static function createFile(string $path, string $content){
		self::writeByFilePath($path, $content);
	}
	/**
	 * @param string $dir
	 * @param string $string
	 * @param string $filenameLike
	 * @param bool $recursive
	 * @param string|null $notLike
	 */
	public static function appendStringIfAbsent(string $dir, string $string, string $filenameLike, bool $recursive = true,
	                                            string $notLike = null){
		$files = FileFinder::listFiles($dir, $recursive, $filenameLike, $notLike);
		foreach($files as $file){
			try {
				if(!self::getLineContainingString($file, $string)){
					self::append($file, $string);
				}
			} catch (QMFileNotFoundException $e) {
				le($e);
			}
		}
	}
	/**
	 * @param string $file
	 * @param string $find
	 * @return string|null
	 * @throws QMFileNotFoundException
	 */
	public static function getLineContainingString(string $file, string $find): ?string{
		$file_content = self::getContents($file);
		$lines = explode("\n", $file_content);
		foreach($lines as $line){
			$pos = strpos($line, $find);
			if($pos !== false){
				return $line;
			}
		}
		return null;
	}
	/**
	 * @param SplFileInfo|string $file
	 * @param string $string
	 * @throws QMFileNotFoundException
	 */
	public static function append($file, string $string): void{
		$lines = self::getLines($file);
		$lines[] = $string;
		self::writeLines($file, $lines);
	}
	/**
	 * @param string $filePath Absolute or relative to repo
	 * @param array $lines
	 * @param array $blackListedStrings
	 * @return string Returns absolute path
	 */
	public static function writeLines(string $filePath, array $lines, array $blackListedStrings = []): string{
		return self::writeByFilePath($filePath, implode("\n", $lines), $blackListedStrings);
	}
	public static function fileSystem(): Filesystem{
		return new FileSystem();
	}
	/**
	 * @param $dir
	 * @return string
	 */
	public static function permissions($dir): string{
		return self::listDirectoryPermissions($dir);
	}
	public static function listDirectoryPermissions(string $dir): string{
		return ThisComputer::listDirectoryPermissions("$dir");
	}
	/**
	 * @param string $path
	 * @param $content
	 */
	public static function save(string $path, $content){
		self::write($path, $content);
	}
	/**
	 * @param string $path
	 * @return false|SimpleXMLElement|string|null
	 * @throws QMFileNotFoundException
	 */
	public static function readXmlFile(string $path): SimpleXMLElement{
		$myXMLData = self::getContents($path);
		return simplexml_load_string($myXMLData);
	}
	/**
	 * @param $path
	 * @param string $needle
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	public static function hasLineStartingWith($path, string $needle): ?string{
		$lines = static::getLines($path);
		return collect($lines)->filter(function($line) use ($needle){
			return strpos($line, $needle) === 0;
		})->first();
	}
	public static function mkDir(string $destination){
		self::createDirectoryIfNecessary($destination);
	}
	public static function deleteFilesLike(string $dirPath, bool $recursive = false, string $filenameLike = null,
	                                       string $notLike = null){
		$files = FileFinder::getFilesLike($dirPath, $recursive, $filenameLike, $notLike);
		foreach($files as $file){
			self::deleteFile($file, __FUNCTION__);
		}
	}
	public static function validateExistence(string $path){
		if(!self::exists($path)){
			le("$path DOES NOT EXIST!");
		}
	}
	private static function exists(string $path): bool{
		return self::fileExists($path);
	}
	/**
	 * @param string $path
	 * @throws QMFileNotFoundException
	 */
	public static function validateNotEmpty(string $path){
		if(self::isEmpty($path)){
			le("$path IS EMPTY!");
		}
	}
	/**
	 * @param string $path
	 * @return bool
	 * @throws QMFileNotFoundException
	 */
	public static function isEmpty(string $path): bool{
		return empty(self::getContents($path));
	}
	public static function pathToName(string $path, bool $stripExtension): string{
		return self::name($path, $stripExtension);
	}
	public static function name(string $path, bool $stripExtension): string{
		return self::getFileNameFromPath($path, $stripExtension);
	}
	/**
	 * @param string $getAbsolutePath
	 * @param bool $recursive
	 * @param array $excludeClasses
	 * @return object[]
	 */
	public static function instantiateModelsInFolder(string $getAbsolutePath, bool $recursive = true,
	                                                 array  $excludeClasses = []): array{
		$models = [];
		$classes = static::getClassesInFolder($getAbsolutePath, $recursive);
		foreach($classes as $class){
			if(in_array($class, $excludeClasses)){
				continue;
			}
			try {
				$models[] = new $class;
			} catch (\Throwable $e) {
				QMLog::info("Could not instantiate $class because: ".$e->getMessage());
			}
		}
		return $models;
	}
	public static function toFileName(string $path): string{
		return self::getFileNameFromPath($path);
	}
	/**
	 * @param string $path
	 * @return void
	 */
	public static function dos2Unix(string $path): void{
		$abs = abs_path($path);
		$cmd = ThisComputer::terminalRun(abs_path('tools/dos2unix.exe')." $abs $abs");
	}
	/**
	 * @param string $absPath
	 */
	protected static function assertCanWrite(string $absPath): void{
		if(!touch($absPath)){
			le("No permission for $absPath");
		}
	}
	/**
	 * @param string $absPath
	 */
	public static function setPermissions0777(string $absPath): void{
		self::setPermissions($absPath, 0777);
	}
	/**
	 * @param string $path
	 * @param $permissions
	 */
	public static function setPermissions(string $path, $permissions){
		$absPath = self::absPath($path);
		//$permissions = $permissions - umask();
		try {
			chmod($absPath, octdec($permissions));
			$perms = self::getPermissions($absPath);
			if($perms !== $permissions){
				le("Could not set permissions for $absPath to $permissions. It is $perms");
			}
		} catch (Throwable $e) {
			QMLog::error("Could not chmod $absPath because ".$e->getMessage());
		}
	}
	public static function getDefaultFileOwnerName(): string{
		return IndexDotPhp::instance()->getOwnerName();
    }

    /**
     * @throws FilePermissionsException
     */
    public static function getDefaultGroupName(): string{
		return IndexDotPhp::instance()->getGroupName();
	}
	public static function setGroup(string $absPath, string $getDefaultGroupName){
		try {
            chgrp($absPath, $getDefaultGroupName);
        } catch (Throwable $e) {
            QMLog::error("Could not chgrp $absPath because ".$e->getMessage());
        }
	}
	public static function setOwner(string $path, string $owner){
		$absPath = self::absPath($path);
		try {
            chown($absPath, $owner);
        } catch (Throwable $e) {
            QMLog::error("Could not chown $absPath because ".$e->getMessage());
        }
	}
}
