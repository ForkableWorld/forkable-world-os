<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\UI;
use App\Files\FileHelper;
use App\Reports\AnalyticalReport;
use App\Storage\S3\S3Public;
use App\Utils\AppMode;
use Pelago\Emogrifier\CssInliner;
use Symfony\Component\CssSelector\Exception\ParseException;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
class CssHelper {
	public const BASE_CSS_URL = "https://static.quantimo.do/css/";
	public const MATERIAL_ICONS = 'https://fonts.googleapis.com/icon?family=Material+Icons';
	public const FA_5 = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css'; // Make sure to include 5 before 4
	public const FA_4 = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
	public const LARAVEL_MATERIAL_CSS = self::BASE_CSS_URL . 'material-dashboard.css';
	public const MODERN_ADMIN_LTE_CSS = self::BASE_CSS_URL . 'modern-AdminLTE.min.css';
	public const TAILWIND = "https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css";
	public const CREATIVE_TIM = "https://cdn.jsdelivr.net/gh/creativetimofficial/tailwind-starter-kit/tailwind.min.css";
	public const CLASS_ROUNDED_BUTTON_WITH_IMAGE = 'rounded-button-with-image';
	public const SMALL_IMAGE_STYLE = "height: 50px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;";
	const GLOBAL_MAX_PAGE_WIDTH = 959;
	const GLOBAL_MAX_POST_CONTENT_WIDTH = 681;
	const DEFAULT_TABLE_WIDTH = 679;
	public static $fetchedCss;
	public static $cachedHtmlWithInlineCss;
	/**
	 * @param string $titleString
	 * @return string
	 */
	public static function addTitleCss(string $titleString): string{
		// <h1> makes titles huge in list at https://wp.quantimo.do/user-variables-user-230-variable-1398/ so using <h2>
		//return HtmlHelper::addMediumCss('<h1 class="study-title" >'.$titleString.'</h1>');
		$html =
			//'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">'.
			//'<html>'.
			'<div style="-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100%;
                    color: #37302d; background: #ffffff; font-size: 16px;">' . "\n" . '<h2 class="study-title" ' .
			'style="--x-height-multiplier: 0.342; --baseline-multiplier: 0.22; ' .
			'font-weight: 700; font-style: normal; line-height: 1.04; letter-spacing: -.015em;">' . "\n" .
			$titleString . "\n" . '</h2>' . "\n" . '</div>' . "\n"//.'</html>'
		;
		if(AppMode::isTestingOrStaging()){
			$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
		return $html;
	}
	/**
	 * @param string $before
	 * @return string
	 */
	public static function addBodyCss(string $before): string{
		//$after = HtmlHelper::addMediumCss('<p class="study-section-body">'.$before.'</p>');
		$html = '
            <div style="-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100%; height: 100%; color: #37302d; background: #ffffff; font-size: 16px;">
                <p class="study-section-body" style="--x-height-multiplier: 0.375; --baseline-multiplier: 0.17; font-family: medium-content-serif-font,Georgia,Cambria,\'Times New Roman\',Times,serif; font-weight: 400; font-style: normal; font-size: 21px; line-height: 1.58; letter-spacing: -.003em;">
                    ' . $before . '
                </p>
            </div>
        ';
		if(AppMode::isTestingOrStaging()){
			$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
		return $html;
	}
	/**
	 * @return bool|string
	 */
	public static function getMediumStudyTextCss(): string{
		return self::getCssStringFromFile('medium-study');
	}
	/**
	 * @return bool|string
	 */
	public static function getWpButtonCss(): string{
		return self::getCssStringFromFile('wp-button');
	}
	public static function getEmailCssStyleTag(): string{
		return HtmlHelper::renderView(view('email-css-style-tag'));
	}
	/**
	 * @param string $file
	 * @return bool|string
	 */
	public static function getCssStringFromFile(string $file): string{
		if(isset(self::$fetchedCss[$file])){
			return self::$fetchedCss[$file];
		}
		return self::$fetchedCss[$file] = file_get_contents(FileHelper::absPath('public/css/' . $file . '.css'));
	}

    /**
     * @param string $html
     * @param string $css
     * @return string
     * @throws ParseException
     */
	public static function addInlineCssToHtmlWithHead(string $html, string $css): string{
		$key = $html . $css;
		if(isset(self::$cachedHtmlWithInlineCss[$key])){
			return self::$cachedHtmlWithInlineCss[$key];
		}
		if(AppMode::isTestingOrStaging()){
			$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
        $html = CssInliner::fromHtml($html)->inlineCss($css)->render();
		//$html = StringHelper::getStringAfterSubString('<html>', $html, $html);
		//$html = str_replace('</html>', '', $html);
		//$html = str_replace('body', 'div', $html);
		if(AppMode::isTestingOrStaging()){
			$html = HtmlHelper::checkForMissingHtmlClosingTags($html, __FUNCTION__);
		}
		return self::$cachedHtmlWithInlineCss[$key] = $html;
	}
	public static function uploadCss(){
		S3Public::uploadFolder('public/css', "css", true);
	}
	public static function uploadOneCss(string $path){
		S3Public::uploadFile('public/css/' . $path, "css/$path", true);
	}
	public static function getCssLinkStylesheetTags($cssUrls = null): string{
		return self::addCssUrlsAsLinkTags('', $cssUrls);
	}
	/**
	 * @param string $html
	 * @param array|null $cssUrls
	 * @return string
	 */
	public static function addCssUrlsAsLinkTags(string $html = '', $cssUrls = null): string{
		$cssString = '';
		if(is_string($cssUrls)){
			$cssUrls = [$cssUrls];
		}
		if(!$cssUrls){
			$cssUrls = AnalyticalReport::CSS_PATHS;
		}
		foreach($cssUrls as $url){
			if(stripos($html, $url) !== false){
				continue;
			}
			$cssString .= "<link rel=\"stylesheet\" href=\"$url\" type=\"text/css\">\n";
		}
		return $cssString . $html;
	}
	/**
	 * @param string $emailHtml
	 * @param string|null $cssString
	 * @return mixed|string
	 */
	public static function inlineCss(string $emailHtml, string $cssString = null): string{
		if(empty($emailHtml)){
			le("Empty!");
		}
		$cssToInlineStyles = new CssToInlineStyles();
		if(!$cssString){
			$cssString = '';
			if(stripos($emailHtml, 'w320') !== false){
				$cssString .= self::getCssStringFromFile('study-blue');
			}
			if(stripos($emailHtml, 'class="mcn') !== false){
				$cssString .= self::getCssStringFromFile('mailchimp');
			}
			if(stripos($emailHtml, 'wp-block') !== false){
				$cssString .= self::getWpButtonCss();
			}
			if(stripos($emailHtml, 'study-') !== false){
				$cssString .= self::getMediumStudyTextCss();
			}
			if(stripos($emailHtml, 'vlp-') !== false){
				$cssString .= self::getCssStringFromFile('visual-link-preview');
			}
			if(stripos($emailHtml, 'statistics-table') !== false){
				$cssString .= self::getCssStringFromFile('statistics-table');
			}
		}
		$emailHtml = $cssToInlineStyles->convert($emailHtml, $cssString);
		if(empty($emailHtml)){
			le("Empty!");
		}
		// Why??? This messes up emails when we don't include encoding $emailHtml = self::removeHeadAndHtmlTags($emailHtml);
		if(empty($emailHtml)){
			le("Empty!");
		}
		return $emailHtml;
	}
	/**
	 * @param array $cssPathsOrUrls
	 * @param string $html
	 * @return string
	 */
	public static function inlineCssFromPathsOrUrls(array $cssPathsOrUrls, string $html): string{
		$css = CssHelper::cssPathsOrUrlsToRulesString($cssPathsOrUrls);
		try {
			$inline = CssInliner::fromHtml($html)->inlineCss($css)->renderBodyContent();
		} catch (SyntaxErrorException $e) {
			le($e);
		}
		return $inline;
	}
	/**
	 * @param array $cssUrlsOrPaths
	 * @return string
	 */
	public static function cssPathsOrUrlsToRulesString(array $cssUrlsOrPaths): string{
		$cssString = '';
		foreach($cssUrlsOrPaths as $PATH){
			if(stripos($PATH, "http") !== 0){
				$PATH = FileHelper::absPath($PATH);
			}
			$cssString .= file_get_contents($PATH);
		}
		return $cssString;
	}
	public static function generateGradientBackground(string $color): string{
		$arr = QMColor::toGradient($color);
		$one = $arr[0];
		$two = $arr[1];
		return "background: linear-gradient(60deg, $one, $two);";
	}
}
