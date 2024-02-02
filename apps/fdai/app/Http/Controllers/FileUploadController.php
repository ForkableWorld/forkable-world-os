<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Http\Controllers;
use App\DataSources\QMSpreadsheetImporter;
use App\DataSources\SpreadsheetImporters\GeneralSpreadsheetImporter;
use App\Exceptions\BadRequestException;
use App\Exceptions\InvalidClientIdException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\Collaborator;
use App\Slim\Middleware\QMAuth;
use App\Storage\S3\S3Public;
use App\Storage\S3\S3PublicApps;
use App\Types\BoolHelper;
use App\Utils\UrlHelper;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
/** Class FileUploadController
 * @package App\Http\Controllers
 */
class FileUploadController extends Controller {
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function uploadUserFile(Request $request): JsonResponse{
		$user = QMAuth::getQMUser();
		$file = $request->file('file');
		$name = $request->get('name');
		if(!$name){
			$name = $file->getClientOriginalName();
		}
		try {
			$contents = $file->get();
		} catch (FileNotFoundException $e) {
			le($e);
		}
		return new JsonResponse([
			'success' => true,
			'url' => $user->uploadFile($name, $contents, $request->get('folder')),
		]);
	}
	/**
	 * @param Request $request
	 * @return StreamedResponse
	 */
	public function downloadUserFile(Request $request): StreamedResponse{
		$url = $request->fullUrl();
		$name = FileHelper::getFileNameFromPath($url);
		return response()->streamDownload(function() use ($request){
			$user = QMAuth::getQMUser();
			$name = $request->get('name');
			if($name){
				$data = $user->getFileDataByNameAndFolder($name, $request->get('folder'));
			} else{
				$data = $user->getFileDataByUrl($request->fullUrl());
			}
			echo $data;
		}, $name);
	}
	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @throws \Throwable
	 */
	public function storeSpreadsheet(Request $request): JsonResponse{
		$userId = QMAuth::id();
		$file = $request->file('file');
        if(!$file){
            throw new BadRequestException('Please provide a file in your post request! '
                .UrlHelper::API_DOCS_URL);
        }
		try {
            $type = $request->get('connectorName', $request->get('sourceName', $request->get('source')));
            if(!$type){$type = GeneralSpreadsheetImporter::NAME;}
			$res = QMSpreadsheetImporter::encryptAndUploadSpreadsheetToS3($userId, $file, $type);
		} catch (\Throwable $e) { // Not sure why these aren't being reported?
			QMLog::error("Could not upload spreadsheet because " . $e->getMessage(), ['exception' => $e]);
			throw $e;
		}
		return new JsonResponse(['success' => $res]);
	}
	/**
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function uploadClientFile(Request $request): JsonResponse{
		$clientId = $this->getClientId($request);
		$file = $request->file('file');
		$filename = $request->get('filename');
		if(!$clientId){
			throw new InvalidClientIdException($clientId);
		}
		Collaborator::userIsCollaboratorOrAdmin($clientId, true);
		if(BoolHelper::isTruthy($request->get('encrypt'))){
			$url = S3PublicApps::uploadEncryptedAppFileToS3($clientId, $file, $filename);
		} else{
			$url = S3PublicApps::uploadUnencryptedAppFileToS3($clientId, $file, $filename);
		}
		return new JsonResponse([
			'success' => true,
			"url" => $url,
		]);
	}
	/**
	 * @param Request $request
	 * @return BinaryFileResponse
	 * @throws BadRequestException
	 */
	public function downloadAndDecrypt(Request $request): BinaryFileResponse{
		$clientId = $this->getClientId($request);
		$fileUrl = $request->get('filename');
		Collaborator::userIsCollaboratorOrAdmin($clientId, true);
		if(strpos($fileUrl, $clientId) === false){
			throw new BadRequestException("Proved client id ($clientId) should be in provided " .
				"the file url in the filename parameter but the filename parameter was $fileUrl");
		}
		$decryptedFilePath = S3Public::downloadAndDecryptByUrl($fileUrl);
		return response()->download($decryptedFilePath);
		// TODO: @unlink($decryptedFilePath); afterwards
	}
	/**
	 * @param Request $request
	 * @return mixed
	 */
	private function getClientId(Request $request){
		return $request->get('clientId', $request->get('client_id'));
	}
}
