<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Slim\View\Request\QMRequest;
use App\Types\QMStr;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\Markdown;
use App\UI\QMColor;
use App\Utils\UrlHelper;
use Illuminate\Http\RedirectResponse;
class PHPStormButton extends QMButton {
	public function __construct(string $text, string $url){
		$this->markdownBadgeLogo = Markdown::PHP;
		parent::__construct($text, $url, QMColor::HEX_PURPLE, IonIcon::bug);
		$this->tooltip = "Open in PHPStorm";
		$this->setImage(ImageUrls::PHPSTORM);
	}
	/**
	 * @param string $class
	 * @param $function
	 * @return string
	 */
	public static function urlToFunction(string $class, $function): string{
		$file = FileHelper::getFilePathToClass($class);
		try {
			$line = FileFinder::findLineNumberContainingString($file, "function " . $function);
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return PHPStormButton::redirectUrl($file, $line);
	}
	public static function getPHPStormUrlForBaseModel(string $table): string{
		$class = BaseModel::getClassByTable($table);
		$file = $class::getFilePathByTable($table);
		return PHPStormButton::redirectUrl($file);
	}
	/**
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	public static function directLink(string $file, int $line = 0): string{
		//$rel = relative_path($file);
		//return "jetbrains://php-storm/navigate/reference?project=qm-api&path=$rel&line=$line";
		//$file = FileHelper::getWindowsPath($file);
		return "phpstorm://open?file=$file" . "&line=$line";
	}
	public static function redirectUrl(string $file = null, int $line = null): string{
		if(!$file){
			$debugBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			if(!isset($debugBacktrace[1])){
				error_log(__METHOD__.": debug_backtrace() did not return a second frame.  Returning empty string.");
				return "";
			}
			$frame = $debugBacktrace[1];
			$file = $frame['file'];
			$line = $frame['line'];
		}
		$file = FileHelper::getRelativePath($file);
		$url = UrlHelper::getLocalUrl('dev/phpstorm');
		$url .= "?project=cd-api&path=$file&file=$file&line=$line";
		return $url;
	}
	public static function log(string $file = null, int $line = null, string $title = null){
		QMLog::logLink(self::redirectUrl($file, $line), $title);
	}
	/**
	 * @param string|null $file
	 * @param int|null $line
	 * @return RedirectResponse
	 */
	public static function redirectToFile(string $file = null, int $line = null): RedirectResponse{
		if(!$file){
			$file = QMRequest::getParam(['path', 'file', 'filename']);
		}
		if(!$line){
			$line = QMRequest::getParam('line') ?? 0;
		}
		return redirect(self::directLink($file, $line));
	}
    public static function generateUrlByClassFunction(int $classFunction): string{
		$file = FileHelper::classToPath(QMStr::before($classFunction, "::", $classFunction));
		$function = QMStr::after($classFunction, "::", null);
		$line = FileFinder::findLineNumberContainingString($file, "function ".$function);
		return self::redirectUrl($file, $line);
    }
}
