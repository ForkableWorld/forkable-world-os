<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Override\QMErrorPageHandler;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Storage\Memory;
use App\Storage\S3\S3PrivateGlobal;
use App\Types\TimeHelper;
use App\Utils\EnvOverride;
use App\Utils\N8N;
use App\Utils\UrlHelper;
use Facade\Ignition\QueryRecorder\QueryRecorder;
use Throwable;
class QMIgnition
{
    public const IGNITION_REPORTS = 'ignition-reports';
	public static $ignitionUrls;
	/**
	 * @param string $html
	 * @return string|string[]
	 */
	public static function addPathPlaceholders(string $html){
		$base_path = base_path();
		$withSlashes = self::base_path_with_slashes();
		$replacedPath = str_replace($base_path, '__QM_API__', $html);
		$replacedPath = str_replace($withSlashes, '__QM_API_SLASHES__', $replacedPath);
		return $replacedPath;
	}
	public static function base_path_with_slashes(): string{
		$p = str_replace('/', '\\/', base_path());
		return $p;
	}
	/**
	 * @param Throwable $e
	 * @return QMErrorPageHandler
	 */
	public static function errorPageHandler(Throwable $e): QMErrorPageHandler{
		return QMErrorPageHandler::get($e);
	}
	/**
	 * @param Throwable $e
     * @return string
     */
	public static function getIgnitionReportHtml(Throwable $e): string{
		$h = self::errorPageHandler($e);
		$html = $h->getHtml($e);
		return $html;
	}
	public static function generateUrl(Throwable $e): string{
		$url = static::$ignitionUrls[$e->getMessage()] ?? '';
		if(!$url){
			try {
				$html = self::getIgnitionReportHtml($e);
			} catch (\Throwable $e) {
				QMLog::info("Returning console button instead of ignition because" . $e->getMessage());
				return JenkinsConsoleButton::instance()->getUrl();
			}
			$time = TimeHelper::toCarbon(time())->format('H:i:s'); // Limits number stored to 86400
			$time = FirebaseGlobalPermanent::formatKey($time);
			// We shouldn't need this if we use relative paths $html = self::addPathPlaceholders($html);
			$result = S3PrivateGlobal::uploadHTML(self::getReportPath($time), $html, false);
			if(!$result){
				QMLog::error("Could not upload ignition report!");
			}
			$url = UrlHelper::getLocalUrl('admin/ignitionReport', ['time' => $time]);
		}
		LinksLogMetaData::add(LinksLogMetaData::IGNITION, $url);
		QMLog::linkButton(get_class($e) . " Report", $url);
		LinksLogMetaData::add('IGNITION_REPORT', $url);
		static::$ignitionUrls[$e->getMessage()] = $url;
		return $url;
	}
	public static function getUrlOrGenerateAndOpen(Throwable $e): string{
		if(isset($e->ignitionUrl)){
			return $e->ignitionUrl;
		}
		if(isset(self::$ignitionUrls[get_class($e)])){
			return self::$ignitionUrls[get_class($e)];
		}
		$url = self::generateUrl($e);
		if(EnvOverride::getFormatted('OPEN_IGNITION_REPORT_IN_BROWSER')){
			N8N::openUrl($url);
		}
		return self::$ignitionUrls[get_class($e)] = $e->ignitionUrl = $url;
	}
	/**
	 * @param string $time
	 * @return string
	 */
	public static function getReportPath(string $time): string{
		return self::IGNITION_REPORTS . '/' . $time . ".html";
	}
	public static function getUrl(): ?string {
		return LinksLogMetaData::find(LinksLogMetaData::IGNITION);
	}
	/**
     * @return QueryRecorder
     */
    public static function queryRecorder(): QueryRecorder{
	    /** @var QueryRecorder $r */
        if($r = Memory::getByPrimaryKey(Memory::IGNITION_QUERY_RECORDER)){
            return $r;
        }
        /** @var QueryRecorder $r */
        $r = resolve(QueryRecorder::class);
        Memory::setByPrimaryKey(Memory::IGNITION_QUERY_RECORDER, $r);
        return $r;
    }
	/**
	 * @noinspection PhpUnused
	 */
	public static function pruneReportsFromDO(){
        S3PrivateGlobal::deleteAllFilesDirectory(self::IGNITION_REPORTS);
    }
	/**
	 * @param string $html
	 * @return string
	 */
	public static function replace_path_placeholders(string $html): string{
		$base_path = base_path();
		$html = str_replace('__QM_API__', $base_path, $html);
		$html = str_replace('__QM_API_SLASHES__', self::base_path_with_slashes(), $html);
		$local = str_replace("\\", "\\\\", config('ignition.local_sites_path'));
		$html = str_replace("/www/wwwroot/default.quantimo.do",
		                    $local, $html);
		$html = str_replace("\/www\/wwwroot\/default.quantimo.do",
		                    $local, $html);
		$html = str_replace("\/home\/ubuntu\/qm-api",
		                    $local, $html);
		$html = str_replace("/home/ubuntu/qm-api",
		                    $local, $html);
		$html = str_replace('/__w/curedao-api/curedao-api',
		                    $local, $html);
		$html = str_replace('\/__w\/curedao-api\/curedao-api',
		                    $local, $html);
		$html = str_replace('\\\\\\\\wsl$\\\\Ubuntu-22..04\\\\www\\\\wwwroot\\\\cd-api',
		                    $local, $html);
		$html = str_replace('feature.quantimo.do',
		                    'testing.quantimo.do', $html);
		return $html;
	}
}
