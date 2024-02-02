<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\S3;
use App\Logging\QMLog;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Illuminate\Http\UploadedFile;
/**
 * @package App\Storage\S3
 */
class S3PublicApps extends S3Public {
    public const BASE_S3_PATH = "app_uploads/";
    /**
     * @param string $clientId
     * @param UploadedFile $uploadedFile
     * @param string $fileName
     * @return bool
     * @throws \App\Exceptions\InvalidS3PathException
     * @throws \App\Exceptions\QMFileNotFoundException
     * @throws \App\Exceptions\SecretException
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed
     */
    public static function uploadEncryptedAppFileToS3(string $clientId, UploadedFile $uploadedFile, string $fileName): bool{
        $ext = $uploadedFile->getClientOriginalExtension().'.enc';
        $s3filePath = self::getClientS3Path($clientId, $fileName.'.'.$ext);
        $fileUrl = S3Public::encryptAndUpload($uploadedFile, $fileName, $s3filePath);
        return $fileUrl;
    }
    /**
     * @param string $clientId
     * @param string|null $fileName
     * @return string
     */
    public static function getAppResourceUrl(string $clientId, string $fileName = null): string{
        $url = S3Public::S3_CACHED_ORIGIN . static::getClientS3Path($clientId, $fileName);
        return $url;
    }
    /**
     * @param string $clientId
     * @param UploadedFile $uploadedFile
     * @param string $fileName
     * @return bool
     * @throws \App\Exceptions\SecretException
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed
     */
    public static function uploadUnencryptedAppFileToS3(string $clientId, UploadedFile $uploadedFile, string $fileName){
        $ext = $uploadedFile->getClientOriginalExtension();
        $fileName .= '.'.$ext;
        $fileName = str_replace($ext.'.'.$ext, $ext, $fileName);
        $s3filePath = static::getClientS3Path($clientId, $fileName);
        $success = static::put($s3filePath, file_get_contents($uploadedFile));
        if(stripos($fileName, "icon") !== false){
            try {
                $success = self::resizeAndUploadFavicon($clientId, $uploadedFile);
            } catch (ImageResizeException $e) {
                QMLog::logicExceptionIfNotProductionApiRequest("Could not generate favicon because: " .
                    $e->getMessage());
            }
        }
        $url = static::getAppResourceUrl($clientId, $fileName);
        if($success){return $url;}
        le("Could not upload image to ".$url);
    }
    /**
     * @param string $clientId
     * @param string $fileName
     * @return string
     */
    public static function getClientS3Path(string $clientId, string $fileName): string{
        return static::BASE_S3_PATH.$clientId."/$fileName";
    }
    /**
     * @param string $clientId
     * @param UploadedFile $uploadedFile
     * @return bool
     * @throws ImageResizeException
     * @throws \App\Exceptions\SecretException
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed
     */
    public static function resizeAndUploadFavicon(string $clientId, UploadedFile $uploadedFile): bool{
        $image = new ImageResize($uploadedFile);
        $image->resizeToHeight(16);
        $name = 'icon_16.png';
        $path = storage_path($name);
        $image->save($path, IMAGETYPE_PNG);
        $faviconS3Path = static::getClientS3Path($clientId, $name);
        $success = static::put($faviconS3Path, file_get_contents($path));
        return $success;
    }
}
