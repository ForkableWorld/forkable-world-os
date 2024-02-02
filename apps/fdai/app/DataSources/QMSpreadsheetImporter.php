<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Buttons\QMButton;
use App\Buttons\States\ImportStateButton;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\SecretException;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Connector;
use App\Models\Unit;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Slim\Model\QMUnit;
use App\Storage\S3\S3PrivateUsers;
use App\Traits\HardCodable;
use App\Types\ObjectHelper;
use App\Types\QMArr;
use App\Utils\UrlHelper;
use App\Variables\QMVariable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Exceptions\MimeTypeNotAllowed;
class QMSpreadsheetImporter extends QMDataSource {
    use HardCodable;
    public $connected = false;
    public $connectStatus;
    public $dataSourceType = self::TYPE_spreadsheet_upload;
    public $lastSuccessfulUpdatedAt;
    public $message;
    public $qmClient = false;
    public $spreadsheetUploadLink;
    public $updateError;
    public $updateRequestedAt = false;
    public $updateStatus = SpreadsheetImportRequest::STATUS_NEVER_UPLOADED;
    public const LARAVEL_CLASS = Connector::class;
    public $platforms = [
	    BasePlatformProperty::PLATFORM_CHROME,
	    BasePlatformProperty::PLATFORM_WEB
    ];
    /**
     * @param null $row
     */
    public function __construct($row = null){
        $this->clientId = static::NAME;
        parent::__construct($row);
        $this->message = $this->longDescription;
        $this->getSpreadSheetUploadLink();
        $this->getLinkedDisplayNameHtml();
        $this->addImageHtml();
        $this->setDefaultButtons();
    }
    /**
     * @return string
     */
    public static function getHardCodedDirectory(): string{
        return FileHelper::absPath("app/DataSources/SpreadsheetImporters");
    }
    /**
     * @return QMButton[]
     */
    public function setDefaultButtons(): array{
        if($this->userId){
            return $this->buttons = [$this->getSpreadSheetUploadButton()];
        }
        return $this->buttons = [new ImportStateButton()];
    }
    /**
     * @return string
     */
    private function getSpreadSheetUploadLink(): string{
        $url = UrlHelper::getApiUrlForPath('v2/spreadsheetUpload');
	    $url = BaseClientIdProperty::addToUrlIfNecessary($url);
	    $url = BaseAccessTokenProperty::addToUrlIfNecessary($url);
        return $this->spreadsheetUploadLink = $url;
    }
	/**
	 * @param SpreadsheetImportRequest $request
	 */
    public function setWaitingSpreadsheetProperties(SpreadsheetImportRequest $request){
        $this->updateRequestedAt = $request->getCreatedAt();
        $this->message = "Import scheduled at ".$request->getCreatedAt();
        $this->buttons[0] = new QMButton("Import Scheduled", null, null, "ion-clock");
    }
    /**
     * @param SpreadsheetImportRequest $request
     */
    public function setErroredSpreadSheetProperties(SpreadsheetImportRequest $request){
        $this->updateError = $request->getErrorMessage();
        $this->connectStatus = SpreadsheetImportRequest::STATUS_ERROR;
        $this->updateStatus = SpreadsheetImportRequest::STATUS_ERROR;
        if(!empty($this->updateError) && stripos($this->message, $this->updateError) === false){
	        $this->errorMessage = $this->message = $this->updateError;
        }
    }
    /**
     * @param SpreadsheetImportRequest $request
     */
    public function setRequest(SpreadsheetImportRequest $request){
	    QMLog::debug(__METHOD__);
        $status = $this->updateStatus = $request->getStatus();
        if($status === SpreadsheetImportRequest::STATUS_ERROR){
            $this->setErroredSpreadSheetProperties($request);
        }
        if($status === SpreadsheetImportRequest::STATUS_UPDATED){
            $this->lastSuccessfulUpdatedAt = $request->getUpdatedAt();
            if($request->getErrorMessage()){
                $this->message = $request->getErrorMessage();
            }
        }
        if($status === SpreadsheetImportRequest::STATUS_WAITING){
            $this->setWaitingSpreadsheetProperties($request);
        }
    }
    public function importData(): void {
        // TODO: Implement update() method.
    }
    /**
     * @param QMVariable|\App\Models\Variable $variable
     * @param array $urlParams
     * @return string
     */
    public function setInstructionsHtml($variable, array $urlParams = []): string {
        $paragraph = '<p>
            Upload your exported spreadsheet data from '.$this->getLinkedDisplayNameHtml().
            ' from the <a href="'.$this->getConnectWebPageUrl($urlParams).'">Import Data page</a>.
            </p>';
	    $variableName = $variable->getOrSetVariableDisplayName();
	    $variableImage = $variable->getVariableAvatarImageHtml(6);
	    $sourceName = $this->getDisplayNameAttribute();
	    $getItButton = $this->getGetItHereButton();
	    $getItButton->setImage($this->getImage());
	    $connectButton = $this->getSpreadSheetUploadButton();
	    $connectButton->setTextAndTitle("Click here to upload your spreadsheet");
		$connectButton->setImage($this->getImage());
		$helpText = $this->getLongDescription();
	    $importPill = $connectButton->getChipSmall();
	    $paragraph = "
<div class='tracking-instructions' id='$this->name-tracking-instructions'>
	<h3 class='text-2xl font-extrabold dark:text-white'>
	$variableImage
	$this->displayName Upload Option
	</h3>
	<p class='my-4 text-xl text-gray-500'>
	$helpText
	</p>
	<p class='my-4 text-xl text-gray-500'>
	$importPill
	</p>
	
</div>";
	    return $this->instructionsHtml = $paragraph;
    }
    /**
     * @param array $params
     * @return QMSpreadsheetImporter[]
     */
    public static function get(array $params = []): array{
        $path = self::getHardCodedDirectory();
        /** @var static[] $importers */
        $importers = ObjectHelper::instantiateAllModelsInFolder("SpreadsheetImporter", $path);
        $importers = QMArr::filter($importers, $params);
		foreach($importers as $importer){
			static::$ID_TO_NAME[$importer->id] = $importer->name;
		}
        return $importers;
    }
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
        return S3PrivateUsers::encryptAndUploadSpreadsheetToS3($userId, $uploadedFile, $sourceName);
    }
    /**
     * @return BaseModel|Connector
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function l(){
        return $this->attachedOrNewLaravelModel();
    }
    protected function generateFileContentOfHardCodedModel(): string{
        // TODO: Implement generateFileContentOfHardCodedModel() method.
    }
	/**
	 * @param $unit
	 * @return bool
	 */
	private function inAllowedUnits($unit): bool {
        $unit = QMUnit::find($unit);
        return in_array($unit->id, $this->getAllowedUnitIds());
    }
    public function getAllowedUnitIds(): array {
        return Arr::pluck(QMUnit::get(), 'id');
    }
    /**
     * @return array
     */
    private function getAllowedUnits(): array {
        $allowed = [];
        foreach($this->getAllowedUnitIds() as $id){
            $allowed[] = QMUnit::find($id);
        }
        return $allowed;
    }
    private function getAllowedUnitNames(): array {
        return Arr::pluck($this->getAllowedUnits(), Unit::FIELD_NAME);
    }
	/**
	 * @param $unit
	 */
	public function validateUnit($unit){
		if(!$this->inAllowedUnits($unit)){
		    le( QMUnit::find($unit)->name." is not in allowed units for $this. \n\t".
                $this->getAllowedUnitsList());
		}
    }
    private function getAllowedUnitsList():string{
        return "Allowed Units:\n\t-".implode("\n\t-", $this->getAllowedUnitNames());
    }
	/**
	 * @return QMButton
	 */
	private function getSpreadSheetUploadButton(): QMButton{
		return new QMButton("Upload Spreadsheet", $this->getSpreadSheetUploadLink(), null, "ion-upload");
	}
}
