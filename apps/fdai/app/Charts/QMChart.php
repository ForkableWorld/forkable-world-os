<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\BaseHighstock;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseMarker;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\ScatterHighchartConfig;
use App\Charts\QMHighcharts\Series;
use App\Exceptions\HighchartExportException;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Files\FileHelper;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Logging\QMLog;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\User\UserIdProperty;
use App\Repos\ResponsesRepo;
use App\Slim\Model\StaticModel;
use App\Storage\S3\S3Helper;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\UI\ImageHelper;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use MikeSinn\HighchartsExporter\HighchartsExport;
use stdClass;
use Throwable;
/** Class QMChart
 * @package App\Charts
 */
abstract class QMChart extends StaticModel {
	private $dynamicHtml;
	protected $exception;
	protected $imageData = [];
	protected $jpgHtml;
	protected $pairs;
	protected $pngHtml; // Keep protected so we don't have a huge user_variable_relationships table
	protected $sourceObject;
	protected $svgHtml;
	protected const FORMAT_MODIFIED_AT = "01-01-2000";
	protected static $modifiedTimes;
	protected $canExport = true;
	public $chartId;
	public $chartTitle;
	public $explanation;
	public $highchartConfig;
	public $id;
	public $imageGeneratedAt;
	public $imageUrl;
	public $jpgUrl;
	public $pngUrl;
	public $subtitle;
	public $svgUrl;
	public $title;
	public $validImageOnS3;
	public $variableName;
	public const CREATE_WP_POSTS = false;
	/**
	 * QMChart constructor.
	 * @param null $chartRow
	 * @param null $sourceObject
	 * @param string|null $title
	 */
	public function __construct($chartRow = null, $sourceObject = null, string $title = null){
		if($title){
			$this->setTitleAndId($title);
		}
		if($sourceObject){
			$this->sourceObject = $sourceObject;
			if($sourceObject->hasId()){
				$this->setImageUrls();
			}
		}
		if($chartRow){
			foreach($chartRow as $key => $value){
				$this->$key = $value;
			}
		}
	}
	/**
	 * @param string $key
	 * @param int $modifiedTime
	 */
	public static function setModifiedTime(string $key, int $modifiedTime): void{
		self::$modifiedTimes[$key] = $modifiedTime;
	}
	public function unsetPrivateAndProtectedProperties(): void{
		$public = ObjectHelper::getPublicPropertiesOfObject($this);
		foreach($this as $key => $value){
			if(!in_array($key, $public, true)){
				unset($this->$key);
			}
		}
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		$title = $this->chartTitle ?? $this->title;
		if(!$title){
			/** @var HighchartConfig $hc */
			if($hc = $this->highchartConfig){
				$title = $hc->getHCTitle()->text;
			}
		}
		if(!$title){
			$title = parent::getTitleAttribute();
		}
		return $this->title = $this->chartTitle = $title;
	}
	/**
	 * @param string $svgOrHtml
	 * @return string
	 */
	public function containsChartTitle(string $svgOrHtml): string{
		$title = $this->getTitleAttribute();
		$title = substr($title, 0, 30); // End of title gets wrapped to new line
		$title = str_replace('&', '&amp;', $title);
		return stripos($svgOrHtml, $title) !== false;
	}
	/**
	 * @param string $chartTitle
	 */
	public function setTitleAndId(string $chartTitle): void{
		$this->setTitle($chartTitle);
		$this->id = $this->chartId = QMStr::slugify($chartTitle);
	}
	public function setTitle(string $title){
		$this->validateTitle($title);
		$this->title = $this->chartTitle = $title;
	}
	/**
	 * @param string $t
	 */
	protected function validateTitle(string $t): void{
		if(strpos($t, ' intake ')){
			le("intake should be uppercase");
		}
	}
	/**
	 * @return string
	 */
	public function getSubtitleAttribute(): string{
		if(!$this->explanation){
			$hc = $this->getHighchartConfig();
			$this->explanation = $hc->getSubtitleAttribute();
		}
		if(!$this->explanation){
			le("Please add explanation to chart model titled " . $this->getTitleAttribute());
		}
		return $this->explanation;
	}
	/**
	 * @param string $explanation
	 */
	public function setExplanation(string $explanation): void{
		$this->explanation = $explanation;
	}
	/**
	 * @return HighchartConfig|stdClass
	 */
	public function getExportableConfig(): \stdClass{
		try {
			$config = $this->getHighchartConfig();
			$config->validate();
		} catch (HighchartExportException $e) {
			le($e);
		}
		/** @noinspection PhpUndefinedVariableInspection */
		if(is_array($config->chart)){
			le('is_array($config->chart)');
		}
		return $config->getExportableConfig($this->getTitleAttribute(), $this->getSubtitleAttribute());
	}
	/**
	 * @return string
	 * @throws HighchartExportException
	 */
	public function saveImageHtmlLocally(): string{
		$html = $this->generateEmbeddedImageHtml(HighchartsExport::PNG);
		$path = ResponsesRepo::saveFile($this->getSlugWithNames() . '.html', $html);
		$url = FileHelper::getStaticUrlForFile($path);
		return $url;
	}
	/**
	 * @return string
	 */
	public function saveDynamicHtmlLocally(): string{
		return $this->getHighchartConfig()->saveHtmlLocally();
	}
	/**
	 * @return HighchartConfig|BaseHighstock
	 */
	public function getHighchartConfig(): HighchartConfig{
		$config = $this->highchartConfig;
		/** @var HighchartConfig|BaseHighstock $config */
		if($config){
			if(isset($config->plotOptions->scatter)){
				$config = ScatterHighchartConfig::instantiateIfNecessary($config);
			} elseif($config->useHighStocks){
				$config = BaseHighstock::instantiateIfNecessary($config);
			} else{
				$config = HighchartConfig::instantiateIfNecessary($config);
			}
			$this->setHighchartConfig($config);
			if($this->chartId){
				$this->setFilename($this->chartId);
			}
		}
		if(!$config){
			try {
				$config = $this->generateHighchartConfig();
			} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
				le($e);
				throw new \LogicException();
			}
			if(is_array($config->chart)){
				le("Chart should not be an array");
			}
		}
		$config->id = $this->getId();
		$config->setExportFileName($this->getId());
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$this->setTheme($config);
		if(is_array($config->chart)){
			le('is_array($config->chart)');
		}
		return $config;
	}
	/**
	 * @param string $filename
	 */
	public function setFilename(string $filename){
		/** @var HighchartConfig $config */
		$config = $this->highchartConfig;
		if(!$config){
			return;
		}
		$config->validate();
		$config->setExportFileName($filename);
	}
	/**
	 * @param string $type
	 * @return mixed
	 * @throws HighchartExportException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getImageUrlAndGenerateIfNecessary(string $type): string{
		$this->setImageUrlsAndGenerateIfNecessaryForType($type);
		try {
			return S3Helper::getUrlForS3BucketAndPath($this->getS3BucketAndFilePath($type));
		} catch (InvalidS3PathException $e) {
			le($e);
		}
	}
	public function getS3BucketAndFilePath(string $extension = null): string{
		return $this->getSourceObject()->getS3BucketAndFolderPath() . $this->getFileName($extension);
	}
	private function setImageUrls(): void{
		if(!$this->canExport()){
			return;
		}
		if(HighchartExport::DEFAULT_IMAGE_FORMAT === HighchartExport::PNG){
			$s3 = $this->getS3BucketAndFilePath(HighchartExport::PNG);
			try {
				$this->imageUrl = $this->pngUrl = S3Helper::getUrlForS3BucketAndPath($s3);
			} catch (InvalidS3PathException $e) {
				le($e);
				throw new \LogicException();
			}
		} else{
			$s3 = $this->getS3BucketAndFilePath(HighchartExport::SVG);
			try {
				$this->imageUrl = $this->svgUrl = S3Helper::getUrlForS3BucketAndPath($s3);
			} catch (InvalidS3PathException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function generateAndUploadImageData(string $type): string{
		if(!$this->canExport){
			throw new HighchartExportException("Cannot export " . static::class, $this);
		}
		$this->validate();
		$this->logDebug(__METHOD__ . " for type $type");
		$this->chartTitle = QMStr::titleCaseSlow($this->getTitleAttribute());
		HighchartExport::shouldGenerate();
		$highchartExport = new HighchartExport($this);
		$response = $highchartExport->generateImageDataAndUploadToS3($type);
		if($type === "svg"){
			$this->svgUrl = $response['url'];
			$this->setImageGeneratedAt($type);
		} elseif($type === "png"){
			$this->pngUrl = $response['url'];
		} elseif($type === "jpg"){
			$this->jpgUrl = $response['url'];
		} else{
			le("Unrecognized type $type");
		}
		$this->validImageOnS3 = true;
		return $this->imageData[$type] = $response['data'];
	}
	/**
	 * @param string $type
	 * @return string
	 */
	public function getImageGeneratedAt(string $type): ?string{
		if(!isset($this->imageGeneratedAt[$type])){
			return null;
		}
		return $this->imageGeneratedAt[$type];
	}
	/**
	 * @param string $type
	 */
	public function setImageGeneratedAt(string $type): void{
		$this->imageGeneratedAt[$type] = now_at();
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		return $this->getS3BucketAndFilePath(null) . ": ";
	}
	/**
	 * @param string $type
	 * @return bool
	 */
	public function hasValidImageOnS3(string $type): bool{
		if($this->validImageOnS3 !== null){
			return $this->validImageOnS3;
		}
		$maxAgeInSeconds = $this->getMaxAgeInSeconds();
		return $this->validImageOnS3 =
			S3Helper::existsAndNotExpired($this->getS3BucketAndFilePath($type), $maxAgeInSeconds);
	}
	/**
	 * @return string
	 */
	public function getPngUrl(): string{
		//        if(!self::PNG_ENABLED){
		//            QMLog::error("PNG's disabled because Google likes SVG's!");
		//        }
		return $this->pngUrl;
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function generateEmbeddedImageHtml(string $type): string{
		$this->generateAndUploadImageData($type);
		return $this->getOrGenerateEmbeddedImageHtml($type);
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function getOrGenerateEmbeddedImageHtml(string $type): string{
		if($type === HighchartExport::PNG && $this->pngHtml){
			return $this->pngHtml;
		}
		if($type === HighchartExport::SVG && $this->svgHtml){
			return $this->svgHtml;
		}
		if($type === HighchartExport::JPG && $this->jpgHtml){
			return $this->jpgHtml;
		}
		try {
			$data = $this->getOrGenerateImageData($type);
			$elementId = QMStr::slugify($this->getTitleAttribute());
			$imageHtml = ImageHelper::imageDataToHtml($type, $data, $this->getAltText(), $this->getTitleAttribute(), $elementId);
			$html = "
                <div id='$elementId-container'>
                    $imageHtml
                </div>
            ";
			QMStr::errorIfLengthGreaterThan($html, "$elementId $type", 500);
			if($type === HighchartExport::JPG){
				return $this->jpgHtml = $html;
			}
			if($type === HighchartExport::PNG){
				return $this->pngHtml = $html;
			}
			if($type === HighchartExport::SVG){
				return $this->svgHtml = $html;
			}
			le("wrong type $type");
		} catch (TooSlowToAnalyzeException | NotEnoughDataException $e) {
			return $this->getFailedChartHtml($e->getMessage());
		}
	}
	/**
	 * @param bool $includeJS
	 * @return string
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getDynamicHtml(bool $includeJS = true): string{
		$config = $this->getOrSetHighchartConfig();
		$config->setTitle($this->getTitleAttribute());
		$config->setSubtitle($this->getSubtitleAttribute());
		$this->setTheme($config);
		$html = $config->getHtml($includeJS);
		return $this->dynamicHtml[$config->getId()] = $html;
	}
	/**
	 * @return string
	 */
	public function getDataQuantityOrTrackingInstructionsHTML(): string{
		$s = $this->getSourceObject();
		return $s->getDataQuantityOrTrackingInstructionsHTML();
	}
	public function getImageDataIfSet(string $type): ?string{
		if(isset($this->imageData[$type])){
			return $this->imageData[$type];
		}
		return null;
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 * @throws NotEnoughDataException
	 * @noinspection PhpDocRedundantThrowsInspection
	 */
	public function getOrGenerateImageData(string $type): string{
		if($data = $this->getImageDataIfSet($type)){
			return $data;
		}
		$this->logDebug(__FUNCTION__ . " for type $type");
		if($this->getIsPublic()){
			$data = $this->getImageDataFromS3IfNotExpired($type);
		} else{
			$data = null;
		}
		if(!$data){
			$data = $this->generateAndUploadImageData($type);
		}
		if(QMChart::isHighchartErrorPng($data)){
			throw new HighchartExportException("Highchart export error!", $this);
		}
		return $this->imageData[$type] = $data;
	}
	/**
	 * @param string $str
	 * @return bool
	 */
	public static function isHighchartErrorPng(string $str): bool{
		return stripos($str,
				"data:image/png;base64,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") !==
			false;
	}
	/**
	 * @param mixed $sourceObject
	 */
	public function setSourceObject($sourceObject): void{
		if(!$sourceObject){
			le("No source provided! ");
		}
		if($sourceObject instanceof QMChart){
			le("Source should not be a chart! ");
		}
		$this->sourceObject = $sourceObject;
	}
	/**
	 * @return string
	 */
	public function getVariableName(): ?string{
		if(!$this->variableName){
			$this->variableName = $this->getSourceObject()->getOrSetVariableDisplayName();
		}
		return $this->variableName;
	}
	/**
	 * @param string $variableName
	 */
	public function setVariableName(string $variableName): void{
		$this->variableName = $variableName;
	}
	public function validate(): void{
		if($highchart = $this->highchartConfig){
			$config = HighchartConfig::instantiateIfNecessary($highchart);
			$this->setHighchartConfig($config);
		}
		if(!$this->getTitleAttribute()){
			le('!$this->getTitleAttribute()');
		}
	}
	/**
	 * @param Throwable $exception
	 * @return QMChart
	 */
	public function setException(Throwable $exception): self{
		$this->exception = $exception;
		return $this;
	}
	/**
	 * @return Throwable
	 */
	public function getException(): Throwable{
		return $this->exception;
	}
	/**
	 * @param HighchartConfig $highchartConfig
	 * @return HighchartConfig
	 */
	public function setHighchartConfig(HighchartConfig $highchartConfig): HighchartConfig{
		$highchartConfig->validate();
		$highchartConfig->setQmChart($this);
		return $this->highchartConfig = $highchartConfig;
	}
	public function canExport(): bool{
		return $this->canExport;
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getShowContent(bool $inlineJs = false): string{
		return $this->getDynamicHtml($inlineJs);
	}
	protected function setCss(string $css){
		$this->getHighchartConfig()->setCss($css);
	}
	/**
	 * @return string
	 */
	private function getAltText(): string{
		return $this->getSubtitleAttribute(); // Don't use Title here because there's already a title attribute on the image HTML
	}
	/**
	 * @param string $type
	 * @return string
	 */
	public function getLinkedImageHtml(string $type): string{
		try {
			$url = $this->getImageUrlAndGenerateIfNecessary($type);
		} catch (HighchartExportException | NotEnoughDataException | TooSlowToAnalyzeException $e) {
			return $this->getFailedChartHtml(__METHOD__.": ".$e->getMessage());
		}
		if(empty($imageUrl)){
			le("Missing image url!");
		}
		$id = $this->getId();
		return '
            <div id="' . $id . '-image-section"
                style="text-align: center; max-width: 100%;">
                <img id="' . $id . '-image"
                    class="chart-img"
                    style="' . ImageHelper::CHART_IMAGE_STYLES . '"
                    src="' . $imageUrl . '"
                    alt="' . $this->getAltText() . '"
                    title="' . $this->getTitleAttribute() . '"/>
            </div>';
	}
	/**
	 * @param string $errorMessage
	 * @return string
	 */
	private function getFailedChartHtml(string $errorMessage): string{
		$this->logError($errorMessage);
		$title = $this->getTitleAttribute();
		$html = "<h3>$title</h3>";
		$html .= "<p>Could not generate $title chart because $errorMessage</p>";
		$html .= "<div>" . $this->getDataQuantityOrTrackingInstructionsHTML() . "</div>";
		$html .= HtmlHelper::getHelpInstructionsHTML();
		return "
            <div>
                $html
            </div>
            ";
	}
	/**
	 * @param string $type
	 * @return bool
	 * @throws HighchartExportException
	 * @throws TooSlowToAnalyzeException
	 * @throws NotEnoughDataException
	 */
	public function setImageUrlsAndGenerateIfNecessaryForType(string $type): bool{
		$this->setImageUrls();
		if($this->hasValidImageOnS3($type)){
			return false;
		}
		return $this->generateAndUploadImageData($type);
	}
	/**
	 * @return bool
	 */
	protected function getIsPublic(): ?bool{
		return $this->getSourceObject()->getIsPublic();
	}
	/**
	 * @return QMVariable|UserVariableRelationship|GlobalVariableRelationship|QMUserVariable|QMCommonVariable
	 */
	public function getSourceObject(){
		if(!$this->sourceObject){
			le("No source object!");
		}
		return $this->sourceObject;
	}
	/**
	 * @return string
	 */
	public function getS3BucketAndFolderPath(): string{
		$source = $this->getSourceObject();
		if($source instanceof stdClass){
			le("source should not be stdClass");
		}
		return $source->getS3BucketAndFolderPath();
	}
	/**
	 * @param string $type
	 * @return false|int|null
	 */
	public function getAgeOfImageInSeconds(string $type): ?int{
		if($generatedAt =
			$this->getImageGeneratedAt($type)){ // Faster because less S3 requests and more accurate because getAgeOfChartImageOnS3 gets old cached image
			$age = time() - strtotime($generatedAt);
		} else{
			$age = S3Helper::getSecondsSinceLastModified($this->getS3BucketAndFilePath($type));
		}
		return $age;
	}
	/**
	 * @param string $type
	 * @return string
	 */
	public function getUrlForImage(string $type): string{
		try {
			return S3Helper::getUrlForS3BucketAndPath($this->getS3BucketAndFilePath($type));
		} catch (InvalidS3PathException $e) {
			le($e);
		}
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws FileNotFoundException
	 */
	public function getImageDataFromS3(string $type): string{
		return S3Helper::get($this->getS3BucketAndFilePath($type));
	}
	/**
	 * @param string $type
	 * @return string|null
	 */
	public function getImageDataFromS3IfNotExpired(string $type): ?string{
		$age = $this->getAgeOfImageInSeconds($type);
		if(!$age){
			return null;
		}
		if($age > HighchartExport::LIFETIME){
			return null;
		}
		$newestDataAt = $this->getNewestDataAt();
		$timeSinceNewestData = time() - strtotime($newestDataAt);
		if($age > $timeSinceNewestData){
			return null;
		}
		try {
			$data = $this->getImageDataFromS3($type);
			return $data;
		} catch (FileNotFoundException $e) {
			le($e);
		}
	}
	/**
	 * @return HighchartConfig
	 * @throws TooSlowToAnalyzeException
	 * @throws NotEnoughDataException
	 */
	abstract public function generateHighchartConfig(): HighchartConfig;
	/**
	 * @return HighchartConfig|BaseHighstock
	 * @throws TooSlowToAnalyzeException
	 * @throws NotEnoughDataException
	 */
	public function getOrSetHighchartConfig(): HighchartConfig{
		$config = $this->highchartConfig;
		if(!$config){
			$config = $this->generateHighchartConfig();
			$config->validate();
		}
		if($config->useHighStocks){
			if(!$config instanceof BaseHighstock){
				$config = BaseHighstock::instantiateIfNecessary($config);
				$config->validate();
			}
		} else{
			if(!$config instanceof HighchartConfig){
				$config = HighchartConfig::instantiateIfNecessary($config);
				$config->validate();
			}
		}
		if($t = $this->chartTitle){
			$config->setTitle($t);
		}
		$config->validate();
		return $this->setHighchartConfig($config);
	}
	/**
	 * @return string
	 */
	public function getId(): string{
		if(!$this->id){
			$this->id = $this->chartId;
		}
		if(!$this->chartId){
			$this->chartId = $this->id;
		}
		$id = $this->id;
		if(!$id){
			$id = QMStr::slugify($this->getTitleAttribute());
		}
		if(!$id){
			le("No id on " . get_class($this));
		}
		$this->setFilename($id);
		return $id;
	}
	/**
	 * @return string|null
	 */
	public function getIdWithNames(): ?string{
		return $this->getId();
	}
	/**
	 * @param string|null $extension
	 * @param bool $useNames
	 * @return string
	 */
	public function getFileName(string $extension = null, bool $useNames = false): string{
		if($useNames){ // We always use names in the id here already. Might want to have option for numeric ids later  to avoid SEO issues on name changes
			$id = $this->getIdWithNames();
		} else{
			$id = $this->getId();
		}
		if($id){
			$filename = $id . '-' . $this->getSlugifiedClassName();
		} else{
			$filename = $this->getSlugifiedClassName();
		}
		if($extension){
			return $filename . '.' . $extension;
		}
		return $filename;
	}
	/**
	 * @return int
	 */
	protected function getMaxAgeInSeconds(): int{
		$maxAge = $this->getSourceObject()->getMaxAgeInSeconds();
		$secondsSinceFormatModified = TimeHelper::secondsAgo(static::FORMAT_MODIFIED_AT);
		if($secondsSinceFormatModified < $maxAge){
			$maxAge = $secondsSinceFormatModified;
		}
		return $maxAge;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		$src = $this->getSourceObject();
		if(isset($src->userId)){
			return $src->userId;
		}
		return UserIdProperty::USER_ID_SYSTEM;
	}
	/**
	 * @return string
	 */
	public function getUniqueIndexIdsSlug(): string{
		$slug = $this->getSourceObject()->getUniqueIndexIdsSlug() . "-" . $this->getTypeSlug();
		if(strlen($slug) > 199){
			le("Max slug length is 200 but is $slug");
		}
		return $slug;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return $this->getPngUrl();
	}
	/**
	 * @return string
	 */
	public function getCategoryDescription(): string{
		return "Data Visualizations";
	}
	/**
	 * @return string|null
	 */
	public function getParentCategoryName(): ?string{
		return "Charts";
	}
	/**
	 * @return string|null
	 */
	public function getCategoryName(): string{
		return QMStr::classToTitle($this->getShortClassName());
	}
	/**
	 * @return string
	 */
	public function getTypeSlug(): string{
		$type = $this->getSlugifiedClassName();
		return $type;
	}
	public function getNewestDataAt(): ?string{
		return $this->getSourceObject()->getNewestDataAt();
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function export(string $type = HighchartsExport::PNG): string{
		return $this->generateAndUploadImageData($type);
	}
	public function shrink(){
		$before = round(strlen(json_encode($this)) / 1024);
		foreach($this as $key => $value){
			if(stripos($key, 'html') !== false){
				$this->$key = null;
			}
		}
		$kb = round(strlen(json_encode($this)) / 1024);
		$this->logInfo("$before KB before and $kb KB AFTER shrinkage");
	}
	/**
	 * @param array|object $arrayOrObject
	 * @return static
	 */
	public static function instantiateIfNecessary(array|object|string $arrayOrObject){ // For debugging break points
		if(get_class($arrayOrObject) === static::class){
			return $arrayOrObject;
		}
		/** @var QMChart $chart */
		$chart = parent::instantiateIfNecessary($arrayOrObject);
		if($config = $chart->highchartConfig){
			$config = HighchartConfig::instantiateIfNecessary($config);
			/** @var HighchartConfig $config */
			unset($config->loading->style); // Why is this necessary?
			foreach($config->series as $s){
				if(!isset($s->marker)){
					$s->marker = new BaseMarker();
				}
			}
			$hc = $chart->setHighchartConfig($config);
			if(!isset($hc->chart->renderTo)){
				$hc->setRenderTo($chart->getId());
			}
			if(empty($hc->title->text)){
				$hc->setTitle($chart->getTitleAttribute());
			}
			if(empty($hc->subtitle->text)){
				$hc->setSubtitle($chart->getSubtitleAttribute());
			}
			if(isset($hc->loading->style)){
				le('isset($hc->loading->style)');
			}
		}
		return $chart;
	}
	/**
	 * @return Factory|View
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function getMaterialView(){
		return $this->getOrSetHighchartConfig()->getMaterialView();
	}
	/**
	 * @return mixed
	 */
	public function generateCSVs(): array{
		$series = $this->getSeries();
		$paths = [];
		foreach($series as $one){
			$data[] = $one->data;
			$paths[] = QMSpreadsheet::convertToCsvFile($one->data,
				'tmp/' . $this->getId() . "-series-" . $one->getId() . ".csv");
		}
		return $paths;
	}
	/**
	 * @return Series[]
	 */
	public function getSeries(): array{
		try {
			return $this->getHighchartConfig()->series;
		} catch (NotEnoughDataException $e) {
			/** @var \LogicException $e */
			throw $e;
		} catch (TooSlowToAnalyzeException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
	}
	/**
	 * @return array
	 */
	public function getData(): array{
		/** @var Series $series */
		$series = $this->getHighchartConfig()->series;
		$data = [];
		foreach($series as $one){
			$data[] = $one->data;
		}
		return $data;
	}
	public function setHeight(int $pixels): QMChart{
		try {
			$conf = $this->getHighchartConfig();
		} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
			/** @var \LogicException $e */
			throw $e;
		}
		$conf->getChart()->height = $pixels;
		return $this;
	}
	public function getTooltip(): BaseTooltip{
		return $this->getHighchartConfig()->getTooltip();
	}
	public function setTooltip(BaseTooltip $tt): BaseTooltip{
		return $this->getHighchartConfig()->tooltip = $tt;
	}
	protected function setTheme(HighchartConfig $config){ // Override this for individual charts if necessary
		$config->setWhiteBackgroundTheme();
	}
	/**
	 * @return string[]
	 */
	public function getKeyWords(): array{
		$keywords = $this->getSourceObject()->getKeyWords();
		$keywords[] = $this->getTitleAttribute();
		$type = ucfirst($this->getChartType())." Chart";
		$keywords[] = $this->getTitleAttribute()." ".$type;
		$keywords[] = $this->getHighchartConfig()->getTitleAttribute();
		$keywords[] = $this->getSourceObject()->getTitleAttribute() ." ".$type;
		return array_values(array_unique($keywords));
	}
	/**
	 * @param mixed ...$args
	 * @return string
	 */
	public static function generateInline(...$args): ?string{
		$c = new static(...$args);
		try {
			$config = $c->getOrSetHighchartConfig();
			$config->assertHasData($args[0]);
			$c->setTheme($config);
			return $config->inlineWithHeading($c->getSourceObject());
		} catch (TooSlowToAnalyzeException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return null;
		} catch (NotEnoughDataException $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	/**
	 * @param mixed ...$args
	 * @return string
	 */
	public static function generateScriptTag(...$args): ?string{
		$c = new static(...$args);
		try {
			$config = $c->getOrSetHighchartConfig();
			$c->setTheme($config);
			return $config->urlTagDiv($c->getSourceObject());
		} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return null;
		}
	}
	/**
	 * @param mixed ...$args
	 * @return string
	 */
	public static function generateCardHtml(...$args): ?string{
		$c = new static(...$args);
		try {
			$html = $c->getDynamicHtml(false);
			return HtmlHelper::renderView(view('tailwind-card', [
				'content' => $html,
			]));
		} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return null;
		} catch (Throwable $e) {
			le($e);
		}
	}
	public function hasData(): bool{
		try {
			return $this->getOrSetHighchartConfig()->hasData();
		} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
			return false;
		}
	}
	public function getKeyWordString(): string{
		$keywords = $this->getKeyWords();
		return QMStr::generateKeyWordString($keywords);
	}
	/**
	 * @return string
	 */
	private function getChartType(): string{
		return $this->getHighchartConfig()->getType();
	}
}
