<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** Created by PhpStorm.
 * User: m
 * Date: 9/3/2017
 * Time: 8:08 AM
 */
namespace App\Variables;
use App\UI\ImageHelper;
use App\DataSources\BackEndImporters\SiderProduct;
class TreatmentVariableMetaData {
    public $images;
    public $applicationNumber;
    public $productNo;
    public $strengths = [];
    public $forms = [];
    public $sideEffects = [];
    public $indications = [];
    /**
     * TreatmentVariableMetaData constructor.
     * @param SiderProduct $siderProduct
     */
    public function __construct($siderProduct){
        $this->images = ImageHelper::getDrugImages($siderProduct->DrugName);
        $this->productNo = $siderProduct->ProductNo;
        $this->strengths = $siderProduct->strengths;
        $this->forms = $siderProduct->forms;
        $this->sideEffects = $siderProduct->sideEffects;
        $this->indications = $siderProduct->indications;
    }
}
