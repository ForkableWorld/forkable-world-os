<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Spreadsheet;
use App\Files\FileHelper;
use App\Files\ZipHelper;
use App\Logging\QMLog;
use App\Tables\TableCell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use function le;
/** Class SpreadsheetHelper
 * @package App\Utils
 */
class QMSpreadsheet extends Spreadsheet {
	/**
	 * @param string $filePath
	 * @param int $sheetNumber
	 * @return array
	 */
	public static function getWorksheetAssociativeArray(string $filePath, int $sheetNumber = 0): array{
		$filePath = FileHelper::absPath($filePath);
		$objPHPExcel = IOFactory::load($filePath);
		try {
			$worksheet = $objPHPExcel->getSheet($sheetNumber);
		} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
			le($e);
		}
		$data = $worksheet->toArray();
		return $data;
	}
	/**
	 * @param array $dataArray
	 * @param string $filename
	 * @param string $relativePath
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws Exception
	 */
	public static function writeToSpreadsheet(array $dataArray, string $filename, string $relativePath): string{
		if(!$dataArray){
			le("No data provided to " . __FUNCTION__);
		}
		$objPHPExcel = new Spreadsheet();
		$sheet = $objPHPExcel->getActiveSheet();
		self::headerRow($dataArray, $sheet);
		$rowNumber = 1;
		foreach($dataArray as $dataRow){
			$rowNumber++;
			$columnLetter = 'A';
			foreach($dataRow as $value){
				$cell = $sheet->getCell($columnLetter . $rowNumber);
				if($value instanceof TableCell){
					$value->toSpreadsheetCell($cell);
				} else{
					$cell->setValue($value);
				}
				$columnLetter++;
			}
		}
		$filePath = self::formatAndSave($filename, $sheet, $objPHPExcel, $relativePath);
		return $filePath;
	}
	/**
	 * @param array $dataArray
	 * @param string $filename
	 * @param string $relativePath
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws Exception
	 */
	public static function writeToSpreadsheetHorizontally(array $dataArray, string $filename,
		string $relativePath): string{
		$objPHPExcel = new Spreadsheet();
		$sheet = $objPHPExcel->getActiveSheet();
		$rowNumber = 0;
		foreach($dataArray as $name => $value){
			$sheet->getCell('A' . $rowNumber)->setValue($name);
			$sheet->getCell('B' . $rowNumber)->setValue($value);
			$rowNumber++;
		}
		$filePath = self::formatAndSave($filename, $sheet, $objPHPExcel, $relativePath);
		return $filePath;
	}
	/**
	 * @param Spreadsheet $objPHPExcel
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	private static function autoSizeColumns(Spreadsheet $objPHPExcel){
		// Auto size columns for each worksheet
		foreach($objPHPExcel->getWorksheetIterator() as $worksheet){
			$objPHPExcel->setActiveSheetIndex($objPHPExcel->getIndex($worksheet));
			$sheet = $objPHPExcel->getActiveSheet();
			$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(true);
			foreach($cellIterator as $cell){
				$sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
			}
		}
	}
	/**
	 * @param array $dataArray
	 * @param Worksheet $sheet
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 */
	private static function headerRow(array $dataArray, Worksheet $sheet){
		if(!$dataArray){
			le("No data provided to " . __FUNCTION__);
		}
		$columnLetter = 'A';
		foreach($dataArray[0] as $key => $value){
			$cell = $sheet->getCell($columnLetter . '1');
			$title = str_replace(" ", "\n", $key);
			$cell->setValue($title);
			$columnLetter++;
		}
	}
	/**
	 * @param $input_array
	 * @param $output_file_name
	 * @param $delimiter
	 */
	public static function convertToCsvDownload($input_array, $output_file_name, $delimiter = ','){
		/** open raw memory as file, no need for temp files */
		$temp_memory = fopen('php://memory', 'wb');
		/** loop through array  */
		foreach($input_array as $line){
			/** default php csv handler **/
			fputcsv($temp_memory, $line, $delimiter);
		}
		/** rewind the "file" with the csv lines **/
		fseek($temp_memory, 0);
		/** modify header to be downloadable csv file **/
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="' . $output_file_name . '";');
		/** Send file to browser for download */
		fpassthru($temp_memory);
	}
	/**
	 * @param array $inputArray
	 * @param $outputFilePath
	 * @param string $delimiter
	 * @return string
	 */
	public static function convertToCsvFile(array $inputArray, $outputFilePath, $delimiter = ','): string{
		$outputFilePath = FileHelper::absPath($outputFilePath);
		$fp = fopen($outputFilePath, 'w');
		foreach($inputArray as $line){
			fputcsv($fp, $line, $delimiter);
		}
		fclose($fp);
		return $outputFilePath;
	}
	/**
	 * @param string $filename
	 * @param Worksheet $sheet
	 * @param Spreadsheet $objPHPExcel
	 * @param string $relativePath
	 * @return string
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws Exception
	 */
	private static function formatAndSave(string $filename, Worksheet $sheet, Spreadsheet $objPHPExcel,
		string $relativePath): string{
		$sheet->getStyle('A1:' . $objPHPExcel->getActiveSheet()->getHighestColumn() . '1')->getAlignment()
			->setWrapText(true);
		$sheet->freezePane('B2');
		self::autoSizeColumns($objPHPExcel);
		$objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
		$folderPath = FileHelper::absPath($relativePath);
		$filePath = $folderPath . DIRECTORY_SEPARATOR . $filename . '.xlsx';
		FileHelper::createDirectoryIfNecessary($folderPath);
		FileHelper::deleteDirectoryOrFileIfNecessary($filePath); // Accidentally created directory
		QMLog::info("Saving $filePath...");
		$objWriter->save($filePath);
		return $filePath;
	}
	/**
	 * @param string $path
	 * @param bool $associativeArray
	 * @return array
	 */
	private static function getDataFromXlsSpreadsheetFileSlow(string $path, bool $associativeArray = true): array{
		$s = microtime(true);
		QMLog::info("Loading $path spreadsheet...");
		ZipHelper::unzipIfNecessary($path);
		$objPHPExcel = IOFactory::load($path);
		QMLog::info("Converting parsed spreadsheet to array...");
		$spreadsheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, $associativeArray);
		$d = microtime(true) - $s;
		QMLog::infoWithoutContext(__FUNCTION__ . " took " . $d . " seconds");
		return $spreadsheetData;
	}
	/**
	 * @param string $path
	 * @param bool $associativeArray
	 * @return array
	 */
	public static function getDataFromSpreadsheet(string $path, bool $associativeArray = true): array{
		QMLog::info("Reading $path spreadsheet...");
		$ext = FileHelper::getExtension($path);
		if($ext === "xls" || $ext === "xlsx"){
			$spreadsheetData = self::getDataFromXlsSpreadsheetFileSlow($path, $associativeArray);
		} else{
			$spreadsheetData = self::getDataFromCsvSpreadsheetFileFast($path, $associativeArray);
		}
		return $spreadsheetData;
	}
	/**
	 * @param string $path
	 * @param bool $associativeArray
	 * @return array
	 */
	private static function getDataFromCsvSpreadsheetFileFast(string $path, bool $associativeArray = true): array{
		$s = microtime(true);
		$csv = file_get_contents($path);
		$csvLines = explode("\n", $csv);
		$indexes = str_getcsv(array_shift($csvLines));
		$numberOfIndices = count($indexes);
		$indexed = [];
		if(!$associativeArray){
			$indexed[] = $indexes;
		}
		foreach($csvLines as $index => $lineString){
			$lineArray = str_getcsv($lineString);
			$numberOfValues = count($lineArray);
			if(count($lineArray) === $numberOfIndices){
				if($associativeArray){
					$indexed[] = array_combine($indexes, $lineArray);
				} else{
					$indexed[] = $lineArray;
				}
			} else{
				QMLog::info("The number of values ($numberOfValues) in row $index of $path don't match number of " .
					"headers ($numberOfIndices)! Here's the date for this row: $lineString");
			}
		}
		$d = microtime(true) - $s;
		QMLog::infoWithoutContext(__FUNCTION__ . " took " . $d . " seconds");
		return $indexed;
	}
	public static function readCsv(string $path): array{
		$path = FileHelper::absPath($path);
		$objPHPExcel = IOFactory::load($path);
		$data = $objPHPExcel->getActiveSheet()->toArray();
		return $data;
	}
}
