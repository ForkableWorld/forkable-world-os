<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Utils;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\RateLimitConnectorException;
use App\Logging\QMLog;
use App\Storage\QMFileCache;
use Pandoc\Pandoc;
use Pandoc\PandocException;
class WikiHelper {
	/**
	 * @param string $variableName
	 * @return array
	 */
	public static function getWikipediaPages(string $variableName): array{
		$key = __METHOD__ . "-$variableName";
		if($cached = QMFileCache::get($key)){
			return $cached;
		}
		try {
			$wikiObject = APIHelper::callAPI('GET', 'https://en.wikipedia.org/w/api.php?format=json&action=query&' .
				'prop=extracts%7Cpageimages%7Cimages%7Cinfo&exintro=&explaintext=&redirects=1&titles=' . $variableName);
		} catch (RateLimitConnectorException $e) {
			QMLog::error(__METHOD__.": ".$e->getMessage());
			return [];
		}
		if(isset($wikiObject->error)){
			QMLog::error($wikiObject->error);
			return [];
		}
		$pages = get_object_vars($wikiObject->query->pages);
		QMFileCache::set($key, $pages);
		return $pages;
	}
	public static function getImage(string $variableName): ?string{
		$pages = self::getWikipediaPages($variableName);
		foreach($pages as $page){
			if(isset($page->thumbnail->source)){
				return $page->thumbnail->source;
			}
		}
		return null;
	}
	public static function getExtract(string $variableName): ?string{
		$pages = self::getWikipediaPages($variableName);
		foreach($pages as $page){
			if(isset($page->extract)){
				return $page->extract;
			}
		}
		return null;
	}
	/**
	 * @param string $html
	 * @return string
	 */
	public static function convertHtmlToWiki(string $html){
		try {
			$pandoc = new Pandoc();
			$options = [
				"from" => "html",
				"to" => "mediawiki",
				//"css"   => "/assets/css/documents.css"
			];
			return $pandoc->runWith($html, $options);
		} catch (PandocException $e) {
			ExceptionHandler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
			return false;
		}
	}
}
