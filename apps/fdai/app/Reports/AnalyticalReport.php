<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Reports;
use App\Buttons\QMButton;
use App\Computers\ThisComputer;
use App\DevOps\XDebug;
use App\Exceptions\InvalidS3PathException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\SecretException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Files\FileHelper;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Logging\QMLog;
use App\Logging\QMLogger;
use App\Mail\QMSendgrid;
use App\Models\Vote;
use App\Models\WpPost;
use App\Properties\User\UserIdProperty;
use App\Repos\StudiesRepo;
use App\Slim\Model\Slack\SlackAttachment;
use App\Slim\Model\Slack\SlackMessage;
use App\Slim\Model\StaticModel;
use App\Slim\Model\User\QMUser;
use App\Slim\Model\User\UserMeta;
use App\Storage\S3\S3Helper;
use App\Storage\S3\S3Images;
use App\Storage\S3\S3Private;
use App\Storage\S3\S3Public;
use App\Studies\QMStudy;
use App\Traits\HasFiles;
use App\Traits\HasModel\HasUser;
use App\Traits\PostableTrait;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\UI\HtmlHelper;
use App\UI\QMColor;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LogicException;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Spatie\MediaLibrary\HasMedia;
use Throwable;
abstract class AnalyticalReport extends StaticModel implements HasMedia {
    public const TITLE = null;
    use PostableTrait, HasUser, HasFiles;
    protected bool $writeToPdf = false;
    public const DEMO_USER_ID = UserIdProperty::USER_ID_MIKE;
    protected $bodyHtml;
    protected $mpdf;
    protected $pdfPath;
    protected array $sections = [];
    protected $xlsxPath;
    protected $blade;
    protected const DISABLED_FILE_TYPES = [];
    public $emailHtml;
    public $userId;
	/**
	 * @var string
	 */
	public $title;
    public const DEVELOPMENT_CSS_URL = IonicHelper::IONIC_DEV_ORIGIN."/css/";
    public const EXAMPLE_PDF = "https://images.quantimo.do/root-cause-analysis/root-cause-analysis-overall-mood-example.pdf";
    public const FILE_TYPE_CSV = 'csv';
    public const FILE_TYPE_EMAIL_HTML = 'email.html';
    public const FILE_TYPE_HTML = 'html';
    public const FILE_TYPE_PDF = 'pdf';
    public const FILE_TYPE_XLS = 'xls';
    public const FORMAT_LAST_MODIFIED_AT = "2019-08-01";
    public const MAX_AGE_IN_DAYS = 30;
    //public const PRODUCTION_CSS_URL = "https://static.quantimo.do/css/";
    public const STATIC_ASSETS_ORIGIN = S3Public::S3_CACHED_ORIGIN;
    public const PRODUCTION_CSS_URL = self::STATIC_ASSETS_ORIGIN."/css/";
    public const CSS_PATHS = [
        //"https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css",  // What is this for?  It messes up lists!
        "https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css",
        self::STATIC_ASSETS_ORIGIN."/lib/sortable/css/sortable-theme-minimal.css",
        self::STATIC_ASSETS_ORIGIN."/wp-content/themes/twentytwenty/style.css?ver=5.3.2",
        self::STATIC_ASSETS_ORIGIN."/wp-content/plugins/wp-reactions-child/assets/css/common.css",
        self::PRODUCTION_CSS_URL . "medium-study.css",
        self::PRODUCTION_CSS_URL . "statistics-table.css",
        self::PRODUCTION_CSS_URL . "wp-button.css",
    ];
    protected const JS_PATHS = [
        "https://static.quantimo.do/lib/sortable/js/sortable.js",
    ];
    public const AVAILABLE_FILE_TYPES = [
        self::FILE_TYPE_EMAIL_HTML,
        self::FILE_TYPE_HTML
    ];
	public static function getDemoByClassName(string $shortClassName): AnalyticalReport{
        /** @var static $class */
        $class = str_replace("AnalyticalReport", $shortClassName, self::class);
        return $class::getDemoReport();
    }
    /**
     * @return void
     */
    public function generatePDF(): void{
        throw new LogicException(__METHOD__." not implemented! ");
    }
    /**
     * @return string
     */
    abstract public function generateBodyHtml(): string;
    /**
     * @return string
     */
    public function generateAndSavePdfLocally(): string{
        //DesignRepo::cloneOrPullIfNecessary();
        $this->logInfo("Generating PDF...");
        $this->generatePDF();
        $this->logInfo("Saving PDF locally...");
        return $this->savePdfLocally();
    }
    /**
     * @return string
     */
    public function savePdfLocally(): string{
        $mpdf = $this->getMpdf();
        $mpdf->title = $this->getTitleAttribute();
        return FileHelper::MPDFtoFILE($mpdf, $this->getPdfFileName(), $this->getLocalShowFolderPath());
    }
    protected function getPdfFileName():string{
        return $this->getFileName(self::FILE_TYPE_PDF);
    }
    /**
     * @return int
     */
    public function getUserId(): ?int {
        if(!is_int($this->userId)){
            le("user id not int");
        }
        return $this->userId;
    }
    /**
     * @return bool
     */
    public function isWriteToPdf(): bool {
        return $this->writeToPdf;
    }
    /**
     * @param bool $writeToPdf
     */
    public function setWriteToPdf(bool $writeToPdf): void {
        $this->writeToPdf = $writeToPdf;
    }
    /**
     * @return string
     */
    public function getOrGenerateHtmlWithHead(): string{
        if($this->existsAndNotExpired(self::FILE_TYPE_HTML)){
            try {
                return S3Helper::getDataForS3BucketAndPath($this->getS3HtmlPath());
            } catch (FileNotFoundException $e) {
                le(__METHOD__.": ".$e->getMessage());
            }
        }
        return $this->generateAndUploadHtmlAndPost();
    }
    public function getS3HtmlPath():string{
        $path = $this->getS3BucketAndFolderPath()."/index.html";
        try {
            S3Helper::validateS3BucketAndPath($path);
        } catch (InvalidS3PathException $e) {
            le($e);
        }
        return $path;
    }
    /**
     * @return string
     */
    public function getOrGenerateEmailHtml(): string{
        if($this->emailHtml){return $this->emailHtml;}
        // Avoid getting outdated versions
//        if($this->existsAndNotExpired(self::FILE_TYPE_EMAIL_HTML)){
//            try {
//                return $this->getDataForType(self::FILE_TYPE_EMAIL_HTML);
//            } catch (FileNotFoundException $e) {
//                le($e);
//            }
//        }
        return $this->generateAndUploadEmailHtml();
    }
    /**
     * @return string
     */
    public function generateHtmlWithHead(): string{
        if($this->blade){
            return HtmlHelper::renderView(view($this->blade, ['report' => $this]));
        } else {
            return HtmlHelper::renderReportWithoutTailwind($this->getShowContent(), $this, ['report' => $this]);
        }
    }
    /**
     * @return string
     */
    public function getPlainText(): string{
        return $this->getSubtitleAttribute();
    }
    /**
     * @return array
     */
    abstract protected function getSpreadsheetRows(): array;
    /**
     * @param string $extension
     * @return string|null
     * @throws FileNotFoundException
     */
    public function getOrDownloadFile(string $extension): ?string{
	    return S3Private::getLocalOrDownload($this->getS3FilePath($extension),
	        $this->getMaxAgeInSeconds());
    }
	public function deletePDF(): bool{
		$this->deleteLocal(self::FILE_TYPE_PDF);
		return $this->deleteFromS3(self::FILE_TYPE_PDF);
	}
	/**
	 * @param string $extension
	 * @return bool
	 */
	public function deleteFromS3(string $extension): bool {
		return S3Private::delete($this->getS3FilePath($extension));
	}
	/**
	 * @param string $extension
	 * @return bool
	 */
	public function deleteLocal(string $extension): bool {
		return S3Private::delete($this->getS3FilePath($extension));
	}
    /**
     * @param string $extension
     * @return string|null
     * @throws FileNotFoundException
     */
    public function getFileContents(string $extension): ?string{
        return S3Private::get($this->getS3FilePath($extension));
    }
    /**
     * @param string $extension
     * @return string|null
     * @throws FileNotFoundException
     */
    public function getFileContentsIfNotExpired(string $extension): ?string{
        $existsAndNotExpired = $this->existsAndNotExpired($extension);
        if($existsAndNotExpired){
            return $this->getFileContents($extension);
        }
        return null;
    }
    /**
     * @param string $extension
     * @return string
     */
    public function getDemoS3FilePath(string $extension): string{
        $path = $this->getFileName($extension);
        $path = str_replace('users/230', 'examples', $path);
        $path = str_replace('-user-230', '', $path);
        $path = 'demo/'.$this->replaceUserNamesWithDemoUserName($path);
        return $path;
    }
    /**
     * @return string
     */
    public function getDemoHtmlFilePath(): string{
        return $this->getDemoS3FilePath(self::FILE_TYPE_HTML);
    }
    /**
     * @return string
     */
    public function getDemoUrl(): string {
        return qm_url("demo/".static::getSlugifiedClassName());
    }
    /**
     * @param string|null $extension
     * @return string
     */
    public function getFileName(string $extension): string{
        $obj = $this->getSourceObject();
        return $obj->getSlugWithNames()."-".
	        $this->getSlugifiedClassName()."."
	        .$extension;
    }
    public function getShowFolderPath():string{
        return $this->getUser()->getShowFolderPath()."/".static::getSlugifiedClassName();
    }
    /**
     * @return string
     */
    private function getLocalShowFolderPath(): string{
        return S3Private::convertS3PathToLocalPath($this->getShowFolderPath());
    }
    public function toWord(){
        $html = $this->generateHtmlWithHead();
        $filename = $this->getUniqueIndexIdsSlug();
        FileHelper::toWord($html, $filename, 'tmp/root-cause-analyses');
    }
    public function getSlug(): string{
        return $this->getUniqueIndexIdsSlug();
    }
    /**
     * @param string $extension
     * @return string
     */
    public function getDownloadOrCreateFile(string $extension): string {
        try {
            $path = $this->getOrDownloadFile($extension);
            if($path){return $path;}
        } catch (FileNotFoundException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
        }
        if ($extension === self::FILE_TYPE_PDF) {
            $localAbsolutePath = $this->generateAndUploadPdf();
        } elseif ($extension === self::FILE_TYPE_XLS) {
            $localAbsolutePath = $this->generateAndUploadXls();
        } elseif ($extension === self::FILE_TYPE_HTML || $extension === self::FILE_TYPE_EMAIL_HTML) {
            $localAbsolutePath = $this->generateAndUploadHtmlAndPost();
        } else {
            le("$extension not found!");
        }
        return $localAbsolutePath;
    }
	public static function getS3Bucket(): string{
		return S3Private::getBucketName();
	}
    /**
     * @return \Mpdf\Mpdf
     */
    public function getMpdf(): \Mpdf\Mpdf{
        if($this->mpdf){return $this->mpdf;}
	    try {
		    $mpdf = new Mpdf();
	    } catch (Throwable $e) {
		    le($e);
	    }
	    $mpdf->setLogger(QMLogger::get());
	    $mpdf->title = $this->getTitleAttribute(); // Need to do this again before output because HTML can overwrite it
	    $mpdf->debug = true; // I think this causes output to fail when there's a PHP warning
	    return $this->mpdf = $mpdf;
    }
    /**
     * @return string
     */
    public function getTitleHTML(): string {
        // Don't use username to reduce risk of HIPAA violation or in case it's being used as a demo
        return "<h1 style='text-align: center;' class='page-title p-name'>".$this->getTitleAttribute()."</h1>";
    }
    /**
     * @return string
     */
    public function generateAndUploadPdf(): string {
        $localAbsolutePath = $this->generateAndSavePdfLocally();
        try {
            $s3Path = S3Helper::uploadPDF($this->getPdfPath(),
                    $localAbsolutePath, false);
        } catch (SecretException $e) {
            le($e);
        }
        return $this->pdfPath = $localAbsolutePath;
    }
    public function getPdfPath():string{
        return  $this->getS3BucketAndFolderPath()."/".$this->getPdfFileName();
    }
    /**
     * @param string $extension
     * @return string
     */
    protected function getValidDemoS3Path(string $extension): ?string {
        $maxAgeInSeconds = $this->getMaxAgeInSeconds();
        $relativePath = $this->getDemoS3FilePath($extension);
        $existsAndNotExpired = S3Images::existsAndNotExpired($relativePath, $maxAgeInSeconds);
        if($existsAndNotExpired){return $relativePath;}
        return null;
    }
    /**
     * @param string $extension
     * @return bool
     */
    protected function existsAndNotExpired(string $extension): bool{
        //if(AppMode::isTestingOrStaging()){return false;}
        if($this->getSourceObject()->needToAnalyze()){return false;}
        $maxAgeInSeconds = $this->getMaxAgeInSeconds();
        $relativePath = $this->getS3FilePath($extension);
        $existsAndNotExpired = S3Private::existsAndNotExpired($relativePath, $maxAgeInSeconds);
        return $existsAndNotExpired;
    }
    /**
     * @return string
     */
    public function generateAndUploadXls(): ?string{
        if (in_array(self::FILE_TYPE_XLS, static::DISABLED_FILE_TYPES)) {
            le("XLS disabled!");
        }
        if($this->xlsxPath){return $this->xlsxPath;}
        $rows = $this->getSpreadsheetRows();
        if (!$rows) {
            $this->logError("No rows for spreadsheet!");
            return null;
            //le("No rows for spreadsheet!");
        }
        $filename = $this->getUniqueIndexIdsSlug();
        $folder = $this->getLocalShowFolderPath();
        try {
            $localPath = QMSpreadsheet::writeToSpreadsheet($rows, $filename, $folder);
            try {
                S3Private::uploadPDF($this->getS3FilePath(self::FILE_TYPE_XLS), $localPath);
            } catch (SecretException $e) {
                le($e);
            }
            return $this->xlsxPath = $localPath;
        } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
            le($e);
        }
    }
    protected function addIndex(): void{
        if (!$this->writeToPdf) {
            le("writeToPdf is false!");
        }
        $mpdf = $this->getMpdf();
        $mpdf->SetHeader("Index");
        $mpdf->AddPage();
        try {
            $mpdf->WriteHTML("<h1>Index</h1>");
        } catch (MpdfException $e) {
            le($e);
        }
        $mpdf->InsertIndex(1, true);
    }
    protected function addTableOfContents(): void{
        if (!$this->writeToPdf) {
            $this->logInfo("Need to implement table of contents for HTML");
            return;
        }
        $this->getMpdf()->TOCpagebreakByArray([
            'tocfont'          => '',
            'tocfontsize'      => '',
            'tocindent'        => '',
            'TOCusePaging'     => true,
            'TOCuseLinking'    => true,
            'toc_orientation'  => '',
            'toc_mgl'          => '',
            'toc_mgr'          => '',
            'toc_mgt'          => '',
            'toc_mgb'          => '',
            'toc_mgh'          => '',
            'toc_mgf'          => '',
            'toc_ohname'       => '',
            'toc_ehname'       => '',
            'toc_ofname'       => '',
            'toc_efname'       => '',
            'toc_ohvalue'      => 0,
            'toc_ehvalue'      => 0,
            'toc_ofvalue'      => 0,
            'toc_efvalue'      => 0,
            'toc_preHTML'      => '<h1>Table of Contents</h1>',
            'toc_postHTML'     => '',
            'toc_bookmarkText' => '',
            'resetpagenum'     => '',
            'pagenumstyle'     => '',
            'suppress'         => '',
            'orientation'      => '',
            'mgl'              => '',
            'mgr'              => '',
            'mgt'              => '',
            'mgb'              => '',
            'mgh'              => '',
            'mgf'              => '',
            'ohname'           => '',
            'ehname'           => '',
            'ofname'           => '',
            'efname'           => '',
            'ohvalue'          => 0,
            'ehvalue'          => 0,
            'ofvalue'          => 0,
            'efvalue'          => 0,
            'toc_id'           => 0,
            'pagesel'          => '',
            'toc_pagesel'      => '',
            'sheetsize'        => '',
            'toc_sheetsize'    => '',
        ]);
    }
    /**
     * @param QMUserVariable $v
     */
    protected function addVariableOverview(QMUserVariable $v) {
        $title = $v->getReportTitleAttribute();
        $this->addHeaderNewPageTOCIndexEntry($title, 1);
        $variableOverview = $v->getChartAndTableHTML(true);
        $variableOverview = str_replace('<?xml version="1.0" standalone="no"?>', '', $variableOverview);
        $this->addHtml('<div style="text-align: center;">'.$variableOverview.'</div>');
    }
    /**
     * @param string $html
     */
    public function addToBodyHtml(string $html) {
        $this->bodyHtml .= $html;
        if(XDebug::active() || AppMode::isTestingOrStaging()){
            try {
                HtmlHelper::validateHtml($this->bodyHtml, __FUNCTION__);
            } catch (InvalidStringException $e) {
                le($e);
            }
        }
    }
    /**
     * @param string $html
     * @param int $mode
     * @param bool $init
     * @param bool $close
     */
    public function addHtml(string $html, int $mode = HTMLParserMode::DEFAULT_MODE, bool $init = true, bool $close = true){
        if(XDebug::active() || AppMode::isTestingOrStaging()){HtmlHelper::validateHtml($html, __FUNCTION__);}
        if ($this->isWriteToPdf()) {
            //$html = ImagesRepo::replaceImageUrlsWithLocalPaths($html);
            try {
                self::validatePdfHtml($html);
            } catch (InvalidStringException $e) {
                le($e);
            }
            try {
                $this->getMpdf()->WriteHTML($html, $mode, $init, $close);
            } catch (MpdfException $e) {
                try {
					//$this->getMpdf()->debug = true;
                    $this->getMpdf()->WriteHTML($html, $mode, $init, $close);
                } catch (MpdfException $e) {
                    le("Could not write this html:\n".$html."\nbecause:\n".$e->getMessage());
                }
            }
        } else {
            $this->addToBodyHtml($html);
        }
    }
    /**
     * @param string $content
     * @throws InvalidStringException
     */
    public static function validatePdfHtml(string $content){
        QMStr::assertStringDoesNotContain($content, [
            UrlHelper::getLocalUrl(),
	        ThisComputer::LOCAL_HOST_NAME,
            "AWS_STAGING"
        ], __FUNCTION__);
        QMStr::assertStringContains($content, [], __FUNCTION__);
        HtmlHelper::validateHtml($content, static::class);
    }
    /**
     * @return QMSendgrid
     */
    public function email(): QMSendgrid{
        $u = $this->getQMUser();
        return $u->sendEmail($this->getTitleAttribute(), $this->generateHtmlWithHead(), true);
    }
    /**
     * @return string
     */
    public function generateAndUploadHtmlAndPost(): string {
        $html = $this->generateHtmlWithHead();
        $u = $this->getQMUser();
        $s3FilePath = $this->getS3FilePath(self::FILE_TYPE_HTML);
        if($this->getIsPublic()){
            S3Private::uploadHTML($s3FilePath, $html);
        } else {
            S3Public::uploadHTML($s3FilePath, $html);
        }
        StudiesRepo::writeToFile($s3FilePath, $html);
        //$this->postToWordPress();
        $u->setUserMetaValue(UserMeta::LAST_REPORT, $this->getS3FilePath(self::FILE_TYPE_HTML));
        $this->slack();
        return $html;
    }
    /**
     * @return string
     */
    public function generatePostContent(): string {
        $html = $this->generateBodyHtml();
        try {
            $this->validateHtml($html);
        } catch (InvalidStringException $e) {
            le($e);
        }
        return $html;
//        try {
//            return view('wp.posts.wp-report-post', ['report' => $this])->render();
//        } catch (Throwable $e) {
//            le($e);
//        }
    }
    /**
     * @return string
     */
    public function generateHtmlBodyWithInlineCss(): string {
        $htmlWithClasses = $this->generateBodyHtml();
        $inline = $this->inlineCss($htmlWithClasses);
        return $inline;
    }
    /**
     * @return string
     */
    public function generateAndUploadEmailHtml(): string {
        $html = $this->generateEmailBody();
        try {
            $this->validateEmailHtml($html);
        } catch (InvalidStringException $e) {
            le($e);
        }
        if ($this->getUserId() === UserIdProperty::USER_ID_MIKE) {
            S3Public::uploadHTML($this->getDemoS3FilePath(self::FILE_TYPE_EMAIL_HTML), $html);
        }
        S3Private::uploadHTML($this->getS3FilePath(self::FILE_TYPE_EMAIL_HTML), $html);
        $this->slack();
        return $this->emailHtml = $html;
    }
    public static function emailAllUsers(){
        le("Need to implement ".__METHOD__);
    }
    /**
     * @param string $extension
     * @return string
     */
    public function getUrlForFile(string $extension): string{
        $path = $this->getS3FilePath($extension);
        try {
            $url = S3Helper::getUrlForS3BucketAndPath(S3Private::getBucketName() . '/' . $path);
        } catch (InvalidS3PathException $e) {
            le($e);
        }
        return $url;
    }
    /**
     * @param string $text
     * @return string
     */
    public function getStaticHtmlButton(string $text): string {
        $button = new QMButton();
        $button->setTextAndTitle($text);
        $button->setUrl($this->getUrl());
        return $button->getRectangleWPButton();
    }
    public function getShowUrl(array $params = []): string{
        return qm_url($this->getShowFolderPath(), $params);
    }
    public function generateHtmlPdfXlsAndUploadToS3AndPostIfNecessary(): void {
        $source = $this->getSourceObject();
        $needToAnalyze = $source->needToAnalyze();
        if($needToAnalyze){
            try {
                $source->analyzeFullyIfNecessary(__FUNCTION__);
            } catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
                le($e);
            }
        }
        if ($needToAnalyze || !$this->existsAndNotExpired(self::FILE_TYPE_HTML)) {
            $this->generateAndUploadHtmlAndPost();
        }
        if ($needToAnalyze || !$this->existsAndNotExpired(self::FILE_TYPE_PDF)) {
            $this->generateAndUploadPdf();
        }
        if(!in_array(self::FILE_TYPE_XLS, static::DISABLED_FILE_TYPES)){
            if ($needToAnalyze || !$this->existsAndNotExpired(self::FILE_TYPE_XLS)) {
                $this->generateAndUploadXls();
            }
        }
    }
    /**
     * @param string $extension
     * @return string
     */
    public function getUrlAndGenerateIfNecessary(string $extension): string {
        if (!$this->existsAndNotExpired($extension)) {
            if ($extension === self::FILE_TYPE_HTML || $extension === self::FILE_TYPE_EMAIL_HTML) {
                $this->generateAndUploadHtmlAndPost();
            }
            if ($extension === self::FILE_TYPE_PDF) {
                $this->generateAndUploadPdf();
            }
            if ($extension === self::FILE_TYPE_XLS) {
                $this->generateAndUploadXls();
            }
        }
        return $this->getUrlForFile($extension);
    }
    /**
     * @param string $title
     * @param int|null $tocLevel
     * @param string|null $icon
     */
    public function addHeaderNewPageTOCIndexEntry(string $title, int $tocLevel, string $icon = null): void {
        if ($this->writeToPdf) {
            $mpdf = $this->getMpdf();
            $mpdf->SetHeader($title);
            if($tocLevel < 2){
                $mpdf->AddPage();
            }
            $mpdf->TOC_Entry($title, $tocLevel);
            $mpdf->IndexEntry($title);
        }
        $imageHtml = '';
        if($icon){$imageHtml = HtmlHelper::getImageHtmlWithSize(Vote::NO_VOTE_IMAGE, 16, 16, $title, 
                                                                "float: left;");}
        $h = $tocLevel + 1;
        $this->addHtml("<h$h class='text-xl'> $imageHtml $title </h$h> ");
    }
    /**
     * @return string
     */
    protected function getCss(): string {
        $paths = static::CSS_PATHS;
        $css = '';
        foreach ($paths as $PATH) {
            $css .= '
            <link rel="stylesheet" href="' . $PATH . '"/>
            ';
        }
        return $css;
    }
    /**
     * @return QMVariable|QMUser|QMStudy
     */
    abstract public function getSourceObject();
    /**
     * @return int
     */
    public function getMaxAgeInSeconds(): int{
        $lastModified = static::FORMAT_LAST_MODIFIED_AT;
        $maxAge = time() - strtotime($lastModified);
        $fromSource = $this->getSourceObject()->getMaxAgeInSeconds();
        if($fromSource < $maxAge){
            $maxAge = $fromSource;
        }
        return $maxAge;
    }
    public function slack(){
        if(AppMode::isTestingOrStaging()){
            return;
        }
        $m = new SlackMessage($this->getTitleAttribute());
        $m->withIcon(":bar_chart:");
        $attachments = $this->getSlackAttachments();
        foreach($attachments as $a){
            $m->attach($a);
        }
        $m->send($this->getTitleAttribute()."\n".$this->getSubtitleAttribute());
    }
    /**
     * @return string
     */
    abstract public function generateEmailBody(): string;
    /**
     * @return SlackAttachment[]
     */
    public function getSlackAttachments(): array{
        $attachments = [];
        $attachments[] = new SlackAttachment([
            'title_link' => $this->getUrlForFile(self::FILE_TYPE_EMAIL_HTML),
            'title'      => "Email HTML",
            'color'      => 'good'
        ]);
        $attachments[] = new SlackAttachment([
            'title_link' => $this->getUrlForFile(self::FILE_TYPE_HTML),
            'title'      => "Full HTML",
            'color'      => 'good'
        ]);
        $attachments[] = new SlackAttachment([
            'title_link' => $this->getUrlForFile(self::FILE_TYPE_PDF),
            'title'      => "PDF",
            'color'      => 'good'
        ]);
        return $attachments;
    }
    /**
     * @return \App\Models\Button[]
     */
    public function getButtons(): array {
        $buttons = [];
        $attachments = $this->getSlackAttachments();
        foreach($attachments as $a){
            $buttons[] = $a->toButton();
        }
        return $buttons;
    }
    /**
     * @return string
     */
    public function getFullHtmlUrlLink(): string {
        return $this->getUrlForFile(self::FILE_TYPE_HTML);
    }
    /**
     * @return string
     */
    public function getPdfUrl(): string {
        return $this->getUrlForFile(self::FILE_TYPE_PDF);
    }
    /**
     * @return string
     */
    public function getEmailHtmlUrlLink(): string {
        return $this->getUrlForFile(self::FILE_TYPE_EMAIL_HTML);
    }
    /**
     * @return string
     */
    abstract public function getCoverImage(): string ;
    /**
     * @return string[]
     */
    public function generateStaticFilesIfNecessary(): array {
        $urls = [];
        foreach(static::AVAILABLE_FILE_TYPES as $extension){
            $urls[] = $this->getUrlAndGenerateIfNecessary($extension);
        }
        return $urls;
    }
    /**
     * @return array
     */
    public function getFileUrls(): array {
        return $this->generateStaticFilesIfNecessary();
    }
    /**
     * @param array $params
     * @return string
     */
    public function getUrl(array $params = []): string{
        $url = $this->getShowUrl();
        if(!$params){return $url;}
        return UrlHelper::addParams($url, $params);
    }
    /**
     * @return string|null
     */
    public function getParentCategoryName(): ?string {
        return WpPost::PARENT_CATEGORY_REPORTS;
    }
    /**
     * @return string
     */
    public function getUniqueIndexIdsSlug(): string {
        return $this->getSourceObject()->getUniqueIndexIdsSlug()."-".static::getSlugifiedClassName();
    }
    /**
     * @param string $html
     * @return string
     */
    protected function inlineCss(string $html): string{
        $cssPathsOrUrls = static::CSS_PATHS;
        $inline = CssHelper::inlineCssFromPathsOrUrls($cssPathsOrUrls, $html);
        return $inline;
    }
    /**
     * @return string
     */
    public function getTitleDescriptionHeaderHtml(): string {
        $title = $this->getTitleAttribute();
        $description = $this->getSubtitleAttribute();
        return "
            <header>
                <h1 class='factors-title'>
                    $title
                </h1>
                <p>$description </p>
            </header>";
    }
    /**
     * @return string|null
     */
    protected function getHtmlWithHeadIfNotExpired(): ?string {
        try {
            return $this->getFileContentsIfNotExpired(self::FILE_TYPE_HTML);
        } catch (FileNotFoundException $e) {
            return null;
        }
    }
    /**
     * @return bool|null
     */
    public function deleteWpPost(): ?bool{
        $p = $this->findWpPost();
        if(!$p){
            $this->logInfo("No post found to delete");
            return false;
        }
        return $p->forceDelete();
    }
    /**
     * @param string $html
     * @throws InvalidStringException
     */
    protected function validateHtml(string $html): void{
        QMStr::assertStringContains($html, [
            //"users/$this->userId"  // Why should it contain this string?
        ], $this->getShortClassName());
    }
    /**
     * @param string $html
     * @throws InvalidStringException
     */
    protected function validateEmailHtml(string $html): void{
        $this->validateHtml($html);
        QMStr::assertStringDoesNotContain($html, CssHelper::CLASS_ROUNDED_BUTTON_WITH_IMAGE,
            "email needs tables not buttons");
    }
    protected function addCoverPage(): void{
        $img = $this->getImageHTML();
        $title = $this->getTitleHTML();
        $intro = $this->getIntroSentenceHTML();
        $this->addHtml("
            <div>
                $img
                $title
                $intro
            </div>
        ");
    }
    /**
     * @return string
     */
    protected function getIntroSentenceHTML():string {
        return "<p>".$this->getSubtitleAttribute()."</p>";
    }
    protected function addIntroduction(): void{
        $this->addHeaderNewPageTOCIndexEntry("Introduction", 1);
        $this->addHtml($this->getIntroSectionHTML());
    }
    /**
     * @return string
     */
    protected function getIntroSectionHTML(): string {
        return HtmlHelper::renderView(view('root-cause-intro'));
    }
    public function addYourDataSection() {
        $title = "Your Data";
        $this->addHeaderNewPageTOCIndexEntry($title, 1);
        $u = $this->getQMUser();
        if(!$this->isWriteToPdf()){
            $buttons = $u->getDataQuantityListRoundedButtonsHTML();
            $this->addHtml($buttons);
        }
        $table = $u->getDataQuantityTableHTML();
        $this->addHtml("
            <div style='text-align: center; margin: auto;'>
                $table
            </div>"
        );
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    public static function columnDefinitionsHTML(string $outcomeVariableName = "outcome"): string {
        return
            static::getReviewDefinitionHtml().
            static::getConfidenceDefinition().
            static::getAssociationDefinition($outcomeVariableName).
            static::getChangeColumnExplanation($outcomeVariableName).
            static::getRsdDefinitionHTML($outcomeVariableName).
            static::getBaselineDefinitionHTML($outcomeVariableName).
            static::getCorrelationExplanation($outcomeVariableName).
            static::getStudyLinkDefinition()
            ;
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    protected static function getChangeColumnExplanation(string $outcomeVariableName = "outcome"): string{
        $html = "<h3>Change Column</h3>".
            "<p>This is the typical amount of change seen in $outcomeVariableName".
            " when a particular variable ".
            "is above average relative to when it is below average.  For example, if it says <i>&uarr;10%</i> in the Multivitamin ".
            "column, it means that your $outcomeVariableName is 30% higher after you take a Multivitamin. ".
            "To be specific, this refers to the time during the specified <i>duration of action</i> ".
            "following the <i>onset delay</i> following consumption of the vitamins.  Conversely, if it says <i>&darr;10%</i> in the Multivitamin ".
            "column, it means that your $outcomeVariableName".
            " is 30% higher after you take a Multivitamin (relative to the days when vitamins were not ".
            "consumed). ".
            "</p>";
        return $html;
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    public static function getBaselineDefinition(string $outcomeVariableName = "outcome"): string {
        return "Baseline refers to the $outcomeVariableName measurements taken when the predictor values in the preceding duration of ".
            "action window were below average. For instance, if the predictor is a medication, this would refer to ".
            "the period in which you were not taking the medication. ";
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    public static function getBaselineDefinitionHTML(string $outcomeVariableName = "outcome"): string {
        return "<h3>Baseline</h3>".
            "<p>".AnalyticalReport::getBaselineDefinition($outcomeVariableName)
            ."</p>";
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    public static function getRsdDefinitionHTML(string $outcomeVariableName = "outcome"): string{
        $html = "<h3>RSD Column</h3>".
            "<p>RSD refers to the relative standard deviation of the $outcomeVariableName measurements at baseline.  ".
            AnalyticalReport::getBaselineDefinition().
            "If the absolute value of the change is more than the RSD, there is 68% chance that this change is not solely due to random fluctuation.  ".
            "If the absolute value of the change is more than twice the RSD, there is 95% chance that this change is not solely due to random fluctuation.  ".
            "</p>";
        return $html;
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    public static function getAssociationDefinition(string $outcomeVariableName = "outcome"): string{
        $html = "<h3>Association Column</h3>".
            "<p>This shows the degree to which change seen in $outcomeVariableName".
            " coincides with or is preceded by changes in a given variable.</p>";
        return $html;
    }
    /**
     * @return string
     */
    public static function getConfidenceDefinition(): string{
        $html = "<h3>Confidence Column</h3>".
            "<p>This is just a subjective estimate of how seriously we may want to take the analysis
            based on several factors such as the amount of data available for the analysis.</p>";
        return $html;
    }
    /**
     * @return string
     */
    public static function getStudyLinkDefinition(): string{
        $html = "<h3>Study Column</h3>".
            "<p>Click the <b>Study</b> link for any variable to see a more detailed analysis of the relationship.</p>";
        return $html;
    }
    /**
     * @param string $outcomeVariableName
     * @return string
     */
    protected static function getCorrelationExplanation(string $outcomeVariableName = "outcome"): string{
        $html = "<p><b>UserVariableRelationship</b> - The correlation coefficient represents is the degree to which change seen in ".
            $outcomeVariableName.
            " coincides with changes in a given variable ".
            ".  The value can range from -1 to 1.  </p>".
            "<ul class='list-disc'>
                <li>".
            "<b>Positive UserVariableRelationship</b> - The closer to 1, the more likely it is that above average values for the variable ".
            " will coincide with ABOVE average ".$outcomeVariableName.".".
            "</li>
                <li>".
            "<b>Negative UserVariableRelationship</b> - The closer to -1, the more likely it is that above average values for a particular variable ".
            " will coincide with BELOW average ".$outcomeVariableName.".".
            "</li>
                <li>".
            "<b>No UserVariableRelationship</b> - The closer to 0, the more likely it is that the variable ".
            " and ".$outcomeVariableName." are not related. ".
            "</li>
            </ul>";
        return $html;
    }
    protected function addDefinitionsSection(): void{
        $this->addHeaderNewPageTOCIndexEntry("Definitions", 1);
        $this->addHtml($this->getDefinitionsHtml());
    }
    /**
     * @return string
     */
    public function getDefinitionsHtml():string {
        return static::columnDefinitionsHTML();
    }
    /**
     * @return string
     */
    public static function getReviewDefinitionHtml(): string{
        $html =
            "<h3>Review Column</h3>".
            "<p>
                Your puny human brains aren't worthless... yet.
                So it's best for you and your physician review the detailed analysis for each relationship
                and identify any issues such as:
             </p>
            <ul class='list-disc'>
                <li>erroneous data</li>
                <li>missing data</li>
                <li>incorrect assumed duration of action</li>
                <li>incorrect assumed onset delay</li>
                <li>incorrect or missing ingredient tags</li>
            </ul>
            <p>
                The Review column in each table is either marked as Verified, Unverified, or Erroneous. It links to
                a full analysis of the relationship.  If it's Unverified, you can click it to review the data and make an assessment.
                If the analysis appears acceptable, click the Thumbs Up button in the study and it will be marked
                Verified on future reports.  If the analysis appears flawed,
                either you can do one of the following:
            </p>
            <ul class='list-disc'>
                <li>correct it within the app</li>
                <li>contact us at <a href='https://help.quantimo.do'>https://help.quantimo.do</a> for assistance</li>
                <li>click the Thumbs Down button and it will be moved from this list to the Flagged Studies list in future reports</li>
            </ul>
        ";
        return $html;
    }
    /**
     * @return string
     */
    public function getImageHTML(): string {
        $color = QMColor::HEX_DARK_BLUE;
        $img = '<div style="text-align: center;" class="page-image">'.
            HtmlHelper::getImageHtml($this->getImage(), $this->getTitleAttribute(), "width: 100%;", "entry-feature-image u-photo") .
            '</div>';
        $html = "
            <div style=\"text-align: center; background-color: $color\">
                $img
            </div>
        ";
        return $html;
    }
    /**
     * @return AnalyticalReport
     */
    abstract public static function getDemoReport(): AnalyticalReport;
    public function publish(){
        $this->generateAndUploadHtmlAndPost();
        //$this->postToWordPress();
        //$this->updatePostStatus(BasePostStatusProperty::STATUS_PUBLISH);
    }
    public function setWpPostIdAndSave(int $id){
        QMLog::info("TODO: Implement model for reports");
    }
    /**
     * @return AnalyticalReport
     */
    static public function publishDemoReport(): AnalyticalReport {
        $a = static::getDemoReport();
        $html = $a->getOrGenerateDemoHtmlWithHead();
        $a->publish();
        $url = S3Public::uploadHTML($a->getDemoS3FilePath(self::FILE_TYPE_HTML), $html, false);
        $a->logInfo("Published demo report at $url");
        return $a;
    }
    /**
     * @return string
     */
    protected function getOrGenerateDemoHtmlWithHead(): string {
        $html = $this->getOrGenerateHtmlWithHead();
        $html = $this->replaceUserNamesWithDemoUserName($html);
        return $html;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->title = static::TITLE;
    }
    /**
     * @return string
     */
    protected function getUserDisplayName(): string {
        return $this->getQMUser()->getDisplayNameAttribute();
    }
    /**
     * @param string $html
     * @return string|string[]
     */
    protected function replaceUserNamesWithDemoUserName(string $html){
        $u = $this->getQMUser();
        $html = str_replace($u->getDisplayNameAttribute(), "Super Dude", $html);
        $html = str_replace(QMStr::slugify($u->getDisplayNameAttribute()), "super-dude", $html);
        $html = str_replace($u->getLoginName(), "super-dude", $html);
        return $html;
    }
    public function exceptionIfWeShouldNotPost(): void{
        // TODO: Implement exceptionIfWeShouldNotPost() method.
    }
    public static function wherePostable(){
        // TODO: Implement wherePostable() method.
    }
    public function getBody(): string{
        return $this->generateBodyHtml();
    }
    public function getShowContent(bool $inlineJs = false): string {
        return $this->getBody($inlineJs);
    }
    /**
     * @return string[]
     */
    public function getKeyWords(): array{
        return $this->getSourceObject()->getKeyWords();
    }
    public function getHtmlContent(): string{
        return $this->getBody();
    }
    public function getSlugWithNames(): string{
        return $this->getUniqueIndexIdsSlug();
    }
    protected function setUserId(int $id){
        $this->userId = $id;
    }
    public function getIcon(): string{
        return $this->getImage();
    }
	public function getIsPublic(): ?bool{return false;}
	public function getNameAttribute(): string{return $this->getTitleAttribute();}
	public static function getUniqueIndexColumns(): array{return ['title', 'userId'];}
	public function getTags(): array{
		$tags = $this->getSourceObject()->getTags();
		$tags[] = static::getClassNameTitle();
		return $tags;
	}
}
