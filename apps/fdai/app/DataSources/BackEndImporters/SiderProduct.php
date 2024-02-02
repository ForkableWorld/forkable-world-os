<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\BackEndImporters;
use App\Models\Variable;
use App\Slim\Model\QMUnit;
use App\Types\QMStr;
use App\UI\ImageHelper;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariableCategory;
use App\Variables\TreatmentVariableMetaData;
class SiderProduct {
    public $Strength;
    public $ReferenceStandard;
    public $ActiveIngredient;
    public $DrugName;
    public $ReferenceDrug;
    public $ApplNo;
    public $Form;
    public $ProductNo;
    public $synonyms = [];
    public $strengths = [];
    public $dosages = [];
    public $forms = [];
    public $sideEffects = [];
    public $indications = [];
    /**
     * SiderProduct constructor.
     * @param array $row
     */
    public function __construct($row){
        $this->ApplNo = $row[0];
        $this->ProductNo = $row[1];
        $this->Form = $row[2];
        $this->Strength = $row[3];
        $this->ReferenceDrug = $row[4];
        $this->DrugName = $row[5];
        $this->ActiveIngredient = $row[6];
        $this->ReferenceStandard = $row[7];
    }
    /**
     * @return mixed
     */
    public function getDrugName(){
        return $this->DrugName;
    }
    /**
     * @param $form
     */
    public function addForm($form){
        $this->forms[] = $form;
        $this->forms = array_unique($this->forms);
    }
    /**
     * @param $strength
     */
    public function addStrength($strength){
        $this->strengths[] = $strength;
        $this->strengths = array_unique($this->strengths);
        $numbers = QMStr::extractNumericValuesFromString($strength);
        if(isset($numbers[0]) && (float)$numbers[0]){
            $this->dosages[] = (float)$numbers[0];
        }
        $this->dosages = array_unique($this->dosages);
    }
    /**
     * @return float
     */
    public function getMostCommonDosage(){
        if(isset($this->dosages[0])){
            return $this->dosages[0];
        }
        return null;
    }
    /**
     * @return float
     */
    public function getSecondMostCommonDosage(){
        if(isset($this->dosages[1])){
            return $this->dosages[1];
        }
        return null;
    }
    /**
     * @return float
     */
    public function getThirdMostCommonDosage(){
        if(isset($this->dosages[2])){
            return $this->dosages[2];
        }
        return null;
    }
    /**
     * @param $synonym
     */
    public function addSynonym($synonym){
        $this->synonyms[] = $synonym;
        $this->synonyms = array_unique($this->synonyms);
    }
    /**
     * @param $sideEffect
     */
    public function addSideEffect($sideEffect){
        $this->sideEffects[] = $sideEffect;
        $this->sideEffects = array_unique($this->sideEffects);
    }
    /**
     * @param $indication
     */
    public function addIndication($indication){
        $this->indications[] = $indication;
        $this->indications = array_unique($this->indications);
    }
    /**
     * @return array
     */
    private function getNewVariableParameters(){
        $newVariableData = [];
        $newVariableData[Variable::FIELD_CLIENT_ID] = "Sider2";
        $newVariableData[Variable::FIELD_IS_PUBLIC] = 1;
        $newVariableData[Variable::FIELD_ADDITIONAL_META_DATA] = json_encode(new TreatmentVariableMetaData($this));
        $imageUrl = ImageHelper::getDrugImageUrl($this->DrugName);
        if($imageUrl){
            $newVariableData[Variable::FIELD_IMAGE_URL] = $imageUrl;
        }
        $newVariableData[Variable::FIELD_SYNONYMS] = $this->synonyms;
        $unit = QMUnit::getUnitFromArrayOfStrings($this->strengths, TreatmentsVariableCategory::NAME, 'mg');
        $newVariableData[Variable::FIELD_DEFAULT_UNIT_ID] = $unit->id;
        $newVariableData[Variable::FIELD_VARIABLE_CATEGORY_ID] = QMVariableCategory::findByNameOrSynonym(TreatmentsVariableCategory::NAME)->id;
        return $newVariableData;
    }
    /**
     * @return QMCommonVariable
     */
    public function updateOrCreateCommonVariable(){
        $newVariableData = $this->getNewVariableParameters();
        return QMCommonVariable::updateOrCreate(ucwords(strtolower($this->DrugName)), $newVariableData);
    }
}
