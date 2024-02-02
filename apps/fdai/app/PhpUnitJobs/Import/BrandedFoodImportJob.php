<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection MultiAssignmentUsageInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace App\PhpUnitJobs\Import;
use App\Exceptions\NotGitRepositoryException;
use App\Exceptions\QMException;
use App\Files\FileHelper;
use App\Files\Spreadsheet\QMSpreadsheet;
use App\Files\ZipHelper;
use App\Logging\QMLog;
use App\Models\Variable;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseClientIdProperty;
use App\Repos\ReferenceDataRepo;
use App\Storage\QMFileCache;
use App\Storage\MemoryOrRedisCache;
use App\Types\QMStr;
use App\Units\ServingUnit;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\Variables\QMCommonVariable;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

/** @package App\PhpUnitJobs
 */
class BrandedFoodImportJob extends JobTestCase {
    private $nutrientData;
    private $productData;
    private $servingSizeData;
    private const ZIP_PATH = 'tmp/qm-reference-databases/food/USDA Branded Food Products Database/BFPD_csv_07132018.zip';
    private const FILE_PATH = 'tmp/qm-reference-databases/unzipped/BFPD_csv_07132018/';
    //private const FILE_PATH = '/food/USDA Branded Food Products Database/test-BFPD_csv_07132018/';
    protected const CLIENT_ID = 'USDA';
    /** @var int|QMCommonVariable */
    private $parentFood;
    public function testUSDABrandedFoodImport(): void{
        ReferenceDataRepo::clonePullAndOrUpdateRepo();
        ZipHelper::unzip(self::ZIP_PATH, self::FILE_PATH);
        // Download from https://ndb.nal.usda.gov/ndb/search/list
        BaseClientIdProperty::setInMemory(self::CLIENT_ID);
        $productData = $this->getProductData();
        $total = count($productData);
        $i = 0;
        foreach($productData as $id => $product){
            $this->parentFood = false;
            $i++;
            QMLog::infoWithoutContext("=== Completed $i of $total products... ===");
            $cacheKey = __FUNCTION__.'_'.$id;
            $done = MemoryOrRedisCache::get($cacheKey);
            if($done){continue;}
            $foodVariable = $this->getFoodVariable($product);
            //$this->addParentCategory($foodRow, $foodVariable, $clientId);
            $this->addNutrientTags($id, $foodVariable);
            $this->addIngredientTags($product["ingredients_english"], $foodVariable);
            if($parentFood = $this->parentFood){
                /** @var QMCommonVariable $parent */
                $this->addNutrientTags($id, $parentFood);
                $this->addIngredientTags($product["ingredients_english"], $parentFood);
            }
            QMFileCache::set($cacheKey, true, 86400 * 7);
        }
    }
    /**
     * @param string $name
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function getData(string $name): array{
/*        try {
            $fromJson = $this->getFromJson($name);
            if($fromJson){return $fromJson;}
        } catch (FileNotFoundException $exception){
            QMLog::info($exception->getMessage());
        }*/
        QMLog::info("Getting $name from spreadsheet...");
        return $this->getFromSpreadsheet($name);
    }
    /**
     * @param string $id
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function getServingSizeDataForProduct(string $id): ?array{
        $servingSizeData = $this->getServingSizeData();
        return $servingSizeData[$id] ?? null;
    }
    /**
     * @param string $id
     * @return array
     */
    private function getNutrientDataForProduct(string $id): ?array{
        $allProducts = $this->getNutrientData();
        $nutrientRows = $allProducts[$id];
        $tagParams = [];
        foreach($nutrientRows as $nutrientRow){
            $value = (float)$nutrientRow["Output_value"];
            if(!$value || $value === 0.0){
                continue;
            }
            $tagParams[$nutrientRow["Nutrient_name"]] = [
                'conversionFactor'    => $value,
                'unitAbbreviatedName' => $nutrientRow["Output_uom"]
            ];
        }
        return $tagParams;
    }
    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function getServingSizeData(): array {
        if($this->servingSizeData){return $this->servingSizeData;}
        $data = $this->getData('Serving_size');
        foreach($data as $datum){
            foreach($datum as $key => $value){
                if(empty($value)){unset($datum[$key]);}
            }
            $this->servingSizeData[$datum["NDB_No"]] = $datum;
        }
        return $this->servingSizeData;
    }
    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function getProductData(): array {
        if($this->productData){return $this->productData;}
        $data = $this->getData('Products');
        foreach($data as $datum){
            $this->productData[$datum["NDB_Number"]] = $datum;
        }
        return $this->productData;
    }
    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    private function getNutrientData(): array {
        if($this->nutrientData){return $this->nutrientData;}
        $data = $this->getData('Nutrients');
        foreach($data as $datum){
            $this->nutrientData[$datum["NDB_No"]][] = $datum;
        }
        return $this->nutrientData;
    }
    /**
     * @param $foodRow
     * @param QMCommonVariable $foodVariable
     */
    private function addParentCategory($foodRow, QMCommonVariable $foodVariable): void{
        $parentName = $foodRow[3];
        $foodVariable->addParentCommonTag($parentName, [
            'variableCategoryName' => FoodsVariableCategory::NAME,
            'unitName'             => ServingUnit::NAME,
            Variable::FIELD_IS_PUBLIC               => true,
            'clientId'             => self::CLIENT_ID
        ]);
    }
    /**
     * @param $foodRow
     * @return QMCommonVariable
     */
    private function getFoodVariable($foodRow): QMCommonVariable{
        $id = $foodRow["NDB_Number"];
        $upc = QMStr::before(".", (string)$foodRow["gtin_upc"], (string)$foodRow["gtin_upc"]);
        $brand = $foodRow["manufacturer"];
        $variableName = $longName = $foodRow["long_name"];
        $parentVariableName = false;
        if(stripos($variableName, $brand) === false){
            $variableName = $longName.' from '.$brand;
            $parentVariableName = $longName;
        }
        $meta = [
            //'ingredients' => $foodRow["ingredients_english"], // We already do this with tags
            "NBD_Number" => $id
        ];
        $servingSize = $this->getServingSizeDataForProduct($id);
        if($servingSize){$meta['servingSizeData'] = $servingSize;}
        $params = [
            'variableCategoryName'                     => FoodsVariableCategory::NAME,
            'unitName'                                 => ServingUnit::NAME,
            Variable::FIELD_IS_PUBLIC                                   => true,
            'clientId'                                 => self::CLIENT_ID,
            Variable::FIELD_UPC_14                  => $upc,
            Variable::FIELD_BRAND_NAME           => $brand,
            Variable::FIELD_ADDITIONAL_META_DATA => $meta
        ];
        $foodVariable = QMCommonVariable::findOrCreateByName($variableName, [
            'variableCategoryName'                     => FoodsVariableCategory::NAME,
            'unitName'                                 => ServingUnit::NAME,
            Variable::FIELD_IS_PUBLIC                                   => true,
            'clientId'                                 => self::CLIENT_ID,
            Variable::FIELD_UPC_14                  => $upc,
            Variable::FIELD_BRAND_NAME           => $brand,
            Variable::FIELD_ADDITIONAL_META_DATA => $meta
        ]);
        $foodVariable->updateUpcIfNecessary($upc);
        $foodVariable->updateCommonAdditionalMetaDataIfNecessary($meta);
        if($parentVariableName){
            unset($params[Variable::FIELD_BRAND_NAME], $params[Variable::FIELD_UPC_14], $params[Variable::FIELD_ADDITIONAL_META_DATA]);
            $this->parentFood = $foodVariable->addParentCommonTag($parentVariableName, $params);
        }
        return $foodVariable;
    }
    /**
     * @param float $foodId
     * @param QMCommonVariable $foodVariable
     * @throws QMException
     */
    private function addNutrientTags(string $foodId, QMCommonVariable $foodVariable): void{
        $nutrients = $this->getNutrientDataForProduct($foodId);
        foreach($nutrients as $nutrientName => $nutrientData){ // Not sure what this weird 4:0    (g) stuff after 49 is
            $ingredientValue = $nutrientData['conversionFactor'];
            $ingredientUnitName = $nutrientData['unitAbbreviatedName'];
            if($nutrientName === $foodVariable->name){continue;}
            $foodVariable->addIngredientTag($nutrientName, $ingredientValue, NutrientsVariableCategory::NAME, $ingredientUnitName, self::CLIENT_ID);
            $ingredientsAfter = $foodVariable->getIngredientCommonTagVariables();
            if(!count($ingredientsAfter)){
                le("No ingredients after import!");
            }
        }
    }
    /**
     * @param string $ingredientsString
     * @param QMCommonVariable $foodVariable
     * @throws QMException
     */
    private function addIngredientTags(string $ingredientsString, QMCommonVariable $foodVariable): void{
        $ingredientsString = str_replace([
            "*",
            "), ",
            " (",
            ": ",
            "FOR COLOR, ",
            ". "
        ], [
            "",
            ", ",
            ", ",
            ", ",
            " ",
            ", "
        ], $ingredientsString);
        $names = explode(', ', $ingredientsString);
        foreach($names as $ingredientName){ // Not sure what this weird 4:0    (g) stuff after 49 is
            $ingredientName = trim($ingredientName);
            if(stripos($ingredientName, "OR LESS OF") !== false){continue;}
            $ingredientName = QMStr::removeIfLastCharacter('.', $ingredientName);
            if($ingredientName === $foodVariable->name){continue;}
            $foodVariable->addIngredientTag($ingredientName, 1, FoodsVariableCategory::NAME, ServingUnit::NAME, self::CLIENT_ID);
            $ingredientsAfter = $foodVariable->getIngredientCommonTagVariables();
            if(!count($ingredientsAfter)){
                $foodVariable->logError("No ingredients after import!");
            }
        }
    }
    /**
     * @param string $name
     * @return array
     */
    private function getFromJson(string $name): ?array {
        $jsonPath = ReferenceDataRepo::getAbsolutePath($name.'.json');
        $s = microtime(true);
        $fromJson = FileHelper::readJsonFile($jsonPath);  // Might be faster?
        $d = microtime(true) - $s;
        QMLog::infoWithoutContext("readJsonFile took ".$d." seconds");
        return $fromJson;
    }
    /**
     * @param string $name
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     * @throws NotGitRepositoryException
     */
    private function getFromSpreadsheet(string $name): array{
        $path = ReferenceDataRepo::getAbsolutePath(self::FILE_PATH.$name.'.csv');
        $foodData = QMSpreadsheet::getDataFromSpreadsheet($path);
        if(false && stripos(self::FILE_PATH, 'test') === false){
            $json = json_encode($foodData);
            FileHelper::writeJsonFile(ReferenceDataRepo::getAbsPath(), $json, $name);
        }
        return $foodData;
    }
}
