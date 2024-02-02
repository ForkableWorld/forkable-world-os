<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Models\User;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Variable\VariableNameProperty;
use App\Repos\StaticDataRepo;
use App\Types\QMStr;
use App\Units\IndexUnit;
use App\Units\ServingUnit;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
/** @package App\PhpUnitJobs
 */
class SupplementDBImportJob extends JobTestCase {
	public function testImportSpreadSheet(){
		$arr = QMSpreadsheet::readCsv('data.csv');
		$values = [];
		foreach($arr as $i => $row){
			if($i < 10){
				continue;
			}
			$value = $row[14];
			if(!$value || $value === "NA"){
				continue;
			}
			$date = $row[0];
			$year = QMStr::before(".", $date);
			$month = QMStr::after(".", $date);
			if($month === "1"){$month = 10;}
			$at = db_date("$year-$month-01");
			$values[$at] = $value;
		}
		$uv = User::econ()->findOrCreateQMUserVariable(VariableNameProperty::SP_CYCLICALLY_ADJUSTED_PRICE_EARNINGS_RATIO_OR_CAPE, [
			Variable::FIELD_VARIABLE_CATEGORY_ID => EconomicIndicatorsVariableCategory::ID,
			Variable::FIELD_DEFAULT_UNIT_ID => IndexUnit::ID,
		]);
		$measurements = [];
		foreach($values as $at => $value){
			$m = $uv->newMeasurementByValueTime($at, $value, IndexUnit::NAME);
			$measurements[] = $m;
		}
		$uv->saveMeasurements($measurements);
	}
	public function testDSLDSupplementSpreadsheetImporterJob(): void{
		// https://dietarysupplementdatabase.usda.nih.gov/
		$clientId = 'DSLD';
		BaseClientIdProperty::setInMemory($clientId);
		$spreadsheetData = QMSpreadsheet::getDataFromSpreadsheet(StaticDataRepo::getAbsPath() . '/supplements/' .
		                                                         'Small Test Verions of lstProducts.csv'//'2015-2016 FNDDS At A Glance - FNDDS Nutrient Values.xlsx'
		);
		$headers = $spreadsheetData[4];
		unset($spreadsheetData[0], $spreadsheetData[1], $spreadsheetData[2], $spreadsheetData[3], $spreadsheetData[4]);
		foreach($spreadsheetData as $foodRow){
			$name = $foodRow[2];
			$i = 0;
			$json = [];
			foreach($headers as $header){
				$json[$header] = $foodRow[$i];
				$i++;
			}
			$foodVariable = QMCommonVariable::findOrCreateByName($name, [
				'variableCategoryName' => TreatmentsVariableCategory::NAME,
				'unitName' => ServingUnit::NAME,
				Variable::FIELD_IS_PUBLIC => true,
				'clientId' => $clientId,
				Variable::FIELD_ADDITIONAL_META_DATA => json_encode($json),
				Variable::FIELD_BRAND_NAME => $json["Brand Name"]
			]);
			$parentName = $foodRow[3];
			$foodVariable->addParentCommonTag($parentName, [
				'variableCategoryName' => FoodsVariableCategory::NAME,
				'unitName' => ServingUnit::NAME,
				Variable::FIELD_IS_PUBLIC => true,
				'clientId' => $clientId
			]);
			$i = 4;
			while(isset($headers[$i]) && $i < 49){ // Not sure what this weird 4:0    (g) stuff after 49 is
				$ingredientName = $headers[$i];
				$ingredientValue = $foodRow[$i];
				$foodVariable->addIngredientTag($ingredientName, $ingredientValue, null, $clientId);
				$ingredientsAfter = $foodVariable->getIngredientCommonTagVariables();
				$this->assertGreaterThan(0, count($ingredientsAfter));
				$i++;
			}
		}
	}
}
