<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\S3;
use App\Computers\ThisComputer;
use App\DevOps\XDebug;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\SecretException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Files\MimeContentTypeHelper;
use App\Files\QMEncrypt;
use App\Logging\QMLog;
use App\Repos\IonicRepo;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\SecretHelper;
use App\Utils\UrlHelper;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use LogicException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
use Storage;
abstract class S3Helper {
	public const DISK_NAME = null;
	protected const TIMEOUT = 60;
	public const S3_REGION = 'us-east-1';
	public const BASE_S3_PATH = null;
	const S3CMD_CONFIG = "configs/.s3cfg";
	public static $lastModified;
    public static $cachedResponses;
	/**
     * @param string $url
     * @return string
     */
    public static function getFilenameFromUrl(string $url): string{
        $fileNameWithExtension = basename($url);
        return $fileNameWithExtension;
    }
	/**
	 * @param string $s3Path
	 * @return int|null
	 */
    public static function getSecondsSinceLastModified(string $s3Path): ?int{
        $lastModified = static::lastModified($s3Path);
        if (!$lastModified) {
            return null;
        }
        return time() - $lastModified;
    }
    /**
     * @param string $s3Path
     * @return bool
     */
    public static function exists(string $s3Path): bool{
        $lastModified = static::lastModified($s3Path);
        if (!$lastModified) {return false;}
        return true;
    }
    /**
     * @param string $s3Path
     * @return int
     */
    public static function lastModified(string $s3Path): int {
        $bucket = static::getBucketName();
        if (!$bucket) {
	        [
		        $bucket,
		        $s3Path,
	        ] = self::getBucketAndPathArray($s3Path);
            if ($bucket === S3Private::getBucketName()) {
                return S3Private::lastModified($s3Path);
            } elseif ($bucket === S3Public::getBucketName()) {
                return S3Public::lastModified($s3Path);
            } else {
                le("No bucket!");
            }
        }
        $s3Path = static::formatAndValidateS3Path($s3Path);
        $previousChecks = static::$lastModified;
        if(isset($previousChecks[$s3Path])){
            return $previousChecks[$s3Path];
        }
        $s3 = static::disk();
        try {
            $lastModified = $s3->lastModified($s3Path);
            QMLog::infoWithoutContext(static::url($s3Path) . " last modified  " .
                TimeHelper::timeSinceHumanString($lastModified));
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (QMFileNotFoundException | \League\Flysystem\FileNotFoundException $e) {
            QMLog::infoWithoutContext(static::getBucketName() . ": " . $e->getMessage());
            $lastModified = 0;
        }
        return static::$lastModified[$s3Path] = $lastModified;
    }
    /**
     * @param string $s3Path
     * @return string
     */
    protected static function prefixWithBaseS3PathIfNecessary(string $s3Path): string {
        $baseS3Path = static::BASE_S3_PATH;
        if($baseS3Path){
            if(stripos($s3Path, $baseS3Path) === 0){
                return $s3Path;
            }
            $s3Path = static::BASE_S3_PATH.'/'.$s3Path;
        }
        $s3Path = str_replace('//', '/', $s3Path);
        return $s3Path;
    }
    /**
     * @param string $s3Path
     * @param string $absoluteFilePath
     * @param bool $deleteLocal
     * @return string
     * @throws SecretException
     */
    public static function uploadPDF(string $s3Path, string $absoluteFilePath, bool $deleteLocal = false): string {
        $args = ['ContentType' => 'application/pdf'];
        try {
            $result = static::put($s3Path, file_get_contents($absoluteFilePath), $args);
        } catch (MimeTypeNotAllowed $e) {
            /** @var LogicException $e */
            throw $e;
        }
        if($result){
            QMLog::info("Uploaded $s3Path to ".static::getBucketName());
            if($deleteLocal){FileHelper::deleteFile($absoluteFilePath, __METHOD__);}
            return $s3Path;
        }
        le("Could not upload $s3Path");
    }
    /**
     * @param string $s3Path
     * @param string $html
     * @param bool $log
     * @return string
     */
    public static function uploadHTML(string $s3Path, string $html, bool $log = true): string {
        if(!str_contains($s3Path, '.html')){$s3Path .= '.html';}
        $args = ['ContentType' => MimeContentTypeHelper::HTML];
        $root = FileHelper::absPath('public/');
        $html = str_replace($root, 'https://app.quantimo.do/', $html);
        try {
            $result = static::put($s3Path, $html, $args);
            if(EnvOverride::isLocal()){
	            FileHelper::writeHtmlFile('tmp/'.$s3Path, $html);
            }
        } catch (MimeTypeNotAllowed | SecretException $e) {
            /** @var LogicException $e */
            throw $e;
        }
        if($result){
            try {
                $url = static::getUrlForS3BucketAndPath(static::getBucketName() . '/' . $s3Path);
            } catch (InvalidS3PathException $e) {
                le($e);
            }
            if($log){QMLog::info("Uploaded $s3Path to " . static::getBucketName() . ". See at:\n => $url");}
            return $url;
        }
        le("Could not upload $s3Path");
    }
    /**
     * @return Filesystem
     */
    public static function disk(): Filesystem{
//        if(AppMode::isTestingOrStaging()){ // Don't use this or we can't upload diffs from CI
//            return static::diskForTesting();
//        }
        return Storage::disk(static::DISK_NAME);
    }
    /**
     * @param string $s3Path
     * @return string
     */
    public static function url(string $s3Path): string {
        if (!static::getBucketName()) {
	        [
		        $bucket,
		        $s3Path,
	        ] = self::getBucketAndPathArray($s3Path);
            if ($bucket === S3Private::getBucketName()) {
                return S3Private::url($s3Path);
            } elseif ($bucket === S3Public::getBucketName()) {
                return S3Public::url($s3Path);
            } else {
                le("No bucket!");throw new \LogicException();
            }
        }
        $url = static::getObjectUrl($s3Path);
        if(!$url){$url = static::disk()->url($s3Path);}
        return $url;
    }
    /**
     * @param string $s3Path
     * @return string
     */
    public static function getObjectUrl(string $s3Path): string {
        $url = static::getS3Client()->getObjectUrl(static::getBucketName(), $s3Path);
        return $url;
    }
    /**
     * Write the contents of a file.
     * @param string $s3Path
     * @param string|resource $contents
     * @param mixed $optionStringOrArray
     * @param string|null $description
     * @return bool
     * @throws MimeTypeNotAllowed
     * @throws SecretException
     * @noinspection PhpUnusedParameterInspection
     */
    public static function put(string $s3Path, $contents, $optionStringOrArray = [], string $description = null): bool{
        $bucket = static::getBucketName();
		if (!$bucket) {
	        [
		        $bucket,
		        $s3Path,
	        ] = self::getBucketAndPathArray($s3Path);
            if ($bucket === S3Private::getBucketName()) {
                return S3Private::put($s3Path, $contents, $optionStringOrArray);
            } elseif ($bucket === S3Public::getBucketName()) {
                return S3Public::put($s3Path, $contents, $optionStringOrArray);
            } else {
                le("No bucket!");
            }
        }
        $s3Path = static::formatAndValidateS3Path($s3Path);
        static::$lastModified[$s3Path] = time();
        static::$cachedResponses[$s3Path] = $contents;
        QMLog::logLocalLinkButton(static::url($s3Path), $s3Path);
        //WpPost::firstOrCreateByUrl($url, $contents, $description);
        $disk = static::disk();
        $result = $disk->put($s3Path, $contents, $optionStringOrArray);
//        $userId = static::getUserIdFromS3Path($s3Path);
//        if($userId){
//            $u = QMUser::getById($userId);
//            $u->addMediaByS3FilePath($s3Path);
//        }
        return $result;
    }
    /**
     * @param string $s3Path
     * @param string|null $localPath
     * @return string
     * @throws FileNotFoundException
     */
    public static function download(string $s3Path, string $localPath = null): string {
        $s3Path = static::formatAndValidateS3Path($s3Path);
        $s3File = static::get($s3Path);
        if(!$localPath){
            $localPath = static::convertS3PathToLocalPath($s3Path);
        }
        $changed = FileHelper::writeByFilePath($localPath, $s3File);
        if(!$changed){
            le("Downloaded file was no different from local! ");
        }
        return $localPath;
    }
    /**
     * @return S3Client
     */
    protected static function getS3Client(): S3Client{
        $credentials = new Credentials(\App\Utils\Env::get('STORAGE_ACCESS_KEY_ID'), \App\Utils\Env::get('STORAGE_SECRET_ACCESS_KEY'));
        return new S3Client([
            //'profile' => 'default',  Should we be using this? https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html
            'version' => 'latest',
            'region'  => static::S3_REGION,
            'credentials' => $credentials
        ]);
    }
	/**
	 * @param string $s3FilePath
	 * @param int|null $maxAgeInSeconds
	 * @return string
	 * @throws FileNotFoundException
	 */
    public static function getLocalOrDownload(string $s3FilePath, int $maxAgeInSeconds = 0): ?string {
        static::validateS3Path($s3FilePath);
        $localPath = static::convertS3PathToLocalPath($s3FilePath);
        $existsAndNotExpired = FileHelper::existsAndNotExpired($localPath, $maxAgeInSeconds);
        if($existsAndNotExpired){return $localPath;}
        $existsAndNotExpired = static::existsAndNotExpired($s3FilePath, $maxAgeInSeconds);
        if(!$existsAndNotExpired){return null;}
        $localPath = static::download($s3FilePath);
        return $localPath;
    }
    /**
     * @param string $s3Path
     * @param int $maxAgeInSeconds
     * @return bool
     */
    public static function existsAndNotExpired(string $s3Path, int $maxAgeInSeconds = 0): bool {
        if (!static::getBucketName()) {
	        [
		        $bucket,
		        $s3Path,
	        ] = self::getBucketAndPathArray($s3Path);
            if ($bucket === S3Private::getBucketName()) {
                return S3Private::existsAndNotExpired($s3Path, $maxAgeInSeconds);
            } elseif ($bucket === S3Public::getBucketName()) {
                return S3Public::existsAndNotExpired($s3Path, $maxAgeInSeconds);
            } else {
                le("No bucket!");
            }
        }
        $age = static::getSecondsSinceLastModified($s3Path);
        if(!$age){return false;}
        if(!$maxAgeInSeconds){return true;}
        return $age < $maxAgeInSeconds;
    }
    /**
     * @param string $s3Path
     * @return string
     */
    public static function convertS3PathToLocalPath(string $s3Path): string {
        $localPath = FileHelper::absPath('tmp');
        $localPath .= DIRECTORY_SEPARATOR.static::getBucketName();
        return $localPath.DIRECTORY_SEPARATOR.$s3Path;
    }
    /**
     * @param string $directory
     * @return bool
     */
    public static function deleteDirectory(string $directory): bool{
        $directory = static::removeBucketFromS3PathIfNecessary($directory);
        $directory = static::prefixWithBaseS3PathIfNecessary($directory);
        $directory = str_replace('\\', '/', $directory); // Fix windows paths
        QMLog::error("Deleting $directory from S3 bucket " . static::getBucketName());
        return static::disk()->deleteDirectory($directory);
    }
    /**
     * @param string $directory
     * @return bool
     */
    public static function deleteAllFilesDirectory(string $directory): bool{
        QMLog::error("Deleting all files in $directory from S3 bucket " . static::getBucketName());
        $disk = static::disk();
        $files = $disk->allFiles($directory);
        return $disk->delete($files);
    }
    /**
     * @param string $directory
     * @return void
     * @noinspection PhpUnusedParameterInspection
     */
    public static function softDeleteDirectory(string $directory){
        le("Not implemented yet!");
        /** @noinspection PhpUnreachableStatementInspection */
        $directory = static::prefixWithBaseS3PathIfNecessary($directory);
        QMLog::error("Deleting $directory from S3 bucket " . static::getBucketName());
        $disk = static::disk();
        $deletedDir = 'deleted/'.$directory;
        $disk->makeDirectory($deletedDir);
        $files = $disk->allFiles($directory);
        foreach($files as $file) {
            $new_loc = str_replace($directory, $deletedDir, $file);
            $disk->copy($file, $new_loc);
        }
        //return $disk->deleteDirectory($directory);
    }
    /**
     * @param string $s3Path
     * @return string
     * @throws FileNotFoundException
     */
    public static function get(string $s3Path){
	    $bucket = static::getBucketName();
        $s3Path = static::formatAndValidateS3Path($s3Path);
        $previous = static::$cachedResponses;
        if(isset($previous[$s3Path])){
            return $previous[$s3Path];
        }
        try {
            $data = static::disk()->get($s3Path);
        } catch (FileNotFoundException $e){
            if(AppMode::isApiRequest()){
                throw new NotFoundException("$s3Path not found!");
            } else {
                throw $e;
            }
        }
        if(!$data){
            return self::$cachedResponses[$s3Path] = false;
        }
        return self::$cachedResponses[$s3Path] = $data;
    }
    /**
     * @param string $s3BucketAndPath
     * @return string
     * @throws InvalidS3PathException
     */
    public static function getUrlForS3BucketAndPath(string $s3BucketAndPath): string {
	    [
		    $bucket,
		    $s3Path,
	    ] = self::getBucketAndPathArray($s3BucketAndPath);
	    self::validateS3BucketAndPath($s3BucketAndPath);
        if(!$bucket){
            $bucket = static::getBucketName();
        }
	    if (str_contains($bucket, 'private')) {
            return UrlHelper::addParams(QMRequest::origin()."/static", ['bucket' => $bucket, 'path' => $s3Path]);
        }
        if ($bucket === S3Public::getBucketName()) {
            return S3Public::S3_CACHED_ORIGIN . $s3Path;
        }
        le("Please provide bucket with $s3BucketAndPath");
    }
    /**
     * @param string $s3BucketAndPath
     * @return string
     * @throws FileNotFoundException
     */
    public static function getDataForS3BucketAndPath(string $s3BucketAndPath): string{
	    [
		    $bucket,
		    $s3Path,
	    ] = self::getBucketAndPathArray($s3BucketAndPath);
        if ($bucket === S3Private::getBucketName()) {
            return S3Private::get($s3Path);
        }
        if ($bucket === S3Public::getBucketName()) {
            return S3Public::get($s3Path);
        }
        le("Please provide bucket with $s3BucketAndPath");throw new \LogicException();
    }
    /**
     * @param string $s3BucketAndPath
     * @param $contents
     * @return string
     * @throws MimeTypeNotAllowed
     * @throws SecretException
     */
    public static function putForS3BucketAndPath(string $s3BucketAndPath, $contents): string{
	    \App\Logging\ConsoleLog::info(__METHOD__ . " $s3BucketAndPath");
	    [
		    $bucket,
		    $s3Path,
	    ] = self::getBucketAndPathArray($s3BucketAndPath);
        if ($bucket === S3Private::getBucketName()) {
            return S3Private::put($s3Path, $contents);
        }
        if ($bucket === S3Public::getBucketName()) {
            return S3Public::put($s3Path, $contents);
        }
        le("Please provide bucket with $s3BucketAndPath");throw new \LogicException();
    }
    /**
     * @param string $s3BucketAndPath
     * @return array
     */
    public static function getBucketAndPathArray(string $s3BucketAndPath): array {
        $bucket = null;
        if (str_contains($s3BucketAndPath, S3Public::getBucketName())) {
            $bucket = S3Public::getBucketName();
            $s3BucketAndPath = str_replace(S3Public::getBucketName() . '/', '', $s3BucketAndPath);
        }
        if (str_contains($s3BucketAndPath, S3Private::getBucketName())) {
            $bucket = S3Private::getBucketName();
            $s3BucketAndPath = str_replace(S3Private::getBucketName() . '/', '', $s3BucketAndPath);
        }
        return [
            $bucket,
            $s3BucketAndPath,
        ];
    }
    public static function getBucket(string $s3BucketAndPath):?string {
        $arr = self::getBucketAndPathArray($s3BucketAndPath);
        return $arr[0] ?? null;
    }
    /**
     * @param string $s3Path
     */
    public static function validateS3Path(string $s3Path): void {
        if(stripos($s3Path, '/') === false){
	        le("Please provide full S3 path instead of just filename.  Provided s3Path was: $s3Path");
        }
        FileHelper::validateFileExtension($s3Path);
        if (stripos($s3Path, S3Public::getBucketName()) !== false ||
            stripos($s3Path, S3Private::getBucketName()) !== false) {
            le("$s3Path should not contain bucket name!");
        }
        //if (stripos($s3Path, 'img/charts') !== false) {le("$s3Path should not contain img/charts!");}
        if (stripos($s3Path, 'https') === 0) {
            le("$s3Path should not start with https");
        }
    }
    /**
     * @param string $s3BucketAndPath
     * @throws InvalidS3PathException
     */
    public static function validateS3BucketAndPath(string $s3BucketAndPath){
        if(stripos($s3BucketAndPath, '/users/') && stripos($s3BucketAndPath, '/population-studies/')){
            throw new InvalidS3PathException("User s3 folders should not contain population data", $s3BucketAndPath);
        }
        if(stripos($s3BucketAndPath, '//')){
            throw new InvalidS3PathException("s3BucketAndPath should not contain //", $s3BucketAndPath);
        }
        if(stripos($s3BucketAndPath, '/-')){
            throw new InvalidS3PathException("s3BucketAndPath should not contain /- ", $s3BucketAndPath);
        }
        if(stripos($s3BucketAndPath, '/q-m-') !== false){
            throw new InvalidS3PathException("s3BucketAndPath should not contain /q-m- ", $s3BucketAndPath);
        }
    }
    /**
     * @param string $s3Path
     * @return bool
     */
    public static function delete(string $s3Path): bool {
        $s3Path = static::formatAndValidateS3Path($s3Path);
        static::$cachedResponses[$s3Path] = null;
        QMLog::error("Deleting $s3Path");
	    $localPath = static::convertS3PathToLocalPath($s3Path);
		FileHelper::delete($localPath);
		try {
			return static::disk()->delete($s3Path);
		} catch (\Throwable $e) {
		    le("Could not delete $s3Path!  Make sure you don't have versioning turned on.  Error: " . $e->getMessage());
		}
    }
	/**
	 * @param string $s3Path
	 * @return string
	 */
    protected static function removeBucketFromS3PathIfNecessary(string $s3Path): string{
        $s3Path = QMStr::after(S3Private::getBucketName() . '/', $s3Path, $s3Path);
        $s3Path = QMStr::after(S3Public::getBucketName() . '/', $s3Path, $s3Path);
        if(static::getBucketName()){
            $s3Path = QMStr::after(static::getBucketName() . '/', $s3Path, $s3Path);
        }
        return $s3Path;
    }
    /**
     * @param string $s3Path
     * @return mixed|string
     */
    protected static function formatAndValidateS3Path(string $s3Path): string{
        $s3Path = static::removeBucketFromS3PathIfNecessary($s3Path);
        $s3Path = static::prefixWithBaseS3PathIfNecessary($s3Path);
		$s3Path = str_replace('\\', '/', $s3Path); // Fix windows paths
        self::validateS3Path($s3Path);
        return $s3Path;
    }
    /**
     * @param string $s3Path
     * @return int
     */
    public static function getUserIdFromS3Path(string $s3Path): ?int{
        $userId = QMStr::between($s3Path, 'users/', '/');
		if($userId){
			return (int)$userId;
		}
        return $userId;
    }
    /**
     * @param string $s3Path
     * @return string
     */
    public static function getCategoryNameFromS3Path(string $s3Path): string {
        if(strpos($s3Path, "http") === 0){
            $s3Path = self::getS3FilePathFromUrl($s3Path);
        }
        $category = QMStr::before('/', $s3Path);
        return QMStr::titleCaseSlow($category);
    }
    /**
     * @return array
     */
    public static function allDirectories(): array {
        return static::disk()->allDirectories();
    }
    /**
     * @param string $url
     * @return string
     */
    public static function getS3FilePathFromUrl(string $url): string {
        $path = UrlHelper::getQueryParam('path', $url);
        if($path){
            return $path;
        }
        $url = QMStr::after("https://static.quantimo.do/", $url, $url);
        $url = QMStr::after("/static/", $url, $url);
        return static::formatAndValidateS3Path($url);
    }
    /**
     * @param string $folderToUpload
     * @param string $s3FolderPath
     * @param bool $overwrite
     * @param bool $excludeGit
     * @param array $excludePatterns
     * @return string[]
     */
    public static function uploadFolder(string $folderToUpload, string $s3FolderPath, bool $overwrite = false,
                                        bool $excludeGit = true, array $excludePatterns = []): array {
        $folderToUpload = FileHelper::absPath($folderToUpload);
        $files = FileFinder::listFilesRecursively($folderToUpload);
        $urls = [];
        foreach($files as $file){
            $path = $file->getRealPath();
            if($excludeGit && stripos($path, '/.git') !== false){
                continue;
            }
            foreach($excludePatterns as $excludePattern){
                if(stripos($path, $excludePattern) !== false){
                    continue 2;
                }
            }
            $s3Path = $s3FolderPath.str_replace($folderToUpload, "", $path);
            try {
                $previous = self::get($s3Path);
                try {
                    $current = FileHelper::getContents($path);
                    //if(empty($current)){le("$path is empty!");}
                    if($previous === $current){
                        \App\Logging\ConsoleLog::info("No changes to $path");
                        continue;
                    }
                } catch (QMFileNotFoundException $e) {
                    le($e);
                }
            } catch (FileNotFoundException $e) {
                \App\Logging\ConsoleLog::info("$s3Path not found on S3");
            }
            $url = self::uploadFile($s3Path, $path, $overwrite);
            if($url){$urls[] = $url;}
        }
        return $urls;
    }
    /**
     * @param string $s3Path
     * @param string $filePath
     * @return string|null
     * Uploading DB via PHP uses too much memory
     */
    public static function uploadLargeFile(string $s3Path, string $filePath): ?string {
        \App\Logging\ConsoleLog::info("
Make sure you ran:
    sudo apt-get update -y
    sudo apt-get install -y s3cmd
        ");
        $relative = self::S3CMD_CONFIG;
        $bucket = static::getBucketName();
        QMLog::immediately("Uploading $filePath to Digital Ocean Space $bucket/$s3Path using credentials in $relative...");
        self::s3CmdPut($filePath, $s3Path);
        return static::url($s3Path);
    }
    public static function uploadFile(string $s3Path, string $path, bool $overwrite): ?string {
        if(!$overwrite && static::exists($s3Path)){
            \App\Logging\ConsoleLog::info("$s3Path already exists");
            return null;
        }
        $path = FileHelper::absPath($path);
        \App\Logging\ConsoleLog::info("Uploading $path to $s3Path in ".static::getBucketName());
        try {
            static::put($s3Path, file_get_contents($path));
            return static::url($s3Path);
        } catch (MimeTypeNotAllowed $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
            return null;
        } catch (SecretException $e){
            QMLog::error(__METHOD__.": ".$e->getMessage());
            return null;
        }
    }
    public static function uploadStaticAssets(){
        S3Public::uploadFolder(IonicRepo::IONIC_IMG_PATH, "img");
    }
    /**
     * @param string $localPathOrS3BucketAndPath
     * @return bool
     */
    public static function authorized(string $localPathOrS3BucketAndPath): bool{
        $bucket = S3Helper::getBucketAndPathArray($localPathOrS3BucketAndPath);
        if ($bucket[0] === S3Public::getBucketName()) {return true;}
        $s3UserId = S3Helper::getUserIdFromS3Path($localPathOrS3BucketAndPath);
        if (!$s3UserId) {return QMAuth::isAdmin();}
        return self::loggedInUserAuthorizedForThisId($s3UserId);
    }
    /**
     * @param int $s3UserId
     * @return bool
     */
    public static function loggedInUserAuthorizedForThisId(int $s3UserId): bool{
        $s3User = QMUser::find($s3UserId);
        if($s3User->getShareAllData()){
            return true;
        }
        $user = QMAuth::getUser();
        if(!$user){
            return false;
        }
        if($user->getId() === $s3UserId){
            return true;
        }
        if($user->isAdmin()){
            return true;
        }
        return false;
    }
    public static function listFilesRecursively(string $folder = null): array{
        $disk = static::disk();
        $s3Paths = $disk->allFiles($folder);
        return $s3Paths;
    }
    public static function listFilesComplicated(string $folder, array $skip = []): array{
        $disk = static::disk();
        $folders = $disk->directories($folder);
        $files = $disk->files($folder);
        if($folders){
            foreach($folders as $folder){
                if(in_array($folder, $skip)){
                    \App\Logging\ConsoleLog::info("Skipping $folder...");
                    continue;
                }
                \App\Logging\ConsoleLog::info("Listing files for $folder...");
                $f = $disk->allFiles($folder);
                $files[QMStr::afterLast($folder, '/')] = $f;
                $count = count($f);
                \App\Logging\ConsoleLog::info("$count files in $folder");
            }
        } else{
            $files = $disk->allFiles($folder);
        }
        return $files;
    }
    /**
     * @param string $pathWithoutExtension
     * @param $data
     * @throws SecretException
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function uploadJsonAndJsFiles(string $pathWithoutExtension, $data){
        static::put("$pathWithoutExtension.json", QMStr::prettyJsonEncode($data));
        static::put("$pathWithoutExtension.js", QMStr::prettyJsonInJavascript("qmImages", $data));
    }
    /**
     * @param string $s3FolderPath
     * @param string $localFolder
     * @param array|string[] $skipLike
     */
    public static function downloadFolder(string $s3FolderPath, string $localFolder, array $skipLike = ['git']){
        $s3FilePaths = static::listFilesRecursively($s3FolderPath);
        foreach($s3FilePaths as $s3FilePath){
            foreach($skipLike as $item){
                if(stripos($s3FilePath, $item) !== false){continue 2;}
            }
            $local = str_replace($s3FolderPath, $localFolder, $s3FilePath);
            try {
                self::download($s3FilePath, $local);
            } catch (InvalidFilePathException | FileNotFoundException $e) {
                le($e);
            }
        }
    }
    public static function uploadObject(string $key, $value): string{
        $s3Path = 'objects/'.$key;
        $str = QMStr::prettyJsonEncode($value);
        try {
            $result = static::put($s3Path, $str);
        } catch (MimeTypeNotAllowed | SecretException $e) {
            /** @var LogicException $e */
            throw $e;
        }
        if($result){
            try {
                $url = static::getUrlForS3BucketAndPath(static::getBucketName() . '/' . $s3Path);
            } catch (InvalidS3PathException $e) {
                le($e);
            }
            QMLog::info("Uploaded $s3Path to " . static::getBucketName() . ". See at:\n => $url");
            return $url;
        }
        le("Could not upload $s3Path");
    }
    /**
     * @param string $string
     * @return string
     * @throws FileNotFoundException
     */
    public static function getHtml(string $string): string {
        return static::get($string);
    }
    /**
     * @param string $s3Path
     * @param $value
     * @return bool|string
     * @throws MimeTypeNotAllowed
     * @throws SecretException
     */
    public static function upload(string $s3Path, $value): string {
        if(is_object($value) || is_array($value)){
            $value = QMStr::prettyJsonEncode($value);
            if(stripos($s3Path, '.json') === false){$s3Path .= '.json';}
        }
        static::put($s3Path, $value);
        return static::url($s3Path);
    }
    public static function bucketToDisk(string $bucket): string {
        $map = [
            S3Public::getBucketName() => S3Public::DISK_NAME,
            S3Private::getBucketName() => S3Private::DISK_NAME,
	        S3PrivateGlobal::getBucketName() => S3PrivateGlobal::DISK_NAME,
        ];
        return $map[$bucket];
    }
    protected static function copyS3CmdConfig(): void{
	    $configPath = FileHelper::absPath(self::S3CMD_CONFIG);
	    ThisComputer::exec("cp $configPath ~/.s3cfg && echo \"USER is \$USER\"");
    }
    /**
     * @param string $localFilePath
     * @param string $s3Path
     */
    protected static function s3CmdPut(string $localFilePath, string $s3Path): void{
        self::copyS3CmdConfig();
	    $bucket = static::getBucketName();
	    ThisComputer::exec("s3cmd put $localFilePath s3://$bucket/$s3Path");
    }
    /**
     * @param string $s3Path
     */
    public static function s3CmdList(string $s3Path): void{
        self::copyS3CmdConfig();
	    $bucket = static::getBucketName();
	    $res = ThisComputer::exec("s3cmd la --recursive s3://$bucket/$s3Path");
	    QMLog::info($res);
    }
	public static function listFilesLike(string $pattern, string $folder = null): array{
		$all = static::listFilesRecursively($folder);
		$matches = [];
		foreach($all as $one){
			if(stripos($one, $pattern) !== false){
				$matches[] = $one;
			}
		}
		return $matches;
	}
	public static function listFilesStartingWith(string $pattern, string $folder = null): array{
		$all = static::listFilesRecursively($folder);
		$matches = [];
		foreach($all as $one){
			if(QMStr::startsWith($one, $pattern)){
				$matches[] = $one;
			}
		}
		return $matches;
	}
	public static function deleteFilesLike(string $pattern, string $folder = null): array{
		$matches = static::listFilesLike($pattern, $folder);
		foreach($matches as $one){
			static::delete($one);
		}
		return $matches;
	}
	public static function deleteFilesStaringWith(string $pattern, string $folder = null): array{
		$matches = static::listFilesStartingWith($pattern, $folder);
		foreach($matches as $one){
			static::disk()->delete($one);
		}
		return $matches;
	}
	public static function deleteSecretFiles(): array{
		$all = static::listFilesRecursively();
		$matches = [];
		foreach($all as $one){
			if(SecretHelper::containsSecretFilePattern($one)){
				$matches[] = $one;
			}
		}
		foreach($matches as $match){
			static::delete($match);
		}
		return $matches;
	}

    private static function diskForTesting(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk('minio');
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string $fileName
     * @param string $s3filePath
     * @return string
     * @throws \App\Exceptions\InvalidS3PathException
     * @throws \App\Exceptions\SecretException
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed
     */
    public static function encryptAndUpload(UploadedFile $uploadedFile, string $fileName, string $s3filePath): string
    {
        $localFilePath = QMEncrypt::encryptFile($fileName, $uploadedFile);
        $success = static::put($s3filePath, file_get_contents($localFilePath));
        $fileUrl = static::getUrlForS3BucketAndPath($s3filePath);
        if ($success) {
            return $fileUrl;
        }
        le("Could not upload encrypted file to " . $fileUrl);
    }

    /**
     * @param string $s3Path
     * @return string
     * @throws FileNotFoundException
     */
    public static function downloadAndDecryptByS3Path(string $s3Path): string {
        $filePath = static::download($s3Path);
        QMLog::debug("Decrypting $filePath...");
        $decryptedFilePath = QMEncrypt::decryptFile($filePath);
        return $decryptedFilePath;
    }
    /**
     * @param string $url
     * @return string
     */
    public static function downloadAndDecryptByUrl(string $url): string{
        $fileName = S3Helper::getFilenameFromUrl($url);
        $filePath = '/tmp/' . $fileName;
        if (XDebug::active()) {
            $filePath = FileHelper::absPath('tmp/' . $fileName);
        }
        QMLog::debug("Downloading $url...");
        copy($url, $filePath);
        QMLog::debug("Decrypting $filePath...");
        $decryptedFilePath = QMEncrypt::decryptFile($filePath);
        return $decryptedFilePath;
    }
	/**
	 * @param string $s3Path
	 * @param string $relativeDownloadPath
	 * @return void
	 * @throws FileNotFoundException
	 */
	public static function downloadIfNotExists(string $s3Path, string $relativeDownloadPath){
		$absoluteDownloadPath = abs_path($relativeDownloadPath);
		if(FileHelper::fileExists($absoluteDownloadPath)){return;}
		static::download($s3Path, $absoluteDownloadPath);
	}
	abstract public static function getBucketName(): ?string;
	abstract protected static function getLocalFileSystemConfig(): array;
	public static function getConfig(): array{
		$bucket = static::getBucketName();
		if(!$bucket){
			return static::getLocalFileSystemConfig();
		}
		return [
			'driver' => 's3',
			'key' => env('STORAGE_ACCESS_KEY_ID'),
			'secret' => env('STORAGE_SECRET_ACCESS_KEY'),
			'region' => static::S3_REGION,
			'bucket' => env('STORAGE_BUCKET_PRIVATE', $bucket),
			'url' => env('STORAGE_URL'),
			'endpoint' => env('STORAGE_ENDPOINT'),
			'options' => ['timeout' => static::TIMEOUT,],
		];
	}
}
