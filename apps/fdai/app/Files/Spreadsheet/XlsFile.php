<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Spreadsheet;
use App\Folders\DynamicFolder;
class XlsFile extends AbstractSpreadsheetFile {
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::STORAGE . "/" . static::getDefaultExtension();
	}
	public static function getDefaultExtension(): string{
		return "xls";
	}
}
