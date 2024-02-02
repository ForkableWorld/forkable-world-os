<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Buttons\QMButton;
use App\Exceptions\InvalidS3PathException;
use App\Logging\ConsoleLog;
use App\Storage\S3\S3Helper;
use App\Storage\S3\S3Private;
use App\Storage\S3\S3Public;
use App\Types\QMStr;
use App\UI\IonIcon;
use App\Utils\AppMode;
use App\Utils\UrlHelper;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\InteractsWithMedia;

trait HasFiles {
	use InteractsWithMedia;
	/**
	 * @param string $url
	 * @return string
	 */
	protected function urlToS3Path(string $url): string{
		$folder = $this->getShowFolderPath();
		$path = UrlHelper::getQueryParam('path', $url);
		if($path){
			return $path;
		}
		$path = QMStr::after($folder, $url);
		return $folder . $path;
	}
	/**
	 * @param string $fileName
	 * @param string|null $subFolder
	 * @return string
	 */
	protected function fileNameAndFolderToS3Path(string $fileName, string $subFolder = null): string{
		$folder = $this->getShowFolderPath();
		if($subFolder){
			$folder .= '/' . $subFolder;
		}
		$path = $folder . '/' . $fileName;
		$path = str_replace('//', '/', $path);
		return $path;
	}
	/**
	 * @return string
	 */
	public function getShowFolderPath(): string{
		$folderPath = static::getIndexPath();
		return $folderPath . '/' . $this->getSlug();
	}
	public static function getS3Bucket(): string{
		if(static::hasColumn('user_id')){
			return S3Private::getBucketName();
		}
		return S3Public::getBucketName();
	}
	/**
	 * @return string
	 */
	public function getS3BucketAndFolderPath(): string{
		$b = static::getS3Bucket();
		if(!$b){
			le("Please set STORAGE_BUCKET constant on " . $this->getShortClassName());
		}
		$s3 = $b . '/';
		if(AppMode::isTestingOrStaging()){
			$s3 .= "testing/";
		}
		$s3 .= $this->getShowFolderPath();
		try {
			S3Helper::validateS3BucketAndPath($s3);
		} catch (InvalidS3PathException $e) {
			le($e);
		}
		return $s3;
	}
	/**
	 * @param string|null $extension
	 * @return string
	 */
	public function getS3FilePath(string $extension = null): string{
		$folder = $this->getShowFolderPath();
		$name = $this->getFileName($extension);
		$path = $folder . '/' . $name;
		$path = str_replace('//', '/', $path);
		return $path;
	}
	/**
	 * @param string|null $extension
	 * @return string
	 */
	public function getFileName(string $extension): string{
		return $this->getSlugWithNames() . "." . $extension;
	}
	/**
	 * @return int|string
	 */
	abstract public function getId();
	/**
	 * @return array
	 */
	public function listFilesOnS3(): array{
		$folder = $this->getShowFolderPath();
		$disk = $this->getDisk();
		/** @var \League\Flysystem\Filesystem $driver */
		$driver = $disk->getDriver(); // TODO: figure out how to make timeouts work if Digital Ocean fails
		//$driver->getAdapter()->options['timeout'] = 15;
		$start = microtime(true);
		$this->logInfo("Getting list of files in remote disk {$this->getDiskName()} in folder $folder (this can take a while)...");
		$files = $disk->allFiles($folder);
		$count = count($files);
		$duration = round(microtime(true) - $start);
		$this->logInfo("Got $count files from remote disk {$this->getDiskName()} in folder $folder in $duration seconds...");
		foreach($files as $file){
			ConsoleLog::info($file);
		}
		return $files;
	}
	/**
	 * @return Filesystem|FilesystemAdapter
	 */
	public function getDisk(): Filesystem{
		return Storage::disk($this->getDiskName());
	}
	public function getDiskName(): string{
//        if(AppMode::isTestingOrStaging()){
//            return 'minio';
//        }
		return S3Helper::bucketToDisk(static::getS3Bucket());
	}

    public function getFileUrls(): array{
		$files = $this->listFilesOnS3();
		$urls = [];
		foreach($files as $file){
			try {
				$urls[] = S3Helper::getUrlForS3BucketAndPath(static::getS3Bucket() . "/" . $file);
			} catch (InvalidS3PathException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
		return $urls;
	}

    /**
     * @param string|null $like
     * @return QMButton[]
     */
	public function getFileButtons(string $like = null): array{
		$buttons = [];
		$urls = $this->getFileUrls();
		foreach($urls as $url){
			if($like && stripos($url, $like) === false){
				continue;
			}
			$filename = QMStr::getFileNameFromUrl($url, true);
			$text = QMStr::titleCaseSlow($filename);
			$b = new QMButton($text, $url, null, IonIcon::cloud);
			$buttons[] = $b;
		}
		return $buttons;
	}
	public function registerMediaCollections(): void{
		$this
			->addMediaCollection('files')
			->useDisk($this->getDiskName());
	}
}
