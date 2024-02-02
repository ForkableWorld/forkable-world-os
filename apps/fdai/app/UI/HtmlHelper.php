<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UI;
use App\Buttons\Admin\PHPStormButton;
use App\Buttons\Links\HelpButton;
use App\Buttons\QMButton;
use App\Buttons\Sharing\EmailSharingButton;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Properties\Base\BaseImageUrlProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
use App\Utils\UrlHelper;
use Collective\Html\HtmlFacade;
use DOMDocument;
use Facade\Ignition\Exceptions\ViewException;
use Illuminate\View\View;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Tests\TestGenerators\UnitTestFile;

class HtmlHelper {
	public const SHARING_BUTTON_CSS = "
            <style type=\"text/css\">
                ul.share-buttons{
                  list-style: none;
                  padding: 0;
                  margin: 4rem;
                }
                ul.share-buttons li{
                  display: inline;
                }
                ul.share-buttons .sr-only{
                  position: absolute;
                  clip: rect(1px 1px 1px 1px);
                  clip: rect(1px, 1px, 1px, 1px);
                  padding: 0;
                  border: 0;
                  height: 1px;
                  width: 1px;
                  overflow: hidden;
                }
            </style>
            ";
	const START_DEBUG_INFO = "<! -- START DEBUG INFO -->";
	const END_DEBUG_INFO = "<! -- END DEBUG INFO -->";
	private static $checkedForMissingClosingTags;
	/**
	 * @param string $header
	 * @param string $paragraph
	 * @param int $headerLevel
	 * @return string
	 */
	public static function getParagraphWithBoldHeader(string $header, string $paragraph, int $headerLevel = 4): string{
		return "
            <h$headerLevel class=\"text-2xl font-semibold\">
                $header
            </h$headerLevel>
            <p>
                $paragraph
            </p>
            ";
	}
	/**
	 * @param string $content
	 * @param object|null $obj
	 * @param array $params
	 * @return \Illuminate\View\View
	 */
	public static function getReportViewWithoutTailwind(string $content, object $obj = null, array $params = []): View {
		$params['content'] = $content;
		if($obj){$params['obj'] = $obj;}
		return view('layouts.report-layout-without-tailwind', $params);
	}
	/**
	 * @param string $content
	 * @param object|null $obj
	 * @param array $params
	 * @return \Illuminate\View\View
	 */
	public static function getReportViewWithTailwind(string $content, object $obj = null, array $params = []): View {
		$params['content'] = $content;
		if($obj){$params['obj'] = $obj;}
		return view('layouts.report-layout-with-tailwind', $params);
	}
	/**
	 * @param View $view
	 * @return mixed
	 */
	public static function renderBlade(View $view): string{
		return self::renderView($view);
	}
	/**
	 * @param string $content
	 * @param object|null $obj
	 * @param array $params
	 * @return string
	 */
	public static function renderReportWithoutTailwind(string $content, object $obj = null, array $params = []): string{
		ConsoleLog::info(__FUNCTION__);
		return HtmlHelper::renderView(self::getReportViewWithoutTailwind($content, $obj, $params));
	}
	/**
	 * @param string $content
	 * @param object|null $obj
	 * @param array $params
	 * @return string
	 */
	public static function renderReportWithTailwind(string $content, object $obj = null, array $params = []): string{
		ConsoleLog::info(__FUNCTION__);
		return HtmlHelper::renderView(self::getReportViewWithTailwind($content, $obj, $params));
	}
	/**
	 * @param string $html
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function validateStaticHtml(string $html, string $type){
		$length = strlen($html);
		if($length < 1000000){ // Really slow
			self::validateHtml($html, $type);
		}
		$black = VariableNameProperty::TEST_VARIABLE_NAMES_LIKE;
		$black[] = "Crowdsourcing Cures";
		$black[] = "phone Call ";
		// We need this for CureTogether studies $black[] = '"0 studies';
		QMStr::assertDoesNotContain($html, $black, $type);
	}
	/**
	 * @param string $html
	 * @param string $type
	 */
	public static function validateHtml(string $html, string $type){
		try {
			QMStr::assertStringDoesNotContain($html, ['</div\n'], $type);
			self::checkForMissingHtmlClosingTags($html, $type);
			QMStr::assertStringCountLessThan(2, "</html>", $html, $type);
		} catch (InvalidStringException $e) {
			le($e);
		}
	}
	/**
	 * Assert that the given HTML validates
	 * @param string $html The HTML to validate
	 * @param string $type
	 * @return string
	 */
	public static function checkForMissingHtmlClosingTags(string $html, string $type): string{
		$before = $html;
		$withoutJsEscapes = str_replace('<\/', '</', $html);
		if(isset(self::$checkedForMissingClosingTags[$before])){
			return self::$checkedForMissingClosingTags[$before];
		}
		preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $withoutJsEscapes, $result);
		$openedTags = $result[1];
		$openedTags = array_diff($openedTags, ["br"]);
		$openedTags = array_diff($openedTags, ["img"]);
		$openedTags = array_diff($openedTags, ["meta"]);
		$openedTags = array_diff($openedTags, ["link"]);
		//$openedTags = array_diff($openedTags, array("input"));
		$openedTags = array_values($openedTags);
		preg_match_all('#</([a-z]+)>#iU', $withoutJsEscapes, $result);
		$closedTags = $result[1];
		$len_opened = count($openedTags);
		if(count($closedTags) === $len_opened){
			return self::$checkedForMissingClosingTags[$before] = $html;
		}
		$openedTags = array_reverse($openedTags);
		for($i = 0; $i < $len_opened; $i++){
			$currentOpenTag = $openedTags[$i];
			if(!in_array($currentOpenTag, $closedTags, true)){
				if($currentOpenTag === "input"){
					continue;
				}
				if($currentOpenTag === "hr"){
					continue;
				}
				if($currentOpenTag === "path"){
					continue;
				}
				if($currentOpenTag === "use"){
					continue;
				}
				if($currentOpenTag === "feOffset"){
					continue;
				}
				$m = "Missing closing $currentOpenTag tag in $type";
				QMLog::error($m);//: \n $html");
				if(AppMode::isAnyKindOfUnitTest()){
					if(EnvOverride::isLocal()){
						try {
							FileHelper::writeHtmlFile($type, $html);
						} catch (InvalidFilePathException $e) {
							le($e);
							throw new \LogicException();
						}
					}
					le($m);
				}
				//$html .= '</'.$openedTags[$i].'>';
			} else{
				unset($closedTags[array_search($openedTags[$i], $closedTags, true)]);
			}
		}
		return self::$checkedForMissingClosingTags[$before] = $html;
	}
	/**
	 * @param string $text
	 * @param string $url
	 * @param bool $newTab
	 * @param string|null $tooltip
	 * @return string
	 */
	public static function getLinkAnchorHtml(string $text, string $url, bool $newTab = true,
		string $tooltip = null): string{
		$target = "_self";
		if($newTab){
			$target = "_blank";
		}
		return '<a href="' . $url . '" title="' . $tooltip . '" target="' . $target . '">' . $text . '</a>';
	}
	/**
	 * @param string $url
	 * @param int $width
	 * @param int $height
	 * @param string $alt
	 * @return string
	 */
	public static function getImageHtmlWithSize(string $url, int $width, int $height, string $alt, string $style = 
	null): 
	string{
		return HtmlFacade::image($url, $alt, ['width' => $width, 'height' => $height, 'style' => $style]);
//		return '<img src="' . $url . '"  width="' . $width . '" height="' . $height . '" alt="' . $alt .'" style="' . 
//		       $style . '" >';
	}
	/**
	 * @param string $url
	 * @param string $alt
	 * @param string|null $style
	 * @param string|null $class
	 * @return string
	 */
	public static function getImageHtml(string $url, string $alt, string $style = null, string $class = null): string{
		try {
			BaseImageUrlProperty::assertIsImageUrl($url, __FUNCTION__);
		} catch (InvalidStringException $e) {
			le($e);
		}
		if(strpos('<', $alt)){
			le("$alt should not contain < but is $alt");
		}
		if(strpos('”', $alt)){
			le("$alt should not contain ” but is $alt");
		}
		$str = "
            <img src=\"$url\" alt=\"$alt\" ";
		if($style){
			$str .= ' style="' . $style . '"';
		}
		if($class){
			$str .= ' class="' . $class . '"';
		}
		return $str . ">
        ";
	}
	/**
	 * @return string
	 */
	public static function getHelpInstructionsHTML(): string{
		return "<p>Please <a href=\"mailto:help@curedao.org\">contact help@curedao.org</a> for assistance. </p>";
	}
	/**
	 * @param string $all
	 * @return array
	 */
	public static function htmlTableToArray(string $all): array{
		if(stripos($all, '<table') === false){
			$testUrl = UnitTestFile::generateAndGetUrl();
			le("No table in provided HTML!
             TEST: $testUrl
             HTML: $all");
		}
		$tableStr = QMStr::betweenAndIncluding($all, '<table', '</table>');
		//$htmlContent = HtmlNormalizer::fromHtml($htmlContent)->render();
		$output = $associativeArray = $headerTextArray = [];
		$entireDOM = self::HTMLStringToDOM($tableStr);
		$HeaderCells = $entireDOM->getElementsByTagName('th');
		if(!$HeaderCells || !count($HeaderCells)){
			$headerHtml = QMStr::betweenAndIncluding($tableStr, "<thead", "</thead>");
			$headerDOM = self::HTMLStringToDOM($headerHtml);
			$HeaderCells = $headerDOM->getElementsByTagName('td');
			$bodyHtml = str_replace($headerHtml, '', $tableStr);
			$entireDOM = self::HTMLStringToDOM($bodyHtml);
		}
		$bodyCells = $entireDOM->getElementsByTagName('td');
		$numberOfColumns = count($HeaderCells);
		foreach($HeaderCells as $headerCell){
			$txt = $headerCell->textContent;
			$headerTextArray[] = trim($txt);
		}
		if(!isset($headerTextArray[0])){
			UnitTestFile::generateAndGetUrl(static::class . "::" . __FUNCTION__ . '("' . addslashes($tableStr) . '("',
				__FUNCTION__);
			QMLog::error("No first header from from this HTML: " . $tableStr);
			throw new \LogicException("No first header from HeaderCells: " . QMLog::var_export($HeaderCells, true));
		}
		$rowNumber = 0;
		$columnNumber = 0;
		foreach($bodyCells as $bodyCell){
			$txt = $bodyCell->textContent;
			$associativeArray[$columnNumber][] = trim($txt);
			$rowNumber = $rowNumber + 1;
			$columnNumber = $rowNumber % $numberOfColumns == 0 ? $columnNumber + 1 : $columnNumber;
		}
		$numberOfRows = count($associativeArray);
		for($rowNumber = 0; $rowNumber < $numberOfRows; $rowNumber++){
			for($columnNumber = 0; $columnNumber < $numberOfColumns; $columnNumber++){
				$headerName = $headerTextArray[$columnNumber];
				$headerName =
					QMStr::trimWhitespaceAndLineBreaks($headerName);  // Have to do this because the string can be too long for key
				$output[$rowNumber][$headerName] = $associativeArray[$rowNumber][$columnNumber];
			}
		}
		return $output;
	}
	/**
	 * @param string $html
	 * @return DOMDocument
	 */
	private static function HTMLStringToDOM(string $html): DOMDocument{
		$dom = new DOMDocument();
		// https://stackoverflow.com/questions/1148928/disable-warnings-when-loading-non-well-formed-html-by-domdocument-php
		// The libxml_use_internal_errors(true) indicates that you're going to handle the errors and warnings yourself
		// and you don't want them to mess up the output of your script.
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		//Whether or not you're using the collected warnings you should always clear the queue by calling libxml_clear_errors().
		libxml_clear_errors();
		return $dom;
	}
	/**
	 * @param string $string
	 * @return bool
	 */
	public static function isHtml(string $string): bool{
		return $string != strip_tags($string);
	}
	/**
	 * @param string $ul
	 * @return array
	 */
	public static function htmlListToArray(string $ul): array{
		// encode ampersand appropriately to avoid parsing warnings
		$ul = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $ul);
		if(!$ul = simplexml_load_string($ul)){
			throw new \RuntimeException("Syntax error in UL/LI structure");
		}
		return self::htmlListToArray($ul);
	}
	/**
	 * @param string $rawHtml
	 * @return array
	 */
	public static function htmlUnorderedListToArray(string $rawHtml): array{
		$listItems = QMStr::between($rawHtml, "<ul>", "</ul>");
		$arr = explode("<li>", $listItems);
		$clean = [];
		foreach($arr as $key => $value){
			$i = QMStr::trimWhitespaceAndLineBreaks($value);
			$i = str_replace("</li>", "", $i);
			if(empty($i)){
				continue;
			}
			$clean[] = $i;
		}
		return $clean;
	}
	/**
	 * @param string $url
	 * @param string $shortTitle
	 * @param string $imagePreview
	 * @param string $briefDescription
	 * @param string $html
	 * @return string
	 */
	public static function getSocialSharingButtonsHtmlNonEmail(string $url, string $shortTitle, string $imagePreview,
		string $briefDescription, string $html = ''): string{
		$css = self::SHARING_BUTTON_CSS;
		if(stripos($html, $css) === false){
			$html .= $css;
		}
		QMStr::errorIfLengthGreaterThan($html, __FUNCTION__, 10);
		return HtmlHelper::renderView(view('social-sharing-buttons', [
			'liStyle' => "display: inline;",
			'url' => $url,
			'shortTitle' => $shortTitle,
			'imagePreview' => $imagePreview,
			'briefDescription' => $briefDescription,
			'emailUrl' => EmailSharingButton::getEmailShareLink($url, $shortTitle, $briefDescription),
		]));
	}
	/**
	 * @param string $input
	 * @return string
	 */
	public static function stripHtmlTags(string $input): string{
		return strip_tags($input);
	}
	/**
	 * @return string
	 */
	public static function getHelpLinkAnchorHtml(string $text = null): string{
		return HtmlHelper::getLinkAnchorHtml($text ?? HelpButton::url(), HelpButton::url());
	}
	/**
	 * @return string
	 */
	public static function getHelpButton(): string{
		return HelpButton::getHelpButtonHtml();
	}
	/**
	 * @param string $html
	 * @param string $uniqueIdsSlug
	 * @return string
	 */
	public static function wrapWithQMIdCommentTags(string $html, string $uniqueIdsSlug): string{
		return "
            <!-- qm:$uniqueIdsSlug -->
                $html
            <!-- /qm:$uniqueIdsSlug -->
        ";
	}
	public static function getGlobalFooterJS(): string{
		try {
			$footJs = FileHelper::getContents('resources/views/components/buttons/chat-button.blade.php');
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return $footJs;
	}
	/**
	 * @param $html
	 * @param string $footer
	 * @return string|string[]
	 */
	public static function addToEndOrBeforeClosingBodyTag($html, string $footer){
		if(stripos($html, "</body>") !== false){
			$html = str_replace("</body>", $footer . "\n</body>", $html);
		} else{
			$html .= $footer;
		}
		return $html;
	}
	public static function stripDebugInfo(string $html): string{
		return QMStr::removeBetweenAndIncluding(self::START_DEBUG_INFO, self::END_DEBUG_INFO, $html);
	}
	/**
	 * @param string $page
	 * @param string $debugHtml
	 * @return string|string[]
	 */
	public static function addDebugHtml(string $page, string $debugHtml){
		$debugHtml = self::START_DEBUG_INFO . $debugHtml . self::END_DEBUG_INFO;
		if(stripos($page, self::START_DEBUG_INFO) !== false){
			$page = self::stripDebugInfo($page);
		}
		if(stripos($page, "</body>") !== false){
			$page = str_replace("</body>", $debugHtml . "\n</body>", $page);
		} else{
			$page .= $debugHtml;
		}
		return $page;
	}
	public static function globalWrapper(string $html): string{
		$width = "max-width: " . CssHelper::GLOBAL_MAX_POST_CONTENT_WIDTH . "px; ";
		return "
            <div style=\"font-family: 'Source Sans Pro', sans-serif; margin: auto; $width\">
                $html
            </div
        ";
	}
	public static function arrayToTable(array $array): string{
		// start table
		$html = '<table id="#data-table-id">';
		// header row
		$html .= '<tr>';
		foreach($array[0] as $key => $value){
			$html .= '<th>' . htmlspecialchars($key) . '</th>';
		}
		$html .= '</tr>';
		// data rows
		foreach($array as $key => $value){
			$html .= '<tr>';
			foreach($value as $key2 => $value2){
				$html .= '<td>' . htmlspecialchars($value2) . '</td>';
			}
			$html .= '</tr>';
		}
		// finish table and return it
		$html .= '</table>';
		return $html;
	}
	/**
	 * @param string $html
	 * @return mixed
	 */
	public static function outputHtmlElementSizes(string $html){
		$total = round(strlen($html) / 1024);
		\App\Logging\ConsoleLog::info("Total Size $total kb");
		$arr = explode('id=', $html);
		foreach($arr as $key => $value){
			$id = QMStr::before(">", $value);
			$size = round(strlen($value) / 1024);
			$bySize[$size] = QMStr::truncate($id, 100);
		}
		krsort($bySize);
		QMLog::print($bySize, "Element Sizes (kb)");
		return $bySize;
	}
	/**
	 * @param $badgeText
	 * @param string $name
	 * @param string $url
	 * @param string|null $tooltip
	 * @param string $color
	 * @return string
	 */
	public static function generateBadgeListItemHtml($badgeText, string $name, string $url, string $tooltip = null,
		string $color = 'blue'): string{
		if(!$tooltip){
			$tooltip = "See $name";
		}
		$color = QMColor::toString($color);
		return "
                <li>
                    <a href=\"$url\" target='_self' title='$tooltip'>
                        $name <span class=\"pull-right badge bg-$color\">$badgeText</span>
                    </a>
                </li>
            ";
	}
	public static function generateLoaderNotificationHtml(string $text, string $color = 'blue'): string{
		$bootstrap = QMColor::toBootstrap($color);
		if($spinner = true){
			$loadingIcon = "<!-- MDL Spinner Component --><div class=\"mdl-spinner mdl-js-spinner is-active\"></div>";
		} else{
			$loadingIcon =
				"<i class=\"material-icons\" data-notify=\"icon\" style='font-size: 16px;'>hourglass_empty</i>";
		}
		return "
            <div id=\"p2\" style='width: 100%' class=\"mdl-progress mdl-js-progress mdl-progress__indeterminate\"></div>
            <div class=\"alert alert-$bootstrap alert-with-icon\"
                style='text-align: center;'
                data-notify=\"container\">
                  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                    <i class=\"material-icons\">close</i>
                  </button>
                  <div>
                    $loadingIcon
                    </div>
                  <span data-notify=\"message\" style='font-size: 16px;'>
                    $text
                  </span>
            </div>
        ";
	}
	public static function generateLoaderInfoBoxHtml(string $text, string $color, string $icon): string{
		$color = QMColor::toString($color);
		if($spinner = true){
			$loadingIcon = "<!-- MDL Spinner Component --><div class=\"mdl-spinner mdl-js-spinner is-active\"></div>";
		} else{
			$loadingIcon =
				"<i class=\"material-icons\" data-notify=\"icon\" style='font-size: 16px;'>hourglass_empty</i>";
		}
		return "
            <div class=\"info-box\">
                <span class=\"info-box-icon bg-$color\">
                    <i class=\"$icon\"></i>
                </span>
                <div class=\"info-box-content\" style='text-align: center;'>
                    <div id=\"p2\" style='width: 100%' class=\"mdl-progress mdl-js-progress mdl-progress__indeterminate\"></div>
                  <span class=\"info-box-text\">$text</span>
                  <span class=\"info-box-number\"></span>
                  $loadingIcon
                    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
                        <i class=\"material-icons\">close</i>
                      </button>
                </div>
                <!-- /.info-box-content -->
              </div>
        ";
	}
	/**
	 * @param $number
	 * @param string $name
	 * @param string $tooltip
	 * @param string $color
	 * @param string $fontAwesome
	 * @param string $url
	 * @return string
	 */
	public static function generateStatBoxHtml($number, string $name, string $tooltip, string $color,
		string $fontAwesome, string $url): string{
		$color = QMColor::toString($color);
		$str = "
                <div class=\"col-lg-3 col-xs-6\">
                  <!-- small box -->
                  <div class=\"small-box bg-$color\">
                    <div class=\"inner\">
                      <h3 style='padding: 10px 0 0 10px;'>$number</h3>
                      <p style='padding: 0 0 0 10px;'>$name</p>
                    </div>
                    <div class=\"icon\">
                      <i class=\"$fontAwesome\"></i>
                    </div>
                    <a href=\"$url\" class=\"small-box-footer\" title=\"$tooltip\" onclick=\"window.showLoader && showLoader()\">
                      $tooltip <i class=\"fa fa-arrow-circle-right\"></i>
                    </a>
                  </div>
                </div>
        ";
		//return $str;
		return "
            <a href=\"$url\" title=\"$tooltip\" onclick=\"window.showLoader && showLoader()\">
                $str
            </a>
            ";
	}
	/**
	 * @param $largeBottom
	 * @param string|int $smallTop
	 * @param string $description
	 * @param string $iconBackgroundColor
	 * @param string $fontAwesome
	 * @param string $url
	 * @return string
	 */
	public static function generateMaterialStatCard($largeBottom, $smallTop, string $description,
		string $iconBackgroundColor, string $fontAwesome, string $url): string{
		$iconBackgroundColor = QMColor::toBootstrap($iconBackgroundColor);
		$id = QMStr::slugify($smallTop ?? $largeBottom);
		return "
            <div class=\"card-stats-container\" >
                <a href=\"$url\" title=\"$description\" onclick=\"window.showLoader && showLoader()\">
                    <div id=\"$id-card\" class=\"card card-stats\"
                        title=\"$description\">
                        <div class=\"card-header card-header-$iconBackgroundColor card-header-icon\">
                          <div class=\"card-icon\">
                            <i class=\"$fontAwesome\"></i>
                          </div>
                          <p class=\"card-category\">$smallTop</p>
                            <h3 class=\"card-title\">$largeBottom</h3>
                        </div>
                        <div class=\"card-footer\">
                          <div class=\"stats\">
                            <i class=\"material-icons\">launch</i> $description
                          </div>
                        </div>
                  </div>
                </a>
            </div>
        ";
	}
	public static function generateDeleteForm(string $url): string{
		return "
            <form method=\"POST\" action=\"$url\" accept-charset=\"UTF-8\">
                <input name=\"_method\" type=\"hidden\" value=\"DELETE\">
                <input name=\"_token\" type=\"hidden\" value=\"o4Hnkx7jeaGykv9i15cfh0ylo629DKzOlEe5psr7\">
                <button type=\"submit\" style=\"border:none;
                    display: block;
                    padding: 3px;
                    width: 100%;
                    text-align: left;
                    clear: both;
                    font-weight: normal;
                    /* line-height: 1.428571429; */
                    color: #333;
                    white-space: nowrap;
                    background-color: transparent;\"
                    onclick=\"return confirm('Are you sure?')\">
                    <i class=\"fa fa-trash\" title=\"Delete\" style=\"padding-right: 17px;\"></i>
                    Delete
                </button>
            </form>
        ";
	}
	/**
	 * @param string $name
	 * @param QMButton[] $buttons
	 * @param string|null $tooltip
	 * @param string|null $color
	 * @param string|null $url
	 * @return string
	 */
	public static function generateDoubleDropDownButtonHtml(string $name, array $buttons, string $tooltip = null,
		string $color = null, string $url = null): string{
		$chips = '';
		foreach($buttons as $button){
			$chips .= $button->getListItem() . "\n<li class=\"divider\"></li>\n";
		}
		$mainButton = "
            <button type=\"button\" class=\"btn\" style='background-color: $color; color: white;'>
                <a href='$url' title='$tooltip' style='color: white;'>$name</a>
            </button>";
		$str = "
                <div class=\"btn-group pull-right\">
                    $mainButton
                    <button type=\"button\"
                            style='background-color: $color; color: white;'
                            class=\"btn dropdown-toggle\"
                            data-toggle=\"dropdown\"
                            aria-expanded=\"false\">
                        <span class=\"caret\"></span>
                        <span class=\"sr-only\">Toggle Dropdown</span>
                    </button>
                    <ul class=\"dropdown-menu\" role=\"menu\">
                        $chips
                    </ul>
                </div>
        ";
		return $str;
	}
	/**
	 * @param string $titleHtml
	 * @param QMButton[] $buttons
	 * @param string|null $tooltip
	 * @param string|null $color
	 * @param string|null $id
	 * @return string
	 */
	public static function generateDropDownButtonHtml(string $titleHtml, array $buttons, string $tooltip = null,
		string $color = null, string $id = null): string{
		$buttons = array_values($buttons);
		if(!isset($buttons[0])){
			le("No buttons for generateDropDownButtonHtml!");
		}
		$items = '';
		if(!$color){
			$color = QMColor::HEX_BLUE;
		}
		foreach($buttons as $button){
			$items .= $button->getListItem();
		}
		$str = "
            <div class=\"btn-group\">
                <button
                    id=\"$id\"
                    type=\"button\"
                    title='$tooltip'
                    class=\"btn\"
                    style='background-color: $color; color: white; border-radius: 50px;'
                    data-toggle=\"dropdown\"
                    aria-expanded=\"false\">
                    $titleHtml
                    <span class=\"caret\"></span>
                    <span class=\"sr-only\">Toggle Dropdown</span>
                </button>
                <ul class=\"dropdown-menu\" role=\"menu\">
                    $items
                </ul>
            </div>
        ";
		return $str;
	}
	/**
	 * @param string $img
	 * @param QMButton[] $buttons
	 * @param string|null $tooltip
	 * @param string|null $text
	 * @param string $style
	 * @return string
	 */
	public static function generateImageNameDropDown(string $img, array $buttons, string $tooltip = null,
		string $text = null,
		string $style = "height: 32px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;"): string{
		$listItems = '';
		foreach($buttons as $button){
			$listItems .= $button->getListItem();
			//."\n<li class=\"divider\"></li>\n";
		}
		$str = "
            <a href=\"#\"
            target='_self'
            style='text-align: center;'
            title=\"$tooltip\"
            data-toggle=\"dropdown\"
            aria-expanded=\"false\">
                <img src=\"$img\"
                    alt=\"$tooltip\"
                    style=\"$style\"/>
                <p>$text</p>
            </a>
            <ul class=\"dropdown-menu\" role=\"menu\">
                $listItems
            </ul>
        ";
		return $str;
	}
	public static function generateImageDropDown(string $img, array $buttons, string $tooltip = null,
		string $text = null,
		string $style = "height: 32px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;"): string{
		$listItems = '';
		foreach($buttons as $button){
			$listItems .= $button->getListItem();
			//."\n<li class=\"divider\"></li>\n";
		}
		$str = "
            <a href=\"#\"
            target='_self'
            style='text-align: center;'
            title=\"$tooltip\"
            data-toggle=\"dropdown\"
            aria-expanded=\"false\">
                <img src=\"$img\"
                    alt=\"$tooltip\"
                    style=\"$style\"/>
            </a>
            <ul class=\"dropdown-menu\" role=\"menu\">
                $listItems
            </ul>
        ";
		return $str;
	}
	/**
	 * @param string $url
	 * @param $responseHtml
	 * @return string|string[]
	 */
	public static function replaceRelativePathsWithAbsoluteUrls(string $url, $responseHtml){
		$parsed = parse_url($url);
		$origin = $parsed['scheme'] . "://" . $parsed['host'] . "/";
		$responseHtml = str_replace('src="/', 'src="' . $origin, $responseHtml);
		$responseHtml = str_replace('href="/', 'href="' . $origin, $responseHtml);
		$responseHtml = str_replace("location = '/", "location = '" . $origin, $responseHtml);
		$responseHtml = str_replace('Url = "/', 'Url = "' . $origin, $responseHtml);
		return $responseHtml;
	}
	/**
	 * Turn all URLs in clickable links.
	 * @param string $value
	 * @param array $protocols http/https, ftp, mail, twitter
	 * @param array $attributes
	 * @return string
	 */
	public static function linkify(string $value, array $protocols = ['http', 'mail'], array $attributes = []): string{
		// Link attributes
		$attr = '';
		foreach($attributes as $key => $val){
			$attr .= ' ' . $key . '="' . htmlentities($val) . '"';
		}
		$links = [];
		// Extract existing links and tags
		$value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i',
			function($match) use (&$links){ return '<' . array_push($links, $match[1]) . '>'; }, $value);
		// Extract text links for each protocol
		foreach((array)$protocols as $protocol){
			switch($protocol) {
				case 'http':
				case 'https':
					$value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i',
						function($match) use ($protocol, &$links, $attr){
							if($match[1]) $protocol = $match[1];
							$link = $match[2] ?: $match[3];
							return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>';
						}, $value);
					break;
				case 'mail':
					$value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~',
						function($match) use (&$links, $attr){
							return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") .
								'>';
						}, $value);
					break;
				case 'twitter':
					$value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function($match) use (&$links, $attr){
						return '<' . array_push($links,
								"<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') .
								$match[1] . "\">{$match[0]}</a>") . '>';
					}, $value);
					break;
				default:
					$value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i',
						function($match) use ($protocol, &$links, $attr){
							return '<' .
								array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>';
						}, $value);
					break;
			}
		}
		// Insert all link
		return preg_replace_callback('/<(\d+)>/', function($match) use (&$links){ return $links[$match[1] - 1]; },
			$value);
	}
	public static function generateLink(string $text, string $url, bool $newTab, string $tooltip = null): string{
		return self::getLinkAnchorHtml($text, $url, $newTab, $tooltip);
	}
	public static function getMDLTags(): string{
		return "
<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/icon?family=Material+Icons\">
<link rel=\"stylesheet\" href=\"https://code.getmdl.io/1.3.0/material.indigo-pink.min.css\">
<script type=\"application/javascript\" defer src=\"https://code.getmdl.io/1.3.0/material.min.js\"></script>
            ";
	}
	public static function getTailwindTags(): string{
		return "
<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css\">
            ";
	}
	public static function indent(string $input): string{
		return str_replace("\n", "\n\t", $input);
	}
	/**
	 * @param string $html
	 * @param string $url
	 * @return array|string|string[]
	 */
	public static function addAbsolutePathsToTags(string $html, string $url){
		$origin = UrlHelper::origin($url);
		$html = str_replace("src=\"/", "src=\"$origin/", $html);
		$html = str_replace("href=\"/", "href=\"$origin/", $html);
		return $html;
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractJsTags($data): array{
		preg_match_all('#<script(.*?)</script>#is', $data, $matches);
		return $matches[0];
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractLocalJsTags($data): array{
		$all = self::extractJsTags($data);
		$paths = [];
		foreach($all as $one){
			$afterSrc = QMStr::after('src="', $one);
			if(empty($afterSrc)){
				continue;
			}
			$beforeJs = QMStr::before('"', $afterSrc);
			if(stripos($one, '//') !== false){
				continue;
			}
			if(empty($beforeJs)){
				continue;
			}
			$paths[] = $beforeJs;
		}
		return $paths;
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractCssTags($data): array{
		preg_match_all('#<link(.*?)/>#is', $data, $matches);
		return $matches[0];
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractLocalCssTags($data): array{
		$all = self::extractCssTags($data);
		$paths = [];
		foreach($all as $one){
			$afterSrc = QMStr::after('href="', $one);
			if(empty($afterSrc)){
				continue;
			}
			$beforeJs = QMStr::before('"', $afterSrc);
			if(stripos($one, '//') !== false){
				continue;
			}
			if(empty($beforeJs)){
				continue;
			}
			$paths[] = $beforeJs;
		}
		return $paths;
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractImageTags($data): array{
		preg_match_all('#<img(.*?)/>#is', $data, $matches);
		return $matches[0];
	}
	/**
	 * @param $data
	 * @return array
	 */
	public static function extractLocalImageTags($data): array{
		$all = self::extractImageTags($data);
		$paths = [];
		foreach($all as $one){
			$afterSrc = QMStr::after('src="', $one);
			if(empty($afterSrc)){
				continue;
			}
			$ext = QMStr::between($afterSrc, '.', '"');
			$beforeJs = QMStr::before('.' . $ext, $afterSrc);
			if(stripos($one, '//') !== false){
				continue;
			}
			if(empty($beforeJs)){
				continue;
			}
			$paths[] = $beforeJs . '.' . $ext;
		}
		return $paths;
	}
	/**
	 * @param string $html
	 * @param string $type
	 * @throws InvalidStringException
	 */
	public static function validateHtmlPage(string $html, string $type){
		self::validateHtml($html, $type);
		QMStr::assertStringCount(1, "<html", $html, $type);
		QMStr::assertStringCount(1, "</html>", $html, $type);
		/** @noinspection HtmlRequiredTitleElement */
		QMStr::assertStringCount(1, "<head>", $html, $type);
		QMStr::assertStringCount(1, "</head>", $html, $type);
		QMStr::assertStringCount(1, '<meta name="description" ', $html, $type);
		QMStr::assertStringCount(1, '<meta name="author" ', $html, $type);
		QMStr::assertStringCount(1, '<meta name="viewport" ', $html, $type);
	}
	public static function getBody(string $html): string{
		$body = QMStr::betweenAndIncluding($html, "<body", "</body>");
		$body = str_replace("<body", "<div", $body);
		$body = str_replace("</body>", "</div>", $body);
		return $body;
	}
	/**
	 * @param \Illuminate\Contracts\View\View $view
	 * @return string
	 */
	public static function renderView(\Illuminate\Contracts\View\View $view): string{
		try {
			//$view->getFactory()->flushState();
			// This is necessary or the sections won't update if you repeat them if they extend the same layout
			// i.e. 'alpine-bars-images' in getPredictorSearchHtml
			$html = $view->render();
		} catch (\Throwable $e) {
			$path = $view->getPath();
			$url = PHPStormButton::redirectUrl($path);
			$viewException =
				new ViewException("Could not render \n" . $view->getPath() . "\n$url" . " 
				because " . $e->getMessage()."
				happened in ".$e->getFile().":".$e->getLine());
			$viewException->setView($view->getPath());
			$viewException->setViewData($view->getData());
			QMLog::error($viewException->getMessage(), $viewException->context());
			le($e);
		}
		return $html;
	}
	/**
	 * Trim whitespace in multiline text
	 * By default, removes leading and trailing whitespace, collapses all other
	 * space sequences, and removes blank lines. Most of this can be controlled
	 * by passing an options array with the following keys:
	 *   - leading (bool, true): should we trim leading whitespace?
	 *   - inside (bool, true): should we collapse spaces within a line
	 *     (not leading whitespace)?
	 *   - blankLines (int, 0): max number of consecutive blank lines;
	 *     use false to disable.
	 *   - tabWidth (int, 4): number of spaces to use when replacing tabs.
	 * The default settings can be used as basic minification for HTML text
	 * (except for preformatted text!). This function can  be used remove extra
	 * whitespace generated by a mix of PHP and HTML or by a template engine.
	 * This function forces two behaviors:
	 *   - Trailing whitespace will be removed, always.
	 *   - Tab characters will be replaced by space characters, always
	 *     (for performance reasons).
	 * This was summarily tested on PHP 5.6 and PHP 7 to be as fast as possible,
	 * and tested against different approaches (e.g. splitting as an array and using
	 * PHP’s trim function). The fastest solution found was using str_replace when
	 * possible and preg_replace otherwise with very simple regexps to avoid big
	 * perf costs. The current implementation should be, if not the fastest
	 * possible, probably close enough.
	 * @param string $string
	 * @param array $options
	 * @return string
	 */
	public static function trimWhitespace(string $string, array $options = []): string{
		$o = array_merge([
			'leading' => false,
			'inside' => false,
			'blankLines' => 0,
			'tabWidth' => 4,
		], $options);
		// Looking for spaces *and* tab characters is way too costly
		// (running times go x4 or x10) so we forcefully replace tab characters
		// with spaces, but make it configurable.
		$tabWidth = $o['tabWidth'];
		if(!is_int($tabWidth) || $tabWidth < 1 || $tabWidth > 8){
			$tabWidth = 4;
		}
		// Replacement patterns should be applied in a specific order
		$patterns = [];
		// Trim leading whitespace first (if active). In typical scenarios,
		// especially for indented HTML, this will remove of the target whitespace
		// and it turns out to be really quick.
		if($o['leading']){
			$patterns[] = ['/^ {2,}/m', ''];
		}
		// Always trim at the end. Warning: this seems to be the costlier
		// operation, perhaps because looking ahead is harder?
		$patterns[] = ['/ +$/m', ''];
		// Collapse space sequences inside lines (excluding leading/trailing)
		if($o['inside']){
			// No leading spaces? We can avoid a very costly condition!
			// Using a look-behind (or similar solutions) seems to make the whole
			// function go 2x-4x slower (PHP7) or up to 10x slower (PHP 5.6),
			// except on very big strings where whatever perf penalty was incurred
			// seems to be more limited (or at least not exponential).
			$spaces = ($o['leading'] ? ' ' : '(?<=\b) ') . '{2,}';
			$patterns[] = ['/' . $spaces . '/', ' '];
		}
		// Remove empty lines
		if(is_int($l = $o['blankLines']) && $l >= 0){
			// We need blank lines to be truly empty; if trimStart is disabled
			// we have to fall back to this slightly more costly regex.
			if(!$o['leading']) $patterns[] = ['/^ +$/m', ''];
			// Not using '\R' because it's too slow, so we must do it by hand
			// and replace CRLF before touching any LF.
			$patterns[] = ['/(\r\n){' . ($l + 2) . ',}/m', str_repeat("\r\n", $l + 1)];
			$patterns[] = ['/\n{' . ($l + 2) . ',}/m', str_repeat("\n", $l + 1)];
		}
		// Doing the replacement in one go without storing intermediary
		// values helps a bit for big strings (around 20 percent quicker).
		return preg_replace(array_map(function($x){ return $x[0]; }, $patterns),
			array_map(function($x){ return $x[1]; }, $patterns),
			str_replace("\t", str_repeat(' ', $tabWidth), $string));
	}
	public static function relativizePaths(string $html): string{
		return str_replace(\App\Utils\Env::getAppUrl(), '', $html);
	}
	/**
	 * @param string $url
	 * @param string $text
	 * @param string $tooltip
	 * @param string $target
	 * @return string
	 */
	public static function getTailwindLink(string $url, string $text, string $tooltip = "",
		string $target = QMButton::TARGET_BLANK): string{
		return "
<a href='$url'
    target='$target'
    title='$tooltip'
    class='no-underline font-bold dim text-primary'>
    {$text}
</a>
";
	}
	/**
	 * @param $requiredStrings
	 * @param string $haystack
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $message
	 * @throws InvalidStringException
	 */
	public static function assertHtmlContains($requiredStrings, string $haystack, string $type,
		bool $ignoreCase = false, string $message = null){
		QMStr::assertStringContains($haystack, $requiredStrings, $type, $ignoreCase, $message);
	}
	/**
	 * @param $blackList
	 * @param string $haystack
	 * @param string $type
	 * @param false $ignoreCase
	 * @param string|null $message
	 * @throws InvalidStringException
	 */
	public static function assertHtmlDoesNotContain($blackList, string $haystack, string $type,
		bool $ignoreCase = false, string $message = null){
		QMStr::assertStringDoesNotContain($haystack, $blackList, $type, $ignoreCase, $message);
	}
	/**
	 * @param string $html
	 * @return string
	 * @throws Html2TextException
	 */
	public static function htmlToText(string $html): string{
		// https://stackoverflow.com/questions/14648442/domdocumentloadhtml-warning-htmlparseentityref-no-name-in-entity
		$html = preg_replace("/&(?!\S+;)/", "&amp;", $html);
		return Html2Text::convert($html);
	}

    public static function largePinkButton(string $text = null, string $url = null, string $backgroundColor = null,
                                      string $fontAwesome = null) :string
    {
		$button = new QMButton($text, $url, $backgroundColor);
		$button->setFontAwesome($fontAwesome);
		return $button->getPinkRoundedButton();
	
    }
	public static function smallPillButton(string $text = null, string $url = null, string $backgroundColor = null,
	                                  string $fontAwesome = null) :string
	{
		$button = new QMButton($text, $url, $backgroundColor);
		$button->setFontAwesome($fontAwesome);
		return $button->getChipSmall();

	}
	public static function text_to_html($text) {
		// Split the text into lines
		$lines = explode("\n", $text);

		// Initialize a string to store the HTML output
		$html = "";

		// Iterate through the lines of text
		foreach ($lines as $line) {
			// Check if the line is empty
			if (trim($line) === "") {
				// If it's empty, add the line break and padding to the HTML
				$html .= "\n<div class='pb-4'></div>\n";
			} else {
				// If it's not empty, start a new text block and add the line to the HTML
				$html .= "<p class='text-gray-700 leading-relaxed'>\n" . $line . "<br>\n</p>\n";
			}
		}

		// Return the HTML
		return $html;
	}
	public static function getAvatarImageHtml(string $imageUrl, string $altText = null, int $size = 6){
		return "
	<span slot=\"avatar\" style=\"float: left;\">
        <span class=\"flex relative w-$size h-$size bg-orange-500 justify-center items-center m-1 mr-2 ml-0 my-0 text-lg rounded-full\">
            <img class=\"rounded-full\" alt=\"$altText\" src=\"$imageUrl\">
        </span>
    </span>	
		";
	}
	public static function array_to_list($items): string {
		$list = "<ul class=\"list-disks\">";
		foreach ($items as $item) {
			$list .= "<li>$item</li>";
		}
		$list .= "</ul>";
		return $list;
	}
}
