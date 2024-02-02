<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Exceptions\BadRequestException;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\QMException;
use App\Logging\QMLog;
use App\Mail\MeasurementExportEmail;
use App\Mail\QMSendgrid;
use App\Models\Application;
use App\Models\MeasurementExport;
use App\Slim\Model\QMUserRelatedModel;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Measurement\GetMeasurementRequest;
use App\Storage\DB\QMQB;
use App\Types\QMStr;
use App\Types\TimeHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use SendGrid\Response;
use ZipArchive;
class MeasurementExportRequest extends QMUserRelatedModel {
	public const TABLE = 'measurement_exports';
	public const FIELD_ID = 'id';
	public const FIELD_USER_ID = 'user_id';
	public const FIELD_output_type = 'output_type';
	public const FIELD_error_message = 'error_message';
	public const FIELD_status = 'status';
	public const STATUS_WAITING = 'WAITING';
	public const STATUS_FULFILLED = 'FULFILLED';
	public const STATUS_ALREADY_EMAILED = 'ALREADY_EMAILED';
	public const STATUS_ERROR = 'ERROR';
	public const STATUS_EXPORTING = 'EXPORTING';
	public const STATUS_NO_EMAIL = 'NO_EMAIL';
	public const STATUS_NO_USER = 'NO_USER';
	public const OUTPUT_FORMAT_CSV = "csv";
	public const STATUS_NO_MEASUREMENTS = 'NO_MEASUREMENTS';
	public const TYPE_USER = 'user';
	public const TYPE_APP = 'app';
	public const MAXIMUM_MEASUREMENTS_FOR_PDF_XLS = 10000;
	public const LARAVEL_CLASS = MeasurementExport::class;
	private $allowedStatuses = [
		self::STATUS_WAITING,
		self::STATUS_FULFILLED,
		self::STATUS_NO_MEASUREMENTS,
		self::STATUS_NO_USER,
		self::STATUS_ERROR,
		self::STATUS_EXPORTING,
		self::STATUS_NO_EMAIL,
		self::STATUS_ALREADY_EMAILED,
	];
	private $csvFilePath;
	private $measurementsToExport;
	private $spreadSheetData;
	private $zipFilePath;
	public $createdAt;
	public $deletedAt;
	public $errorMessage;
	public $id;
	public $outputType;
	public $status;
	public $type;
	public $updatedAt;
	public $user;
	/**
	 * MeasurementExport constructor.
	 * @param $row
	 */
	public function __construct($row = null){
		if(!$row){
			return;
		}
		QMStr::camelize($this, $row);
		parent::__construct();
	}
	/**
	 * @param string $value
	 * @return int
	 */
	public function setStatus(string $value): int{
		if(!in_array($value, $this->allowedStatuses, true)){
			throw new InvalidArgumentException("Invalid value $value");
		}
		$this->status = $value;
		return $this->updateMeasurementExportRequestDBRow([self::FIELD_status => $value]);
	}
	/**
	 * @param array $array
	 * @return int
	 */
	public function updateMeasurementExportRequestDBRow(array $array): int{
		$array['updated_at'] = date('Y-m-d H:i:s');
		return $this->updateDbRow($array);
	}
	/**
	 * @param string $value
	 * @return int
	 */
	public function setErrorMessage(string $value): int{
		$this->errorMessage = $value;
		return $this->updateMeasurementExportRequestDBRow([self::FIELD_error_message => $value]);
	}
	public static function setExportingAndErroredExportsToWaiting(){
		self::whereMeasurementExportRequest('status', self::STATUS_ERROR)->update(['status' => self::STATUS_WAITING]);
		self::whereMeasurementExportRequest('status', self::STATUS_EXPORTING)
			->update(['status' => self::STATUS_WAITING]);
		self::whereMeasurementExportRequest(self::FIELD_USER_ID, 0)->delete();
	}
	/**
	 * @param $field
	 * @param $value
	 * @return QMQB
	 */
	private static function whereMeasurementExportRequest($field, $value): QMQB{
		return self::writable()->where($field, $value);
	}
	/**
	 * @param QMUser $user
	 * @param string $type
	 * @param null $clientId
	 * @param string $format
	 * @return int
	 */
	public static function createExportRequestRecord(QMUser $user, string $type = 'user', $clientId = null, string $format = 'csv'): int{
		if(!$user->email){
			throw new BadRequestException('You must provide email address before exporting');
		}
		$exportQuery =
			self::whereMeasurementExportRequest(self::FIELD_USER_ID, $user->id)->where('status', self::STATUS_WAITING)
				->where('type', $type);
		if(!empty($clientId)){
			$exportQuery->where('client_id', $clientId);
		}
		$measurementExportRequest = $exportQuery->first();
		if(!empty($measurementExportRequest)){
			throw new QMException(QMException::CODE_BAD_REQUEST,
				'You already have a pending measurement export request!');
		}
		$id = self::writable()->insertGetId([
			'status' => self::STATUS_WAITING,
			'type' => $type,
			self::FIELD_output_type => $format,
			'client_id' => $clientId,
			self::FIELD_USER_ID => $user->id,
		]);
		return $id;
	}
	/**
	 * @param string $errorName
	 * @param string $errorMessage
	 * @param string|null $status
	 * @param array $metaData
	 */
	public function saveMeasurementExportErrorAndThrowException(string $errorName, string $errorMessage = '', string $status = null,
		array $metaData = []){
		if(empty($errorMessage)){
			$errorMessage = $errorName;
		}
		if(!$status){
			$status = self::STATUS_ERROR;
		}
		$this->logError($errorName, $metaData);
		$this->setErrorMessage($errorMessage);
		$this->setStatus($status);
		le($errorMessage);
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		$string = "Measurement export for " . $this->getQMUser()->getLoginNameAndIdString() .
			" with status $this->status created " . TimeHelper::timeSinceHumanString($this->createdAt);
		if(!empty($this->errorMessage)){
			$string .= " with error: $this->errorMessage";
		}
		$string .= ": ";
		return $string;
	}
	/**
	 * @return string
	 */
	public function getOutputType(): string{
		if($this->outputType === 'pdf'){
			// Large PDF's don't work for some reason
			$this->outputType = 'csv';
		}
		return $this->outputType;
	}
	/**
	 * @return self
	 */
	public static function getFirstWaitingOrStuckExportRequest(): ?self{
		$row = self::whereMeasurementExportRequest('status', self::STATUS_WAITING)->first();
		if(!$row){
			QMLog::info("Checking for stuck exports...");
			$row = self::where('status', self::STATUS_EXPORTING)
                ->where('updated_at', '<', Carbon::now()->subHour(1))
				->first();
		}
		if(!$row){
			QMLog::info("Checking for failures from the last day that haven't been retried in last hour...");
            $row = self::where('status', self::STATUS_ERROR)
                ->where('updated_at', '<', Carbon::now()->subHour())
                ->where('created_at', '>', Carbon::now()->subDay())
                ->first();
		}
		if($row){
			return new self($row);
		}
		QMLog::info("No waiting or stuck export requests!");
		return null;
	}
	/**
	 * @return self[]
	 */
	public static function sendAllWaitingOrStuckExportRequests(): array{
		$total = self::whereMeasurementExportRequest('status', self::STATUS_WAITING)->count();
		QMLog::info("$total waiting export requests");
		$count = 0;
		$req = self::getFirstWaitingOrStuckExportRequest();
		if(!$req){
			return [];
		}
		$arr = [];
		while($req){
			$arr[] = $req;
			$count++;
			QMLog::info("Sending $count of $total waiting export requests");
			$req->createAndEmailSpreadsheet();
			$req = self::getFirstWaitingOrStuckExportRequest();
			if($req){
				sleep(5);
			}
		}
		return $arr;
	}
	/**
	 * @param $userId
	 * @return array
	 */
	public function getSpreadSheetDataForUser($userId): array{
		$measurements = GetMeasurementRequest::getBasicMeasurements($userId);
		return $this->convertMeasurementsToSpreadsheetArray($measurements);
	}
	/**
	 * @return bool
	 */
	public function createBasicCsvZipFromMeasurements(): bool{
        $csvPath = $this->csvFilePath;
        MeasurementExportRequest::exportMeasurementsToCsv($csvPath, $this->getMeasurementsToExport());
        $this->logInfo('Creating ' . $this->zipFilePath . '...');
		$zip = new ZipArchive;
		$result = $zip->open($this->zipFilePath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
		if($result === true){
			$zip->addFile($csvPath, $this->getExportFilename($this->getUserId()) . ".csv");
			// All files are added, so close the zip file.
			$zip->close();
		} else{
			$this->saveMeasurementExportErrorAndThrowException('zip->open result: ' . $result);
		}
		$this->logInfo('Created ' . $this->zipFilePath . '...');
		return true;
	}
	/**
	 * @param $data
	 * @param string $delimiter
	 * @return bool|string
	 */
	public function buildCsv($data, string $delimiter = ';'){
		$tempMemory = fopen('php://memory', 'wb');
		foreach($data as $line){
			fputcsv($tempMemory, $line, $delimiter);
		}
		fseek($tempMemory, 0);
		rewind($tempMemory);
		$contents = stream_get_contents($tempMemory);
		fclose($tempMemory);
		return $contents;
	}
	/**
	 * @param $status
	 */
	private function setExportingStatus($status){
		$this->setStatus($status);
	}
	/**
	 * @return array
	 */
	private function getSpreadSheetData(): array{
		if($this->spreadSheetData){
			return $this->spreadSheetData;
		}
		return $this->spreadSheetData = $this->convertMeasurementsToSpreadsheetArray($this->getMeasurementsToExport());
	}
	/**
	 * @return array|false|string
	 */
	private function getEmailAddress(){
		if(\App\Utils\Env::get('DEBUG_EMAIL')){
			return \App\Utils\Env::get('DEBUG_EMAIL');
		}
		return $this->getQMUser()->email;
	}
	public function getTitleAttribute(): string{
		return "Measurement Export";
	}
	/**
	 * @return \SendGrid\Response
	 */
	public function createAndEmailSpreadsheet(): Response{
		$this->logInfo("Exporting");
		$this->setExportingStatus(self::STATUS_EXPORTING);
		try {
			$this->deleteZipAndCsv();
			$fileToEmailPath = $this->getCsvFilePath();
			$this->createBasicCsvZipFromMeasurements();
			$response =  $this->emailFile($fileToEmailPath);
			$this->deleteZipAndCsv();
			return $response;
		} catch (Exception $e) {
			$errorName = $e->getMessage() . " " . $this->getZipFilePath();
			if(strpos($e->getMessage(), "Permission denied") || strpos($e->getMessage(), "Unable to open")){
				$errorName = "Please run sudo chmod -R 777 /tmp";
			}
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e, ['zipFilePath' => $this->zipFilePath]);
			$this->saveMeasurementExportErrorAndThrowException($errorName, $e->getMessage());
			le($e);
		}
	}
	/**
	 * @return \SendGrid\Response
	 */
	public static function sendExportedMeasurementsForFirstWaitingOrStuckRequest(): ?Response{
		$exportRequest = self::getFirstWaitingOrStuckExportRequest();
		if(!self::getFirstWaitingOrStuckExportRequest()){
			return null;
		}
		if(QMSendgrid::emailedInLast($exportRequest->getEmailAddress(), 3600,
			QMSendgrid::SENT_EMAIL_TYPE_MEASUREMENT_EXPORT)){
			$exportRequest->setExportingStatus(self::STATUS_ALREADY_EMAILED);
			return null;
		}
		return $exportRequest->createAndEmailSpreadsheet();
	}
	/**
	 * @param $id
	 * @return null|MeasurementExportRequest
	 */
	public static function findMeasurementExportRequest($id): ?MeasurementExportRequest{
		$row = self::whereMeasurementExportRequest(self::FIELD_ID, $id)->first();
		if($row){
			return new self($row);
		}
		return null;
	}
	/**
	 * @param array $filesToBeZipped
	 * @param string $pathToZipFile
	 * @param bool $overwrite
	 * @return bool
	 */
	public function createZip(array $filesToBeZipped = [], string $pathToZipFile = '', bool $overwrite = true): bool{
		//if the zip file already exists and overwrite is false, return false
		if(file_exists($pathToZipFile) && !$overwrite){
			return false;
		}
		$valid_files = [];
		if(is_array($filesToBeZipped)){
			foreach($filesToBeZipped as $file){
				if(file_exists($file)){
					$valid_files[] = $file;
				}
			}
		}
		if(count($valid_files)){
			$zip = new ZipArchive();
			$result = $zip->open($pathToZipFile, ZIPARCHIVE::CREATE);
			if($result !== true){
				$errorName = 'Could not open new zip archive! Error ' . $result .
					": Look up error at http://php.net/manual/en/ziparchive.open.php";
				$this->saveMeasurementExportErrorAndThrowException($errorName);
			}
			foreach($valid_files as $pathToFileToAdd){
				$fileNameInsideZip = basename($pathToFileToAdd);
				$zip->addFile($pathToFileToAdd, $fileNameInsideZip);
			}
			$zip->close();
			return file_exists($pathToZipFile);
		}
		return false;
	}
	/**
	 * @param string $filePath
	 * @return \SendGrid\Response
	 */
	public function emailFile(string $filePath): Response{
		$this->logInfo('Emailing ' . $filePath . '...');
		try {
			$email = new MeasurementExportEmail($this, $filePath);
			$response = $email->send();
			$this->setExportingStatus(self::STATUS_FULFILLED);
			$this->logInfo('Emailed ' . $filePath . '...');
			return $response;
		} catch (Exception $e) {
			$this->saveMeasurementExportErrorAndThrowException(__METHOD__.": ".$e->getMessage());
			le($e);
		}
	}
	/**
	 * @param $userId
	 * @return string
	 */
	public function getExportFilename($userId): string{
		return "measurement_export_$userId";
	}
	/**
	 * @param [] $spreadsheetData
	 * @return bool
	 */
	public function createAndEmailXlsOrPdf(){
		$message = 'Got ' . count($this->getSpreadSheetData()) . ' measurements for user ' . $this->getUserId() .
			'. Creating ' . $this->csvFilePath;
		$this->logInfo($message);
		//Too slow
		$userId = $this->getUserId();
		$spreadsheetData = $this->getSpreadSheetData();
		// TODO:: Fix excel writer
		$exportedFile = Excel::create($this->getExportFilename($userId), function($excel) use ($spreadsheetData){
			/** @var LaravelExcelWriter $excel */
			$excel->sheet('Measurements', function($sheet) use ($spreadsheetData){
				/** @var LaravelExcelWorksheet $sheet */
				$sheet->setOrientation('landscape');
				$sheet->fromArray($spreadsheetData);
			});
		})->store($this->outputType, $this->getTmpDirectoryPath(), true);
		$message = 'Created ' . $this->csvFilePath . '. Zipping to ' . $this->zipFilePath . '...';
		$this->logInfo($message);
		echo $message . "\n";
		$fileToEmailPath = $this->csvFilePath;
		@unlink($this->zipFilePath);
		$success = $this->createZip([$exportedFile['full']], $this->zipFilePath, true);
		if($success === false){
			$errorMessage = 'Zip failed';
			$metaData = ['$this->zipFilePath' => $this->zipFilePath];
			$this->logError($errorMessage, $metaData);
		}
		if($success != false){
			$fileToEmailPath = $this->zipFilePath;
			$message = 'Zipped ' . $this->getOutputType() . ' for user ' . $this->getUserId() . '. Emailing now...';
			$this->logInfo($message);
		}
		return $$this->emailFile($fileToEmailPath);
	}
	/**
	 * @param array $measurements
	 * @return array
	 */
	public function convertMeasurementsToSpreadsheetArray(array $measurements): array{
		$i = 0;
		$spreadsheetData = [];
		foreach($measurements as $measurement){
			$i++;
			$spreadsheetData[] = [
				'Value' => $measurement->value,
				'Abbreviated Unit Name' => $measurement->unitAbbreviatedName,
				'Variable Name' => $measurement->variableName,
				'Measurement Event Time' => $this->getQMUser()->convertToLocalTimezone($measurement->startTime)->toDateString(),
				'Note' => $measurement->note,
			];
		}
		if(!count($spreadsheetData)){
			$this->saveMeasurementExportErrorAndThrowException("No measurements obtained for user " . $this->userId);
		}
		return $spreadsheetData;
	}
	/**
	 * @return array
	 */
	private function getSpreadsheetDataForApplication(): array{
		try {
			$client = Application::getClientAppSettings($this->clientId);
		} catch (ClientNotFoundException $e) {
			le($e);
		}
		$users = $client->getUsers();
		$spreadsheetData = [];
		foreach($users as $user){
			$spreadsheetData += $this->getSpreadSheetDataForUser($user->id);
		}
		return $spreadsheetData;
	}
	/**
	 * @return array|bool|false|string
	 */
	private function getTmpDirectoryPath(): string{
		$tempDirectoryPath = storage_path();
		if(\App\Utils\Env::get('CIRCLE_ARTIFACTS')){
			$tempDirectoryPath = \App\Utils\Env::get('CIRCLE_ARTIFACTS');
			echo "Using $tempDirectoryPath for zipped file.  However, we still can't create the zip file on CircleCI :( \n";
		}
		return $tempDirectoryPath;
	}
	/**
	 * @return string
	 */
	private function getZipFilePath(): string{
		return $this->zipFilePath =
			$this->getTmpDirectoryPath() . "/" . $this->getExportFilename($this->userId) . ".zip";
	}
	/**
	 * @return string
	 */
	private function getCsvFilePath(): string{
		return $this->csvFilePath =
			$this->getTmpDirectoryPath() . "/" . $this->getExportFilename($this->userId) . ".{$this->getOutputType()}";
	}
	/**
	 * @return QMMeasurement[]|Collection
	 */
	private function getMeasurementsToExport(): Collection{
		if($this->measurementsToExport !== null){
			return $this->measurementsToExport;
		}
		if($this->type == 'app'){
			$measurements = $this->getSpreadsheetDataForApplication();
		} else{
			$measurements = GetMeasurementRequest::getBasicMeasurements($this->userId);
		}
		if(!count($measurements)){
			$this->saveMeasurementExportErrorAndThrowException("No measurements obtained for user " . $this->userId, '',
				self::STATUS_NO_MEASUREMENTS);
		}
		$this->logInfo("Got " . count($measurements) . " measurements", ['user id' => $this->userId]);
        foreach ($measurements as $m){
            $m->userId = $this->userId;
        }
		return $this->measurementsToExport = $measurements;
	}
	private function deleteZipAndCsv(){
		@unlink($this->getCsvFilePath());
		@unlink($this->getZipFilePath());
	}

    /**
     * @param string $csvPath
     * @param QMMeasurement[]|Collection $measurements
     * @return void
     */
    public static function exportMeasurementsToCsv(string $csvPath, $measurements): void
    {
        @unlink($csvPath);
        $file = fopen($csvPath, 'wb');
        QMLog::info('Creating ' . $csvPath . '...');
        // output the column headings
        fputcsv($file, [
            'Measurement Event Time',
            'Variable Name',
            'Value',
            'Abbreviated Unit Name',
            'Variable Category Name',
            'Note',
        ]);
        foreach ($measurements as $m) {
            if(!isset($u)){
                $u = $m->getUser();
            }
            fputcsv($file, [
                $u->convertToLocalTimezone($m->startTime)->toDateTimeString(),
                $m->variableName,
                $m->value,
                $m->getUnitAbbreviatedName(),
                $m->getVariableCategoryName(),
                $m->getNoteMessage(),
                // Needs to be last because of weird parsing problem
            ]);
        }
    }
}
