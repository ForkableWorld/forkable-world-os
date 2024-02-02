<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn, Add your name here
 */

namespace App\Storage\S3;
use App\Computers\ThisComputer;
use App\Exceptions\SecretException;
use App\Files\MimeContentTypeHelper;
use App\Repos\ImagesRepo;
use App\Repos\IonicRepo;
use App\Repos\PublicRepo;
use App\Repos\WPRepo;
use App\UI\CssHelper;
use App\Utils\Env;
use App\Utils\SecretHelper;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
/**
 * @package App\Storage\S3
 */
class S3Public extends S3Helper {
	public const DISK_NAME = 's3-public';
	public const S3_CACHED_ORIGIN = "https://static.quantimo.do/";
	/**
     * Write the contents of a file.
     * @param string $s3Path
     * @param string|resource $contents
     * @param array $optionStringOrArray
     * @param string|null $description
     * @return bool
     * @throws MimeTypeNotAllowed
     * @throws \App\Exceptions\SecretException
     */
    public static function put(string $s3Path, $contents, $optionStringOrArray = [], string $description = null): bool{
        SecretHelper::exceptionIfContainsSecretValue($contents, __METHOD__);
        if($filePattern = SecretHelper::containsSecretFilePattern($s3Path)){
	        throw new SecretException($filePattern, $s3Path, 's3_path');
        }
        $optionStringOrArray['public'] = true;
        $optionStringOrArray['ACL'] = 'public-read';
        if (!isset($optionStringOrArray['ContentType'])) {
            $optionStringOrArray['ContentType'] = MimeContentTypeHelper::guessMimeContentTypeBasedOnFileName($s3Path);
        }
        return parent::put($s3Path, $contents, $optionStringOrArray);
    }
    /**
     * @param string $s3Path
     * @return string
     */
    public static function url(string $s3Path): string{
        $s3Path = static::prefixWithBaseS3PathIfNecessary($s3Path);
        return static::S3_CACHED_ORIGIN.$s3Path;
    }
    public static function uploadStaticAssets(){
        ImagesRepo::uploadToS3Public(false);
        IonicRepo::uploadToS3Public(true);
        PublicRepo::uploadToS3Public(false);
        WPRepo::uploadWpIncludesToS3();
        WPRepo::uploadPluginsToS3();
        CssHelper::uploadCss();
    }
	/**
     * @param string|null $folder
     * @noinspection PhpUnused
     */
    public static function makeACLPublicRecursive(string $folder = null){
	    ThisComputer::exec("s3cmd setacl s3://static.quantimo.do/$folder --acl-public --recursive");
    }
	public static function getBucketName(): ?string{
		return Env::get('STORAGE_BUCKET_PUBLIC');
	}
	protected static function getLocalFileSystemConfig(): array{
		return [
			'driver' => 'local',
			'root' => storage_path('app/public'),
			'url' => env('APP_URL').'/storage',
			'visibility' => 'public',
		];
	}
}
