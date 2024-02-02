<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Sync;
use App\Repos\ImagesRepo;
use App\Storage\S3\S3Public;
use App\PhpUnitJobs\JobTestCase;
class SyncJob extends JobTestCase
{
    public function testUploadStaticAssets(){
        ImagesRepo::outputConstantsForFolder('programming');
        S3Public::uploadStaticAssets();
    }
}
