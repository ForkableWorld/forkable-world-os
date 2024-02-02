<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Repos\StaticDataRepo;
use App\Units\ServingUnit;
use App\VariableCategories\FoodsVariableCategory;
use App\Variables\QMCommonVariable;

class FoodDBImportJob extends JobTestCase {
	public function testImportUPCS(): void{
		// Download from https://fdc.nal.usda.gov/download-datasets.html
		// https://www.ars.usda.gov/northeast-area/beltsville-md-bhnrc/beltsville-human-nutrition-research-center/food-surveys-research-group/docs/fndds-download-databases/
		$clientId = 'food-upcs';
		BaseClientIdProperty::setInMemory($clientId);
		$spreadsheetData = QMSpreadsheet::getDataFromSpreadsheet(StaticDataRepo::getAbsPath() . '/food/' .
		                                                         'Small Test Version of ' . 'food-upcs.csv');
		$headers = $spreadsheetData[0];
		unset($spreadsheetData[0]);
		foreach($spreadsheetData as $foodRow){
			$name = $foodRow[11];
			$upc = $foodRow[13];
			if(empty($upc)){continue;}
			$foodVariable = QMCommonVariable::findOrCreateByName($name, [
				'variableCategoryName' => FoodsVariableCategory::NAME,
				'unitName' => ServingUnit::NAME,
				Variable::FIELD_IS_PUBLIC => true,
				'clientId' => $clientId,
				Variable::FIELD_BRAND_NAME => $foodRow[2],
				Variable::FIELD_UPC_14 => $upc
			]);
			$foodVariable->updateUpcIfNecessary($upc);
		}
	}
	public function testImportFNDDS(): void{
		// Download from https://fdc.nal.usda.gov/download-datasets.html
		// https://www.ars.usda.gov/northeast-area/beltsville-md-bhnrc/beltsville-human-nutrition-research-center/food-surveys-research-group/docs/fndds-download-databases/
		$clientId = 'FNDDS';
		BaseClientIdProperty::setInMemory($clientId);
		$spreadsheetData = QMSpreadsheet::getDataFromSpreadsheet(StaticDataRepo::getAbsPath() . '/food/' .
		                                                         //'Small Test Version of 2015-2016 FNDDS At A Glance - FNDDS Nutrient Values.xlsx'
		                                                         '2015-2016 FNDDS At A Glance - FNDDS Nutrient Values.xlsx');
		$headers = $spreadsheetData[1];
		unset($spreadsheetData[0], $spreadsheetData[1]);
		foreach($spreadsheetData as $foodRow){
			$name = $foodRow[1];
			$foodVariable = QMCommonVariable::findOrCreateByName($name, [
				'variableCategoryName' => FoodsVariableCategory::NAME,
				'unitName' => ServingUnit::NAME,
				Variable::FIELD_IS_PUBLIC => true,
				'clientId' => $clientId
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
				if(!count($ingredientsAfter)){
					$foodVariable->logError("No ingredients after import!");
				}
				//$this->assertGreaterThan(0, count($ingredientsAfter));
				$i++;
			}
		}
	}
}
