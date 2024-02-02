<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Buttons\QMButton;
use App\Charts\QMHighcharts\HighchartConfig;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\HighchartExportException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\Model\DBModel;
use App\Slim\Model\StaticModel;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use MikeSinn\HighchartsExporter\HighchartsExport;
abstract class ChartGroup extends StaticModel {
	protected $sourceObject;
	protected $failed = [];
	/**
	 * ChartGroup constructor.
	 * @param $sourceObject
	 */
	public function __construct($sourceObject = null){
		if($sourceObject){
			$this->sourceObject = $sourceObject;
		}
	}
	public function unsetPrivateAndProtectedProperties(){
		foreach($this as $key => $chart){
			if(!is_object($chart)){
				continue;
			}
			/** @var QMChart $chart */
			$chart->unsetPrivateAndProtectedProperties();
		}
	}
	/**
	 * @param string $imageType
	 * @return bool
	 * @throws TooSlowToAnalyzeException
	 * @throws NotEnoughDataException
	 */
	public function setImageUrlsAndGenerateIfNecessary(string $imageType): bool{
		$charts = $this->getChartsArrayWithSourceObject();
		foreach($charts as $chart){
			try {
				$chart->setImageUrlsAndGenerateIfNecessaryForType($imageType);
			} catch (HighchartExportException $e) {
				$this->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
		return true;
	}
	/**
	 * @return bool
	 */
	public function highchartsPopulated(): bool{
		$arr = $this->getChartsArray();
		foreach($arr as $chart){
			if(!$chart->highchartConfig){
				$chart->logDebug("no highchartConfig on $chart");
				return false;
			}
		}
		return true;
	}
	/**
	 * @return QMChart[]
	 */
	abstract public function getChartsArray(): array;
	/**
	 * @return string
	 */
	public function getErrorsHtml(): string{
		$html = '';
		if($failed = $this->getFailed()){
			$solutions = $exceptions = [];
			$titlesHtml = "
<div id='chart-errors' style='max-width: 600px; margin: auto; text-align: center;'>
    <h3>Could Not Generate the Following Charts</h3>
    <ul style='text-align: left;'>
";
			foreach($failed as $chart){
				$titlesHtml .= "\t\t<li>" . $chart->getTitleAttribute() . "</li>\n";
				$e = $chart->getException();
				$exceptions[] = $e;
				$solutions[] = ExceptionHandler::toUserSolution($e);
			}
			$titlesHtml .= "
    </ul>
</div>
";
			$eHtml = ExceptionHandler::renderExceptions($exceptions);
			$sHtml = ExceptionHandler::renderSolutions($solutions);
			$html = $titlesHtml . $eHtml . $sHtml;
		}
		$source = $this->getSourceObject();
		$html .= $source->getInvalidSourceDataHtml();
		if(AppMode::isUnitOrStagingUnitTest()){
			HtmlHelper::validateHtml($html, __FUNCTION__);
		}
		return $html;
	}
	/**
	 * @return QMChart[]
	 */
	public function getFailed(): array{
		return $this->failed;
	}
	/**
	 * @param DBModel|BaseModel $sourceObject
	 */
	public function setSourceObject($sourceObject): void{
		$this->sourceObject = $sourceObject;
		$this->addSourceObjectToCharts();
	}
	/**
	 * @return bool
	 */
	protected function getIsPublic(): bool{
		return $this->getSourceObject()->getIsPublic();
	}
	/**
	 * @return QMVariable|QMUserVariableRelationship|QMGlobalVariableRelationship|QMUserVariable|QMCommonVariable
	 */
	protected function getSourceObject(){
		return $this->sourceObject;
	}
	/**
	 * @param string $type
	 * @return string
	 */
	public function getChartHtmlWithLinkedImages(string $type = HighchartExport::DEFAULT_IMAGE_FORMAT): string{
		$html = '';
		$chartsArray = $this->getChartsArrayWithSourceObject();
		if(!$chartsArray){
			return '';
		}
		foreach($chartsArray as $chart){
			$html .= $chart->getLinkedImageHtml($type);
		}
		return "
            <div class=\"study-charts\" style=\"margin: auto; text-align: center;\">
                   $html
            </div>
        ";
	}
	/**
	 * @param bool $includeJS
	 * @return string
	 */
	public function getHtmlWithDynamicCharts(bool $includeJS): string{
		$html = '';
		$chartsArray = $this->getChartsArrayWithSourceObject();
		if(!$chartsArray){
			return '';
		}
		foreach($chartsArray as $chart){
			try {
				$html .= $chart->getDynamicHtml(false);
			} catch (NotEnoughDataException | TooSlowToAnalyzeException $e) {
				$this->failed[] = $chart->setException($e);
			}
		}
		$html .= $this->getErrorsHtml();
		if($includeJS){
			$js = HighchartConfig::getHighchartsJsScriptTags();
		} else{
			$js = '';
		}
		return "
            $js
            <div class=\"study-charts\"
                style=\"margin: auto; text-align: center;\">
               $html
            </div>
        ";
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function getChartHtmlWithEmbeddedImageOrReasonForFailure(string $type = HighchartExport::DEFAULT_IMAGE_FORMAT): string{
		$html = '';
		$chartsArray = $this->getChartsArray();
		if(!$chartsArray){
			return '';
		}
		$previousImageData = null;
		foreach($chartsArray as $chart){
			if(!$chart->canExport()){
				$chart->logInfo("TODO: Implement export for " . get_class($chart));
				continue;
			}
			$one = $chart->getOrGenerateEmbeddedImageHtml($type);
			$imageData = $chart->getImageDataIfSet($type);
			if($imageData && $previousImageData && $imageData === $previousImageData){
				le("Duplicate image data!");
			}
			$previousImageData = $imageData;
			$html .= $one;
		}
		$html .= $this->getErrorsHtml();
		$id = $this->getId();
		$html = "
            <div id='$id'
                class=\"study-charts\"
                style=\"margin: auto; text-align: center;\">
                   $html
            </div>
        ";
		QMStr::errorIfLengthGreaterThan($html, "$id $type group", 500);
		return $html;
	}
	/**
	 * @param string $type
	 * @return string
	 * @throws HighchartExportException
	 */
	public function getChartHtmlWithEmbeddedImages(string $type = HighchartExport::DEFAULT_IMAGE_FORMAT): string{
		$html = '';
		$chartsArray = $this->getChartsArrayWithSourceObject();
		if(!$chartsArray){
			return '';
		}
		$previousImageData = null;
		foreach($chartsArray as $chart){
			if(!$chart->canExport()){
				$chart->logInfo("TODO: Implement export for " . get_class($chart));
				continue;
			}
			$chart->validate();
			try {
				$one = $chart->getOrGenerateEmbeddedImageHtml($type);
				$html .= $one;
			} catch (HighchartExportException $e) {
				if(!$chart->canExport()){
					$chart->logError(__METHOD__.": ".$e->getMessage());
				} else{
					throw $e;
				}
			}
			$imageData = $chart->getImageDataIfSet($type);
			if($imageData && $previousImageData && $imageData === $previousImageData){
				le("Duplicate chart image data on $this!");
			}
			$previousImageData = $imageData;
		}
		$id = $this->getId();
		$html = "
            <div id='$id'
                class=\"study-charts\"
                style=\"margin: auto; text-align: center;\">
                   $html
            </div>
        ";
		QMStr::errorIfLengthGreaterThan($html, "$id $type group", 500);
		return $html;
	}
	public function getId(): string{
		return $this->getSourceObject()->getUniqueIndexIdsSlug() . "-charts";
	}
	/**
	 * @return array
	 */
	public function getChartsArrayWithSourceObject(): array{
		$this->addSourceObjectToCharts();
		$arr = $this->getChartsArray();
		return $arr;
	}
	/**
	 * @return void
	 */
	public function getOrSetHighchartConfigs(): void{
		$this->logDebug(__METHOD__);
		$chartsArray = $this->getChartsArrayWithSourceObject();
		foreach($chartsArray as $chart){
			try {
				$chart->getOrSetHighchartConfig();
				if(!$chart->highchartConfig){
					le("no chart->highchartConfig");
				}
			} catch (NotEnoughDataException $e) {
				$chart->setException($e);
				$this->logInfo(__METHOD__.": ".$e->getMessage());
				continue;
			} catch (TooSlowToAnalyzeException $e) {
				$chart->setException($e);
				$this->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
		}
	}
	/**
	 * @return void
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function setHighchartConfigs(): void{
		ConsoleLog::info(__METHOD__);
		$chartsArray = $this->getChartsArrayWithSourceObject();
		foreach($chartsArray as $chart){
			$chart->generateHighchartConfig();
			$hc = $chart->highchartConfig;
			if(!$hc){
				le('!$hc');
			}
			if(empty($hc->title->text)){
				le('empty($hc->title->text)');
			}
			if(empty($hc->subtitle->text)){
				le('empty($hc->subtitle->text)');
			}
		}
	}
	private function addSourceObjectToCharts(): void{
		$charts = $this->getChartsArray();
		foreach($charts as $chart){
			$chart->setSourceObject($this->sourceObject);
		}
	}
	/**
	 * @throws HighchartExportException
	 * @throws NotEnoughDataException
	 */
	public function outputImageSizesByType(){
		$charts = $this->getChartsArray();
		$imageTypes = [HighchartsExport::PNG, HighchartsExport::JPG, HighchartsExport::SVG];
		$sizes = [];
		foreach($charts as $chart){
			$hc = $chart->getHighchartConfig();
			if(is_array($hc->chart)){
				le('is_array($chart->highchartConfig->chart)');
			}
			foreach($imageTypes as $type){
				if(!$chart->canExport()){
					$chart->logInfo("TODO: Implement export for " . get_class($chart));
					continue;
				}
				$data = $chart->getOrGenerateImageData($type);
				$kb = round(strlen($data) / 1024);
				$sizes[$chart->getTitleAttribute()][$type] = $kb;
			}
			$sizes[$chart->getTitleAttribute()]['js'] = round(strlen(json_encode($hc)) / 1024);
		}
		QMLog::print($sizes, $this->getTitleAttribute() . " Sizes (kb)");
	}
	public function getTitleAttribute(): string{
		return $this->getSourceObject()->getTitleAttribute() . " " . $this->getClassNameTitle();
	}
	public function getButton(array $params = []): QMButton{
		return $this->getSourceObject()->getButton();
	}
	public function shrink(){
		$charts = $this->getChartsArray();
		$before = round(strlen(json_encode($this)) / 1024);
		foreach($charts as $type => $chart){
			$chart->shrink();
		}
		$kb = round(strlen(json_encode($this)) / 1024);
		$this->logInfo("$before KB before and $kb KB AFTER shrinkage");
	}
	/**
	 * @param mixed ...$args
	 * @return string
	 */
	public static function generateInline(...$args): ?string{
		$c = new static($args[0]);
		return $c->getInline();
	}
	public function getInline(): string{
		$html = "";
		$arr = $this->getChartsArray();
		foreach($arr as $chart){
			$html .= $chart->generateInline($this->getSourceObject());
		}
		return $html;
	}
}
