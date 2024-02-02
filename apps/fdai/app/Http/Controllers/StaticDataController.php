<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\Buttons\Auth\LoginButton;
use App\Logging\QMIgnition;
use App\Slim\Controller\GetController;
use App\Slim\Controller\StaticData\GetStaticDataController;
use App\Storage\S3\S3Public;
use App\Storage\S3\S3Private;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;

class StaticDataController extends Controller {
	/**
	 * Handle the GET request.
	 * @throws FileNotFoundException
	 * @throws MimeTypeNotAllowed
	 */
	public function index(){
		$bucketName = GetStaticDataController::getBucketName();
		$s3Path = GetStaticDataController::getS3Path();
		if($bucketName === S3Public::getBucketName()){
			$data = S3Public::get($s3Path);
		} elseif($bucketName === S3Private::getBucketName()){
			if(!GetStaticDataController::authorized()){
				LoginButton::logoutAndRedirect("not admin");
				return;
			} else{
				$data = S3Private::get($s3Path);
			}
		} else{
			le("Bucket $bucketName not recognized!");
		}
		if(stripos($s3Path, 'ignition-report') !== false){
			$data = QMIgnition::replace_path_placeholders($data);
		}
		GetController::outputToBrowserOrDownload($s3Path, $data);
	}
}
