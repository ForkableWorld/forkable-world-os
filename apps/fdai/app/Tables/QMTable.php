<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Studies\QMStudy;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\CssHelper;
use App\Utils\AppMode;
use Illuminate\Support\Collection;
use jc21\CliTable;
class QMTable {
	/**
	 * @return bool|string
	 */
	public static function getTableCss(): string{
		return CssHelper::getCssStringFromFile('statistics-table');
	}
	/**
	 * @param array $array
	 * @return string
	 */
	public static function associativeArrayToTable(array $array): string{
		$html = "
        <div style='margin: 30px auto; max-width: 599px;'>
            <table border='1' cellpadding='5'
                class='statistics-table'
                style='border-style: solid; margin: auto;'>
                <tbody>";
		foreach($array as $key => $value){
			if(is_array($value)){
				continue;
			}
			$html .= "
                <tr>
                    <td> $key </td>
                    <td> $value </td>
                </tr>";
		}
		$html .= '
                </tbody>
            </table>
        </div>
        ';
		return $html;
	}
	/**
	 * @param object|array $object
	 * @param string $title
	 * @param bool $excludeIds
	 * @return string
	 */
	public static function convertObjectToVerticalPropertyValueTableHtml($object, string $title = null,
		bool $excludeIds = true): string{
		$rows = '';
		foreach($object as $key => $value){
			if($excludeIds && str_contains($key, 'Id')){
				continue;
			}
			if($value === null || is_object($value) || is_array($value)){
				continue;
			}
			$rows .= '
                <tr>
                    <td class="text-left">
                        ' . QMStr::camelToTitle($key) . '
                    </td>
                    <td class="text-left">
                        ' . $value . '
                    </td>
                </tr>';
		}
		$titleHtml = '';
		$id = QMStr::slugify($title);
		if($title){
			$titleHtml = "
<div class=\"table-title\">
    <h3 style=\"color: black;\" id=\"$id-table-heading\">$title</h3>
</div>
";
		}
		$tableWidth = CssHelper::DEFAULT_TABLE_WIDTH . "px";
		$html = "
            <div class=\"study-text\" style='max-width: $tableWidth; margin: auto; padding: 10px;'>
                $titleHtml
                <table class=\"table-fill statistics-table\">
                    <thead>
                        <tr>
                            <th class=\"text-left\">Property</th>
                            <th class=\"text-left\">Value</th>
                        </tr>
                    </thead>
                    <tbody class=\"table-hover\">
                    $rows
                    </tbody>
                </table>
            </div>
        ";
		if(QMStudy::weShouldGenerateFullStudyWithChartsCssAndInstructions()){
			//$html = CssHelper::addInlineCssToHtmlWithHead($html, self::getTableCss());
		}
		if(stripos($html, "Average  Predictor  Treatment  Value") !== false){
			le("");
		}
		return $html;
	}
	/**
	 * @param array $rows
	 * @return string
	 */
	public static function arrayToHtmlTable(array $rows): string{
		lei(!isset($rows[0]), "Header row 0 not set!");
		// Don't include more than one header row or we can't sort!
		$html = "
            <table border='1' cellpadding='5' class='sortable-theme-minimal statistics-table'
                style='border-style: solid; margin: 5px;' data-sortable>
                <thead>
                    <tr>";
		foreach($rows[0] as $rowNumber => $row){
			$html .= '
                        <th style="padding: 5px;">' . $rowNumber . '</th>
                    ';
		}
		$html .= '
                    </tr>
                </thead>
                <tbody>';
		foreach($rows as $rowNumber => $row){
			$html .= '
                    <tr>';
			foreach($row as $heading => $cellOrValue){
				if($cellOrValue instanceof TableCell){
					$cell = $cellOrValue;
				} else{
					$cell = new TableCell($cellOrValue);
				}
				$html .= $cell->toHtml();
			}
			$html .= '
                    </tr>';
		}
		$html .= '
                </tbody>
            </table>';
		return $html;
	}
	/**
	 * @param Collection $collection
	 * @param string|null $title
	 * @return string
	 */
	public static function collectionToConsoleTable(Collection $collection, string $title = null): string{
		if($title){
			$title = QMStr::titleCaseSlow($title);
			ConsoleLog::info("=== " . $title . " ===");
		}
		$first = $collection->first();
		if(is_object($first) && method_exists($first, 'getLogMetaData')){
			$meta = $first->getLogMetaData();
		} else{
			$meta = QMArr::removeNullsObjectsAndArrays((array)$first);
		}
		$headers = array_keys($meta);
		$table = new CliTable;
		if(!AppMode::isJenkins()){
			QMLog::debug("App mode not jenkins so using colors");
			$table->setTableColor('blue');
			$table->setHeaderColor('cyan');
		}
		$data = [];
		foreach($headers as $header){
			$color = array_rand(['white', 'red', 'green', 'blue']);
			if(AppMode::isJenkins()){
				$color = null;
			}
			$table->addField(QMStr::titleCaseSlow($header), $header, false, $color);
		}
		foreach($collection as $i => $item){
			if(is_object($first) && method_exists($first, 'getLogMetaData')){
				$data[$i] = $item->getLogMetaData();
			} else{
				$data[$i] = QMArr::removeNullsObjectsAndArrays((array)$item);
			}
			foreach($headers as $header){
				if(!isset($data[$i][$header])){
					$data[$i][$header] = null;
				}
			}
		}
		$table->injectData($data);
		$str = $table->get();
		return "\n".$str;
	}
	public static function arrayToCliTable(array $data, string $itemName = 'Row', bool $useColors = false): string{
		$table = new CliTable($itemName, $useColors);
		$keys = array_keys($data[0]);
		foreach($keys as $key){
			$table->addField(QMStr::titleCaseSlow($key), $key);
		}
		$table->injectData($data);
		return $table->get();
	}
	public static function arrayToMarkdownTable(array $data): string{
		$str = "\n---";
		$keys = array_keys($data[0]);
		foreach($keys as $key){
			$str .= " " . QMStr::titleCaseSlow($key) . " |";
		}
		$str .= "\n|";
		/** @noinspection PhpUnusedLocalVariableInspection */
		foreach($keys as $key){
			$str .= " :-------: |";
		}
		foreach($data as $row){
			$str .= "\n| ";
			foreach($row as $cell){
				$str .= $cell . " |";
			}
		}
		return $str . "\n---";
	}
}
