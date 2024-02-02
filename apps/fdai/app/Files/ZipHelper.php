<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files;
use App\Computers\ThisComputer;
use App\Types\QMStr;
use LogicException;
use ZipArchive;
use function le;
class ZipHelper extends ZipArchive {
	/**
	 * @param string $zipFilePath
	 * @param string|null $destination
	 * @return string
	 */
	public static function unzip(string $zipFilePath, string $destination = null): string{
		$zipFilePath = FileHelper::absPath($zipFilePath);
		$destination = FileHelper::absPath($destination);
		$zip = new self();
		if(!$destination){
			$destination = str_replace('.zip', DIRECTORY_SEPARATOR, $zipFilePath);
		}
		$result = $zip->open($zipFilePath);
		if($result === true){
			$zip->extractTo($destination);
			$zip->close();
			return $destination;
		}
		$errorName = "Could not extract $zipFilePath to $destination because " . $zip->getMessageForCode($result);
		throw new LogicException($errorName);
	}
	/**
	 * @param $code
	 * @return string
	 */
	public function getMessageForCode($code){
		switch($code) {
			case 0:
				return 'No error';
			case 1:
				return 'Multi-disk zip archives not supported';
			case 2:
				return 'Renaming temporary file failed';
			case 3:
				return 'Closing zip archive failed';
			case 4:
				return 'Seek error';
			case 5:
				return 'Read error';
			case 6:
				return 'Write error';
			case 7:
				return 'CRC error';
			case 8:
				return 'Containing zip archive was closed';
			case 9:
				return 'No such file';
			case 10:
				return 'File already exists';
			case 11:
				return 'Can\'t open file';
			case 12:
				return 'Failure to create temporary file';
			case 13:
				return 'Zlib error';
			case 14:
				return 'Malloc failure';
			case 15:
				return 'Entry has been changed';
			case 16:
				return 'Compression method not supported';
			case 17:
				return 'Premature EOF';
			case 18:
				return 'Invalid argument';
			case 19:
				return 'Not a zip archive';
			case 20:
				return 'Internal error';
			case 21:
				return 'Zip archive inconsistent';
			case 22:
				return 'Can\'t remove file';
			case 23:
				return 'Entry has been deleted';
			default:
				return 'An unknown error has occurred(' . intval($code) . ')';
		}
	}
	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Adds a file to a ZIP archive from the given path
	 * @link https://php.net/manual/en/ziparchive.addfile.php
	 * @param string $src <p> The path to the file to add.
	 * @param string $dest
	 * @param string|null $localname [optional]  If supplied, this is the local name inside the ZIP archive that will
	 *     override the <i>filename</i>.
	 * @return string <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function zipOne(string $src, string $dest, string $localname = null): string{
		$dest = FileHelper::absPath($dest);
		$src = FileHelper::absPath($src);
		FileHelper::deleteFile($dest, __METHOD__);
		$zip = new ZipArchive;
		$result = $zip->open($dest, ZipArchive::OVERWRITE | ZipArchive::CREATE);
		if($result === true){
			$zip->addFile($src, $localname);
			$zip->close(); // All files are added, so close the zip file.
		} else{
			throw new \RuntimeException("Could not create zip file at $dest zip->open result: " . $result);
		}
		if(!FileHelper::fileExists($dest)){
			le("Could not zip $src\n\tto $dest");
		}
		return $dest;
	}
	/**
	 * (PHP 5 &gt;= 5.2.0, PECL zip &gt;= 1.1.0)<br/>
	 * Adds a file to a ZIP archive from the given path
	 * @link https://php.net/manual/en/ziparchive.addfile.php
	 * @param string $src <p> The path to the file to add.
	 * @param string $dest
	 * @return string <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public static function zipLarge(string $src, string $dest): string{
		$dest = FileHelper::absPath($dest);
		$src = FileHelper::absPath($src);
		FileHelper::deleteFile($dest, __METHOD__);
		ThisComputer::exec("gzip -c \"$src\" > \"$dest\"");
		if(!FileHelper::fileExists($dest)){
			le("Could not zip $src\n\tto $dest");
		}
		return $dest;
	}
	/**
	 * @param $path
	 * @return bool
	 */
	public function isDir($path){
		return substr($path, -1) == DIRECTORY_SEPARATOR;
	}
	/**
	 * @return array
	 */
	public function getTree(){
		$Tree = [];
		$pathArray = [];
		for($i = 0; $i < $this->numFiles; $i++){
			$path = $this->getNameIndex($i);
			$pathBySlash = array_values(explode(DIRECTORY_SEPARATOR, $path));
			$c = count($pathBySlash);
			$temp = &$Tree;
			for($j = 0; $j < $c - 1; $j++) if(isset($temp[$pathBySlash[$j]])) $temp = &$temp[$pathBySlash[$j]]; else{
				$temp[$pathBySlash[$j]] = [];
				$temp = &$temp[$pathBySlash[$j]];
			}
			if($this->isDir($path)) $temp[$pathBySlash[$c - 1]] = []; else
				$temp[] = $pathBySlash[$c - 1];
		}
		return $Tree;
	}
	/**
	 * @param string $extractedFilePath
	 */
	public static function unzipIfNecessary(string $extractedFilePath){
		if(!file_exists($extractedFilePath)){
			$zipPath = QMStr::beforeLast(DIRECTORY_SEPARATOR, $extractedFilePath) . '.zip';
			self::unzip($zipPath);
		}
	}
}
