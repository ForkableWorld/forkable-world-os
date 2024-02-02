<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\S3;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\SecretException;
use App\Models\MeasurementImport;
use App\Properties\MeasurementImport\MeasurementImportStatusProperty;
use App\Types\TimeHelper;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;

/**
 * @package App\Storage\S3
 */
class S3PrivateUsers extends S3Private {
    public const BASE_S3_PATH = "user_uploads/";
	/**
	 * @param int $userId
	 * @param UploadedFile $uploadedFile
	 * @param string $sourceName
	 * @return string
	 * @throws InvalidS3PathException
	 * @throws ModelValidationException
	 * @throws SecretException
	 * @throws MimeTypeNotAllowed
	 */
    public static function encryptAndUploadSpreadsheetToS3(int $userId, UploadedFile $uploadedFile, string $sourceName): string {
        $originalName = $uploadedFile->getClientOriginalName();
        $fileName = TimeHelper::YYYYmmddd().'-'.$sourceName.'-'.$originalName;
        $s3filePath = static::BASE_S3_PATH.$userId.'/'.$fileName.'.enc';
        $fileUrl = static::encryptAndUpload($uploadedFile, $fileName, $s3filePath);
        if(!$fileUrl){return false;}
        $import = new MeasurementImport();
        $import->file = $s3filePath;
        $import->user_id = $userId;
        $import->source_name = $sourceName;
        $import->status = MeasurementImportStatusProperty::STATUS_WAITING;
        $import->save();
        return $fileUrl;
    }
}
