<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Storage\S3\S3Helper;
class InvalidS3PathException extends \Exception
{
    /**
     * InvalidS3PathException constructor.
     * @param string $message
     * @param string $s3BucketAndPath
     */
    public function __construct(string $message, string $s3BucketAndPath){
        [$bucket, $path] = S3Helper::getBucketAndPathArray($s3BucketAndPath);
        $url = "https://cloud.digitalocean.com/spaces/$bucket?i=14a8d8&path=".urlencode($path);
        parent::__construct($message."
        but is: $s3BucketAndPath
         DELETE AT
           => $url
        ", 500);
    }
}
