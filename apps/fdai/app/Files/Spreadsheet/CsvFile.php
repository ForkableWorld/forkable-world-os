<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Spreadsheet;
use App\Files\FileHelper;
use App\Files\Json\JsonFile;
use App\Folders\DynamicFolder;
use App\Logging\QMLog;
class CsvFile extends AbstractSpreadsheetFile {
	/**
	 * @param string $sourceJsonPath
	 * @param string|null $outputPath
	 * @return void
	 */
	public static function jsonToCsv(string $sourceJsonPath, string $outputPath = null){
		if(!$outputPath){
			$outputPath = str_replace('.json', '.csv', $sourceJsonPath);
		}
		$sourceArray = JsonFile::getArray($sourceJsonPath);
		$headers =  self::getHeaders($sourceArray);
		self::arrayToCsv($headers, $sourceArray, $outputPath);
	}
	public static function getDefaultFolderRelative(): string{
		return DynamicFolder::STORAGE . "/" . static::getDefaultExtension();
	}
	public static function getDefaultExtension(): string{
		return "csv";
	}

    /**
     * @param string $path
     * @param bool $associativeArray
     * @return array
     */
    public static function readCsv(string $path, bool $associativeArray = true): array
    {
        return QMSpreadsheet::getDataFromSpreadsheet($path, $associativeArray);
    }
	private static function arrayToCsv(array $headers, array $data, string $outputPath): void {
		$outputPath = abs_path($outputPath);
		@unlink($outputPath);
		$file = fopen($outputPath, 'wb');
		QMLog::info('Creating ' . $outputPath . '...');
		// output the column headings
		fputcsv($file, $headers);
		foreach ($data as $m) {
			$row = [];
			foreach($headers as $header){
				$value = $m[$header] ?? '';
				if(is_array($value)){
					$value = json_encode($value);
				}
				$row[] = $value;
			}
			fputcsv($file, $row);
		}
		fclose($file);
	}
	private static function getHeaders(array $arr){
		$headers = [];
		foreach($arr as $i => $item){
			foreach($item as $key => $value){
				if(!in_array($key, $headers)){
					$headers[] = $key;
				}
			}
		}
		return $headers;
	}
}
