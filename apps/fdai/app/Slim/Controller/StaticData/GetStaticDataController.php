<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\StaticData;
use App\Buttons\Auth\AuthButton;
use App\Buttons\Auth\LoginButton;
use App\Exceptions\AccessTokenExpiredException;
use App\Exceptions\QMFileNotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Files\FileHelper;
use App\Logging\QMIgnition;
use App\Logging\QMLog;
use App\Slim\Controller\GetController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Storage\S3\S3PrivateGlobal;
use App\Storage\S3\S3Public;
use App\Storage\S3\S3Helper;
use App\Storage\S3\S3Private;
use App\Types\QMStr;
use App\Utils\EnvOverride;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
class GetStaticDataController extends GetController {
	/**
	 * Handle the GET request.
	 * @throws FileNotFoundException
	 * @throws MimeTypeNotAllowed
	 */
	public function get(){
		$bucketName = GetStaticDataController::getBucketName();
		$s3Path = GetStaticDataController::getS3Path();
		if($bucketName === S3Public::getBucketName()){
			$data = S3Public::get($s3Path);
		} elseif($bucketName === S3Private::getBucketName()){
			if(!GetStaticDataController::authorized()){
				return LoginButton::logoutAndRedirect("not admin");
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

    /**
     * @param string $localPathOrS3BucketAndPath
     * @return string|null
     * @throws FileNotFoundException
     * @throws QMFileNotFoundException
     * @throws UnauthorizedException
     * @throws AccessTokenExpiredException
     */
	public static function getData(string $localPathOrS3BucketAndPath): ?string{
		$bucketName = S3Helper::getBucket($localPathOrS3BucketAndPath);
		if(!$bucketName){
			$local = EnvOverride::isLocal();
			if(!$local && !QMAuth::isAdmin()){
				return AuthButton::getRedirect();
			}
			$data = FileHelper::getContents($localPathOrS3BucketAndPath);
			return $data;
		}
		if($bucketName === S3Public::getBucketName()){
			return S3Helper::getDataForS3BucketAndPath($localPathOrS3BucketAndPath);
		} elseif($bucketName === S3Private::getBucketName()){
			if(!S3Helper::authorized($localPathOrS3BucketAndPath)){
				throw new UnauthorizedException();
			} else{
				return S3Helper::getDataForS3BucketAndPath($localPathOrS3BucketAndPath);
			}
		} else{
			le("Bucket $bucketName not recognized!");
		}
	}
	/**
	 * @return int|null
	 */
    public static function getS3UserId(): ?int{
		$s3Path = GetStaticDataController::getS3Path();
		$userId = S3Helper::getUserIdFromS3Path($s3Path);
		if(!$userId){
			return null;
		}
		return $userId;
	}
	/**
	 * @return bool
	 */
    public static function authorized(): bool{
		if(GetStaticDataController::getBucketName() === S3Public::getBucketName()){
			return true;
		}
		$s3UserId = GetStaticDataController::getS3UserId();
		if(!$s3UserId){
			$admin = QMAuth::isAdmin();
			if(!$admin){
				QMLog::error("Unauthorized!");
			}
			return $admin;
		}
		return S3Helper::loggedInUserAuthorizedForThisId($s3UserId);
	}
	/**
	 * @return string|null
	 */
	public static function getBucketName(): ?string{
		$bucketName = QMRequest::getParam('bucket');
		if($bucketName){
			return $bucketName;
		}
		$path = QMRequest::getRequestPathWithoutQuery();
		$path = QMStr::after('static/', $path);
		$bucketPath = QMStr::before('/', $path);
		$bucketName = str_replace('.s3.amazonaws.com', '', $bucketPath);
		if(str_contains($path, 'ignition-report')){
			return S3PrivateGlobal::getBucketName();
		}
		return $bucketName;
	}
	/**
	 * @return string
	 */
	public static function getS3Path(): string{
		$path = QMRequest::getParam('path');
		if($path){
			return $path;
		}
		$path = QMRequest::getRequestPathWithoutQuery();
		$path = QMStr::after('static/', $path);
		$s3Path = QMStr::after('.s3.amazonaws.com/', $path);
		if(!$s3Path){
			$bucket = GetStaticDataController::getBucketName();
			$s3Path = QMStr::after($bucket . '/', $path);
		}
		return $s3Path;
	}
}
