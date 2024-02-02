<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Import;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Slim\Model\SpreadsheetImports\SiderProduct;
use App\PhpUnitJobs\JobTestCase;
/** Class ImportVariablesSpreadsheetTest
 * @package App\PhpUnitJobs
 */
class VariablesImportJob extends JobTestCase {
    private $siderProducts;
    private $drugs;
    private $siderIndications;
    public function testImportVariablesSpreadsheet(){
        $this->saveDrugs();
    }
    /**
     * @return array
     */
    public function getSideEffectData(){
        $sideEffects = $this->getSpreadsheetData("medications/meddra/meddra_all_label_se.tsv");
        return $sideEffects;
    }
    /**
     * @return array
     */
    public function getLabelMappingData(){
        $labelMapping = $this->getTsvData("medications/meddra/label_mapping.tsv");
        return $labelMapping;
    }
    /**
     * @param string $path
     * @return array
     */
    public function getSpreadsheetData($path){
        /** Load $inputFileName to a PHPExcel Object  **/
        $objPHPExcel = IOFactory::load($this->getFullFilePath($path));
        QMLog::debug("Converting parsed $path spreadsheet to array...");
        $spreadsheetData = $objPHPExcel->getActiveSheet()->toArray();
        return $spreadsheetData;
    }
    /**
     * @param string $path
     * @return array
     */
    public function getTsvData($path){
        $excel = new SimpleExcel('TSV');
        $excel->parser->loadFile($this->getFullFilePath($path));
        $spreadsheetData = $excel->parser->getField();
        return $spreadsheetData;
    }
    /**
     * @param string $path
     * @return string
     */
    private function getFullFilePath($path){
        return FileHelper::absPath("data/quantimodo-reference-databases/$path");
    }
    /**
     * @return SiderProduct[]
     */
    private function setSiderProducts(){
        $rows = $this->getTsvData("medications/drugsatfda/Products.tsv");
        array_shift($rows);
        $this->siderProducts = [];
        foreach($rows as $row){
            $this->siderProducts[] = new SiderProduct($row);
        }
        return $this->siderProducts;
    }
    /**
     * @return SiderProduct[]
     */
    private function getSiderProducts(){
        return $this->siderProducts ?: $this->setSiderProducts();
    }
    /**
     * @return SiderProduct[]
     */
    private function setSiderIndications(){
        $rows = $this->getTsvData("medications/drugsatfda/Products.tsv");
        $rows = array_shift($rows);
        $this->siderIndications = [];
        foreach($rows as $row){
            $this->siderIndications[] = new SiderProduct($row);
        }
        return $this->siderIndications;
    }
    /**
     * @return SiderProduct[]
     */
    private function getSiderIndications(){
        return $this->siderIndications ?: $this->setSiderIndications();
    }
    /**
     * @return mixed
     */
    private function setDrugs(){
        foreach($this->getSiderProducts() as $siderProduct){
            if(!$this->getDrug($siderProduct->DrugName)){
                $this->drugs[$siderProduct->DrugName] = $siderProduct;
            }
            $this->getDrug($siderProduct->DrugName)->addSynonym($siderProduct->ActiveIngredient);
            $this->getDrug($siderProduct->DrugName)->addStrength($siderProduct->Strength);
            $this->getDrug($siderProduct->DrugName)->addForm($siderProduct->Form);
        }
        return $this->drugs;
    }
    /**
     * @return SiderProduct[]
     */
    private function getDrugs(){
        return $this->drugs ?: $this->setDrugs();
    }
    /**
     * @param $name
     * @return SiderProduct
     */
    private function getDrug($name){
        if(isset($this->drugs[$name])){
            return $this->drugs[$name];
        }
        return null;
    }
    private function saveDrugs(){
        foreach($this->getDrugs() as $siderProduct){
            $variable = $siderProduct->updateOrCreateCommonVariable();
        }
    }
}
