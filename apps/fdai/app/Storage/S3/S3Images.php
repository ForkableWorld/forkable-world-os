<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\S3;
use App\Logging\QMLog;
class S3Images extends S3Public {
    public const BASE_S3_PATH = 'img/';
	public const S3_IMAGE_URL = self::S3_CACHED_ORIGIN . self::BASE_S3_PATH;
    /**
     * @param string $key
     * @param $imageData
     * @param string $type
     * @return bool
     */
    public static function uploadImage(string $key, $imageData, string $type) {
        $key = self::formatImageKey($key, $type);
        $contentType = 'image/' . $type;
        if ($type === 'svg') {
            $contentType = 'image/svg+xml';
        }
        $s3Path = $key . '.' . $type;
        $options = [
            'Bucket'      => static::getBucketName(),
            'Key'         => $s3Path,
            'Body'        => $imageData,
            'ContentType' => $contentType,
            'ACL'         => 'public-read'
        ];
        $result = static::put($s3Path, $imageData, $options);
        if($result){
            $s3Url = static::url($s3Path);
            $quantiModoImageUrl = static::getCachedImageUrl($key, $type);
            if($s3Url !== $quantiModoImageUrl){le("actual replaced url $s3Url from S3 client !== generated quantiModoImageUrl $quantiModoImageUrl");}
            QMLog::info("Uploaded to $s3Url");
        }
        return $result;
    }
    /**
     * @param string $key
     * @param string $type
     * @return string
     */
    public static function formatImageKey(string $key, string $type): string {
        // If you use an underscore '_' character, then Google will combine the two words on either side into one word.
        $key = str_replace([
            ' ',
            '_'
        ], '-', $key);
        if (stripos($key, '.' . $type) === false) {
            $key = $key . '.' . $type;
        }
		if(stripos($key, self::BASE_S3_PATH) === false){
			$key = self::BASE_S3_PATH . $key;
		}
        return $key;
    }
    /**
     * @param string $key
     * @param string $type
     * @return string
     */
    public static function getCachedImageUrl(string $key, string $type): string {
        $key = S3Images::formatImageKey($key, $type);
        return static::S3_CACHED_ORIGIN . $key;
    }
}
