<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Storage\S3;
use App\Utils\Env;
class S3Private extends S3Helper {
    public const DISK_NAME = 's3-private';
    public const S3_PATH_DB_BACKUPS = "db-backups";
    /**
     * @param string $s3Path
     * @return string
     * @noinspection PhpDocMissingThrowsInspection
     */
    public static function url(string $s3Path): string {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::getUrlForS3BucketAndPath(static::getBucketName() . "/$s3Path");
    }
	public static function getBucketName(): ?string{
		return Env::get('STORAGE_BUCKET_PRIVATE');
	}
	protected static function getLocalFileSystemConfig(): array{
		return [
			'driver' => 'local',
			'root' => storage_path('app/private'),
			'url' => env('APP_URL').'/private',
			'visibility' => 'private',
		];
	}
}
