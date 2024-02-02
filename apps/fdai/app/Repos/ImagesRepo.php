<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\UI\ImageHelper;
use App\UI\ImageUrls;
class ImagesRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'qm-images';
	public const USERNAME = 'mikepsinn';
	public const DEFAULT_BRANCH = 'main';
	public const LOCAL_TO_S3_PATH_MAP = ['' => 'img'];
	public const PATH_variable_categories_combined = "variable_categories_combined";
	public const PATH_variable_categories_combined_robot = "variable_categories_combined_robot";
	public const PATH_variable_categories_combined_logo_robot = "variable_categories_combined_logo_robot";
	public const PATH_variable_categories_combined_robot_background = "variable_categories_combined_robot_background";
	public const PATH_variable_categories_gauges = "variable_categories_gauges";
	public const PATH_variable_categories_gauges_background = "variable_categories_gauges_background";
	public const PATH_variable_categories_gauges_logo = "variable_categories_gauges_logo";
	public const PATH_variable_categories_gauges_logo_background = "variable_categories_gauges_logo_background";
	public const PATH_variable_categories_pngs = "variable_categories_pngs";
	/**
	 * @param string $html
	 * @return string
	 */
	public static function replaceImageUrlsWithLocalPaths(string $html): string{
		static::cloneIfNecessary();
		$html = str_replace(ImageHelper::BASE_URL, static::getAbsPath() . '/', $html);
		return $html;
	}
	public static function uploadToS3Public(bool $overwrite = false): array{
		$urls = parent::uploadToS3Public($overwrite);
		ImageUrls::outputConstants();
		return $urls;
	}
	public static function listUrlsInFolder(string $folder): array{
		$files = static::listFilesInFolder($folder);
		$path = static::getAbsolutePath();
		$urls = [];
		foreach($files as $file){
			$url = str_replace($path, "https://static.quantimo.do/img", $file);
			$urls[] = $url;
		}
		return $urls;
	}
	public static function outputConstantsForFolder(string $folder){
		$urls = self::listUrlsInFolder($folder);
		foreach($urls as $url){
			ImageUrls::outputConstant($url);
		}
	}
	public static function convertLargePngsToJpgs(){
		$largePaths = [
			self::PATH_variable_categories_gauges_background,
			self::PATH_variable_categories_gauges_logo_background,
		];
		foreach($largePaths as $largePath){
			$files = self::listFilesInFolder($largePath, ".png");
			foreach($files as $file){
				//$abs = self::getAbsolutePath($file);
				ImageHelper::convertPngToJpg($file);
			}
		}
	}
	public static function postUpdate(){
		parent::postUpdate();
	}
}
